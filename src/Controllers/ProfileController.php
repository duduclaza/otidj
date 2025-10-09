<?php

namespace App\Controllers;

use App\Config\Database;

class ProfileController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $title = 'Meu Perfil - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/profile.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    public function getProfile()
    {
        header('Content-Type: application/json');
        
        try {
            // Get user ID from session (assuming you have session management)
            session_start();
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['error' => 'Usuário não autenticado']);
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT id, name, email, profile_photo, profile_photo_type, notificacoes_ativadas
                FROM users 
                WHERE id = :id
            ");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo json_encode(['error' => 'Usuário não encontrado']);
                return;
            }
            
            // Convert BLOB to base64 if photo exists
            if ($user['profile_photo']) {
                $user['profile_photo'] = base64_encode($user['profile_photo']);
            }
            
            echo json_encode($user);
            
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Erro ao carregar perfil: ' . $e->getMessage()]);
        }
    }

    public function uploadPhoto()
    {
        header('Content-Type: application/json');
        
        try {
            session_start();
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Nenhuma foto foi enviada']);
                return;
            }
            
            $file = $_FILES['profile_photo'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP']);
                return;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB']);
                return;
            }
            
            // Read file content
            $photoData = file_get_contents($file['tmp_name']);
            $photoType = $file['type'];
            
            // Update database
            $stmt = $this->db->prepare("UPDATE users SET profile_photo = :photo, profile_photo_type = :type, profile_photo_size = :size WHERE id = :id");
            $stmt->execute([
                ':photo' => $photoData,
                ':type' => $photoType,
                ':size' => $file['size'],
                ':id' => $userId
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Foto de perfil atualizada com sucesso']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar foto: ' . $e->getMessage()]);
        }
    }

    public function changePassword()
    {
        header('Content-Type: application/json');
        
        try {
            session_start();
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'A nova senha e a confirmação não coincidem']);
                return;
            }
            
            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres']);
                return;
            }
            
            // Verify current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
                return;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha: ' . $e->getMessage()]);
        }
    }

    public function getPhoto($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT profile_photo, profile_photo_type FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user && $user['profile_photo']) {
                header('Content-Type: ' . $user['profile_photo_type']);
                echo $user['profile_photo'];
            } else {
                // Return default avatar or 404
                http_response_code(404);
                echo 'Foto não encontrada';
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao carregar foto';
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications()
    {
        header('Content-Type: application/json');
        
        try {
            // Iniciar sessão se não foi iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            // Get the notification preference (1 = enabled, 0 = disabled)
            $notificacoesAtivadas = isset($_POST['notificacoes_ativadas']) && $_POST['notificacoes_ativadas'] == '1' ? 1 : 0;
            
            // Check if column exists before updating
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas'");
                if ($checkColumn->rowCount() === 0) {
                    echo json_encode(['success' => false, 'message' => 'Funcionalidade de notificações não disponível. Execute a migration necessária.']);
                    return;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao verificar funcionalidade: ' . $e->getMessage()]);
                return;
            }
            
            // Update database
            $stmt = $this->db->prepare("UPDATE users SET notificacoes_ativadas = :notif WHERE id = :id");
            $result = $stmt->execute([
                ':notif' => $notificacoesAtivadas,
                ':id' => $userId
            ]);
            
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados']);
                return;
            }
            
            // Update session immediately
            $_SESSION['notificacoes_ativadas'] = (bool)$notificacoesAtivadas;
            
            // Force session save
            session_write_close();
            
            $message = $notificacoesAtivadas 
                ? 'Notificações ativadas com sucesso! Recarregando página...' 
                : 'Notificações desativadas com sucesso! Recarregando página...';
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'notificacoes_ativadas' => $notificacoesAtivadas,
                'reload_required' => true,
                'debug' => [
                    'user_id' => $userId,
                    'novo_valor' => $notificacoesAtivadas,
                    'db_updated' => true,
                    'session_updated' => true
                ]
            ]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar preferências: ' . $e->getMessage()]);
        }
    }
}
