<?php

namespace App\Controllers;

use App\Config\Database;

class AuthController
{
    private $db = null; // Lazy connection
    
    public function __construct()
    {
        // Do NOT open a DB connection here to keep /login robust.
        // Some shared hosts have strict connection limits and this runs on every page load.
        // We'll lazily fetch the connection only when needed (e.g., authenticate, requests).
        try {
            // No-op. Keep constructor safe.
        } catch (\Exception $e) {
            // Never break the login page due to DB issues in constructor
            error_log('AuthController init warning: ' . $e->getMessage());
        }
    }
    
    /**
     * Show login page
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            redirect('/');
            return;
        }
        
        $title = 'Login - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/auth/login.php';
        include __DIR__ . '/../../views/layouts/auth.php';
    }
    
    /**
     * Process login
     */
    public function authenticate()
    {
        header('Content-Type: application/json');
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios']);
            return;
        }
        
        try {
            // Ensure DB connection (lazy)
            if ($this->db === null) {
                $this->db = Database::getInstance();
            }
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_setor'] = $user['setor'];
                $_SESSION['user_filial'] = $user['filial'];
                
                // Load user profile information
                $profileInfo = \App\Services\PermissionService::getUserProfile($user['id']);
                $_SESSION['user_profile'] = $profileInfo;
                
                // Verificar se é primeiro acesso (se a coluna existir)
                if (isset($user['first_access']) && $user['first_access'] == 1) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Primeiro acesso detectado',
                        'redirect' => '/auth/first-access'
                    ]);
                    return;
                }
                
                // Determinar URL de redirecionamento baseado nas permissões
                $redirectUrl = '/';
                if (!\App\Services\PermissionService::hasPermission($user['id'], 'dashboard', 'view')) {
                    // Se não tem permissão para dashboard, encontrar primeiro módulo permitido
                    $redirectUrl = $this->findFirstAllowedModule($user['id']) ?: '/profile';
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login realizado com sucesso!',
                    'redirect' => $redirectUrl
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
            }
        } catch (\Exception $e) {
            error_log('Authenticate error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    /**
     * Show registration page
     */
    public function register()
    {
        $title = 'Solicitar Acesso - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/auth/register.php';
        include __DIR__ . '/../../views/layouts/auth.php';
    }
    
    /**
     * Process registration (invitation request)
     */
    public function requestInvitation()
    {
        header('Content-Type: application/json');
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $setor = $_POST['setor'] ?? '';
        $filial = $_POST['filial'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Nome e email são obrigatórios']);
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
            
            // Check if invitation already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_invitations WHERE email = ? AND status = 'pending'");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Já existe uma solicitação pendente para este email']);
                return;
            }
            
            // Create invitation request
            $stmt = $this->db->prepare("INSERT INTO user_invitations (name, email, setor, filial, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $setor, $filial, $message]);
            
            // Send notification email to admins
            $this->notifyAdminsNewInvitation($name, $email);
            
            echo json_encode(['success' => true, 'message' => 'Solicitação enviada com sucesso! Aguarde a aprovação do administrador.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao processar solicitação']);
        }
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        session_destroy();
        redirect('/login');
    }
    
    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'setor' => $_SESSION['user_setor'],
            'filial' => $_SESSION['user_filial'],
            'permissions' => $_SESSION['user_permissions'] ?? []
        ];
    }
    
    /**
     * Check if user has permission for specific action
     */
    public static function hasPermission(string $module, string $action): bool
    {
        if (!self::check()) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        return \App\Services\PermissionService::hasPermission($userId, $module, $action);
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Não autenticado', 'redirect' => '/login']);
                exit;
            } else {
                redirect('/login');
                exit;
            }
        }
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        
        if ($_SESSION['user_role'] !== 'admin') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                exit;
            } else {
                redirect('/');
                exit;
            }
        }
    }
    
    /**
     * Redirecionamento inteligente baseado nas permissões do usuário
     */
    private function getSmartRedirectUrl(int $userId): string
    {
        // Primeiro, tentar dashboard se tiver permissão
        if (\App\Services\PermissionService::hasPermission($userId, 'dashboard', 'view')) {
            return '/';
        }
        
        // Lista de módulos em ordem de prioridade (mais usados primeiro)
        $moduleUrls = [
            'toners_cadastro' => '/toners/cadastro',
            'amostragens' => '/toners/amostragens', 
            'toners_retornados' => '/toners/retornados',
            'homologacoes' => '/homologacoes',
            'melhoria_continua' => '/melhoria-continua',
            'solicitacao_melhorias' => '/melhoria-continua/solicitacoes',
            'garantias' => '/garantias',
            'controle_descartes' => '/controle-de-descartes',
            'femea' => '/femea',
            'pops_its' => '/pops-e-its',
            'fluxogramas' => '/fluxogramas',
            'controle_rc' => '/controle-de-rc',
            'registros_filiais' => '/registros/filiais',
            'registros_departamentos' => '/registros/departamentos',
            'registros_fornecedores' => '/registros/fornecedores',
            'registros_parametros' => '/registros/parametros',
            'configuracoes_gerais' => '/configuracoes',
        ];
        
        // Procurar primeiro módulo com permissão
        foreach ($moduleUrls as $module => $url) {
            if (\App\Services\PermissionService::hasPermission($userId, $module, 'view')) {
                return $url;
            }
        }
        
        // Se não encontrar nenhum módulo, ir para perfil
        return '/profile';
    }
    
    /**
     * Encontrar o primeiro módulo que o usuário tem permissão (método legado)
     */
    private function findFirstAllowedModule(int $userId): ?string
    {
        $url = $this->getSmartRedirectUrl($userId);
        return $url === '/profile' ? null : $url;
    }
    
    private function notifyAdminsNewInvitation(string $name, string $email): void
    {
        try {
            // Get all admin emails
            $stmt = $this->db->prepare("SELECT email FROM users WHERE role = 'admin' AND status = 'active'");
            $stmt->execute();
            $admins = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            if (!empty($admins)) {
                $subject = "Nova Solicitação de Acesso - SGQ OTI DJ";
                $body = "
                <h2>Nova Solicitação de Acesso</h2>
                <p><strong>Nome:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p>Acesse o painel administrativo para aprovar ou rejeitar esta solicitação.</p>
                <p><a href='" . ($_ENV['APP_URL'] ?? '') . "/admin/invitations'>Ver Solicitações Pendentes</a></p>
                ";
                
                foreach ($admins as $adminEmail) {
                    sendEmail($adminEmail, $subject, $body);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't show to user
            error_log('Error sending admin notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Show first access page
     */
    public function firstAccess()
    {
        // Verificar se usuário está logado
        if (!isset($_SESSION['user_id'])) {
            redirect('/login');
            return;
        }
        
        // Verificar se realmente é primeiro acesso
        try {
            $stmt = $this->db->prepare("SELECT first_access FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user || !isset($user['first_access']) || $user['first_access'] != 1) {
                redirect('/');
                return;
            }
        } catch (\Exception $e) {
            // Se a coluna não existir, redirecionar para home
            redirect('/');
            return;
        }
        
        $title = 'Primeiro Acesso - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/auth/first-access.php';
        include __DIR__ . '/../../views/layouts/auth.php';
    }
    
    /**
     * Change password on first access
     */
    public function changeFirstPassword()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
        $newPassword = $_POST['new_password'] ?? '';
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres']);
            return;
        }
        
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ?, first_access = 0 WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
            
            // Recarregar informações do usuário e permissões
            $userStmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $userStmt->execute([$_SESSION['user_id']]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                // Atualizar sessão com dados atualizados
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_setor'] = $user['setor'];
                $_SESSION['user_filial'] = $user['filial'];
                
                // Recarregar perfil e permissões
                $profileInfo = \App\Services\PermissionService::getUserProfile($user['id']);
                $_SESSION['user_profile'] = $profileInfo;
            }
            
            // Determinar URL de redirecionamento inteligente
            $redirectUrl = $this->getSmartRedirectUrl($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Senha alterada com sucesso! Bem-vindo ao SGQ OTI!',
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha']);
        }
    }
    
    /**
     * Skip first password change
     */
    public function skipFirstPassword()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET first_access = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Recarregar informações do usuário e permissões
            $userStmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $userStmt->execute([$_SESSION['user_id']]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                // Atualizar sessão com dados atualizados
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_setor'] = $user['setor'];
                $_SESSION['user_filial'] = $user['filial'];
                
                // Recarregar perfil e permissões
                $profileInfo = \App\Services\PermissionService::getUserProfile($user['id']);
                $_SESSION['user_profile'] = $profileInfo;
            }
            
            // Determinar URL de redirecionamento inteligente
            $redirectUrl = $this->getSmartRedirectUrl($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno']);
        }
    }
    
    /**
     * Generate temporary password
     */
    public static function generateTempPassword(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
}
