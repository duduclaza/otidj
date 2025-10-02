<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class MelhoriaContinua2Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        try {
            error_log("=== MELHORIA CONTINUA 2.0 INDEX ===");
            error_log("User ID: " . ($_SESSION['user_id'] ?? 'NULL'));
            error_log("User Role: " . ($_SESSION['user_role'] ?? 'NULL'));
            
            // Verificar permissão (admin sempre tem acesso)
            if ($_SESSION['user_role'] !== 'admin' && !PermissionService::hasPermission($_SESSION['user_id'], 'melhoria_continua_2', 'view')) {
                error_log("ACESSO NEGADO - Sem permissão");
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            error_log("Permissão OK - Carregando dados");
            $userId = $_SESSION['user_id'];
            $isAdmin = $_SESSION['user_role'] === 'admin';

        // Buscar melhorias baseado nas regras de visibilidade
        if ($isAdmin) {
            // Admin vê todas as melhorias
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ');
            $stmt->execute();
        } else {
            // Usuário comum vê apenas suas melhorias e aquelas onde é responsável
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                WHERE m.criado_por = :user_id OR FIND_IN_SET(:user_id2, m.responsaveis)
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId, ':user_id2' => $userId]);
        }

        $melhorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar usuários para dropdown de responsáveis
        $stmt = $this->db->prepare('SELECT id, name FROM users WHERE status = "active" ORDER BY name');
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar departamentos
        $stmt = $this->db->prepare('SELECT id, nome FROM departamentos ORDER BY nome');
        $stmt->execute();
        $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $title = 'Melhoria Contínua 2.0 - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/melhoria-continua-2/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
            
        } catch (\Exception $e) {
            error_log("=== ERRO MELHORIA CONTINUA 2.0 ===");
            error_log("Mensagem: " . $e->getMessage());
            error_log("Arquivo: " . $e->getFile());
            error_log("Linha: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }

    public function store(): void
    {
        // Verificar permissão de view (usuário pode criar se tem acesso ao módulo)
        if ($_SESSION['user_role'] !== 'admin' && !PermissionService::hasPermission($_SESSION['user_id'], 'melhoria_continua_2', 'view')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = (int)($_POST['departamento_id'] ?? 0);
            $descricao = trim($_POST['descricao'] ?? '');
            $o_que = trim($_POST['o_que'] ?? '');
            $como = trim($_POST['como'] ?? '');
            $onde = trim($_POST['onde'] ?? '');
            $porque = trim($_POST['porque'] ?? '');
            $quando = $_POST['quando'] ?? null;
            $quanto_custa = !empty($_POST['quanto_custa']) ? (float)$_POST['quanto_custa'] : null;
            $responsaveis = $_POST['responsaveis'] ?? [];
            $resultado_esperado = trim($_POST['resultado_esperado'] ?? '');
            $idealizador = trim($_POST['idealizador'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');

            // Validações
            if (empty($titulo) || empty($o_que) || empty($como) || empty($onde) || empty($porque) || empty($resultado_esperado) || empty($idealizador)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            // Converter array de responsáveis para string
            $responsaveis_str = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            // Processar anexos
            $anexos = [];
            if (!empty($_FILES['anexos']['name'][0])) {
                error_log("Processando anexos: " . print_r($_FILES['anexos'], true));
                $anexos = $this->processarAnexos($_FILES['anexos']);
                error_log("Anexos processados: " . print_r($anexos, true));
            } else {
                error_log("Nenhum anexo enviado");
            }

            $stmt = $this->db->prepare('
                INSERT INTO melhoria_continua_2 (
                    titulo, departamento_id, descricao, o_que, como, onde, porque, quando, 
                    quanto_custa, responsaveis, resultado_esperado, idealizador, 
                    status, observacao, anexos, criado_por, created_at
                ) VALUES (
                    :titulo, :departamento_id, :descricao, :o_que, :como, :onde, :porque, :quando,
                    :quanto_custa, :responsaveis, :resultado_esperado, :idealizador,
                    "Pendente análise", :observacao, :anexos, :criado_por, NOW()
                )
            ');

            $stmt->execute([
                ':titulo' => $titulo,
                ':departamento_id' => $departamento_id,
                ':descricao' => $descricao,
                ':o_que' => $o_que,
                ':como' => $como,
                ':onde' => $onde,
                ':porque' => $porque,
                ':quando' => $quando,
                ':quanto_custa' => $quanto_custa,
                ':responsaveis' => $responsaveis_str,
                ':resultado_esperado' => $resultado_esperado,
                ':idealizador' => $idealizador,
                ':observacao' => $observacao,
                ':anexos' => json_encode($anexos),
                ':criado_por' => $_SESSION['user_id']
            ]);

            $melhoriaId = $this->db->lastInsertId();

            // Enviar notificações
            $this->enviarNotificacoes($melhoriaId, $titulo, $responsaveis);

            echo json_encode([
                'success' => true, 
                'message' => 'Melhoria registrada com sucesso!',
                'redirect' => '/melhoria-continua-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao salvar melhoria: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function update(): void
    {
        // Admin ou usuário com permissão de view pode editar suas próprias melhorias
        $isAdmin = $_SESSION['user_role'] === 'admin';
        if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'melhoria_continua_2', 'view')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $userId = $_SESSION['user_id'];

            // Verificar se o usuário pode editar esta melhoria
            $stmt = $this->db->prepare('SELECT criado_por, status FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo json_encode(['success' => false, 'message' => 'Melhoria não encontrada']);
                return;
            }

            // Admin pode editar qualquer melhoria, usuário comum só se for criador e status "Pendente Adaptação"
            $isAdmin = $_SESSION['user_role'] === 'admin';
            if (!$isAdmin && ($melhoria['criado_por'] != $userId || $melhoria['status'] !== 'Pendente Adaptação')) {
                echo json_encode(['success' => false, 'message' => 'Você não pode editar esta melhoria']);
                return;
            }

            // Atualizar dados
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = (int)($_POST['departamento_id'] ?? 0);
            $descricao = trim($_POST['descricao'] ?? '');
            $o_que = trim($_POST['o_que'] ?? '');
            $como = trim($_POST['como'] ?? '');
            $onde = trim($_POST['onde'] ?? '');
            $porque = trim($_POST['porque'] ?? '');
            $quando = $_POST['quando'] ?? null;
            $quanto_custa = !empty($_POST['quanto_custa']) ? (float)$_POST['quanto_custa'] : null;
            $responsaveis = $_POST['responsaveis'] ?? [];
            $resultado_esperado = trim($_POST['resultado_esperado'] ?? '');
            $idealizador = trim($_POST['idealizador'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');

            $responsaveis_str = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            // Processar anexos
            // 1. Buscar anexos antigos do banco
            $stmt = $this->db->prepare('SELECT anexos FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $melhoriaAnexos = $stmt->fetch(PDO::FETCH_ASSOC);
            $anexos_antigos = !empty($melhoriaAnexos['anexos']) ? json_decode($melhoriaAnexos['anexos'], true) : [];
            
            // 2. Pegar anexos atuais (já filtrados pelo frontend - removidos os deletados)
            $anexos_atuais = [];
            if (!empty($_POST['anexos_atuais'])) {
                $anexos_atuais = json_decode($_POST['anexos_atuais'], true) ?? [];
            }
            
            // 3. Identificar anexos removidos e deletar arquivos físicos
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/melhorias/';
            foreach ($anexos_antigos as $anexo_antigo) {
                $foi_removido = true;
                foreach ($anexos_atuais as $anexo_atual) {
                    if (isset($anexo_antigo['arquivo']) && isset($anexo_atual['arquivo']) && 
                        $anexo_antigo['arquivo'] === $anexo_atual['arquivo']) {
                        $foi_removido = false;
                        break;
                    }
                }
                
                // Se foi removido, deletar arquivo físico
                if ($foi_removido && isset($anexo_antigo['arquivo'])) {
                    $filePath = $uploadDir . $anexo_antigo['arquivo'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                        error_log("Arquivo removido na edição: $filePath");
                    }
                }
            }
            
            // 4. Processar novos anexos se houver
            if (!empty($_FILES['anexos']['name'][0])) {
                $novos_anexos = $this->processarAnexos($_FILES['anexos']);
                $anexos_atuais = array_merge($anexos_atuais, $novos_anexos);
                error_log("Novos anexos adicionados: " . count($novos_anexos));
            }
            
            error_log("Anexos finais para update: " . print_r($anexos_atuais, true));

            $stmt = $this->db->prepare('
                UPDATE melhoria_continua_2 SET
                    titulo = :titulo, departamento_id = :departamento_id, descricao = :descricao,
                    o_que = :o_que, como = :como, onde = :onde, porque = :porque, quando = :quando,
                    quanto_custa = :quanto_custa, responsaveis = :responsaveis,
                    resultado_esperado = :resultado_esperado, idealizador = :idealizador,
                    observacao = :observacao, anexos = :anexos, status = "Pendente análise",
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':titulo' => $titulo,
                ':departamento_id' => $departamento_id,
                ':descricao' => $descricao,
                ':o_que' => $o_que,
                ':como' => $como,
                ':onde' => $onde,
                ':porque' => $porque,
                ':quando' => $quando,
                ':quanto_custa' => $quanto_custa,
                ':responsaveis' => $responsaveis_str,
                ':resultado_esperado' => $resultado_esperado,
                ':idealizador' => $idealizador,
                ':observacao' => $observacao,
                ':anexos' => json_encode($anexos_atuais)
            ]);

            // Enviar notificações
            $this->enviarNotificacoes($id, $titulo, $responsaveis);

            echo json_encode([
                'success' => true, 
                'message' => 'Melhoria atualizada e reenviada para análise!',
                'redirect' => '/melhoria-continua-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar melhoria: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function updateStatus($id = null): void
    {
        // Desabilitar exibição de erros para não quebrar o JSON
        ini_set('display_errors', 0);
        error_reporting(0);
        
        // Limpar TODO o buffer de saída
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Iniciar novo buffer limpo
        ob_start();
        
        header('Content-Type: application/json');
        
        // Apenas admins podem alterar status
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        try {
            // Suportar tanto POST quanto JSON
            if ($id) {
                $data = json_decode(file_get_contents('php://input'), true);
                $status = $data['status'] ?? '';
                $pontuacao = null;
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $status = $_POST['status'] ?? '';
                $pontuacao = !empty($_POST['pontuacao']) ? (int)$_POST['pontuacao'] : null;
            }

            $statusValidos = ['Pendente análise', 'Em andamento', 'Concluída', 'Recusada', 'Pendente Adaptação'];
            if (!in_array($status, $statusValidos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE melhoria_continua_2 SET 
                    status = :status' . ($pontuacao !== null ? ', pontuacao = :pontuacao' : '') . ',
                    updated_at = NOW()
                WHERE id = :id
            ');

            $params = [
                ':id' => $id,
                ':status' => $status
            ];
            
            if ($pontuacao !== null) {
                $params[':pontuacao'] = $pontuacao;
            }
            
            $stmt->execute($params);

            // Enviar email automático para responsáveis sempre que o status mudar
            try {
                $emailEnviado = $this->enviarEmailMudancaStatus($id, $status);
                if ($emailEnviado) {
                    error_log("✅ Email de mudança de status enviado automaticamente para melhoria #{$id} - Status: {$status}");
                } else {
                    error_log("⚠️ Falha ao enviar email automático para melhoria #{$id} (não crítico)");
                }
            } catch (\Exception $e) {
                // Log do erro mas não falha a operação
                error_log("⚠️ Erro ao enviar email automático (não crítico): " . $e->getMessage());
            }

            // Buscar dados da melhoria para notificações
            $stmt = $this->db->prepare('
                SELECT titulo, criado_por, responsaveis 
                FROM melhoria_continua_2 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($melhoria) {
                // Enviar notificações sobre mudança de status
                $this->notificarMudancaStatus($id, $melhoria['titulo'], $status, $melhoria['criado_por'], $melhoria['responsaveis']);
            }

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
            exit;

        } catch (\PDOException $e) {
            error_log('Erro PDO ao atualizar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao acessar banco de dados']);
            exit;
        } catch (\Exception $e) {
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
            exit;
        }
    }

    public function delete(): void
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $userId = $_SESSION['user_id'];

            // Verificar se pode excluir
            $stmt = $this->db->prepare('SELECT criado_por, status, anexos FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo json_encode(['success' => false, 'message' => 'Melhoria não encontrada']);
                return;
            }

            // Só pode excluir se for o criador e status for "Recusada"
            if ($melhoria['criado_por'] != $userId || $melhoria['status'] !== 'Recusada') {
                echo json_encode(['success' => false, 'message' => 'Você não pode excluir esta melhoria']);
                return;
            }

            // Excluir arquivos físicos dos anexos
            if (!empty($melhoria['anexos'])) {
                $anexos = json_decode($melhoria['anexos'], true);
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/melhorias/';
                
                foreach ($anexos as $anexo) {
                    if (isset($anexo['arquivo'])) {
                        $filePath = $uploadDir . $anexo['arquivo'];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                            error_log("Arquivo excluído: $filePath");
                        }
                    }
                }
            }

            // Excluir registro do banco
            $stmt = $this->db->prepare('DELETE FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Melhoria e anexos excluídos com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir melhoria: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    private function processarAnexos($files): array
    {
        $anexos = [];
        // Caminho absoluto do servidor
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/melhorias/';
        $webPath = '/storage/uploads/melhorias/';
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            error_log("Diretório criado: $uploadDir");
        }
        
        error_log("Upload dir: $uploadDir");
        error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);

        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];

        for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileSize = $files['size'][$i];
                $fileType = $files['type'][$i];
                $fileName = $files['name'][$i];

                if ($fileSize > $maxSize) {
                    error_log("Arquivo muito grande: $fileName ($fileSize bytes)");
                    continue; // Pular arquivo muito grande
                }

                if (!in_array($fileType, $allowedTypes)) {
                    error_log("Tipo não permitido: $fileName ($fileType)");
                    continue; // Pular tipo não permitido
                }

                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = 'melhoria_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                    $anexos[] = [
                        'nome' => $fileName,
                        'arquivo' => $newFileName,
                        'url' => $webPath . $newFileName,
                        'tamanho' => $fileSize,
                        'tipo' => $fileType
                    ];
                    error_log("Arquivo salvo com sucesso: $filePath");
                } else {
                    error_log("Erro ao mover arquivo: $fileName para $filePath");
                }
            } else {
                error_log("Erro no upload do arquivo: " . $files['error'][$i]);
            }
        }

        return $anexos;
    }

    private function notificarMudancaStatus($melhoriaId, $titulo, $novoStatus, $criadorId, $responsaveisStr): void
    {
        try {
            $adminNome = $_SESSION['user_name'] ?? 'Administrador';
            
            // Mapear ícones por status
            $statusIcons = [
                'Pendente análise' => '⏳',
                'Em andamento' => '🔄',
                'Concluída' => '✅',
                'Recusada' => '❌',
                'Pendente Adaptação' => '📝'
            ];
            $icon = $statusIcons[$novoStatus] ?? '📊';
            
            // Mapear tipos de notificação por status
            $notifType = match($novoStatus) {
                'Concluída' => 'success',
                'Recusada' => 'error',
                'Em andamento' => 'info',
                default => 'warning'
            };
            
            // 1. Notificar o CRIADOR
            $stmt = $this->db->prepare('
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $criadorId,
                "$icon Status atualizado",
                "$adminNome alterou o status da sua melhoria \"$titulo\" para: $novoStatus",
                $notifType,
                'melhoria_continua_2',
                $melhoriaId
            ]);
            
            // 2. Notificar os RESPONSÁVEIS
            if (!empty($responsaveisStr)) {
                $responsaveisIds = explode(',', $responsaveisStr);
                foreach ($responsaveisIds as $responsavelId) {
                    // Não notificar o criador duas vezes
                    if ($responsavelId == $criadorId) continue;
                    
                    $stmt->execute([
                        $responsavelId,
                        "$icon Status atualizado",
                        "$adminNome alterou o status da melhoria \"$titulo\" para: $novoStatus",
                        $notifType,
                        'melhoria_continua_2',
                        $melhoriaId
                    ]);
                }
            }
            
            error_log("Notificações de mudança de status enviadas - Melhoria ID: $melhoriaId - Status: $novoStatus");
            
        } catch (\Exception $e) {
            error_log("Erro ao notificar mudança de status: " . $e->getMessage());
        }
    }

    private function enviarNotificacoes($melhoriaId, $titulo, $responsaveis): void
    {
        try {
            $criadorId = $_SESSION['user_id'];
            $criadorNome = $_SESSION['user_name'] ?? 'Usuário';
            
            // 1. Notificar ADMINS sobre nova melhoria
            $stmt = $this->db->prepare('SELECT id FROM users WHERE role = "admin" AND status = "active" AND id != ?');
            $stmt->execute([$criadorId]);
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($admins as $admin) {
                $stmt = $this->db->prepare('
                    INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ');
                $stmt->execute([
                    $admin['id'],
                    '🚀 Nova Melhoria Contínua',
                    "$criadorNome criou uma nova melhoria: \"$titulo\"",
                    'info',
                    'melhoria_continua_2',
                    $melhoriaId
                ]);
            }
            
            // 2. Notificar RESPONSÁVEIS selecionados
            if (!empty($responsaveis)) {
                foreach ($responsaveis as $responsavelId) {
                    // Verificar se o responsável já não foi notificado como admin
                    $jaNotificado = false;
                    foreach ($admins as $admin) {
                        if ($admin['id'] == $responsavelId) {
                            $jaNotificado = true;
                            break;
                        }
                    }
                    
                    // Se já foi notificado como admin, pular
                    if ($jaNotificado) continue;
                    
                    // Notificar responsável (mesmo que seja o criador)
                    $stmt = $this->db->prepare('
                        INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ');
                    
                    // Se for o próprio criador, mensagem diferente
                    if ($responsavelId == $criadorId) {
                        $stmt->execute([
                            $responsavelId,
                            '✅ Melhoria criada com sucesso',
                            "Você criou a melhoria: \"$titulo\" e foi designado como responsável",
                            'success',
                            'melhoria_continua_2',
                            $melhoriaId
                        ]);
                    } else {
                        $stmt->execute([
                            $responsavelId,
                            '👤 Você foi designado como responsável',
                            "$criadorNome designou você como responsável pela melhoria: \"$titulo\"",
                            'warning',
                            'melhoria_continua_2',
                            $melhoriaId
                        ]);
                    }
                }
            }
            
            error_log("Notificações criadas para melhoria ID: $melhoriaId - Admins: " . count($admins) . " - Responsáveis: " . count($responsaveis));
            
        } catch (\Exception $e) {
            error_log("Erro ao enviar notificações: " . $e->getMessage());
        }
    }


    // Atualizar Pontuação Inline (Admin)
    public function updatePontuacao($id): void
    {
        header('Content-Type: application/json');
        
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $pontuacao = (int)($data['pontuacao'] ?? 0);

            $stmt = $this->db->prepare('UPDATE melhoria_continua_2 SET pontuacao = :pontuacao WHERE id = :id');
            $stmt->execute([':pontuacao' => $pontuacao, ':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Pontuação atualizada com sucesso']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar pontuação']);
        }
    }

    // Ver Detalhes da Melhoria (JSON para AJAX)
    public function details($id): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                WHERE m.id = :id
                GROUP BY m.id
            ');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo json_encode(['success' => false, 'message' => 'Melhoria não encontrada']);
                return;
            }

            // Buscar anexos
            $anexos = [];
            if (!empty($melhoria['anexos'])) {
                $anexos = json_decode($melhoria['anexos'], true) ?? [];
            }
            $melhoria['anexos'] = $anexos;

            echo json_encode(['success' => true, 'melhoria' => $melhoria]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar detalhes']);
        }
    }

    // Ver Detalhes da Melhoria (Página HTML)
    public function view($id): void
    {
        try {
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                WHERE m.id = :id
                GROUP BY m.id
            ');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo "Melhoria não encontrada";
                return;
            }

            // Incluir a view
            include __DIR__ . '/../../views/pages/melhoria-continua-2/view.php';
        } catch (\Exception $e) {
            echo "Erro ao carregar detalhes: " . $e->getMessage();
        }
    }

    public function exportExcel(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $isAdmin = $_SESSION['user_role'] === 'admin';

            // Buscar filtros
            $filters = [];
            $params = [];
            
            if (!empty($_GET['status'])) {
                $filters[] = "m.status = :status";
                $params[':status'] = $_GET['status'];
            }
            
            if (!empty($_GET['prioridade'])) {
                $filters[] = "m.prioridade = :prioridade";
                $params[':prioridade'] = $_GET['prioridade'];
            }
            
            if (!empty($_GET['departamento_id'])) {
                $filters[] = "m.departamento_id = :departamento_id";
                $params[':departamento_id'] = $_GET['departamento_id'];
            }
            
            if (!empty($_GET['data_inicio'])) {
                $filters[] = "DATE(m.created_at) >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'];
            }
            
            if (!empty($_GET['data_fim'])) {
                $filters[] = "DATE(m.created_at) <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'];
            }

            // Regras de visibilidade
            if (!$isAdmin) {
                $filters[] = "(m.criado_por = :user_id OR FIND_IN_SET(:user_id, m.responsaveis))";
                $params[':user_id'] = $userId;
            }
            
            $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
            
            // Buscar dados
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    u.name as criador_nome,
                    d.nome as departamento_nome,
                    GROUP_CONCAT(ur.name SEPARATOR ', ') as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                $whereClause
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ");
            $stmt->execute($params);
            $melhorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($melhorias)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado']);
                return;
            }
            
            // Gerar arquivo Excel (CSV)
            $filename = 'melhoria_continua_2_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            fputcsv($output, [
                'Data',
                'Título',
                'Descrição',
                'Departamento',
                'Status',
                'Idealizador',
                'Criado Por',
                'Responsáveis',
                'O Que',
                'Como',
                'Onde',
                'Por Que',
                'Quando',
                'Quanto Custa',
                'Observações',
                'Pontuação'
            ], ';');
            
            // Dados
            foreach ($melhorias as $melhoria) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($melhoria['created_at'])),
                    $melhoria['titulo'] ?? '',
                    $melhoria['resultado_esperado'] ?? '',
                    $melhoria['departamento_nome'] ?? '',
                    $melhoria['status'] ?? '',
                    $melhoria['idealizador'] ?? '',
                    $melhoria['criador_nome'] ?? '',
                    $melhoria['responsaveis_nomes'] ?? '',
                    $melhoria['o_que'] ?? '',
                    $melhoria['como'] ?? '',
                    $melhoria['onde'] ?? '',
                    $melhoria['porque'] ?? '',
                    $melhoria['quando'] ? date('d/m/Y', strtotime($melhoria['quando'])) : '',
                    $melhoria['quanto_custa'] ? 'R$ ' . number_format($melhoria['quanto_custa'], 2, ',', '.') : '',
                    $melhoria['observacao'] ?? '',
                    $melhoria['pontuacao'] ?? ''
                ], ';');
            }
            
            fclose($output);
            
        } catch (\Exception $e) {
            error_log('Erro ao exportar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function enviarEmailDetalhes(): void
    {
        // Assegurar que a resposta seja JSON puro
        while (ob_get_level()) { ob_end_clean(); }
        ini_set('display_errors', '0');
        error_reporting(0);
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            $ok = $this->enviarEmailConclusao($id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => '📧 Email enviado com sucesso aos responsáveis!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar email']);
            }
            exit;
            
        } catch (\Throwable $e) {
            error_log('Erro ao enviar email: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar email']);
            exit;
        }
    }

    private function enviarEmailMudancaStatus(int $melhoriaId, string $novoStatus): bool
    {
        try {
            error_log("=== ENVIANDO EMAIL DE MUDANÇA DE STATUS ===");
            error_log("Melhoria ID: {$melhoriaId}, Novo Status: {$novoStatus}");
            
            // Buscar dados completos da melhoria
            $stmt = $this->db->prepare('
                SELECT 
                    m.*,
                    d.nome as departamento_nome
                FROM melhoria_continua_2 m
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                WHERE m.id = :id
            ');
            $stmt->execute([':id' => $melhoriaId]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                error_log("❌ Melhoria #{$melhoriaId}: Não encontrada");
                return false;
            }

            error_log("✅ Melhoria encontrada: " . $melhoria['titulo']);
            error_log("Responsáveis (IDs): " . ($melhoria['responsaveis'] ?? 'VAZIO'));

            // Buscar emails dos responsáveis se houver
            if (!empty($melhoria['responsaveis'])) {
                $responsaveisIds = array_map('trim', explode(',', $melhoria['responsaveis']));
                error_log("IDs dos responsáveis: " . implode(', ', $responsaveisIds));
                
                $placeholders = str_repeat('?,', count($responsaveisIds) - 1) . '?';
                $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($placeholders) AND email IS NOT NULL AND email != ''");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Responsáveis encontrados: " . count($responsaveis));
                foreach ($responsaveis as $resp) {
                    error_log("  - {$resp['name']} ({$resp['email']})");
                }
                
                $emails = array_column($responsaveis, 'email');

                if (empty($emails)) {
                    error_log("❌ Melhoria #{$melhoriaId}: Nenhum email válido encontrado para os responsáveis");
                    return false;
                }

                error_log("📧 Tentando enviar email para: " . implode(', ', $emails));

                // Enviar email com template específico do status
                $emailService = new \App\Services\EmailService();
                error_log("EmailService criado");
                
                $enviado = $emailService->sendMelhoriaStatusNotification($melhoria, $emails, $novoStatus);

                if ($enviado) {
                    error_log("✅ Email de mudança de status enviado para melhoria #{$melhoriaId} para: " . implode(', ', $emails));
                    return true;
                } else {
                    error_log("❌ Falha ao enviar email de mudança de status para melhoria #{$melhoriaId}");
                    return false;
                }
            } else {
                error_log("⚠️ Melhoria #{$melhoriaId}: Sem responsáveis cadastrados");
                return false;
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar email de mudança de status: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function enviarEmailConclusao(int $melhoriaId): bool
    {
        try {
            error_log("=== INICIANDO ENVIO DE EMAIL DE CONCLUSÃO ===");
            error_log("Melhoria ID: " . $melhoriaId);
            
            // Buscar dados completos da melhoria
            $stmt = $this->db->prepare('
                SELECT 
                    m.*,
                    d.nome as departamento_nome
                FROM melhoria_continua_2 m
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                WHERE m.id = :id
            ');
            $stmt->execute([':id' => $melhoriaId]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                error_log("❌ Melhoria #{$melhoriaId}: Não encontrada");
                return false;
            }

            error_log("✅ Melhoria encontrada: " . $melhoria['titulo']);
            error_log("Responsáveis (IDs): " . ($melhoria['responsaveis'] ?? 'VAZIO'));

            // Buscar emails dos responsáveis se houver
            if (!empty($melhoria['responsaveis'])) {
                $responsaveisIds = array_map('trim', explode(',', $melhoria['responsaveis']));
                error_log("IDs dos responsáveis: " . implode(', ', $responsaveisIds));
                
                $placeholders = implode(',', array_fill(0, count($responsaveisIds), '?'));
                
                $stmt = $this->db->prepare("
                    SELECT id, name, email 
                    FROM users 
                    WHERE id IN ($placeholders) AND email IS NOT NULL AND email != ''
                ");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Responsáveis encontrados: " . count($responsaveis));
                foreach ($responsaveis as $resp) {
                    error_log("  - {$resp['name']} ({$resp['email']})");
                }
                
                $emails = array_column($responsaveis, 'email');

                if (empty($emails)) {
                    error_log("❌ Melhoria #{$melhoriaId}: Nenhum email válido encontrado para os responsáveis");
                    return false;
                }

                error_log("📧 Tentando enviar email para: " . implode(', ', $emails));

                // Enviar email
                $emailService = new \App\Services\EmailService();
                error_log("EmailService criado");
                
                $enviado = $emailService->sendMelhoriaConclusaoNotification($melhoria, $emails);

                if ($enviado) {
                    error_log("✅ Email de conclusão enviado para melhoria #{$melhoriaId} para: " . implode(', ', $emails));
                    return true;
                } else {
                    error_log("❌ Falha ao enviar email de conclusão para melhoria #{$melhoriaId}");
                    return false;
                }
            } else {
                error_log("⚠️ Melhoria #{$melhoriaId}: Sem responsáveis cadastrados");
                return false;
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar email de conclusão: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
}
