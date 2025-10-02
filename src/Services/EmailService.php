<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }
    
    private function configureMailer(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.hostinger.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? 'djsgqoti@sgqoti.com.br';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? 'Pandora@1989';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 465);
            
            // Timeout settings para melhor performance
            $this->mailer->Timeout = 10; // 10 segundos
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Default sender
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'djsgqoti@sgqoti.com.br',
                $_ENV['MAIL_FROM_NAME'] ?? 'SGQ OTI DJ'
            );
            
            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            // Debug em desenvolvimento
            if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                $this->mailer->SMTPDebug = 1;
            }
            
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }
    
    /**
     * Send email
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $altBody Plain text alternative
     * @param array $attachments Array of file paths to attach
     * @return bool Success status
     */
    public function send($to, string $subject, string $body, ?string $altBody = null, array $attachments = []): bool
    {
        try {
            error_log("=== TENTANDO ENVIAR EMAIL ===");
            error_log("Para: " . (is_array($to) ? implode(', ', $to) : $to));
            error_log("Assunto: " . $subject);
            error_log("SMTP Host: " . $this->mailer->Host);
            error_log("SMTP Port: " . $this->mailer->Port);
            error_log("SMTP User: " . $this->mailer->Username);
            
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email) {
                    if (!empty($email)) {
                        $this->mailer->addAddress($email);
                        error_log("Adicionado destinatário: " . $email);
                    }
                }
            } else {
                if (!empty($to)) {
                    $this->mailer->addAddress($to);
                    error_log("Adicionado destinatário: " . $to);
                }
            }
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            if ($altBody) {
                $this->mailer->AltBody = $altBody;
            }
            
            // Add attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment);
                }
            }
            
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("✅ Email enviado com sucesso!");
            } else {
                error_log("❌ Falha ao enviar email");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ ERRO ao enviar email: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Send notification email for amostragem status
     */
    public function sendAmostragemNotification(array $amostragem, string $recipientEmail): bool
    {
        $status = $amostragem['status'] === 'aprovado' ? 'APROVADA' : 'REPROVADA';
        $statusColor = $amostragem['status'] === 'aprovado' ? '#10B981' : '#EF4444';
        
        $subject = "Amostragem {$status} - NF {$amostragem['numero_nf']}";
        
        $body = $this->buildAmostragemEmailTemplate($amostragem, $status, $statusColor);
        
        $altBody = "Amostragem {$status}\n\n";
        $altBody .= "Número da NF: {$amostragem['numero_nf']}\n";
        $altBody .= "Status: {$status}\n";
        $altBody .= "Data: " . date('d/m/Y H:i', strtotime($amostragem['data_registro'])) . "\n";
        
        if (!empty($amostragem['observacao'])) {
            $altBody .= "Observação: {$amostragem['observacao']}\n";
        }
        
        return $this->send($recipientEmail, $subject, $body, $altBody);
    }
    
    /**
     * Send retornado notification email
     */
    public function sendRetornadoNotification(array $retornado, string $recipientEmail): bool
    {
        $subject = "Novo Retornado Registrado - {$retornado['modelo']}";
        
        $body = $this->buildRetornadoEmailTemplate($retornado);
        
        $altBody = "Novo Retornado Registrado\n\n";
        $altBody .= "Modelo: {$retornado['modelo']}\n";
        $altBody .= "Filial: {$retornado['filial']}\n";
        $altBody .= "Destino: {$retornado['destino']}\n";
        $altBody .= "Data: " . date('d/m/Y', strtotime($retornado['data_registro'])) . "\n";
        
        return $this->send($recipientEmail, $subject, $body, $altBody);
    }
    
    private function buildAmostragemEmailTemplate(array $amostragem, string $status, string $statusColor): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Notificação de Amostragem</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>SGQ OTI DJ</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>Sistema de Gestão da Qualidade</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: {$statusColor}; color: white; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 16px;'>
                        AMOSTRAGEM {$status}
                    </div>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Amostragem</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>Número da NF:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['numero_nf']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Status:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef; color: {$statusColor}; font-weight: bold;'>{$status}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data de Registro:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . date('d/m/Y H:i', strtotime($amostragem['data_registro'])) . "</td>
                    </tr>";
        
        if (!empty($amostragem['observacao'])) {
            $body .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Observação:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['observacao']}</td>
                    </tr>";
        }
        
        $body .= "
                </table>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>
                        <strong>Nota:</strong> Esta é uma notificação automática do sistema SGQ OTI DJ. 
                        Para mais detalhes, acesse o sistema através do link: 
                        <a href='" . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/toners/amostragens' style='color: #667eea;'>Sistema SGQ</a>
                    </p>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='margin: 0; color: #666; font-size: 12px;'>
                    © " . date('Y') . " SGQ OTI DJ - Sistema de Gestão da Qualidade<br>
                    Este email foi enviado automaticamente, não responda.
                </p>
            </div>
        </body>
        </html>";
    }
    
    private function buildRetornadoEmailTemplate(array $retornado): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Novo Retornado Registrado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>SGQ OTI DJ</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>Sistema de Gestão da Qualidade</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: #10B981; color: white; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 16px;'>
                        NOVO RETORNADO REGISTRADO
                    </div>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes do Retornado</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>Modelo:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$retornado['modelo']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Filial:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$retornado['filial']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Destino:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . ucfirst($retornado['destino']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data de Registro:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . date('d/m/Y', strtotime($retornado['data_registro'])) . "</td>
                    </tr>";
        
        if (!empty($retornado['valor_calculado'])) {
            $body .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Valor Calculado:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>R$ " . number_format($retornado['valor_calculado'], 2, ',', '.') . "</td>
                    </tr>";
        }
        
        $body .= "
                </table>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>
                        <strong>Nota:</strong> Esta é uma notificação automática do sistema SGQ OTI DJ. 
                        Para mais detalhes, acesse o sistema através do link: 
                        <a href='" . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/toners/retornados' style='color: #667eea;'>Sistema SGQ</a>
                    </p>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='margin: 0; color: #666; font-size: 12px;'>
                    © " . date('Y') . " SGQ OTI DJ - Sistema de Gestão da Qualidade<br>
                    Este email foi enviado automaticamente, não responda.
                </p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Send welcome email with temporary password
     */
    public function sendWelcomeEmail(array $user, string $tempPassword): bool
    {
        $subject = "Bem-vindo ao SGQ OTI DJ - Seus dados de acesso";
        
        $body = $this->buildWelcomeEmailTemplate($user, $tempPassword);
        
        $altBody = "Bem-vindo ao SGQ OTI DJ!\n\n";
        $altBody .= "Seus dados de acesso:\n";
        $altBody .= "Email: {$user['email']}\n";
        $altBody .= "Senha temporária: {$tempPassword}\n\n";
        $altBody .= "Acesse: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/login\n";
        $altBody .= "Recomendamos alterar sua senha no primeiro acesso.";
        
        return $this->send($user['email'], $subject, $body, $altBody);
    }
    
    private function buildWelcomeEmailTemplate(array $user, string $tempPassword): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Bem-vindo ao SGQ OTI DJ</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #1e293b 100%); padding: 40px; text-align: center; border-radius: 15px 15px 0 0;'>
                <div style='background: white; width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; padding: 10px;'>
                    <img src='{$appUrl}/img/logo.png' alt='DJ Logo' style='max-width: 100%; max-height: 100%; object-fit: contain;'>
                </div>
                <h1 style='color: white; margin: 0; font-size: 32px;'>🎉 Bem-vindo!</h1>
                <p style='color: #bfdbfe; margin: 10px 0 0 0; font-size: 18px;'>SGQ OTI DJ - Sistema de Gestão da Qualidade</p>
            </div>
            
            <div style='background: white; padding: 40px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #1e40af; margin: 0 0 10px 0;'>Olá, {$user['name']}!</h2>
                    <p style='color: #666; font-size: 16px; margin: 0;'>Sua conta foi criada com sucesso no SGQ OTI DJ.</p>
                </div>
                
                <div style='background: #f0f9ff; border: 2px solid #bfdbfe; border-radius: 10px; padding: 25px; margin: 25px 0;'>
                    <h3 style='color: #1e40af; margin: 0 0 15px 0; font-size: 18px;'>🔑 Seus dados de acesso:</h3>
                    
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px; background: #dbeafe; border: 1px solid #bfdbfe; font-weight: bold; width: 30%;'>Email:</td>
                            <td style='padding: 10px; border: 1px solid #bfdbfe; font-family: monospace; background: white;'>{$user['email']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; background: #dbeafe; border: 1px solid #bfdbfe; font-weight: bold;'>Senha Temporária:</td>
                            <td style='padding: 10px; border: 1px solid #bfdbfe; font-family: monospace; background: white; font-weight: bold; color: #dc2626;'>{$tempPassword}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/login' style='background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);'>
                        🚀 Acessar Sistema
                    </a>
                </div>
                
                <div style='background: #fef3c7; border: 2px solid #fbbf24; border-radius: 10px; padding: 20px; margin: 25px 0;'>
                    <div style='display: flex; align-items: start;'>
                        <div style='margin-right: 15px; font-size: 24px;'>⚠️</div>
                        <div>
                            <h4 style='color: #92400e; margin: 0 0 10px 0; font-size: 16px;'>Importante - Segurança:</h4>
                            <ul style='color: #92400e; margin: 0; padding-left: 20px; font-size: 14px;'>
                                <li>Esta é uma <strong>senha temporária</strong></li>
                                <li>Recomendamos <strong>alterar sua senha</strong> no primeiro acesso</li>
                                <li>Use uma senha segura com pelo menos 6 caracteres</li>
                                <li>Não compartilhe seus dados de acesso</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px; text-align: center;'>
                        <strong>Precisa de ajuda?</strong><br>
                        Entre em contato com o administrador do sistema ou acesse a documentação de ajuda.
                    </p>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 15px 15px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='margin: 0; color: #666; font-size: 12px;'>
                    © " . date('Y') . " SGQ OTI DJ - Sistema de Gestão da Qualidade<br>
                    Este email foi enviado automaticamente, não responda.
                </p>
            </div>
        </body>
        </html>";
    }

    /**
     * Send melhoria continua conclusion notification
     */
    public function sendMelhoriaConclusaoNotification(array $melhoria, array $responsaveisEmails): bool
    {
        if (empty($responsaveisEmails)) {
            return false;
        }

        $subject = "Melhoria Concluída - {$melhoria['titulo']}";
        $body = $this->buildMelhoriaConclusaoEmailTemplate($melhoria);
        
        $altBody = "Melhoria Concluída\n\n";
        $altBody .= "Título: {$melhoria['titulo']}\n";
        $altBody .= "Departamento: {$melhoria['departamento_nome']}\n";
        $altBody .= "Idealizador: {$melhoria['idealizador']}\n";
        $altBody .= "Data de Conclusão: " . date('d/m/Y') . "\n\n";
        $altBody .= "Parabéns! A melhoria foi concluída com sucesso!";
        
        return $this->send($responsaveisEmails, $subject, $body, $altBody);
    }

    private function buildMelhoriaConclusaoEmailTemplate(array $melhoria): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Melhoria Concluída</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>🎉 Melhoria Concluída!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Melhoria Contínua 2.0</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: #10B981; color: white; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 16px;'>
                        ✅ STATUS: CONCLUÍDA
                    </div>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Melhoria</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>Título:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$melhoria['titulo']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Departamento:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$melhoria['departamento_nome']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Idealizador:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$melhoria['idealizador']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data de Conclusão:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . date('d/m/Y H:i') . "</td>
                    </tr>";
        
        if (!empty($melhoria['resultado_esperado'])) {
            $body .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Resultado Esperado:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$melhoria['resultado_esperado']}</td>
                    </tr>";
        }

        if (!empty($melhoria['pontuacao'])) {
            $body .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Pontuação:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'><strong>{$melhoria['pontuacao']}/10</strong></td>
                    </tr>";
        }
        
        $body .= "
                </table>
                
                <div style='background: #d1fae5; border: 2px solid #10B981; border-radius: 10px; padding: 25px; margin: 25px 0; text-align: center;'>
                    <h3 style='color: #065f46; margin: 0 0 10px 0; font-size: 20px;'>🏆 Parabéns!</h3>
                    <p style='color: #065f46; margin: 0; font-size: 16px;'>
                        A melhoria foi concluída com sucesso!<br>
                        Obrigado pela sua contribuição para a melhoria contínua da empresa.
                    </p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/melhoria-continua-2/{$melhoria['id']}/view' style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Ver Detalhes Completos
                    </a>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>
                        <strong>Nota:</strong> Esta é uma notificação automática do sistema SGQ OTI DJ. 
                        Para mais detalhes, acesse o sistema através do link acima.
                    </p>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='margin: 0; color: #666; font-size: 12px;'>
                    © " . date('Y') . " SGQ OTI DJ - Sistema de Gestão da Qualidade<br>
                    Este email foi enviado automaticamente, não responda.
                </p>
            </div>
        </body>
        </html>";
    }

    /**
     * Test email configuration
     */
    public function testConnection(): array
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            
            return [
                'success' => true,
                'message' => 'Conexão SMTP estabelecida com sucesso!'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão SMTP: ' . $e->getMessage()
            ];
        }
    }
}
