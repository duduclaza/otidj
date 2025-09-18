<?php

namespace App\Controllers;

use App\Config\Database;
use App\Controllers\AuthController;
use App\Services\PermissionService;

class SolicitacoesMelhoriasController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Show solicitações de melhorias page
     */
    public function index()
    {
        AuthController::requireAuth();
        
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'solicitacao_melhorias', 'view')) {
            http_response_code(403);
            echo 'Acesso negado';
            return;
        }

        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        // Get setores for dropdown
        $setores = $this->getSetores();
        
        // Get users for responsáveis dropdown
        $usuarios = $this->getUsuarios();

        require_once __DIR__ . '/../../views/layout.php';
    }

    /**
     * Create new solicitação
     */
    public function create()
    {
        AuthController::requireAuth();
        
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'solicitacao_melhorias', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para criar solicitações']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $userName = $_SESSION['user_name'];
            
            // Validate required fields
            $required = ['setor', 'processo', 'descricao_melhoria', 'resultado_esperado'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
                    return;
                }
            }

            // Validate responsáveis
            if (empty($_POST['responsaveis']) || !is_array($_POST['responsaveis'])) {
                echo json_encode(['success' => false, 'message' => 'Pelo menos um responsável deve ser selecionado']);
                return;
            }

            $this->db->beginTransaction();

            // Insert solicitação
            $stmt = $this->db->prepare("
                INSERT INTO solicitacoes_melhorias 
                (usuario_id, usuario_nome, setor, processo, descricao_melhoria, observacoes, resultado_esperado) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $userName,
                $_POST['setor'],
                $_POST['processo'],
                $_POST['descricao_melhoria'],
                $_POST['observacoes'] ?? '',
                $_POST['resultado_esperado']
            ]);

            $solicitacaoId = $this->db->lastInsertId();

            // Insert responsáveis
            $responsaveisEmails = [];
            foreach ($_POST['responsaveis'] as $responsavelId) {
                $userInfo = $this->getUserInfo($responsavelId);
                if ($userInfo) {
                    $stmt = $this->db->prepare("
                        INSERT INTO solicitacoes_melhorias_responsaveis 
                        (solicitacao_id, usuario_id, usuario_nome, usuario_email) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $solicitacaoId,
                        $responsavelId,
                        $userInfo['name'],
                        $userInfo['email']
                    ]);
                    
                    $responsaveisEmails[] = $userInfo['email'];
                }
            }

            // Handle file uploads
            if (!empty($_FILES['anexos']['name'][0])) {
                $this->handleFileUploads($solicitacaoId, $_FILES['anexos']);
            }

            $this->db->commit();

            // Send notifications to responsáveis
            $this->sendNotificationToResponsaveis($responsaveisEmails, $solicitacaoId, $_POST['processo']);

            echo json_encode(['success' => true, 'message' => 'Solicitação criada com sucesso!']);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao criar solicitação: ' . $e->getMessage()]);
        }
    }

    /**
     * Get solicitações for grid
     */
    public function getSolicitacoes()
    {
        AuthController::requireAuth();
        
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'solicitacao_melhorias', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($userId);
            
            // Admin vê todas, usuários só veem as próprias
            $whereClause = $isAdmin ? '' : 'WHERE s.usuario_id = ?';
            
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       GROUP_CONCAT(CONCAT(r.usuario_nome, ' (', r.usuario_email, ')') SEPARATOR ', ') as responsaveis,
                       COUNT(a.id) as total_anexos
                FROM solicitacoes_melhorias s
                LEFT JOIN solicitacoes_melhorias_responsaveis r ON s.id = r.solicitacao_id
                LEFT JOIN solicitacoes_melhorias_anexos a ON s.id = a.solicitacao_id
                $whereClause
                GROUP BY s.id
                ORDER BY s.created_at DESC
            ");
            
            if ($isAdmin) {
                $stmt->execute();
            } else {
                $stmt->execute([$userId]);
            }
            
            $solicitacoes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'solicitacoes' => $solicitacoes]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar solicitações: ' . $e->getMessage()]);
        }
    }

    /**
     * Update status (admin only)
     */
    public function updateStatus()
    {
        AuthController::requireAuth();
        
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'solicitacao_melhorias', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para alterar status']);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE solicitacoes_melhorias 
                SET status = ?, observacoes = ? 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['status'],
                $_POST['observacoes'] ?? '',
                $_POST['id']
            ]);

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
    }

    /**
     * Get solicitação details
     */
    public function getDetails($id)
    {
        AuthController::requireAuth();
        
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'solicitacao_melhorias', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        try {
            // Get solicitação
            $stmt = $this->db->prepare("SELECT * FROM solicitacoes_melhorias WHERE id = ?");
            $stmt->execute([$id]);
            $solicitacao = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                return;
            }

            // Get responsáveis
            $stmt = $this->db->prepare("
                SELECT usuario_nome, usuario_email 
                FROM solicitacoes_melhorias_responsaveis 
                WHERE solicitacao_id = ?
            ");
            $stmt->execute([$id]);
            $responsaveis = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get anexos
            $stmt = $this->db->prepare("
                SELECT nome_original, nome_arquivo, tipo_arquivo, tamanho_arquivo 
                FROM solicitacoes_melhorias_anexos 
                WHERE solicitacao_id = ?
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'solicitacao' => $solicitacao,
                'responsaveis' => $responsaveis,
                'anexos' => $anexos
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar detalhes: ' . $e->getMessage()]);
        }
    }

    private function getSetores()
    {
        $stmt = $this->db->prepare("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor != '' ORDER BY setor");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getUsuarios()
    {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getUserInfo($userId)
    {
        $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function handleFileUploads($solicitacaoId, $files)
    {
        $uploadDir = __DIR__ . '/../../storage/uploads/melhorias/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $maxFiles = 5;

        $uploadedCount = 0;
        
        for ($i = 0; $i < count($files['name']) && $uploadedCount < $maxFiles; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileType = $files['type'][$i];
                $fileSize = $files['size'][$i];
                $originalName = $files['name'][$i];
                
                if (!in_array($fileType, $allowedTypes)) {
                    continue;
                }
                
                if ($fileSize > $maxSize) {
                    continue;
                }
                
                $fileName = uniqid() . '_' . time() . '_' . $originalName;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                    $stmt = $this->db->prepare("
                        INSERT INTO solicitacoes_melhorias_anexos 
                        (solicitacao_id, nome_arquivo, nome_original, tipo_arquivo, tamanho_arquivo, caminho_arquivo) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $solicitacaoId,
                        $fileName,
                        $originalName,
                        $fileType,
                        $fileSize,
                        $filePath
                    ]);
                    
                    $uploadedCount++;
                }
            }
        }
    }

    /**
     * Print solicitação
     */
    public function printSolicitacao($id)
    {
        AuthController::requireAuth();
        
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'solicitacao_melhorias', 'view')) {
            http_response_code(403);
            echo 'Acesso negado';
            return;
        }

        try {
            // Get solicitação
            $stmt = $this->db->prepare("SELECT * FROM solicitacoes_melhorias WHERE id = ?");
            $stmt->execute([$id]);
            $solicitacao = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$solicitacao) {
                echo 'Solicitação não encontrada';
                return;
            }

            // Get responsáveis
            $stmt = $this->db->prepare("
                SELECT usuario_nome, usuario_email 
                FROM solicitacoes_melhorias_responsaveis 
                WHERE solicitacao_id = ?
            ");
            $stmt->execute([$id]);
            $responsaveis = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get anexos
            $stmt = $this->db->prepare("
                SELECT nome_original, tipo_arquivo, tamanho_arquivo 
                FROM solicitacoes_melhorias_anexos 
                WHERE solicitacao_id = ?
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Render print view
            require_once __DIR__ . '/../../views/melhoria-continua/print-solicitacao.php';

        } catch (\Exception $e) {
            echo 'Erro ao carregar solicitação: ' . $e->getMessage();
        }
    }

    private function sendNotificationToResponsaveis($emails, $solicitacaoId, $processo)
    {
        // Implementar envio de email
        // Por enquanto, apenas log
        error_log("Nova Solicitação de Melhoria #$solicitacaoId - Processo: $processo - Responsáveis: " . implode(', ', $emails));
    }
}
