<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\EmailService;
use PDO;

class PasswordResetController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Página de solicitação de recuperação de senha
     */
    public function requestResetPage()
    {
        include __DIR__ . '/../../views/pages/password-reset-request.php';
    }

    /**
     * Página de validação do código
     */
    public function verifyCodePage()
    {
        include __DIR__ . '/../../views/pages/password-reset-verify.php';
    }

    /**
     * Página de redefinição de senha
     */
    public function resetPasswordPage()
    {
        include __DIR__ . '/../../views/pages/password-reset-new.php';
    }

    /**
     * Solicitar código de recuperação
     */
    public function requestReset()
    {
        header('Content-Type: application/json');

        try {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Email é obrigatório']);
                return;
            }

            // Verificar se o email existe
            $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Email não encontrado no sistema']);
                return;
            }

            // Limpar códigos antigos deste usuário
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$user['id']]);

            // Gerar código de 6 dígitos
            $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            error_log("Token gerado: " . $token . " para email: " . $email);

            // Código expira em 2 minutos
            $expiresAt = date('Y-m-d H:i:s', strtotime('+2 minutes'));

            // Inserir código no banco
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, email, token, expires_at) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user['id'], $email, $token, $expiresAt]);
            
            error_log("Token inserido no banco para user_id: " . $user['id']);

            // Enviar email
            $emailService = new EmailService();
            $subject = 'Código de Recuperação de Senha - SGQ OTI DJ';
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>🔐 Recuperação de Senha</h1>
                </div>
                
                <div style='background: #f7f9fc; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <p style='font-size: 16px; color: #333; margin-bottom: 20px;'>Olá <strong>{$user['name']}</strong>,</p>
                    
                    <p style='font-size: 14px; color: #666; margin-bottom: 20px;'>
                        Você solicitou a recuperação de senha da sua conta no SGQ OTI DJ.
                    </p>
                    
                    <div style='background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;'>
                        <p style='font-size: 14px; color: #666; margin: 0 0 10px 0;'>Seu código de verificação é:</p>
                        <p style='font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 8px; text-align: center; margin: 10px 0;'>
                            {$token}
                        </p>
                        <p style='font-size: 12px; color: #999; margin: 10px 0 0 0; text-align: center;'>
                            ⏰ Use este código rapidamente
                        </p>
                    </div>
                    
                    <div style='background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='font-size: 13px; color: #856404; margin: 0;'>
                            <strong>⚠️ Segurança:</strong> Se você não solicitou esta recuperação, ignore este email. 
                            Sua senha permanecerá inalterada.
                        </p>
                    </div>
                    
                    <p style='font-size: 13px; color: #666; margin-top: 20px;'>
                        <strong>Próximos passos:</strong><br>
                        1. Insira o código acima na página de recuperação<br>
                        2. Defina sua nova senha<br>
                        3. Faça login com a nova senha
                    </p>
                </div>
                
                <div style='text-align: center; padding: 20px; color: #999; font-size: 12px;'>
                    <p>SGQ OTI DJ - Sistema de Gestão da Qualidade</p>
                    <p>Este é um email automático, não responda.</p>
                </div>
            </div>
            ";

            error_log("=== PASSWORD RESET EMAIL DEBUG ===");
            error_log("Enviando email para: " . $email);
            error_log("Token gerado: " . $token);
            error_log("Verificando se token está no body HTML...");
            
            if (strpos($body, $token) !== false) {
                error_log("✓ Token ENCONTRADO no body HTML na posição: " . strpos($body, $token));
            } else {
                error_log("✗ Token NÃO ENCONTRADO no body HTML! ERRO CRÍTICO!");
            }
            
            error_log("Body HTML completo tem " . strlen($body) . " caracteres");
            error_log("Primeiros 300 caracteres: " . substr($body, 0, 300));
            error_log("Caracteres ao redor do token: " . substr($body, strpos($body, $token) - 50, 100));
            
            // Corpo alternativo em texto plano
            $altBody = "
            Olá {$user['name']},
            
            Você solicitou a recuperação de senha da sua conta no SGQ OTI DJ.
            
            Seu código de verificação é: {$token}
            
            Use este código rapidamente.
            
            Próximos passos:
            1. Insira o código acima na página de recuperação
            2. Defina sua nova senha
            3. Faça login com a nova senha
            
            Se você não solicitou esta recuperação, ignore este email.
            
            SGQ OTI DJ - Sistema de Gestão da Qualidade
            ";
            
            $sent = $emailService->send($email, $subject, $body, $altBody);

            if ($sent) {
                error_log("Email enviado com sucesso! Token: " . $token);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Código enviado para o seu email! Verifique sua caixa de entrada.'
                ]);
            } else {
                error_log("ERRO ao enviar email!");
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro ao enviar email. Tente novamente.'
                ]);
            }

        } catch (\Exception $e) {
            error_log("PasswordResetController::requestReset - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao processar solicitação']);
        }
    }

    /**
     * Verificar código de recuperação
     */
    public function verifyCode()
    {
        header('Content-Type: application/json');

        try {
            $email = trim($_POST['email'] ?? '');
            $token = trim($_POST['code'] ?? '');

            if (empty($email) || empty($token)) {
                echo json_encode(['success' => false, 'message' => 'Email e código são obrigatórios']);
                return;
            }

            // Buscar código válido
            $stmt = $this->db->prepare("
                SELECT pr.*, u.name, u.email as user_email
                FROM password_resets pr
                INNER JOIN users u ON pr.user_id = u.id
                WHERE pr.email = ? 
                AND pr.token = ? 
                AND pr.expires_at > NOW() 
                AND pr.used = FALSE
            ");
            $stmt->execute([$email, $token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset) {
                echo json_encode(['success' => false, 'message' => 'Código inválido ou expirado']);
                return;
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Código válido! Você pode redefinir sua senha.',
                'data' => [
                    'email' => $reset['user_email'],
                    'token' => $token
                ]
            ]);

        } catch (\Exception $e) {
            error_log("PasswordResetController::verifyCode - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao verificar código']);
        }
    }

    /**
     * Redefinir senha
     */
    public function resetPassword()
    {
        header('Content-Type: application/json');

        try {
            $email = trim($_POST['email'] ?? '');
            $token = trim($_POST['token'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validações
            if (empty($email) || empty($token) || empty($newPassword) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
                return;
            }

            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
                return;
            }

            // Verificar código válido
            $stmt = $this->db->prepare("
                SELECT pr.*, u.id as user_id
                FROM password_resets pr
                INNER JOIN users u ON pr.user_id = u.id
                WHERE pr.email = ? 
                AND pr.token = ? 
                AND pr.expires_at > NOW() 
                AND pr.used = FALSE
            ");
            $stmt->execute([$email, $token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset) {
                echo json_encode(['success' => false, 'message' => 'Código inválido ou expirado']);
                return;
            }

            // Atualizar senha
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $reset['user_id']]);

            // Marcar código como usado e deletar
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE id = ?");
            $stmt->execute([$reset['id']]);

            echo json_encode([
                'success' => true, 
                'message' => 'Senha redefinida com sucesso! Você já pode fazer login.'
            ]);

        } catch (\Exception $e) {
            error_log("PasswordResetController::resetPassword - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao redefinir senha']);
        }
    }
}
