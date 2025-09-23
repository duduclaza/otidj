<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
                INSERT INTO users (name, email, password, setor, filial, profile_id, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
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

            // Enviar email de boas-vindas
            $emailSent = $this->sendWelcomeEmail($request['email'], $request['name']);

            $this->db->commit();

            $message = 'Usuário aprovado e criado com sucesso!';
            if (!$emailSent) {
                $message .= ' (Aviso: Não foi possível enviar o email de boas-vindas)';
            }

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

    // Enviar email de boas-vindas
    private function sendWelcomeEmail($email, $name): bool
    {
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

            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Remetente
            $mail->setFrom($config['from_email'], $config['from_name']);

            // Destinatário
            $mail->addAddress($email, $name);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = 'Bem-vindo ao SGQ OTI DJ';
            
            $systemUrl = 'https://djbr.sgqoti.com.br';
            
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f9fafb; }
                        .button { display: inline-block; background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Bem-vindo ao SGQ OTI DJ!</h1>
                        </div>
                        <div class='content'>
                            <h2>Olá, {$name}!</h2>
                            <p>É com grande prazer que informamos que sua solicitação de acesso ao Sistema de Gestão da Qualidade (SGQ) da OTI DJ foi <strong>aprovada</strong>!</p>
                            
                            <p>Agora você tem acesso ao nosso sistema e pode começar a utilizar todas as funcionalidades disponíveis para o seu perfil.</p>
                            
                            <p><strong>Para acessar o sistema, clique no link abaixo:</strong></p>
                            <p style='text-align: center;'>
                                <a href='{$systemUrl}' class='button'>Acessar SGQ OTI DJ</a>
                            </p>
                            
                            <p><strong>Suas credenciais de acesso:</strong></p>
                            <ul>
                                <li><strong>Email:</strong> {$email}</li>
                                <li><strong>Senha:</strong> A senha que você definiu durante a solicitação</li>
                            </ul>
                            
                            <p>Se você tiver alguma dúvida ou precisar de ajuda, entre em contato com nossa equipe de suporte.</p>
                            
                            <p>Seja bem-vindo(a) à equipe!</p>
                        </div>
                        <div class='footer'>
                            <p>SGQ OTI DJ - Sistema de Gestão da Qualidade</p>
                            <p>Este é um email automático, não responda.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->AltBody = "
                Bem-vindo ao SGQ OTI DJ!
                
                Olá, {$name}!
                
                Sua solicitação de acesso foi aprovada!
                
                Acesse o sistema em: {$systemUrl}
                
                Suas credenciais:
                Email: {$email}
                Senha: A senha que você definiu durante a solicitação
                
                Seja bem-vindo(a) à equipe!
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }
}
