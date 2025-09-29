<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class Planos5W2HController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal - Lista de planos
    public function index()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $departamentos = $this->getDepartamentos();
            $usuarios = $this->getUsuarios();
            
            // Usar o layout padrão com TailwindCSS
            $title = '5W2H - Planos de Ação - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/5w2h/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Listar planos com filtros
    public function listPlanos()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar planos']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);
            
            // Filtros
            $status = $_GET['status'] ?? '';
            $responsavel = $_GET['responsavel'] ?? '';
            $setor = $_GET['setor'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';
            $meus_planos = $_GET['meus_planos'] ?? '';

            // Construir query base
            $sql = "
                SELECT p.*, 
                       u.name as responsavel_nome,
                       d.nome as setor_nome,
                       uc.name as criado_por_nome,
                       ua.name as atualizado_por_nome,
                       (SELECT COUNT(*) FROM planos_5w2h_anexos a WHERE a.plano_id = p.id) as anexos_count
                FROM planos_5w2h p
                LEFT JOIN users u ON p.who_id = u.id
                LEFT JOIN departamentos d ON p.setor_id = d.id
                LEFT JOIN users uc ON p.created_by = uc.id
                LEFT JOIN users ua ON p.updated_by = ua.id
                WHERE 1=1
            ";
            
            $params = [];

            // Se não é admin, só vê planos do seu setor ou que criou
            if (!$isAdmin) {
                $userDept = $this->getUserDepartmentId($user_id);
                $sql .= " AND (p.setor_id = ? OR p.created_by = ?)";
                $params[] = $userDept;
                $params[] = $user_id;
            }

            // Filtros
            if ($status) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }
            
            if ($responsavel) {
                $sql .= " AND p.who_id = ?";
                $params[] = $responsavel;
            }
            
            if ($setor) {
                $sql .= " AND p.setor_id = ?";
                $params[] = $setor;
            }
            
            if ($data_inicio) {
                $sql .= " AND p.when_inicio >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $sql .= " AND p.when_fim <= ?";
                $params[] = $data_fim;
            }
            
            if ($meus_planos === '1') {
                $sql .= " AND p.created_by = ?";
                $params[] = $user_id;
            }

            $sql .= " ORDER BY p.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'planos' => $planos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar planos: ' . $e->getMessage()]);
        }
    }

    // Criar novo plano
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar planos']);
                return;
            }

            // Receber dados do FormData (POST)
            $data = $_POST;
            
            // Validações
            $required = ['titulo', 'what', 'why', 'who', 'when', 'where', 'how', 'departamento'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Inserir plano
            $stmt = $this->db->prepare("
                INSERT INTO planos_5w2h (
                    titulo, what, why, where_local, when_inicio, 
                    who_id, how, how_much, status, setor_id, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Tratar campo howMuch - se vazio ou não informado, usar 0.00
            $howMuch = 0.00;
            if (!empty($data['howMuch']) && is_numeric($data['howMuch'])) {
                $howMuch = floatval($data['howMuch']);
            }
            
            $stmt->execute([
                $data['titulo'],
                $data['what'],
                $data['why'],
                $data['where'],
                $data['when'],
                $data['who'],
                $data['how'],
                $howMuch,
                $data['status'] ?? 'pendente',
                $data['departamento'],
                $_SESSION['user_id']
            ]);

            $plano_id = $this->db->lastInsertId();

            // Processar upload de arquivos se houver
            if (!empty($_FILES['anexos']['name'][0])) {
                $this->processarAnexos($plano_id, $_FILES['anexos']);
            }

            // Registrar no histórico
            $this->registrarHistorico($plano_id, 'criacao', null, 'Plano criado', $_SESSION['user_id']);

            echo json_encode(['success' => true, 'message' => 'Plano criado com sucesso!', 'plano_id' => $plano_id]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar plano: ' . $e->getMessage()]);
        }
    }

    // Atualizar plano
    public function update()
    {
        header('Content-Type: application/json');
        
        try {
            // Receber dados do FormData (POST)
            $data = $_POST;
            $plano_id = $data['id'] ?? 0;

            if (!$plano_id) {
                echo json_encode(['success' => false, 'message' => 'ID do plano é obrigatório']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar planos']);
                return;
            }

            // Validações
            $required = ['titulo', 'what', 'why', 'who', 'when', 'where', 'how', 'departamento'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Atualizar plano
            $stmt = $this->db->prepare("
                UPDATE planos_5w2h SET
                    titulo = ?, what = ?, why = ?, where_local = ?, when_inicio = ?,
                    who_id = ?, how = ?, how_much = ?, status = ?, setor_id = ?,
                    updated_by = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            // Tratar campo howMuch - se vazio ou não informado, usar 0.00
            $howMuch = 0.00;
            if (!empty($data['howMuch']) && is_numeric($data['howMuch'])) {
                $howMuch = floatval($data['howMuch']);
            }
            
            $stmt->execute([
                $data['titulo'],
                $data['what'],
                $data['why'],
                $data['where'],
                $data['when'],
                $data['who'],
                $data['how'],
                $howMuch,
                $data['status'] ?? 'pendente',
                $data['departamento'],
                $_SESSION['user_id'],
                $plano_id
            ]);

            // Processar upload de arquivos se houver
            if (!empty($_FILES['anexos']['name'][0])) {
                $this->processarAnexos($plano_id, $_FILES['anexos']);
            }

            // Registrar no histórico
            $this->registrarHistorico($plano_id, 'edicao', null, 'Plano editado', $_SESSION['user_id']);

            echo json_encode(['success' => true, 'message' => 'Plano atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar plano: ' . $e->getMessage()]);
        }
    }

    // Excluir plano
    public function delete()
    {
        header('Content-Type: application/json');
        
        try {
            $plano_id = $_POST['id'] ?? 0;

            if (!$plano_id) {
                echo json_encode(['success' => false, 'message' => 'ID do plano é obrigatório']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir planos']);
                return;
            }

            // Verificar se o plano existe
            $plano = $this->getPlanoById($plano_id);
            if (!$plano) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);

            // Se não é admin, só pode excluir planos que criou
            if (!$isAdmin && $plano['created_by'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Você só pode excluir planos que criou']);
                return;
            }

            // Excluir plano (CASCADE remove histórico e anexos)
            $stmt = $this->db->prepare("DELETE FROM planos_5w2h WHERE id = ?");
            $stmt->execute([$plano_id]);

            echo json_encode(['success' => true, 'message' => 'Plano excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir plano: ' . $e->getMessage()]);
        }
    }

    // Obter detalhes de um plano
    public function getPlano($id)
    {
        header('Content-Type: application/json');
        
        try {
            $plano = $this->getPlanoById($id);
            if (!$plano) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            // Verificar permissão de visualização
            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                $userDept = $this->getUserDepartmentId($user_id);
                if ($plano['setor_id'] != $userDept && $plano['created_by'] != $user_id) {
                    echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este plano']);
                    return;
                }
            }

            // Buscar histórico
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as alterado_por_nome
                FROM planos_5w2h_historico h
                LEFT JOIN users u ON h.alterado_por = u.id
                WHERE h.plano_id = ?
                ORDER BY h.alterado_em DESC
            ");
            $stmt->execute([$id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT a.*, u.name as uploaded_by_nome
                FROM planos_5w2h_anexos a
                LEFT JOIN users u ON a.uploaded_by = u.id
                WHERE a.plano_id = ?
                ORDER BY a.uploaded_at DESC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true, 
                'plano' => $plano, 
                'historico' => $historico, 
                'anexos' => $anexos
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar plano: ' . $e->getMessage()]);
        }
    }

    // Métodos auxiliares
    private function getPlanoById($id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   u.name as responsavel_nome,
                   d.nome as setor_nome,
                   uc.name as criado_por_nome,
                   ua.name as atualizado_por_nome
            FROM planos_5w2h p
            LEFT JOIN users u ON p.who_id = u.id
            LEFT JOIN departamentos d ON p.setor_id = d.id
            LEFT JOIN users uc ON p.created_by = uc.id
            LEFT JOIN users ua ON p.updated_by = ua.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getDepartamentos()
    {
        $stmt = $this->db->query("SELECT id, nome FROM departamentos ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUsuarios()
    {
        // Removido filtro por coluna 'active' (inexistente em alguns ambientes)
        $stmt = $this->db->query("SELECT id, name FROM users ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserDepartmentId($user_id)
    {
        $stmt = $this->db->prepare("SELECT departamento_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['departamento_id'] ?? null;
    }

    private function registrarHistorico($plano_id, $acao, $valor_anterior, $observacao, $user_id)
    {
        $stmt = $this->db->prepare("
            INSERT INTO planos_5w2h_historico (plano_id, acao, valor_anterior, observacao, alterado_por)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$plano_id, $acao, $valor_anterior, $observacao, $user_id]);
    }

    // Relatórios
    public function relatorios()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $title = '5W2H - Relatórios - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/5w2h/relatorios.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Kanban view
    public function kanban()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $title = '5W2H - Kanban - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/5w2h/kanban.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Detalhes do plano (para modal)
    public function details($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar planos']);
                return;
            }

            $plano = $this->getPlanoById($id);
            if (!$plano) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            echo json_encode(['success' => true, 'plano' => $plano]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar detalhes: ' . $e->getMessage()]);
        }
    }

    // Imprimir plano
    public function printPlano($id)
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $plano = $this->getPlanoById($id);
            if (!$plano) {
                http_response_code(404);
                echo 'Plano não encontrado';
                return;
            }

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT * FROM planos_5w2h_anexos 
                WHERE plano_id = ? 
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $plano['anexos'] = $anexos;

            // Página de impressão
            include __DIR__ . '/../../views/pages/5w2h/print.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Listar anexos de um plano
    public function anexos($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar anexos']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT a.*, u.name as uploaded_by_nome
                FROM planos_5w2h_anexos a
                LEFT JOIN users u ON a.uploaded_by = u.id
                WHERE a.plano_id = ?
                ORDER BY a.uploaded_at DESC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'anexos' => $anexos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar anexos: ' . $e->getMessage()]);
        }
    }

    // Download de anexo
    public function downloadAnexo($id)
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h', 'view')) {
                http_response_code(403);
                echo 'Sem permissão para baixar anexos';
                return;
            }

            $stmt = $this->db->prepare("SELECT * FROM planos_5w2h_anexos WHERE id = ?");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }

            $filePath = $anexo['caminho_arquivo'];
            if (!file_exists($filePath)) {
                http_response_code(404);
                echo 'Arquivo não encontrado no servidor';
                return;
            }

            // Headers para download
            header('Content-Type: ' . $anexo['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_original'] . '"');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Processar upload de anexos
    private function processarAnexos($plano_id, $files)
    {
        $uploadDir = __DIR__ . '/../../uploads/5w2h/';
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $fileName = $files['name'][$i];
            $fileType = $files['type'][$i];
            $fileSize = $files['size'][$i];
            $fileTmpName = $files['tmp_name'][$i];

            // Validações
            if (!in_array($fileType, $allowedTypes)) {
                throw new \Exception("Tipo de arquivo não permitido: {$fileName}");
            }

            if ($fileSize > $maxSize) {
                throw new \Exception("Arquivo muito grande: {$fileName}");
            }

            // Gerar nome único
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $uniqueName;

            // Mover arquivo
            if (move_uploaded_file($fileTmpName, $filePath)) {
                // Salvar no banco
                $stmt = $this->db->prepare("
                    INSERT INTO planos_5w2h_anexos (
                        plano_id, nome_original, nome_arquivo, tipo_arquivo, 
                        tamanho_arquivo, caminho_arquivo, uploaded_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $plano_id,
                    $fileName,
                    $uniqueName,
                    $fileType,
                    $fileSize,
                    $filePath,
                    $_SESSION['user_id']
                ]);

                // Registrar no histórico
                $this->registrarHistorico($plano_id, 'anexo_add', null, "Anexo adicionado: {$fileName}", $_SESSION['user_id']);
            }
        }
    }
}
