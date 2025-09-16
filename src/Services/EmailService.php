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
            
            // Default sender
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'djsgqoti@sgqoti.com.br',
                $_ENV['MAIL_FROM_NAME'] ?? 'SGQ OTI DJ'
            );
            
            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
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
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email) {
                    $this->mailer->addAddress($email);
                }
            } else {
                $this->mailer->addAddress($to);
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
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
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
