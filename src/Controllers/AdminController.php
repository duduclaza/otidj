<?php

namespace App\Controllers;

use App\Config\Database;
use App\Controllers\AuthController;

class AdminController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        AuthController::requireAdmin();
        
        try {
            // Get statistics
            $stats = $this->getStats();
            
            $title = 'Painel Administrativo - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/dashboard.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar dashboard: ' . $e->getMessage();
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/dashboard.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }
    
    /**
     * Manage users
     */
    public function users()
    {
        AuthController::requireAdmin();
        
        // Check if it's an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            try {
                $stmt = $this->db->prepare("SELECT id, name, email, setor, filial, role, status, created_at FROM users ORDER BY created_at DESC");
                $stmt->execute();
                $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Get departamentos from departamentos table
                $departamentosStmt = $this->db->prepare("SELECT nome FROM departamentos ORDER BY nome");
                $departamentosStmt->execute();
                $setores = $departamentosStmt->fetchAll(\PDO::FETCH_COLUMN);
                
                // Get filiais from filiais table
                $filiaisStmt = $this->db->prepare("SELECT nome FROM filiais ORDER BY nome");
                $filiaisStmt->execute();
                $filiais = $filiaisStmt->fetchAll(\PDO::FETCH_COLUMN);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'users' => $users,
                    'setores' => $setores,
                    'filiais' => $filiais
                ]);
                return;
            } catch (\Exception $e) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
        }
        
        // Regular page load
        try {
            $title = 'Gerenciar Usuários - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/users.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar usuários: ' . $e->getMessage();
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/users.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }
    
    /**
     * Manage invitations
     */
    public function invitations()
    {
        AuthController::requireAdmin();
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_invitations ORDER BY created_at DESC");
            $stmt->execute();
            $invitations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $title = 'Solicitações de Acesso - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/invitations.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar solicitações: ' . $e->getMessage();
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/invitations.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }
    
    /**
     * Create user
     */
    public function createUser()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $setor = $_POST['setor'] ?? '';
        $filial = $_POST['filial'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Nome, email e senha são obrigatórios']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email inválido']);
            return;
        }
        
        try {
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
                return;
            }
            
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, setor, filial, role, status, email_verified_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$name, $email, $hashedPassword, $setor, $filial, $role]);
            
            $userId = $this->db->lastInsertId();
            
            // Set default permissions
            $this->setDefaultPermissions($userId, $role);
            
            echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário']);
        }
    }
    
    /**
     * Update user
     */
    public function updateUser()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $userId = $_POST['user_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $setor = $_POST['setor'] ?? '';
        $filial = $_POST['filial'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($userId) || empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, setor = ?, filial = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $email, $setor, $filial, $role, $status, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
        }
    }
    
    /**
     * Delete user
     */
    public function deleteUser()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $userId = $_POST['user_id'] ?? '';
        
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
            return;
        }
        
        // Prevent deleting self
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Você não pode excluir sua própria conta']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário']);
        }
    }
    
    /**
     * Approve invitation
     */
    public function approveInvitation()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $invitationId = $_POST['invitation_id'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        
        if (empty($invitationId) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
            return;
        }
        
        try {
            // Get invitation
            $stmt = $this->db->prepare("SELECT * FROM user_invitations WHERE id = ? AND status = 'pending'");
            $stmt->execute([$invitationId]);
            $invitation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$invitation) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                return;
            }
            
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, setor, filial, role, status, email_verified_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$invitation['name'], $invitation['email'], $hashedPassword, $invitation['setor'], $invitation['filial'], $role]);
            
            $userId = $this->db->lastInsertId();
            
            // Set default permissions
            $this->setDefaultPermissions($userId, $role);
            
            // Update invitation status
            $stmt = $this->db->prepare("UPDATE user_invitations SET status = 'approved', approved_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $invitationId]);
            
            // Send welcome email
            $this->sendWelcomeEmail($invitation['email'], $invitation['name'], $password);
            
            echo json_encode(['success' => true, 'message' => 'Usuário aprovado e criado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação']);
        }
    }
    
    /**
     * Reject invitation
     */
    public function rejectInvitation()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $invitationId = $_POST['invitation_id'] ?? '';
        
        if (empty($invitationId)) {
            echo json_encode(['success' => false, 'message' => 'ID da solicitação não informado']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE user_invitations SET status = 'rejected', approved_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $invitationId]);
            
            echo json_encode(['success' => true, 'message' => 'Solicitação rejeitada']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao rejeitar solicitação']);
        }
    }
    
    /**
     * Manage user permissions
     */
    public function userPermissions($userId)
    {
        AuthController::requireAdmin();
        
        try {
            // Get user
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                redirect('/admin/users');
                return;
            }
            
            // Get permissions
            $stmt = $this->db->prepare("SELECT * FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $userPermissions = [];
            foreach ($permissions as $perm) {
                $userPermissions[$perm['module']] = $perm;
            }
            
            $title = 'Permissões do Usuário - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/user-permissions.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            redirect('/admin/users');
        }
    }
    
    /**
     * Update user permissions
     */
    public function updatePermissions()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $userId = $_POST['user_id'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
            return;
        }
        
        try {
            // Delete existing permissions
            $stmt = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Insert new permissions
            foreach ($permissions as $module => $perms) {
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $userId,
                    $module,
                    isset($perms['view']) ? 1 : 0,
                    isset($perms['edit']) ? 1 : 0,
                    isset($perms['delete']) ? 1 : 0,
                    isset($perms['import']) ? 1 : 0,
                    isset($perms['export']) ? 1 : 0
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Permissões atualizadas com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar permissões']);
        }
    }
    
    private function getStats(): array
    {
        $stats = [];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stats['active_users'] = $stmt->fetchColumn();
        
        // Pending invitations
        $stmt = $this->db->query("SELECT COUNT(*) FROM user_invitations WHERE status = 'pending'");
        $stats['pending_invitations'] = $stmt->fetchColumn();
        
        // Total amostragens
        $stmt = $this->db->query("SELECT COUNT(*) FROM amostragens");
        $stats['total_amostragens'] = $stmt->fetchColumn();
        
        // Total retornados
        $stmt = $this->db->query("SELECT COUNT(*) FROM retornados");
        $stats['total_retornados'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    private function setDefaultPermissions(int $userId, string $role): void
    {
        $modules = ["toners", "amostragens", "retornados", "registros", "configuracoes"];
        
        foreach ($modules as $module) {
            if ($role === 'admin') {
                // Admin gets all permissions
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 1, 1)");
                $stmt->execute([$userId, $module]);
            } else {
                // Regular user gets view permission only
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 0, 0, 0, 0)");
                $stmt->execute([$userId, $module]);
            }
        }
    }
    
    private function sendWelcomeEmail(string $email, string $name, string $password): void
    {
        try {
            $subject = "Bem-vindo ao SGQ OTI DJ - Sua conta foi aprovada!";
            $body = "
            <h2>Bem-vindo ao SGQ OTI DJ!</h2>
            <p>Olá {$name},</p>
            <p>Sua solicitação de acesso foi aprovada! Agora você pode acessar o sistema com as seguintes credenciais:</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Senha:</strong> {$password}</p>
            <p><strong>Importante:</strong> Recomendamos que você altere sua senha após o primeiro login.</p>
            <p><a href='" . ($_ENV['APP_URL'] ?? '') . "/login'>Fazer Login</a></p>
            ";
            
            sendEmail($email, $subject, $body);
        } catch (\Exception $e) {
            error_log("Error sending welcome email: " . $e->getMessage());
        }
    }
}
