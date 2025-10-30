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
                    (SELECT COUNT(*) FROM controle_rc_evidencias WHERE rc_id = rc.id) as total_evidencias
                FROM controle_rc rc
                LEFT JOIN users u ON rc.usuario_id = u.id
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

            // Inserir registro principal
            $stmt = $this->db->prepare("
                INSERT INTO controle_rc (
                    numero_registro, data_abertura, origem, cliente_nome, categoria,
                    detalhamento, qual_produto, numero_serie, fornecedor_id, testes_realizados, acoes_realizadas,
                    conclusao, usuario_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
                $_SESSION['user_id']
            ]);

            $rcId = $this->db->lastInsertId();

            // Upload de evidências
            if (!empty($_FILES['evidencias']['name'][0])) {
                $this->uploadEvidencias($rcId, $_FILES['evidencias']);
            }

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

            // Buscar evidências
            $stmt = $this->db->prepare("
                SELECT nome_arquivo 
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
