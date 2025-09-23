<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

class AccessRequestController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página de solicitação de acesso
    public function requestAccess()
    {
        // Buscar filiais
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM filiais ORDER BY nome");
            $stmt->execute();
            $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $filiais = [];
        }

        // Buscar departamentos
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM departamentos ORDER BY nome");
            $stmt->execute();
            $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $departamentos = [];
        }

        $title = 'Solicitar Acesso - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/auth/request-access.php';
        include __DIR__ . '/../../views/layouts/auth.php';
    }

    // Página de gerenciamento de solicitações (admin)
    public function index()
    {
        // Verificar se é admin
        if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
            header('Location: /403');
            exit();
        }

        $title = 'Solicitações de Acesso - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/admin/access-requests.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // Processar solicitação de acesso
    public function processRequest()
    {
        header('Content-Type: application/json');
        
        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            $setor = trim($_POST['setor'] ?? '');
            $filial = trim($_POST['filial'] ?? '');
            $justificativa = trim($_POST['justificativa'] ?? '');

            // Validações
            if (empty($name) || empty($email) || empty($password) || empty($justificativa)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                return;
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres']);
                return;
            }

            if ($password !== $password_confirm) {
                echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
                return;
            }

            // Verificar se email já existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado no sistema']);
                return;
            }

            // Verificar se já existe solicitação pendente
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM access_requests WHERE email = ? AND status = 'pendente'");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Já existe uma solicitação pendente para este email']);
                return;
            }

            // Hash da senha
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Inserir solicitação
            $stmt = $this->db->prepare("
                INSERT INTO access_requests (name, email, password_hash, setor, filial, justificativa) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $passwordHash, $setor, $filial, $justificativa]);

            echo json_encode(['success' => true, 'message' => 'Solicitação enviada com sucesso! Aguarde a aprovação do administrador.']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao processar solicitação: ' . $e->getMessage()]);
        }
    }

    // Listar solicitações pendentes (para admins)
    public function listPendingRequests()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT id, name, email, setor, filial, justificativa, created_at
                FROM access_requests 
                WHERE status = 'pendente'
                ORDER BY created_at ASC
            ");
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $requests]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar solicitações: ' . $e->getMessage()]);
        }
    }

    // Aprovar solicitação
    public function approveRequest()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }

            $request_id = (int)($_POST['request_id'] ?? 0);
            $profile_id = (int)($_POST['profile_id'] ?? 0);
            $admin_id = $_SESSION['user_id'];

            if ($request_id <= 0 || $profile_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID da solicitação e perfil são obrigatórios']);
                return;
            }

            $this->db->beginTransaction();

            // Buscar solicitação
            $stmt = $this->db->prepare("
                SELECT * FROM access_requests 
                WHERE id = ? AND status = 'pendente'
            ");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                $this->db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
                return;
            }

            // Criar usuário
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, setor, filial, profile_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $request['name'],
                $request['email'],
                $request['password_hash'],
                $request['setor'],
                $request['filial'],
                $profile_id
            ]);

            $user_id = $this->db->lastInsertId();

            // Atualizar solicitação
            $stmt = $this->db->prepare("
                UPDATE access_requests 
                SET status = 'aprovado', profile_id = ?, approved_by = ?, approved_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$profile_id, $admin_id, $request_id]);

            // Enviar email de boas-vindas (desabilitado temporariamente)
            // $emailSent = $this->sendWelcomeEmail($request['email'], $request['name']);

            $this->db->commit();

            $message = 'Usuário aprovado e criado com sucesso! (Email será enviado em breve)';

            echo json_encode(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()]);
        }
    }

    // Rejeitar solicitação
    public function rejectRequest()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }

            $request_id = (int)($_POST['request_id'] ?? 0);
            $rejection_reason = trim($_POST['rejection_reason'] ?? '');
            $admin_id = $_SESSION['user_id'];

            if ($request_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID da solicitação é obrigatório']);
                return;
            }

            // Atualizar solicitação
            $stmt = $this->db->prepare("
                UPDATE access_requests 
                SET status = 'rejeitado', rejection_reason = ?, approved_by = ?, approved_at = NOW()
                WHERE id = ? AND status = 'pendente'
            ");
            $stmt->execute([$rejection_reason, $admin_id, $request_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Solicitação rejeitada com sucesso']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao rejeitar solicitação: ' . $e->getMessage()]);
        }
    }

    // Listar perfis disponíveis
    public function listProfiles()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT id, name, description 
                FROM profiles 
                WHERE is_active = 1 
                ORDER BY name
            ");
            $stmt->execute();
            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $profiles]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar perfis: ' . $e->getMessage()]);
        }
    }

    // Enviar email de boas-vindas (desabilitado temporariamente - requer PHPMailer)
    private function sendWelcomeEmail($email, $name): bool
    {
        // Temporariamente desabilitado até instalar PHPMailer
        // Para ativar: composer require phpmailer/phpmailer
        return false;
        
        /*
        try {
            // Buscar configurações de email
            $stmt = $this->db->prepare("SELECT * FROM email_config WHERE is_active = 1 LIMIT 1");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$config) {
                error_log("Configuração de email não encontrada");
                return false;
            }

            $mail = new PHPMailer(true);
            // ... resto do código de email
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
        */
    }

    // Endpoint para buscar filiais
    public function getFiliais()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM filiais ORDER BY nome");
            $stmt->execute();
            $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $filiais]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar filiais: ' . $e->getMessage()]);
        }
    }

    // Endpoint para buscar departamentos
    public function getDepartamentos()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM departamentos ORDER BY nome");
            $stmt->execute();
            $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $departamentos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar departamentos: ' . $e->getMessage()]);
        }
    }
}
