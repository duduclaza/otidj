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
        // Verificar se tem permissão de dashboard (não precisa ser admin)
        if (!\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
            http_response_code(403);
            echo "<h1>Acesso Negado</h1><p>Você não tem permissão para acessar o dashboard.</p>";
            return;
        }
        
        try {
            // Get statistics
            $stats = $this->getStats();
            
            // Get totais acumulados dos gráficos
            $totaisAcumulados = $this->getTotaisAcumuladosGraficos();
            
            $title = 'Painel Administrativo - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/dashboard.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar dashboard: ' . $e->getMessage();
            $totaisAcumulados = ['retornados_total' => 0, 'destinos_total' => 0, 'valor_recuperado' => 0];
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
                
                // Get setores usando a mesma lógica da API
                $setores = $this->getSetoresList();
                
                // Get filiais usando a mesma lógica da API  
                $filiais = $this->getFiliaisList();
                
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
        try {
            // Limpar qualquer output anterior
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Headers JSON
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            
            // Verificar se é admin
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }
            
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
                exit;
            }
            
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $setor = $_POST['setor'] ?? '';
            $filial = $_POST['filial'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $profileId = $_POST['profile_id'] ?? null;
            $podeAprovarPopsIts = isset($_POST['pode_aprovar_pops_its']) ? 1 : 0;
            
            // Validar dados obrigatórios
            if (empty($name) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Nome e email são obrigatórios']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                exit;
            }
            
            // Verificar se usuário já existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
                exit;
            }
            
            // Se não especificou perfil, usar o padrão
            if (empty($profileId)) {
                $defaultProfileStmt = $this->db->prepare("SELECT id FROM profiles WHERE is_default = 1 LIMIT 1");
                $defaultProfileStmt->execute();
                $profileId = $defaultProfileStmt->fetchColumn();
                
                // Se não encontrou perfil padrão, usar o primeiro disponível
                if (!$profileId) {
                    $firstProfileStmt = $this->db->prepare("SELECT id FROM profiles LIMIT 1");
                    $firstProfileStmt->execute();
                    $profileId = $firstProfileStmt->fetchColumn();
                }
            }
            
            // Gerar senha temporária
            $tempPassword = $this->generateTempPassword();
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Verificar se coluna existe antes de inserir
            $columns = "name, email, password, setor, filial, role, profile_id, status";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, 'active'";
            $params = [$name, $email, $hashedPassword, $setor, $filial, $role, $profileId];
            
            // Adicionar coluna pode_aprovar_pops_its se existir
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
                if ($checkColumn->rowCount() > 0) {
                    $columns .= ", pode_aprovar_pops_its";
                    $placeholders .= ", ?";
                    $params[] = $podeAprovarPopsIts;
                }
            } catch (\Exception $e) {
                error_log("Coluna pode_aprovar_pops_its não existe ainda: " . $e->getMessage());
            }
            
            $stmt = $this->db->prepare("INSERT INTO users ($columns) VALUES ($placeholders)");
            $stmt->execute($params);
            
            $userId = $this->db->lastInsertId();
            
            // Retornar sucesso com a senha (sem tentar enviar email por enquanto)
            echo json_encode([
                'success' => true, 
                'message' => 'Usuário criado com sucesso! Senha temporária: ' . $tempPassword,
                'user_id' => $userId
            ]);
            
        } catch (\Exception $e) {
            error_log('Error creating user: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Send credentials via email
     */
    public function sendCredentials()
    {
        try {
            // Limpar qualquer output anterior
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Headers JSON
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');

            // Verificar sessão
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }

            // Verificar se é admin
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
                exit;
            }

            $userId = $_POST['user_id'] ?? $_POST['id'] ?? null;
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
                exit;
            }

            // Buscar usuário
            $stmt = $this->db->prepare("SELECT id, name, email, status FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                exit;
            }

            if ($user['status'] !== 'active') {
                echo json_encode(['success' => false, 'message' => 'Usuário não está ativo']);
                exit;
            }

            // Verificar configurações de email
            if (empty($_ENV['MAIL_HOST']) || empty($_ENV['MAIL_USERNAME'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Configurações de email não encontradas'
                ]);
                exit;
            }

            // Sempre enviar a senha temporária mudar@123
            $senhaTemporaria = 'mudar@123';

            // Enviar email de credenciais com a senha temporária
            $emailService = new \App\Services\EmailService();
            $emailSent = $emailService->sendWelcomeEmail($user, $senhaTemporaria);

            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Credenciais enviadas com sucesso para ' . $user['email'] . '! Senha: mudar@123'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Falha ao enviar email para ' . $user['email']
                ]);
            }

        } catch (\Exception $e) {
            error_log('Error in sendCredentials: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Generate temporary password
     */
    private function generateTempPassword(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $charactersLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $password;
    }
    
    /**
     * Test email configuration
     */
    public function testEmail()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Verificar se PHPMailer está disponível
            if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'PHPMailer não está instalado'
                ]);
                exit;
            }
            
            // Verificar configurações
            $config = [
                'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? null,
                'MAIL_USERNAME' => $_ENV['MAIL_USERNAME'] ?? null,
                'MAIL_PASSWORD' => $_ENV['MAIL_PASSWORD'] ?? null,
                'MAIL_PORT' => $_ENV['MAIL_PORT'] ?? null,
            ];
            
            $missing = [];
            foreach ($config as $key => $value) {
                if (empty($value)) {
                    $missing[] = $key;
                }
            }
            
            if (!empty($missing)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Configurações ausentes: ' . implode(', ', $missing)
                ]);
                exit;
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'PHPMailer instalado e configurações OK',
                'config' => [
                    'host' => $config['MAIL_HOST'],
                    'port' => $config['MAIL_PORT'],
                    'username' => $config['MAIL_USERNAME']
                ]
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erro: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Send clean JSON response
     */
    private function sendJsonResponse($data)
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Enviar resposta JSON limpa
        echo json_encode($data);
        exit;
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
     * Get setores list using robust query logic
     */
    private function getSetoresList(): array
    {
        $queries = [
            "SELECT name FROM departments WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM departments WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome",
            "SELECT name FROM departamentos WHERE name IS NOT NULL AND name <> '' ORDER BY name", 
            "SELECT nome as name FROM departamentos WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome",
            "SELECT name FROM setores WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM setores WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome"
        ];
        
        foreach ($queries as $query) {
            try {
                $stmt = $this->db->query($query);
                $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($result)) {
                    return $result;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: buscar dos usuários
        try {
            $stmt = $this->db->query("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get filiais list using robust query logic
     */
    private function getFiliaisList(): array
    {
        $queries = [
            "SELECT name FROM filiais WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM filiais WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome",
            "SELECT name FROM branches WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM branches WHERE nome IS NOT NULL AND nome <> '' ORDER by nome",
            "SELECT name FROM subsidiarias WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM subsidiarias WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome"
        ];
        
        foreach ($queries as $query) {
            try {
                $stmt = $this->db->query($query);
                $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($result)) {
                    return $result;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: buscar dos usuários
        try {
            $stmt = $this->db->query("SELECT DISTINCT filial FROM users WHERE filial IS NOT NULL AND filial <> '' ORDER BY filial");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
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
            $podeAprovarPopsIts = isset($_POST['pode_aprovar_pops_its']) ? 1 : 0;
            
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
            
            // Verificar se coluna pode_aprovar_pops_its existe
            $hasColumn = false;
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
                $hasColumn = $checkColumn->rowCount() > 0;
            } catch (\Exception $e) {
                error_log("Erro ao verificar coluna: " . $e->getMessage());
            }
            
            if (!empty($password)) {
                error_log("Updating user with new password");
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                if ($hasColumn) {
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, password = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ?, pode_aprovar_pops_its = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $email, $hashedPassword, $setor, $filial, $role, $profileId, $status, $podeAprovarPopsIts, $userId]);
                } else {
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, password = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $email, $hashedPassword, $setor, $filial, $role, $profileId, $status, $userId]);
                }
                
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
                
                if ($hasColumn) {
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ?, pode_aprovar_pops_its = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $email, $setor, $filial, $role, $profileId, $status, $podeAprovarPopsIts, $userId]);
                } else {
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $email, $setor, $filial, $role, $profileId, $status, $userId]);
                }
                
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
        
        try {
            // Total users
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $stats['active_users'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['active_users'] = 0;
        }
        
        try {
            // Pending access requests
            $stmt = $this->db->query("SELECT COUNT(*) FROM access_requests WHERE status = 'pendente'");
            $stats['pending_invitations'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['pending_invitations'] = 0;
        }
        
        try {
            // Total amostragens
            $stmt = $this->db->query("SELECT COUNT(*) FROM amostragens");
            $stats['total_amostragens'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['total_amostragens'] = 0;
        }
        
        try {
            // Total retornados (soma das quantidades)
            $stmt = $this->db->query("SELECT COALESCE(SUM(quantidade), 0) FROM retornados");
            $stats['total_retornados'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['total_retornados'] = 0;
        }
        
        return $stats;
    }

    /**
     * Get totais acumulados dos gráficos até a data atual
     * Usa a mesma lógica dos gráficos existentes
     */
    private function getTotaisAcumuladosGraficos(): array
    {
        $totais = [
            'retornados_total' => 0,
            'destinos_total' => 0,
            'valor_recuperado' => 0
        ];

        try {
            // Usar mesma detecção de colunas dos gráficos
            $valorColumn = $this->getValorColumn();
            $destinoColumn = $this->getDestinoColumn();
            
            // 1. Total de Retornados (soma de todas as quantidades) - Igual ao gráfico de barras
            $stmt = $this->db->query("SELECT COALESCE(SUM(quantidade), 0) as total FROM retornados");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['retornados_total'] = (int)($result['total'] ?? 0);

            // 2. Total de registros processados - Igual ao gráfico de pizza
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM retornados");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['destinos_total'] = (int)($result['total'] ?? 0);

            // 3. Valor total recuperado - Igual ao gráfico de linha (destino = 'estoque')
            $stmt = $this->db->query("SELECT COALESCE(SUM({$valorColumn}), 0) as total FROM retornados WHERE {$destinoColumn} = 'estoque'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['valor_recuperado'] = (float)($result['total'] ?? 0);

        } catch (\Exception $e) {
            error_log("Erro ao buscar totais acumulados dos gráficos: " . $e->getMessage());
        }

        return $totais;
    }

    /**
     * Get dashboard chart data
     */
    public function getDashboardData()
    {
        header('Content-Type: application/json');
        
        try {
            // Debug: verificar se a tabela existe
            $tableExists = $this->checkTableExists();
            if (!$tableExists) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Tabela retornados não encontrada',
                    'debug' => ['table_exists' => false]
                ]);
                exit;
            }
            
            $filial = $_GET['filial'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            // Debug: verificar estrutura da tabela
            $tableStructure = $this->getTableStructure();
            
            $data = [
                'retornados_mes' => $this->getRetornadosPorMes($filial, $dataInicial, $dataFinal),
                'retornados_destino' => $this->getRetornadosPorDestino($filial, $dataInicial, $dataFinal),
                'toners_recuperados' => $this->getTonersRecuperados($filial, $dataInicial, $dataFinal),
                'filiais' => $this->getFiliaisFromRetornados(),
                'debug' => [
                    'table_exists' => true,
                    'columns' => $tableStructure,
                    'date_column' => $this->getDateColumn(),
                    'filial_column' => $this->getFilialColumn(),
                    'destino_column' => $this->getDestinoColumn(),
                    'valor_column' => $this->getValorColumn()
                ]
            ];
            
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage(),
                'debug' => [
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]
            ]);
        }
        exit;
    }

    /**
     * Check if retornados table exists
     */
    private function checkTableExists()
    {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'retornados'");
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get table structure for debugging
     */
    private function getTableStructure()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get retornados por mês
     */
    private function getRetornadosPorMes($filial = '', $dataInicial = '', $dataFinal = '')
    {
        // Verificar estrutura da tabela e ajustar query
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        
        $sql = "
            SELECT 
                MONTH({$dateColumn}) as mes,
                YEAR({$dateColumn}) as ano,
                SUM(quantidade) as quantidade
            FROM retornados 
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filial)) {
            $sql .= " AND {$filialColumn} = ?";
            $params[] = $filial;
        }
        
        if (!empty($dataInicial)) {
            $sql .= " AND {$dateColumn} >= ?";
            $params[] = $dataInicial;
        }
        
        if (!empty($dataFinal)) {
            $sql .= " AND {$dateColumn} <= ?";
            $params[] = $dataFinal;
        }
        
        $sql .= " GROUP BY YEAR({$dateColumn}), MONTH({$dateColumn}) ORDER BY ano, mes";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Preparar dados para o gráfico
            $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $dados = array_fill(0, 12, 0);
            
            foreach ($results as $row) {
                $mesIndex = $row['mes'] - 1;
                if ($mesIndex >= 0 && $mesIndex < 12) {
                    $dados[$mesIndex] = (int)$row['quantidade'];
                }
            }
            
            return [
                'labels' => $meses,
                'data' => $dados
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
            ];
        }
    }

    /**
     * Get retornados por destino
     */
    private function getRetornadosPorDestino($filial = '', $dataInicial = '', $dataFinal = '')
    {
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        $destinoColumn = $this->getDestinoColumn();
        
        $sql = "
            SELECT 
                COALESCE({$destinoColumn}, 'Não Informado') as destino,
                SUM(quantidade) as quantidade
            FROM retornados 
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filial)) {
            $sql .= " AND {$filialColumn} = ?";
            $params[] = $filial;
        }
        
        if (!empty($dataInicial)) {
            $sql .= " AND {$dateColumn} >= ?";
            $params[] = $dataInicial;
        }
        
        if (!empty($dataFinal)) {
            $sql .= " AND {$dateColumn} <= ?";
            $params[] = $dataFinal;
        }
        
        $sql .= " GROUP BY destino ORDER BY quantidade DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $labels = [];
            $data = [];
            
            foreach ($results as $row) {
                $labels[] = $row['destino'];
                $data[] = (int)$row['quantidade'];
            }
            
            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Sem Dados'],
                'data' => [0]
            ];
        }
    }

    /**
     * Get valor recuperado em toners
     */
    private function getTonersRecuperados($filial = '', $dataInicial = '', $dataFinal = '')
    {
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        $valorColumn = $this->getValorColumn();
        $destinoColumn = $this->getDestinoColumn();
        
        $sql = "
            SELECT 
                MONTH({$dateColumn}) as mes,
                YEAR({$dateColumn}) as ano,
                SUM(COALESCE({$valorColumn}, 0)) as valor_total,
                SUM(CASE WHEN {$destinoColumn} = 'estoque' THEN quantidade ELSE 0 END) as quantidade_estoque
            FROM retornados 
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filial)) {
            $sql .= " AND {$filialColumn} = ?";
            $params[] = $filial;
        }
        
        if (!empty($dataInicial)) {
            $sql .= " AND {$dateColumn} >= ?";
            $params[] = $dataInicial;
        }
        
        if (!empty($dataFinal)) {
            $sql .= " AND {$dateColumn} <= ?";
            $params[] = $dataFinal;
        }
        
        $sql .= " GROUP BY YEAR({$dateColumn}), MONTH({$dateColumn}) ORDER BY ano, mes";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Preparar dados para o gráfico
            $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $valores = array_fill(0, 12, 0);
            $quantidades = array_fill(0, 12, 0);
            $percentuais = array_fill(0, 12, 0);
            $cores = array_fill(0, 12, 'gray');
            
            foreach ($results as $row) {
                $mesIndex = $row['mes'] - 1;
                if ($mesIndex >= 0 && $mesIndex < 12) {
                    $valores[$mesIndex] = (float)$row['valor_total'];
                    $quantidades[$mesIndex] = (int)$row['quantidade_estoque'];
                }
            }
            
            // Calcular percentuais e cores
            for ($i = 0; $i < 12; $i++) {
                if ($i > 0 && $valores[$i - 1] > 0) {
                    $percentuais[$i] = (($valores[$i] - $valores[$i - 1]) / $valores[$i - 1]) * 100;
                    $cores[$i] = $percentuais[$i] >= 0 ? 'green' : 'red';
                } else if ($i > 0 && $valores[$i] > 0) {
                    $percentuais[$i] = 100;
                    $cores[$i] = 'green';
                }
            }
            
            return [
                'labels' => $meses,
                'data' => $valores,
                'quantidades' => $quantidades,
                'percentuais' => $percentuais,
                'cores' => $cores
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'quantidades' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'percentuais' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'cores' => ['gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray']
            ];
        }
    }

    /**
     * Get filiais from retornados table
     */
    private function getFiliaisFromRetornados()
    {
        try {
            $filialColumn = $this->getFilialColumn();
            $stmt = $this->db->query("SELECT DISTINCT {$filialColumn} FROM retornados WHERE {$filialColumn} IS NOT NULL AND {$filialColumn} != '' ORDER BY {$filialColumn}");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get the correct date column name from retornados table
     */
    private function getDateColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de data (prioridade: data_registro primeiro)
            $possibleDateColumns = ['data_registro', 'data_retorno', 'data', 'created_at', 'date_created', 'data_criacao'];
            
            foreach ($possibleDateColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            // Se não encontrar, usar a primeira coluna que contenha 'data'
            foreach ($columns as $col) {
                if (stripos($col, 'data') !== false) {
                    return $col;
                }
            }
            
            return 'data_registro'; // fallback para data_registro
        } catch (\Exception $e) {
            return 'data_registro'; // fallback para data_registro
        }
    }

    /**
     * Get the correct filial column name from retornados table
     */
    private function getFilialColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de filial
            $possibleFilialColumns = ['filial', 'branch', 'subsidiary', 'unidade'];
            
            foreach ($possibleFilialColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            return 'filial'; // fallback
        } catch (\Exception $e) {
            return 'filial'; // fallback
        }
    }

    /**
     * Get the correct destino column name from retornados table
     */
    private function getDestinoColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de destino
            $possibleDestinoColumns = ['destino', 'destination', 'status', 'tipo_destino'];
            
            foreach ($possibleDestinoColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            return 'destino'; // fallback
        } catch (\Exception $e) {
            return 'destino'; // fallback
        }
    }

    /**
     * Get the correct valor column name from retornados table
     */
    private function getValorColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de valor (prioridade: valor_calculado primeiro)
            $possibleValorColumns = ['valor_calculado', 'valor_recuperado', 'valor', 'value', 'amount', 'preco'];
            
            foreach ($possibleValorColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            return 'valor_calculado'; // fallback para valor_calculado
        } catch (\Exception $e) {
            return 'valor_calculado'; // fallback para valor_calculado
        }
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

    /**
     * Diagnóstico de permissões de usuário
     */
    public function diagnosticoPermissoes()
    {
        // Verificar se é admin
        if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
            http_response_code(403);
            echo "<h1>Acesso Negado</h1><p>Apenas administradores podem acessar o diagnóstico.</p>";
            return;
        }

        try {
            echo "<!DOCTYPE html>
            <html lang='pt-br'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Diagnóstico de Permissões - SGQ OTI DJ</title>
                <script src='https://cdn.tailwindcss.com'></script>
            </head>
            <body class='bg-gray-100 p-8'>
                <div class='max-w-6xl mx-auto'>
                    <h1 class='text-3xl font-bold mb-6 text-gray-900'>🔍 Diagnóstico: Permissões de Usuário</h1>";

            // 1. Listar todos os usuários
            echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                    <h2 class='text-xl font-semibold mb-4'>1. Usuários do Sistema</h2>";

            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
                FROM users u 
                LEFT JOIN profiles p ON u.profile_id = p.id 
                ORDER BY u.name
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo "<div class='overflow-x-auto'>
                    <table class='min-w-full bg-white border border-gray-200'>
                        <thead class='bg-gray-50'>
                            <tr>
                                <th class='px-4 py-2 text-left'>ID</th>
                                <th class='px-4 py-2 text-left'>Nome</th>
                                <th class='px-4 py-2 text-left'>Email</th>
                                <th class='px-4 py-2 text-left'>Perfil</th>
                                <th class='px-4 py-2 text-center'>Dashboard</th>
                                <th class='px-4 py-2 text-center'>Ações</th>
                            </tr>
                        </thead>
                        <tbody>";

            foreach ($users as $user) {
                $hasDashboard = \App\Services\PermissionService::hasPermission($user['id'], 'dashboard', 'view');
                echo "<tr class='border-t'>
                        <td class='px-4 py-2'>" . $user['id'] . "</td>
                        <td class='px-4 py-2'>" . htmlspecialchars($user['name']) . "</td>
                        <td class='px-4 py-2'>" . htmlspecialchars($user['email']) . "</td>
                        <td class='px-4 py-2'>" . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</td>
                        <td class='px-4 py-2 text-center'>" . ($hasDashboard ? '<span class="text-green-600">✅</span>' : '<span class="text-red-600">❌</span>') . "</td>
                        <td class='px-4 py-2 text-center'>
                            <a href='?user_id=" . $user['id'] . "' class='bg-blue-600 text-white px-2 py-1 rounded text-sm hover:bg-blue-700'>Analisar</a>
                        </td>
                      </tr>";
            }
            echo "</tbody></table></div>";
            echo "</div>";

            // 2. Análise específica de usuário
            if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
                $userId = (int)$_GET['user_id'];
                
                $stmt = $this->db->prepare("
                    SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
                    FROM users u 
                    LEFT JOIN profiles p ON u.profile_id = p.id 
                    WHERE u.id = ?
                ");
                $stmt->execute([$userId]);
                $selectedUser = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($selectedUser) {
                    echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                            <h2 class='text-xl font-semibold mb-4'>2. Análise Detalhada: " . htmlspecialchars($selectedUser['name']) . "</h2>";

                    // Informações do usuário
                    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6 mb-6'>
                            <div>
                                <h3 class='font-semibold mb-2'>Informações do Usuário:</h3>
                                <ul class='space-y-1'>
                                    <li><strong>ID:</strong> " . $selectedUser['id'] . "</li>
                                    <li><strong>Nome:</strong> " . htmlspecialchars($selectedUser['name']) . "</li>
                                    <li><strong>Email:</strong> " . htmlspecialchars($selectedUser['email']) . "</li>
                                    <li><strong>Perfil:</strong> " . htmlspecialchars($selectedUser['profile_name'] ?? 'Sem perfil') . "</li>
                                    <li><strong>É Admin:</strong> " . ($selectedUser['is_admin'] ? '<span class="text-green-600">✅ SIM</span>' : '<span class="text-red-600">❌ NÃO</span>') . "</li>
                                </ul>
                            </div>";

                    // Verificar permissões específicas
                    $modules = ['dashboard', 'toners_cadastro', 'homologacoes', 'pops_its_visualizacao', 'admin_usuarios'];
                    echo "<div>
                            <h3 class='font-semibold mb-2'>Permissões Principais:</h3>
                            <ul class='space-y-1'>";
                    foreach ($modules as $module) {
                        $hasPermission = \App\Services\PermissionService::hasPermission($userId, $module, 'view');
                        echo "<li><strong>" . $module . ":</strong> " . ($hasPermission ? '<span class="text-green-600">✅ TEM</span>' : '<span class="text-red-600">❌ NÃO TEM</span>') . "</li>";
                    }
                    echo "</ul></div></div>";

                    // Permissões do perfil no banco
                    if ($selectedUser['profile_id']) {
                        echo "<h3 class='font-semibold mb-2'>Permissões do Perfil no Banco de Dados:</h3>";
                        $stmt = $this->db->prepare("
                            SELECT module, can_view, can_edit, can_delete, can_import, can_export
                            FROM profile_permissions 
                            WHERE profile_id = ? 
                            ORDER BY module
                        ");
                        $stmt->execute([$selectedUser['profile_id']]);
                        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                        if ($permissions) {
                            echo "<div class='overflow-x-auto'>
                                    <table class='min-w-full bg-white border border-gray-200'>
                                        <thead class='bg-gray-50'>
                                            <tr>
                                                <th class='px-3 py-2 text-left'>Módulo</th>
                                                <th class='px-3 py-2 text-center'>View</th>
                                                <th class='px-3 py-2 text-center'>Edit</th>
                                                <th class='px-3 py-2 text-center'>Delete</th>
                                                <th class='px-3 py-2 text-center'>Import</th>
                                                <th class='px-3 py-2 text-center'>Export</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                            foreach ($permissions as $perm) {
                                echo "<tr class='border-t'>
                                        <td class='px-3 py-2 font-mono text-sm'>" . $perm['module'] . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_view'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_import'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_export'] ? '✅' : '❌') . "</td>
                                      </tr>";
                            }
                            echo "</tbody></table></div>";
                        } else {
                            echo "<p class='text-red-600'>❌ Este perfil não tem permissões configuradas!</p>";
                        }
                    }

                    // Botão para corrigir dashboard
                    $hasDashboard = \App\Services\PermissionService::hasPermission($userId, 'dashboard', 'view');
                    if (!$hasDashboard && $selectedUser['profile_id']) {
                        echo "<div class='mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded'>
                                <h3 class='font-semibold text-yellow-800 mb-2'>🔧 Correção Disponível</h3>
                                <p class='text-yellow-700 mb-3'>Este usuário não tem permissão de dashboard. Clique abaixo para adicionar:</p>
                                <a href='?user_id=" . $userId . "&fix_dashboard=1' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>✅ Adicionar Permissão Dashboard</a>
                              </div>";
                    }

                    echo "</div>";
                }
            }

            // 3. Correção automática
            if (isset($_GET['fix_dashboard']) && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
                $userId = (int)$_GET['user_id'];
                
                $stmt = $this->db->prepare("SELECT profile_id FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($user && $user['profile_id']) {
                    echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                            <h2 class='text-xl font-semibold mb-4'>3. 🔧 Correção Executada</h2>";
                    
                    // Verificar se já existe
                    $checkStmt = $this->db->prepare("SELECT id FROM profile_permissions WHERE profile_id = ? AND module = 'dashboard'");
                    $checkStmt->execute([$user['profile_id']]);
                    
                    if ($checkStmt->fetch()) {
                        // Atualizar
                        $updateStmt = $this->db->prepare("UPDATE profile_permissions SET can_view = 1 WHERE profile_id = ? AND module = 'dashboard'");
                        $updateStmt->execute([$user['profile_id']]);
                        echo "<p class='text-green-600'>✅ Permissão de dashboard ATUALIZADA para visualização!</p>";
                    } else {
                        // Inserir
                        $insertStmt = $this->db->prepare("
                            INSERT INTO profile_permissions 
                            (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                            VALUES (?, 'dashboard', 1, 0, 0, 0, 0)
                        ");
                        $insertStmt->execute([$user['profile_id']]);
                        echo "<p class='text-green-600'>✅ Permissão de dashboard ADICIONADA para visualização!</p>";
                    }
                    
                    echo "<p class='mt-2'><a href='?user_id=" . $userId . "' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>🔄 Verificar Novamente</a></p>";
                    echo "</div>";
                }
            }

            echo "</div></body></html>";

        } catch (\Exception $e) {
            echo "<div class='bg-red-50 border border-red-200 rounded p-4'>
                    <h3 class='text-red-800 font-semibold'>❌ Erro no Diagnóstico:</h3>
                    <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
    }
    
}
