<?php
namespace App\Controllers;

use App\Config\Database;

class MelhoriaContinuaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal de Melhoria Contínua
    public function index()
    {
        $title = 'Melhoria Contínua - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/melhoria-continua/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // Listar melhorias (AJAX)
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $search = $_GET['search'] ?? '';
            $dataInicio = $_GET['data_inicio'] ?? '';
            $dataFim = $_GET['data_fim'] ?? '';
            
            $sql = "
                SELECT m.*, d.nome as departamento_nome,
                       GROUP_CONCAT(u.name SEPARATOR ', ') as responsaveis_nomes,
                       COUNT(a.id) as total_anexos
                FROM melhorias_continuas m
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN melhorias_continuas_responsaveis mr ON m.id = mr.melhoria_id
                LEFT JOIN users u ON mr.user_id = u.id
                LEFT JOIN melhorias_continuas_anexos a ON m.id = a.melhoria_id
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (m.processo LIKE ? OR m.descricao_melhoria LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($dataInicio)) {
                $sql .= " AND DATE(m.data_registro) >= ?";
                $params[] = $dataInicio;
            }
            
            if (!empty($dataFim)) {
                $sql .= " AND DATE(m.data_registro) <= ?";
                $params[] = $dataFim;
            }
            
            $sql .= " GROUP BY m.id ORDER BY m.data_registro DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $melhorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $melhorias]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar melhorias: ' . $e->getMessage()]);
        }
    }

    // Buscar departamentos
    public function getDepartamentos()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM departamentos ORDER BY nome");
            $stmt->execute();
            $departamentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $departamentos]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar departamentos: ' . $e->getMessage()]);
        }
    }

    // Criar nova melhoria
    public function store()
    {
        header('Content-Type: application/json');
        
        try {
            $departamento_id = (int)($_POST['departamento_id'] ?? 0);
            $processo = trim($_POST['processo'] ?? '');
            $descricao_melhoria = trim($_POST['descricao_melhoria'] ?? '');
            $responsaveis = $_POST['responsaveis'] ?? [];
            $observacao = trim($_POST['observacao'] ?? '');
            $resultado = trim($_POST['resultado'] ?? '');
            
            // Validações
            if ($departamento_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Departamento é obrigatório']);
                return;
            }
            
            if (empty($processo)) {
                echo json_encode(['success' => false, 'message' => 'Processo é obrigatório']);
                return;
            }
            
            if (empty($descricao_melhoria)) {
                echo json_encode(['success' => false, 'message' => 'Descrição da melhoria é obrigatória']);
                return;
            }
            
            if (empty($responsaveis)) {
                echo json_encode(['success' => false, 'message' => 'Pelo menos um responsável deve ser selecionado']);
                return;
            }
            
            $this->db->beginTransaction();
            
            // Inserir melhoria
            $stmt = $this->db->prepare("
                INSERT INTO melhorias_continuas (departamento_id, processo, descricao_melhoria, observacao, resultado, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $departamento_id,
                $processo,
                $descricao_melhoria,
                $observacao,
                $resultado,
                $_SESSION['user_id'] ?? null
            ]);
            
            $melhoriaId = $this->db->lastInsertId();
            
            // Inserir responsáveis
            foreach ($responsaveis as $userId) {
                $stmt = $this->db->prepare("INSERT INTO melhorias_continuas_responsaveis (melhoria_id, user_id) VALUES (?, ?)");
                $stmt->execute([$melhoriaId, (int)$userId]);
            }
            
            // Processar anexos
            if (!empty($_FILES['anexos']['name'][0])) {
                $this->processAnexos($melhoriaId, $_FILES['anexos']);
            }
            
            $this->db->commit();
            
            // Enviar notificações e emails
            $this->sendNotificationsAndEmails($melhoriaId, $responsaveis, $processo, $descricao_melhoria);
            
            echo json_encode(['success' => true, 'message' => 'Melhoria registrada com sucesso!', 'id' => $melhoriaId]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar melhoria: ' . $e->getMessage()]);
        }
    }

    // Processar anexos
    private function processAnexos($melhoriaId, $files)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        $maxFiles = 10;
        
        if (count($files['name']) > $maxFiles) {
            throw new \Exception("Máximo de $maxFiles arquivos permitidos");
        }
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $fileName = $files['name'][$i];
            $fileType = $files['type'][$i];
            $fileSize = $files['size'][$i];
            $fileTmpName = $files['tmp_name'][$i];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new \Exception("Tipo de arquivo não permitido: $fileName");
            }
            
            if ($fileSize > $maxSize) {
                throw new \Exception("Arquivo muito grande: $fileName");
            }
            
            $fileContent = file_get_contents($fileTmpName);
            
            $stmt = $this->db->prepare("
                INSERT INTO melhorias_continuas_anexos (melhoria_id, arquivo, nome_arquivo, tipo_arquivo, tamanho_arquivo) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$melhoriaId, $fileContent, $fileName, $fileType, $fileSize]);
        }
    }
    
    // Enviar notificações e emails
    private function sendNotificationsAndEmails($melhoriaId, $responsaveis, $processo, $descricao)
    {
        try {
            foreach ($responsaveis as $userId) {
                // Criar notificação
                \App\Controllers\NotificationsController::create(
                    $userId,
                    "Nova Melhoria sob sua Responsabilidade - Pendente",
                    "Processo: $processo - $descricao",
                    'melhoria',
                    'melhoria_continua',
                    $melhoriaId
                );
                
                // TODO: Implementar envio de email com anexos
                // $this->sendEmailToResponsavel($userId, $processo, $descricao, $melhoriaId);
            }
        } catch (\Exception $e) {
            error_log("Erro ao enviar notificações: " . $e->getMessage());
        }
    }

    // Buscar usuários para responsáveis
    public function getUsuarios()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $usuarios]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar usuários: ' . $e->getMessage()]);
        }
    }

    // Atualizar status (apenas admins)
    public function updateStatus($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!$this->isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            $status = $_POST['status'] ?? '';
            $validStatuses = ['pendente', 'em_andamento', 'concluido', 'cancelado'];
            
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }
            
            $stmt = $this->db->prepare("UPDATE melhorias_continuas SET status = ? WHERE id = ?");
            $stmt->execute([$status, (int)$id]);
            
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
    }

    // Atualizar pontuação (apenas admins)
    public function updatePontuacao($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!$this->isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            $pontuacao = (int)($_POST['pontuacao'] ?? 0);
            
            $stmt = $this->db->prepare("UPDATE melhorias_continuas SET pontuacao = ? WHERE id = ?");
            $stmt->execute([$pontuacao, (int)$id]);
            
            echo json_encode(['success' => true, 'message' => 'Pontuação atualizada com sucesso']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar pontuação: ' . $e->getMessage()]);
        }
    }

    // Atualizar observação (apenas admins)
    public function updateObservacao($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!$this->isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            $observacao = trim($_POST['observacao'] ?? '');
            
            $stmt = $this->db->prepare("UPDATE melhorias_continuas SET observacao = ? WHERE id = ?");
            $stmt->execute([$observacao, (int)$id]);
            
            echo json_encode(['success' => true, 'message' => 'Observação atualizada com sucesso']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar observação: ' . $e->getMessage()]);
        }
    }

    // Atualizar resultado (apenas admins)
    public function updateResultado($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!$this->isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            $resultado = trim($_POST['resultado'] ?? '');
            
            $stmt = $this->db->prepare("UPDATE melhorias_continuas SET resultado = ? WHERE id = ?");
            $stmt->execute([$resultado, (int)$id]);
            
            echo json_encode(['success' => true, 'message' => 'Resultado atualizado com sucesso']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar resultado: ' . $e->getMessage()]);
        }
    }

    // Excluir melhoria
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("DELETE FROM melhorias_continuas WHERE id = ?");
            $stmt->execute([(int)$id]);
            
            echo json_encode(['success' => true, 'message' => 'Melhoria excluída com sucesso']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir melhoria: ' . $e->getMessage()]);
        }
    }

    // Imprimir melhoria
    public function print($id)
    {
        try {
            // Buscar dados da melhoria
            $stmt = $this->db->prepare("
                SELECT m.*, d.nome as departamento_nome, u.name as created_by_name
                FROM melhorias_continuas m
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.id = ?
            ");
            $stmt->execute([(int)$id]);
            $melhoria = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$melhoria) {
                http_response_code(404);
                echo 'Melhoria não encontrada';
                return;
            }
            
            // Buscar responsáveis
            $stmt = $this->db->prepare("
                SELECT u.name, u.email
                FROM melhorias_continuas_responsaveis mr
                JOIN users u ON mr.user_id = u.id
                WHERE mr.melhoria_id = ?
                ORDER BY u.name
            ");
            $stmt->execute([(int)$id]);
            $melhoria['responsaveis'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, tipo_arquivo, tamanho_arquivo, created_at
                FROM melhorias_continuas_anexos
                WHERE melhoria_id = ?
                ORDER BY created_at
            ");
            $stmt->execute([(int)$id]);
            $melhoria['anexos'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Renderizar página de impressão
            include __DIR__ . '/../../views/pages/melhoria-continua/print.php';
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao gerar impressão: ' . $e->getMessage();
        }
    }

    // Buscar anexos de uma melhoria
    public function getAnexos($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, nome_arquivo, tipo_arquivo, tamanho_arquivo, created_at
                FROM melhorias_continuas_anexos
                WHERE melhoria_id = ?
                ORDER BY created_at
            ");
            $stmt->execute([(int)$id]);
            $anexos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $anexos]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar anexos: ' . $e->getMessage()]);
        }
    }

    // Download de anexo
    public function downloadAnexo($anexoId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, tipo_arquivo, arquivo
                FROM melhorias_continuas_anexos
                WHERE id = ?
            ");
            $stmt->execute([(int)$anexoId]);
            $anexo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }
            
            // Headers para download
            header('Content-Type: ' . $anexo['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_arquivo'] . '"');
            header('Content-Length: ' . strlen($anexo['arquivo']));
            
            // Enviar arquivo
            echo $anexo['arquivo'];
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar anexo: ' . $e->getMessage();
        }
    }

    // Verificar se é admin
    private function isAdmin()
    {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }
}
