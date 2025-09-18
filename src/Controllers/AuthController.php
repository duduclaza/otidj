<?php

namespace App\Controllers;

use App\Config\Database;

class AuthController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
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
     * Encontrar o primeiro módulo que o usuário tem permissão
     */
    private function findFirstAllowedModule(int $userId): ?string
    {
        // Lista de módulos em ordem de prioridade
        $moduleUrls = [
            'toners_cadastro' => '/toners/cadastro',
            'toners_retornados' => '/toners/retornados',
            'amostragens' => '/toners/amostragens',
            'homologacoes' => '/homologacoes',
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
            'profile' => '/profile',
        ];
        
        foreach ($moduleUrls as $module => $url) {
            if (\App\Services\PermissionService::hasPermission($userId, $module, 'view')) {
                return $url;
            }
        }
        
        return null;
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
            error_log("Error notifying admins: " . $e->getMessage());
        }
    }
}
