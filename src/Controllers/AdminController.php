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
                $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.setor, u.filial, u.role, u.status, u.created_at, u.profile_id,
                       p.name as profile_name, p.description as profile_description
                FROM users u 
                LEFT JOIN profiles p ON u.profile_id = p.id 
                ORDER BY u.created_at DESC
            ");
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
                
                // Get profiles
                $profilesStmt = $this->db->prepare("SELECT id, name, description FROM profiles ORDER BY is_admin DESC, name ASC");
                $profilesStmt->execute();
                $profiles = $profilesStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'users' => $users,
                    'setores' => $setores,
                    'filiais' => $filiais,
                    'profiles' => $profiles
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
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        AuthController::requireAdmin();
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $setor = $_POST['setor'] ?? '';
            $filial = $_POST['filial'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $profileId = $_POST['profile_id'] ?? null;
            
            if (empty($name) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Nome, email e senha são obrigatórios']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                exit;
            }
            
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
                exit;
            }
            
            // If no profile specified, get default profile
            if (empty($profileId)) {
                $defaultProfileStmt = $this->db->prepare("SELECT id FROM profiles WHERE is_default = 1 LIMIT 1");
                $defaultProfileStmt->execute();
                $profileId = $defaultProfileStmt->fetchColumn();
            }
            
            // Generate temporary password
            $tempPassword = \App\Controllers\AuthController::generateTempPassword();
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Check if first_access column exists
            $hasFirstAccessColumn = false;
            try {
                $this->db->query("SELECT first_access FROM users LIMIT 1");
                $hasFirstAccessColumn = true;
            } catch (\Exception $e) {
                // Column doesn't exist, we'll add it
                try {
                    $this->db->exec("ALTER TABLE users ADD COLUMN first_access TINYINT(1) DEFAULT 0");
                    $hasFirstAccessColumn = true;
                } catch (\Exception $alterError) {
                    error_log('Could not add first_access column: ' . $alterError->getMessage());
                }
            }
            
            // Create user
            if ($hasFirstAccessColumn) {
                $stmt = $this->db->prepare("INSERT INTO users (name, email, password, setor, filial, role, profile_id, status, first_access) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 1)");
                $stmt->execute([$name, $email, $hashedPassword, $setor, $filial, $role, $profileId]);
            } else {
                // Fallback without first_access column
                $stmt = $this->db->prepare("INSERT INTO users (name, email, password, setor, filial, role, profile_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $email, $hashedPassword, $setor, $filial, $role, $profileId]);
            }
            
            $userId = $this->db->lastInsertId();
            
            // Send welcome email with temporary password (don't let email errors break user creation)
            try {
                $emailService = new \App\Services\EmailService();
                $userData = ['id' => $userId, 'name' => $name, 'email' => $email];
                $emailService->sendWelcomeEmail($userData, $tempPassword);
                echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso! Email de boas-vindas enviado com senha temporária.']);
            } catch (\Exception $emailError) {
                error_log('Error sending welcome email: ' . $emailError->getMessage());
                echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso! (Erro ao enviar email: verifique configurações SMTP)']);
            }
            
        } catch (\Exception $e) {
            error_log('Error creating user: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit; // Ensure no additional output
    }
    
    /**
     * Send password change notification
     */
    private function sendPasswordChangeNotification($name, $email, $newPassword)
    {
        try {
            $emailService = new \App\Services\EmailService();
            
            $subject = 'SGQ-OTI DJ - Sua senha foi alterada';
            $loginUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/login';
            
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0; font-size: 28px;'>Senha Alterada</h1>
                    </div>
                    
                    <div style='padding: 30px; background: #f8f9fa;'>
                        <h2 style='color: #333; margin-bottom: 20px;'>Olá, {$name}!</h2>
                        
                        <p style='color: #666; font-size: 16px; line-height: 1.6;'>
                            Sua senha no sistema SGQ-OTI DJ foi alterada pelo administrador. Abaixo estão seus novos dados de acesso:
                        </p>
                        
                        <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;'>
                            <h3 style='color: #333; margin-top: 0;'>Novos Dados de Acesso:</h3>
                            <p style='margin: 10px 0;'><strong>Email:</strong> {$email}</p>
                            <p style='margin: 10px 0;'><strong>Nova Senha:</strong> {$newPassword}</p>
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$loginUrl}' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                                Acessar Sistema
                            </a>
                        </div>
                        
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                            <p style='margin: 0; color: #856404;'>
                                <strong>Importante:</strong> Por segurança, recomendamos que você altere sua senha no primeiro acesso.
                            </p>
                        </div>
                        
                        <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                            Se você não solicitou esta alteração, entre em contato com o administrador do sistema imediatamente.
                        </p>
                    </div>
                    
                    <div style='background: #333; padding: 20px; text-align: center;'>
                        <p style='color: #ccc; margin: 0; font-size: 12px;'>
                            SGQ-OTI DJ - Sistema de Gestão da Qualidade
                        </p>
                    </div>
                </div>
            ";
            
            $emailService->send($email, $subject, $body);
        } catch (\Exception $e) {
            // Log error but don't fail user update
            error_log('Failed to send password change notification: ' . $e->getMessage());
        }
    }

    /**
     * Send welcome email to new user
     */
    private function sendWelcomeEmail($name, $email, $password)
    {
        try {
            $emailService = new \App\Services\EmailService();
            
            $subject = 'Bem-vindo ao SGQ-OTI DJ - Seus dados de acesso';
            $loginUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/login';
            
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0; font-size: 28px;'>Bem-vindo ao SGQ-OTI DJ!</h1>
                    </div>
                    
                    <div style='padding: 30px; background: #f8f9fa;'>
                        <h2 style='color: #333; margin-bottom: 20px;'>Olá, {$name}!</h2>
                        
                        <p style='color: #666; font-size: 16px; line-height: 1.6;'>
                            Sua conta foi criada com sucesso no sistema SGQ-OTI DJ. Abaixo estão seus dados de acesso:
                        </p>
                        
                        <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;'>
                            <h3 style='color: #333; margin-top: 0;'>Dados de Acesso:</h3>
                            <p style='margin: 10px 0;'><strong>Email:</strong> {$email}</p>
                            <p style='margin: 10px 0;'><strong>Senha Temporária:</strong> {$password}</p>
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$loginUrl}' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                                Acessar Sistema
                            </a>
                        </div>
                        
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                            <p style='margin: 0; color: #856404;'>
                                <strong>Importante:</strong> Por segurança, recomendamos que você altere sua senha no primeiro acesso.
                            </p>
                        </div>
                        
                        <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                            Se você tiver alguma dúvida, entre em contato com o administrador do sistema.
                        </p>
                    </div>
                    
                    <div style='background: #333; padding: 20px; text-align: center;'>
                        <p style='color: #ccc; margin: 0; font-size: 12px;'>
                            SGQ-OTI DJ - Sistema de Gestão da Qualidade
                        </p>
                    </div>
                </div>
            ";
            
            $emailService->send($email, $subject, $body);
        } catch (\Exception $e) {
            // Log error but don't fail user creation
            error_log('Failed to send welcome email: ' . $e->getMessage());
        }
    }
    
    /**
     * Update user
     */
    public function updateUser()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON headers first
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            // Log start of method
            error_log("=== UpdateUser method started ===");
            error_log("Session data: " . json_encode([
                'user_id' => $_SESSION['user_id'] ?? 'not set',
                'user_role' => $_SESSION['user_role'] ?? 'not set'
            ]));
            
            // Check authentication first
            if (!isset($_SESSION['user_id'])) {
                error_log("Authentication failed: user_id not in session");
                echo json_encode(['success' => false, 'message' => 'Não autenticado', 'redirect' => '/login']);
                exit;
            }
            
            if ($_SESSION['user_role'] !== 'admin') {
                error_log("Authorization failed: user role is " . ($_SESSION['user_role'] ?? 'undefined'));
                echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
                exit;
            }
            
            // Accept both 'id' and 'user_id' for compatibility
            $userId = $_POST['id'] ?? $_POST['user_id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $setor = $_POST['setor'] ?? '';
            $filial = $_POST['filial'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $status = $_POST['status'] ?? 'active';
            $profileId = $_POST['profile_id'] ?? null;
            
            // Debug log
            error_log("UpdateUser - UserID: $userId, Name: $name, Email: $email");
            
            // Test database connection and table structure
            try {
                $testStmt = $this->db->query("DESCRIBE users");
                $columns = $testStmt->fetchAll(\PDO::FETCH_COLUMN);
                error_log("Users table columns: " . implode(', ', $columns));
                
                // Test if user exists
                $checkStmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = ?");
                $checkStmt->execute([$userId]);
                $existingUser = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                if (!$existingUser) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                    exit;
                }
                error_log("Existing user found: " . json_encode($existingUser));
                
            } catch (\Exception $e) {
                error_log("Error checking users table: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro na estrutura da tabela users: ' . $e->getMessage()]);
                exit;
            }
            
            // Validation
            if (empty($userId) || empty($name) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                exit;
            }
            
            // Check database connection
            if (!$this->db) {
                echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
                exit;
            }
            
            // Check if email is already used by another user
            error_log("Checking email duplication...");
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            $emailCount = $stmt->fetchColumn();
            error_log("Email count for other users: $emailCount");
            
            if ($emailCount > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está sendo usado por outro usuário']);
                exit;
            }
            
            // Update user with or without password
            error_log("Starting user update...");
            
            if (!empty($password)) {
                error_log("Updating user with new password");
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, password = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ? WHERE id = ?");
                $result = $stmt->execute([$name, $email, $hashedPassword, $setor, $filial, $role, $profileId, $status, $userId]);
                
                error_log("Update result with password: " . ($result ? 'success' : 'failed'));
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("SQL Error: " . implode(' - ', $errorInfo));
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário no banco de dados: ' . $errorInfo[2]]);
                    exit;
                }
                
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso! (Nova senha definida)']);
            } else {
                error_log("Updating user without password change");
                $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ? WHERE id = ?");
                $result = $stmt->execute([$name, $email, $setor, $filial, $role, $profileId, $status, $userId]);
                
                error_log("Update result without password: " . ($result ? 'success' : 'failed'));
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("SQL Error: " . implode(' - ', $errorInfo));
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário no banco de dados: ' . $errorInfo[2]]);
                    exit;
                }
                
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
            }
            
        } catch (\Exception $e) {
            error_log('Error updating user: ' . $e->getMessage() . ' - Line: ' . $e->getLine() . ' - File: ' . $e->getFile());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
        
        exit; // Ensure no additional output
    }
    
    /**
     * Delete user
     */
    public function deleteUser()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        AuthController::requireAdmin();
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $userId = $_POST['user_id'] ?? '';
            
            if (empty($userId)) {
                echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
                exit;
            }
            
            // Prevent deleting self
            if ($userId == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Você não pode excluir sua própria conta']);
                exit;
            }
            
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit; // Ensure no additional output
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
            $this->sendWelcomeEmail($invitation['name'], $invitation['email'], $password);
            
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
        
        // Check if it's an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            
            try {
                // Get user
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$user) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                    return;
                }
                
                // Get permissions (or create default structure if table doesn't exist)
                $permissions = [];
                try {
                    $stmt = $this->db->prepare("SELECT * FROM user_permissions WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $dbPermissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    foreach ($dbPermissions as $perm) {
                        $permissions[$perm['module']] = $perm;
                    }
                } catch (\Exception $e) {
                    // If table doesn't exist, return default permissions structure
                    $modules = ['dashboard', 'toners', 'homologacoes', 'amostragens', 'auditorias', 'garantias'];
                    foreach ($modules as $module) {
                        $permissions[$module] = [
                            'module' => $module,
                            'can_view' => 1,
                            'can_edit' => 0,
                            'can_delete' => 0
                        ];
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'user' => $user,
                    'permissions' => $permissions
                ]);
                return;
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
        }
        
        // Regular page load (fallback)
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                redirect('/admin/users');
                return;
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
    
}
