<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;

class ControleRcController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Página principal do módulo
     */
    public function index()
    {
        // Verificar permissão
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'view')) {
            http_response_code(403);
            echo 'Acesso negado';
            exit;
        }

        try {
            // Buscar fornecedores para o dropdown
            $fornecedores = $this->getFornecedores();

            // Configurar view
            $title = 'Controle de RC - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/controle-rc/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            error_log('Erro no ControleRcController::index(): ' . $e->getMessage());
            http_response_code(500);
            echo 'Erro interno do servidor';
            exit;
        }
    }

    /**
     * Listar registros de RC
     */
    public function list()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'view')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $stmt = $this->db->query("
                SELECT 
                    rc.*,
                    u.name as usuario_nome,
                    f.nome as fornecedor_nome,
                    ua.name as alterado_por_nome,
                    (SELECT COUNT(*) FROM controle_rc_evidencias WHERE rc_id = rc.id) as total_evidencias
                FROM controle_rc rc
                LEFT JOIN users u ON rc.usuario_id = u.id
                LEFT JOIN users ua ON rc.status_alterado_por = ua.id
                LEFT JOIN fornecedores f ON rc.fornecedor_id = f.id
                ORDER BY rc.created_at DESC
            ");

            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $registros]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Criar novo registro
     */
    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            // Validações
            $required = ['data_abertura', 'origem', 'cliente_nome', 'categoria'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("Campo {$field} é obrigatório");
                }
            }

            // Gerar número do registro (auto-incremento + prefixo)
            $stmt = $this->db->query("SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM controle_rc");
            $nextId = $stmt->fetch(\PDO::FETCH_ASSOC)['next_id'];
            $numeroRegistro = 'RC-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Inserir registro principal com status
            $status = $_POST['status'] ?? 'Em analise';
            $stmt = $this->db->prepare("
                INSERT INTO controle_rc (
                    numero_registro, data_abertura, origem, cliente_nome, categoria,
                    detalhamento, qual_produto, numero_serie, fornecedor_id, testes_realizados, acoes_realizadas,
                    conclusao, status, usuario_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $numeroRegistro,
                $_POST['data_abertura'],
                $_POST['origem'],
                $_POST['cliente_nome'],
                $_POST['categoria'],
                $_POST['detalhamento'] ?? null,
                $_POST['qual_produto'] ?? null,
                $_POST['numero_serie'] ?? null,
                !empty($_POST['fornecedor_id']) ? $_POST['fornecedor_id'] : null,
                $_POST['testes_realizados'] ?? null,
                $_POST['acoes_realizadas'] ?? null,
                $_POST['conclusao'] ?? null,
                $status,
                $_SESSION['user_id']
            ]);

            $rcId = $this->db->lastInsertId();

            // Upload de evidências
            if (!empty($_FILES['evidencias']['name'][0])) {
                $this->uploadEvidencias($rcId, $_FILES['evidencias']);
            }

            // Enviar notificações para administradores
            $this->notificarAdministradoresNovoRC($numeroRegistro, $_POST, $_SESSION['user_name'] ?? 'Usuário');

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Registro criado com sucesso',
                'numero_registro' => $numeroRegistro,
                'id' => $rcId
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Atualizar registro
     */
    public function update()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID não informado');
            }

            $stmt = $this->db->prepare("
                UPDATE controle_rc SET
                    data_abertura = ?,
                    origem = ?,
                    cliente_nome = ?,
                    categoria = ?,
                    detalhamento = ?,
                    qual_produto = ?,
                    numero_serie = ?,
                    fornecedor_id = ?,
                    testes_realizados = ?,
                    acoes_realizadas = ?,
                    conclusao = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['data_abertura'],
                $_POST['origem'],
                $_POST['cliente_nome'],
                $_POST['categoria'],
                $_POST['detalhamento'] ?? null,
                $_POST['qual_produto'] ?? null,
                $_POST['numero_serie'] ?? null,
                !empty($_POST['fornecedor_id']) ? $_POST['fornecedor_id'] : null,
                $_POST['testes_realizados'] ?? null,
                $_POST['acoes_realizadas'] ?? null,
                $_POST['conclusao'] ?? null,
                $_POST['status'] ?? 'Em analise',
                $id
            ]);

            // Upload de novas evidências se houver
            if (!empty($_FILES['evidencias']['name'][0])) {
                $this->uploadEvidencias($id, $_FILES['evidencias']);
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Atualizar apenas o status do registro
     */
    public function updateStatus()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$id || !$status) {
                throw new \Exception('ID e Status são obrigatórios');
            }

            // Validar status
            $statusValidos = [
                'Em analise',
                'Aguardando ações do fornecedor',
                'Aguardando retorno do produto',
                'Finalizado',
                'Concluída'
            ];

            if (!in_array($status, $statusValidos)) {
                throw new \Exception('Status inválido');
            }

            $stmt = $this->db->prepare("
                UPDATE controle_rc SET
                    status = ?,
                    status_alterado_por = ?,
                    status_alterado_em = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([$status, $_SESSION['user_id'], $id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Excluir registro
     */
    public function delete()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'delete')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID não informado');
            }

            // Excluir evidências (CASCADE fará isso automaticamente se configurado)
            $stmt = $this->db->prepare("DELETE FROM controle_rc WHERE id = ?");
            $stmt->execute([$id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Registro excluído com sucesso']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Buscar detalhes de um registro
     */
    public function show($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'view')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    rc.*,
                    u.name as usuario_nome,
                    f.nome as fornecedor_nome
                FROM controle_rc rc
                LEFT JOIN users u ON rc.usuario_id = u.id
                LEFT JOIN fornecedores f ON rc.fornecedor_id = f.id
                WHERE rc.id = ?
            ");
            $stmt->execute([$id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$registro) {
                throw new \Exception('Registro não encontrado');
            }

            // Buscar evidências
            $stmt = $this->db->prepare("
                SELECT id, nome_arquivo, tipo_arquivo, tamanho 
                FROM controle_rc_evidencias 
                WHERE rc_id = ?
                ORDER BY created_at
            ");
            $stmt->execute([$id]);
            $evidencias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $registro['evidencias'] = $evidencias;

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $registro]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Imprimir relatório individual
     */
    public function print($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'view')) {
            http_response_code(403);
            echo 'Acesso negado';
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    rc.*,
                    u.name as usuario_nome,
                    f.nome as fornecedor_nome
                FROM controle_rc rc
                LEFT JOIN users u ON rc.usuario_id = u.id
                LEFT JOIN fornecedores f ON rc.fornecedor_id = f.id
                WHERE rc.id = ?
            ");
            $stmt->execute([$id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$registro) {
                echo 'Registro não encontrado';
                exit;
            }

            // Buscar evidências com arquivo_blob para exibir imagens
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, tipo_arquivo, arquivo_blob 
                FROM controle_rc_evidencias 
                WHERE rc_id = ?
                ORDER BY created_at
            ");
            $stmt->execute([$id]);
            $evidencias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $registro['evidencias'] = $evidencias;

            include __DIR__ . '/../../views/pages/controle-rc/print.php';
        } catch (\Exception $e) {
            error_log('Erro no ControleRcController::print(): ' . $e->getMessage());
            echo 'Erro ao gerar relatório: ' . $e->getMessage();
        }
    }

    /**
     * Exportar relatório múltiplo
     */
    public function exportReport()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'export')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $ids = $_POST['ids'] ?? [];
            
            if (empty($ids)) {
                throw new \Exception('Nenhum registro selecionado');
            }

            // Buscar registros
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $this->db->prepare("
                SELECT 
                    rc.*,
                    u.name as usuario_nome,
                    f.nome as fornecedor_nome
                FROM controle_rc rc
                LEFT JOIN users u ON rc.usuario_id = u.id
                LEFT JOIN fornecedores f ON rc.fornecedor_id = f.id
                WHERE rc.id IN ($placeholders)
                ORDER BY rc.created_at DESC
            ");
            $stmt->execute($ids);
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Exportar para Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="controle-rc-' . date('Y-m-d-His') . '.xls"');
            
            echo '<table border="1">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Número Registro</th>';
            echo '<th>Data Abertura</th>';
            echo '<th>Origem</th>';
            echo '<th>Cliente/Empresa</th>';
            echo '<th>Categoria</th>';
            echo '<th>Nº Série</th>';
            echo '<th>Fornecedor</th>';
            echo '<th>Testes Realizados</th>';
            echo '<th>Ações Realizadas</th>';
            echo '<th>Conclusão</th>';
            echo '<th>Usuário</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($registros as $reg) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($reg['numero_registro']) . '</td>';
                echo '<td>' . date('d/m/Y', strtotime($reg['data_abertura'])) . '</td>';
                echo '<td>' . htmlspecialchars($reg['origem']) . '</td>';
                echo '<td>' . htmlspecialchars($reg['cliente_nome']) . '</td>';
                echo '<td>' . htmlspecialchars($reg['categoria']) . '</td>';
                echo '<td>' . htmlspecialchars($reg['numero_serie'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($reg['fornecedor_nome'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($reg['testes_realizados'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($reg['acoes_realizadas'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($reg['conclusao'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($reg['usuario_nome']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Download de evidência
     */
    public function downloadEvidencia($id)
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo 'Não autenticado';
            exit;
        }

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_rc', 'view')) {
            http_response_code(403);
            echo 'Sem permissão';
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT arquivo_blob, nome_arquivo, tipo_arquivo 
                FROM controle_rc_evidencias 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $evidencia = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$evidencia) {
                http_response_code(404);
                echo 'Evidência não encontrada';
                exit;
            }

            header('Content-Type: ' . $evidencia['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="' . $evidencia['nome_arquivo'] . '"');
            echo $evidencia['arquivo_blob'];
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro: ' . $e->getMessage();
        }
    }

    /**
     * Upload de evidências (MEDIUMBLOB)
     */
    private function uploadEvidencias($rcId, $files)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileType = $files['type'][$i];
                $fileSize = $files['size'][$i];
                $fileName = $files['name'][$i];

                // Validações
                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Pular arquivo inválido
                }

                if ($fileSize > $maxSize) {
                    continue; // Pular arquivo muito grande
                }

                // Ler conteúdo do arquivo
                $fileContent = file_get_contents($files['tmp_name'][$i]);

                // Inserir no banco
                $stmt = $this->db->prepare("
                    INSERT INTO controle_rc_evidencias (
                        rc_id, arquivo_blob, nome_arquivo, tipo_arquivo, tamanho, created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $rcId,
                    $fileContent,
                    $fileName,
                    $fileType,
                    $fileSize
                ]);
            }
        }
    }

    /**
     * Notificar administradores sobre novo RC
     */
    private function notificarAdministradoresNovoRC($numeroRegistro, $rcData, $usuarioNome)
    {
        try {
            error_log("======================================");
            error_log("🔔 NOTIFICAÇÃO NOVO RC: {$numeroRegistro}");
            error_log("======================================");
            
            // Buscar administradores e super administradores ativos
            $stmt = $this->db->prepare("
                SELECT id, name, email, role 
                FROM users 
                WHERE role IN ('admin', 'super_admin') 
                AND status = 'active' 
                AND email IS NOT NULL 
                AND email != ''
                ORDER BY name
            ");
            $stmt->execute();
            $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("👥 Administradores encontrados: " . count($admins));
            
            if (empty($admins)) {
                error_log("⚠️ Nenhum administrador encontrado para notificação");
                return false;
            }
            
            // Buscar nome do fornecedor se houver
            $fornecedorNome = null;
            if (!empty($rcData['fornecedor_id'])) {
                $stmtFornecedor = $this->db->prepare("SELECT nome FROM fornecedores WHERE id = ?");
                $stmtFornecedor->execute([$rcData['fornecedor_id']]);
                $fornecedor = $stmtFornecedor->fetch(\PDO::FETCH_ASSOC);
                $fornecedorNome = $fornecedor['nome'] ?? null;
            }
            
            // Coletar emails
            $emails = [];
            foreach ($admins as $admin) {
                if (!empty($admin['email'])) {
                    $emails[] = $admin['email'];
                    error_log("   📧 {$admin['name']} ({$admin['email']}) - {$admin['role']}");
                }
            }
            
            if (empty($emails)) {
                error_log("⚠️ Nenhum email válido encontrado");
                return false;
            }
            
            // Preparar dados do RC para o email
            $rcDataForEmail = [
                'data_abertura' => $rcData['data_abertura'],
                'origem' => $rcData['origem'],
                'cliente_nome' => $rcData['cliente_nome'],
                'categoria' => $rcData['categoria'],
                'detalhamento' => $rcData['detalhamento'] ?? null,
                'qual_produto' => $rcData['qual_produto'] ?? null,
                'numero_serie' => $rcData['numero_serie'] ?? null,
                'fornecedor_nome' => $fornecedorNome,
                'usuario_nome' => $usuarioNome
            ];
            
            // Enviar email
            error_log("📧 Enviando email para " . count($emails) . " administrador(es)...");
            
            $emailService = new \App\Services\EmailService();
            $resultado = $emailService->sendRcNovoNotification($emails, $numeroRegistro, $rcDataForEmail);
            
            if ($resultado) {
                error_log("✅ Emails enviados com sucesso!");
            } else {
                error_log("❌ Falha ao enviar emails: " . $emailService->getLastError());
            }
            
            error_log("======================================");
            
            return $resultado;
            
        } catch (\Exception $e) {
            error_log("❌ ERRO ao notificar administradores: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Alterar status do RC (apenas admin ou qualidade)
     */
    public function alterarStatus()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $rc_id = $_POST['id'] ?? 0;
            $novo_status = $_POST['status'] ?? '';
            $justificativa = trim($_POST['justificativa'] ?? '');
            
            if (!$rc_id) {
                echo json_encode(['success' => false, 'message' => 'ID do RC é obrigatório']);
                return;
            }
            
            // Validar status
            $status_validos = [
                'Em analise',
                'Aguardando ações do fornecedor', 
                'Aguardando retorno do produto', 
                'Finalizado', 
                'Concluída'
            ];
            if (!in_array($novo_status, $status_validos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }
            
            // Verificar se usuário tem permissão (admin ou super_admin)
            $user_role = $_SESSION['user_role'] ?? '';
            $user_id = $_SESSION['user_id'] ?? 0;
            
            $tem_permissao = ($user_role === 'admin' || $user_role === 'super_admin');
            
            if (!$tem_permissao) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão. Apenas Admin ou Qualidade podem alterar status.']);
                return;
            }
            
            // Verificar se RC existe
            $stmt = $this->db->prepare("SELECT * FROM controle_rc WHERE id = ?");
            $stmt->execute([$rc_id]);
            $rc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rc) {
                echo json_encode(['success' => false, 'message' => 'RC não encontrado']);
                return;
            }
            
            // Atualizar status
            $stmt = $this->db->prepare("
                UPDATE controle_rc 
                SET status = ?,
                    status_alterado_por = ?,
                    status_alterado_em = NOW(),
                    justificativa_status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $novo_status,
                $user_id,
                $justificativa,
                $rc_id
            ]);
            
            // Enviar notificações sobre mudança de status (não crítico)
            try {
                $this->notificarMudancaStatus($rc_id, $novo_status);
            } catch (\Exception $e) {
                error_log("Erro ao enviar notificações de mudança de status (não crítico): " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true, 
                'message' => "Status alterado para '{$novo_status}' com sucesso!"
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao alterar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Notificar sobre mudança de status
     */
    private function notificarMudancaStatus($rc_id, $novo_status)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM controle_rc WHERE id = ?");
            $stmt->execute([$rc_id]);
            $rc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rc) {
                return;
            }
            
            $adminNome = $_SESSION['user_name'] ?? 'Administrador';
            $criadorId = $rc['usuario_id'];
            
            // Mapear ícones por status
            $statusIcons = [
                'Em analise' => '🔍',
                'Aguardando ações do fornecedor' => '⏳',
                'Aguardando retorno do produto' => '📦',
                'Finalizado' => '✅',
                'Concluída' => '🎯'
            ];
            $icon = $statusIcons[$novo_status] ?? '📊';
            
            // Mapear tipo de notificação por status
            $notifType = match($novo_status) {
                'Finalizado' => 'success',
                'Concluída' => 'success',
                'Em analise' => 'info',
                default => 'warning'
            };
            
            // Notificar o CRIADOR
            $stmt = $this->db->prepare('
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            
            $stmt->execute([
                $criadorId,
                "$icon Status atualizado",
                "$adminNome alterou o status do RC {$rc['numero_registro']} para: $novo_status",
                $notifType,
                'controle_rc',
                $rc_id
            ]);
            
            error_log("Notificação de mudança de status enviada - RC ID: $rc_id - Status: $novo_status");
            
        } catch (\Exception $e) {
            error_log("Erro ao notificar mudança de status: " . $e->getMessage());
        }
    }

    /**
     * Buscar fornecedores
     */
    private function getFornecedores()
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM fornecedores ORDER BY nome");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
