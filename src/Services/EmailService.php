<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;
    private ?string $lastError = null;
    
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
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? 'suporte@djbr.sgqoti.com.br';
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
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'suporte@djbr.sgqoti.com.br',
                $_ENV['MAIL_FROM_NAME'] ?? 'SGQ OTI DJ'
            );
            
            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            // Debug ativado para troubleshooting
            $this->mailer->SMTPDebug = 2; // 0 = off, 1 = client, 2 = client and server
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug [$level]: $str");
            };
            
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
            $this->lastError = null;
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
                $this->lastError = $this->mailer->ErrorInfo ?: 'Falha desconhecida ao enviar email';
                error_log("❌ Falha ao enviar email: " . $this->lastError);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("❌ ERRO ao enviar email: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
    
    // Método sendAmostragemNotification antigo removido - usando nova versão mais abaixo
    
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
    
    // Template antigo buildAmostragemEmailTemplate removido - usando novos templates mais abaixo
    
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
     * Send melhoria continua status change notification
     */
    public function sendMelhoriaStatusNotification(array $melhoria, array $responsaveisEmails, string $novoStatus): bool
    {
        if (empty($responsaveisEmails)) {
            return false;
        }

        $subject = $this->getStatusSubject($novoStatus);
        $body = $this->buildMelhoriaStatusEmailTemplate($melhoria, $novoStatus);
        
        $altBody = $this->getStatusAltBody($melhoria, $novoStatus);
        
        return $this->send($responsaveisEmails, $subject, $body, $altBody);
    }

    /**
     * Send melhoria continua conclusion notification
     */
    public function sendMelhoriaConclusaoNotification(array $melhoria, array $responsaveisEmails): bool
    {
        if (empty($responsaveisEmails)) {
            return false;
        }

        $subject = "NOVA NOTIFICAÇÃO DO SGQ - MELHORIA CONTINUA 2.0";
        $body = $this->buildMelhoriaConclusaoEmailTemplate($melhoria);
        
        $altBody = "MELHORIA CONTÍNUA 2.0 - Detalhes do Registro\n\n";
        $altBody .= "Título: {$melhoria['titulo']}\n";
        $altBody .= "Departamento: {$melhoria['departamento_nome']}\n";
        $altBody .= "Idealizador: {$melhoria['idealizador']}\n";
        if (!empty($melhoria['descricao'])) { $altBody .= "Descrição: " . strip_tags($melhoria['descricao']) . "\n"; }
        if (!empty($melhoria['resultado_esperado'])) { $altBody .= "Resultado Esperado: " . strip_tags($melhoria['resultado_esperado']) . "\n"; }
        if (!empty($melhoria['pontuacao'])) { $altBody .= "Pontuação: {$melhoria['pontuacao']}/10\n"; }
        $altBody .= "Data: " . date('d/m/Y H:i') . "\n\n";
        $altBody .= "Acesse o SGQ para ver os detalhes completos.";
        
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
            <title>NOVA NOTIFICAÇÃO DO SGQ - MELHORIA CONTINUA 2.0</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>🎉 Melhoria Concluída!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Melhoria Contínua 2.0</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px; font-weight: bold; font-size: 18px; color: #047857;'>
                    NOVA NOTIFICAÇÃO DO SGQ - MELHORIA CONTINUA 2.0
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Melhoria</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>Título:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$melhoria['titulo']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Descrição:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . (!empty($melhoria['descricao']) ? nl2br(htmlspecialchars($melhoria['descricao'])) : '<em>Não informado</em>') . "</td>
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

    private function getStatusSubject(string $status): string
    {
        $subjects = [
            'Pendente análise' => 'SGQ - Melhoria Aguardando Análise',
            'Enviado para Aprovação' => 'SGQ - Melhoria Enviada para Aprovação 📤',
            'Em andamento' => 'SGQ - Melhoria em Andamento',
            'Em análise' => 'SGQ - Melhoria em Análise',
            'Aprovada' => 'SGQ - Melhoria Aprovada! 🎉',
            'Em implementação' => 'SGQ - Melhoria em Implementação',
            'Concluída' => 'SGQ - Melhoria Concluída com Sucesso! ✅',
            'Recusada' => 'SGQ - Melhoria Recusada',
            'Pendente Adaptação' => 'SGQ - Melhoria Precisa de Adaptação'
        ];

        return $subjects[$status] ?? 'SGQ - Atualização de Status da Melhoria';
    }

    private function getStatusAltBody(array $melhoria, string $status): string
    {
        $altBody = "MELHORIA CONTÍNUA 2.0 - Atualização de Status\n\n";
        $altBody .= "Status: {$status}\n";
        $altBody .= "Título: {$melhoria['titulo']}\n";
        $altBody .= "Departamento: {$melhoria['departamento_nome']}\n";
        $altBody .= "Idealizador: {$melhoria['idealizador']}\n";
        if (!empty($melhoria['descricao'])) { $altBody .= "Descrição: " . strip_tags($melhoria['descricao']) . "\n"; }
        $altBody .= "Data: " . date('d/m/Y H:i') . "\n\n";
        $altBody .= $this->getStatusMessage($status) . "\n\n";
        $altBody .= "Acesse o SGQ para ver os detalhes completos.";
        
        return $altBody;
    }

    private function getStatusMessage(string $status): string
    {
        $messages = [
            'Pendente análise' => 'Sua melhoria foi registrada e está aguardando análise da equipe.',
            'Enviado para Aprovação' => 'Sua melhoria foi enviada para aprovação da gerência. Em breve você receberá um retorno.',
            'Em andamento' => 'Sua melhoria foi aprovada e está em processo de implementação.',
            'Em análise' => 'Sua melhoria está sendo analisada pela equipe técnica.',
            'Aprovada' => 'Parabéns! Sua melhoria foi aprovada e será implementada.',
            'Em implementação' => 'Sua melhoria aprovada está sendo implementada.',
            'Concluída' => 'Excelente! Sua melhoria foi concluída com sucesso. Obrigado pela contribuição!',
            'Recusada' => 'Sua melhoria foi recusada. Verifique os comentários para mais detalhes.',
            'Pendente Adaptação' => 'Sua melhoria precisa de algumas adaptações. Verifique os comentários.'
        ];

        return $messages[$status] ?? 'Status da sua melhoria foi atualizado.';
    }

    private function buildMelhoriaStatusEmailTemplate(array $melhoria, string $status): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        $statusColors = [
            'Pendente análise' => '#6B7280',
            'Enviado para Aprovação' => '#4F46E5',
            'Em andamento' => '#3B82F6',
            'Em análise' => '#3B82F6',
            'Aprovada' => '#10B981',
            'Em implementação' => '#F59E0B',
            'Concluída' => '#059669',
            'Recusada' => '#EF4444',
            'Pendente Adaptação' => '#8B5CF6'
        ];
        
        $statusColor = $statusColors[$status] ?? '#6B7280';
        $statusMessage = $this->getStatusMessage($status);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Atualização de Status - Melhoria Contínua</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, {$statusColor} 0%, " . $this->darkenColor($statusColor) . " 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📋 Status Atualizado!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Melhoria Contínua 2.0</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: {$statusColor}; color: white; padding: 15px 25px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 18px;'>
                        {$status}
                    </div>
                </div>
                
                <div style='background: #f8f9fa; border-left: 4px solid {$statusColor}; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #374151;'>{$statusMessage}</p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Melhoria</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>Título:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$melhoria['titulo']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Descrição:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . (!empty($melhoria['descricao']) ? nl2br(htmlspecialchars($melhoria['descricao'])) : '<em>Não informado</em>') . "</td>
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
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data da Atualização:</td>
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

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/melhoria-continua-2/{$melhoria['id']}/view' style='background: linear-gradient(135deg, {$statusColor} 0%, " . $this->darkenColor($statusColor) . " 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
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

    private function darkenColor(string $color): string
    {
        // Escurece a cor em 20% para o gradiente
        $darkColors = [
            '#6B7280' => '#4B5563',
            '#3B82F6' => '#2563EB',
            '#10B981' => '#059669',
            '#F59E0B' => '#D97706',
            '#059669' => '#047857',
            '#EF4444' => '#DC2626',
            '#8B5CF6' => '#7C3AED'
        ];
        
        return $darkColors[$color] ?? $color;
    }

    /**
     * Send amostragem notification
     */
    public function sendAmostragemNotification(array $amostragem, array $responsaveisEmails, string $tipo, string $status = null): bool
    {
        if (empty($responsaveisEmails)) {
            return false;
        }

        if ($tipo === 'nova') {
            $subject = "SGQ - Nova Amostragem Criada 🔬";
            $body = $this->buildAmostragemNovaEmailTemplate($amostragem);
            $altBody = $this->getAmostragemNovaAltBody($amostragem);
        } else {
            $subject = $this->getAmostragemStatusSubject($status);
            $body = $this->buildAmostragemStatusEmailTemplate($amostragem, $status);
            $altBody = $this->getAmostragemStatusAltBody($amostragem, $status);
        }
        
        return $this->send($responsaveisEmails, $subject, $body, $altBody);
    }

    private function getAmostragemStatusSubject(string $status): string
    {
        $subjects = [
            'Pendente' => 'SGQ - Amostragem Aguardando Análise 🔬',
            'Em Análise' => 'SGQ - Amostragem em Análise 🔍',
            'Aprovado' => 'SGQ - Amostragem Aprovada! ✅',
            'Reprovado' => 'SGQ - Amostragem Reprovada ❌',
            'Concluído' => 'SGQ - Amostragem Concluída 🎉'
        ];

        return $subjects[$status] ?? 'SGQ - Atualização de Status da Amostragem';
    }

    private function getAmostragemNovaAltBody(array $amostragem): string
    {
        $altBody = "AMOSTRAGENS 2.0 - Nova Amostragem Criada\n\n";
        $altBody .= "NF: {$amostragem['numero_nf']}\n";
        $altBody .= "Produto: {$amostragem['nome_produto']} ({$amostragem['codigo_produto']})\n";
        $altBody .= "Fornecedor: {$amostragem['fornecedor_nome']}\n";
        $altBody .= "Criado por: {$amostragem['criador_nome']}\n";
        $altBody .= "Quantidade Recebida: {$amostragem['quantidade_recebida']}\n";
        $altBody .= "Quantidade Testada: {$amostragem['quantidade_testada']}\n";
        $altBody .= "Data: " . date('d/m/Y H:i') . "\n\n";
        $altBody .= "Você foi designado como responsável por esta amostragem.\n\n";
        $altBody .= "Acesse o SGQ para ver os detalhes completos.";
        
        return $altBody;
    }

    private function getAmostragemStatusAltBody(array $amostragem, string $status): string
    {
        $altBody = "AMOSTRAGENS 2.0 - Atualização de Status\n\n";
        $altBody .= "Status: {$status}\n";
        $altBody .= "NF: {$amostragem['numero_nf']}\n";
        $altBody .= "Produto: {$amostragem['nome_produto']} ({$amostragem['codigo_produto']})\n";
        $altBody .= "Fornecedor: {$amostragem['fornecedor_nome']}\n";
        $altBody .= "Quantidade Aprovada: {$amostragem['quantidade_aprovada']}\n";
        $altBody .= "Quantidade Reprovada: {$amostragem['quantidade_reprovada']}\n";
        $altBody .= "Data: " . date('d/m/Y H:i') . "\n\n";
        $altBody .= $this->getAmostragemStatusMessage($status) . "\n\n";
        $altBody .= "Acesse o SGQ para ver os detalhes completos.";
        
        return $altBody;
    }

    private function getAmostragemStatusMessage(string $status): string
    {
        $messages = [
            'Pendente' => 'A amostragem foi registrada e está aguardando análise.',
            'Em Análise' => 'A amostragem está sendo analisada pela equipe técnica.',
            'Aprovado' => 'Excelente! A amostragem foi aprovada nos testes de qualidade.',
            'Reprovado' => 'A amostragem foi reprovada nos testes. Verifique os detalhes.',
            'Concluído' => 'A amostragem foi concluída com sucesso. Processo finalizado!'
        ];

        return $messages[$status] ?? 'Status da amostragem foi atualizado.';
    }

    private function buildAmostragemNovaEmailTemplate(array $amostragem): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Nova Amostragem - SGQ</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>🔬 Nova Amostragem!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Amostragens 2.0</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #EBF8FF; border-left: 4px solid #3B82F6; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #1E40AF;'>
                        <strong>Você foi designado como responsável por esta amostragem.</strong><br>
                        Acesse o sistema para acompanhar o processo de análise.
                    </p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Amostragem</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>NF:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['numero_nf']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Produto:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['nome_produto']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Código:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['codigo_produto']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Fornecedor:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['fornecedor_nome']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Criado por:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['criador_nome']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Qtd. Recebida:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['quantidade_recebida']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Qtd. Testada:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['quantidade_testada']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data de Criação:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . date('d/m/Y H:i') . "</td>
                    </tr>
                </table>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/amostragens-2/{$amostragem['id']}/editar-resultados' style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 16px 35px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 17px; display: inline-block; margin-bottom: 10px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);'>
                        ✅ Adicionar Resultados dos Testes
                    </a>
                    <br>
                    <a href='{$appUrl}/amostragens-2' style='background: #6B7280; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px; display: inline-block;'>
                        👁️ Ver Todas as Amostragens
                    </a>
                </div>
                
                <div style='background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 20px; border-radius: 0 8px 8px 0; margin: 20px 0;'>
                    <p style='margin: 0; color: #92400E; font-size: 14px;'>
                        <strong>⚠️ Ação Necessária:</strong> Esta amostragem está aguardando os resultados dos testes. Clique no botão acima para adicionar:
                    </p>
                    <ul style='margin: 10px 0 0 20px; color: #92400E; font-size: 14px;'>
                        <li>Quantidade Testada</li>
                        <li>Quantidade Aprovada</li>
                        <li>Quantidade Reprovada</li>
                        <li>Status Final</li>
                    </ul>
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

    private function buildAmostragemStatusEmailTemplate(array $amostragem, string $status): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        $statusColors = [
            'Pendente' => '#6B7280',
            'Em Análise' => '#3B82F6',
            'Aprovado' => '#10B981',
            'Reprovado' => '#EF4444',
            'Concluído' => '#059669'
        ];
        
        $statusColor = $statusColors[$status] ?? '#6B7280';
        $statusMessage = $this->getAmostragemStatusMessage($status);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Atualização de Status - Amostragem</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, {$statusColor} 0%, " . $this->darkenColor($statusColor) . " 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>🔬 Status Atualizado!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Amostragens 2.0</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: {$statusColor}; color: white; padding: 15px 25px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 18px;'>
                        {$status}
                    </div>
                </div>
                
                <div style='background: #f8f9fa; border-left: 4px solid {$statusColor}; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #374151;'>{$statusMessage}</p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Amostragem</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 30%;'>NF:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['numero_nf']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Produto:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['nome_produto']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Fornecedor:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$amostragem['fornecedor_nome']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Qtd. Aprovada:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'><span style='color: #10B981; font-weight: bold;'>{$amostragem['quantidade_aprovada']}</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Qtd. Reprovada:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'><span style='color: #EF4444; font-weight: bold;'>{$amostragem['quantidade_reprovada']}</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data da Atualização:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . date('d/m/Y H:i') . "</td>
                    </tr>
                </table>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/amostragens-2/{$amostragem['id']}/details' style='background: linear-gradient(135deg, {$statusColor} 0%, " . $this->darkenColor($statusColor) . " 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
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
     * Send POPs e ITs pendente notification
     */
    public function sendPopItsPendenteNotification(array $emails, string $titulo, string $mensagem, $registroId = null): bool
    {
        if (empty($emails)) {
            return false;
        }

        $subject = "SGQ - Novo POP/IT Pendente de Aprovação 📋";
        $body = $this->buildPopItsPendenteEmailTemplate($titulo, $mensagem, $registroId);
        
        $altBody = "SGQ OTI DJ - POPs e ITs\n\n";
        $altBody .= "$titulo\n\n";
        $altBody .= "$mensagem\n\n";
        $altBody .= "Acesse o sistema para revisar e aprovar/reprovar o registro.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/pops-e-its";
        
        return $this->send($emails, $subject, $body, $altBody);
    }

    private function buildPopItsPendenteEmailTemplate(string $titulo, string $mensagem, $registroId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Novo POP/IT Pendente</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📋 Novo POP/IT Pendente!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - POPs e ITs</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #92400E;'>
                        <strong>⏳ Atenção: Há um novo registro aguardando sua aprovação!</strong>
                    </p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>$titulo</h2>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #374151; font-size: 15px;'>
                        $mensagem
                    </p>
                </div>
                
                <div style='background: #EBF8FF; border: 2px solid #3B82F6; border-radius: 10px; padding: 25px; margin: 25px 0;'>
                    <h3 style='color: #1E40AF; margin: 0 0 15px 0; font-size: 18px;'>🔍 Próximos Passos:</h3>
                    <ul style='color: #1E40AF; margin: 0; padding-left: 20px; font-size: 14px;'>
                        <li style='margin: 8px 0;'>Acesse o sistema SGQ OTI DJ</li>
                        <li style='margin: 8px 0;'>Navegue até <strong>POPs e ITs → Pendente Aprovação</strong></li>
                        <li style='margin: 8px 0;'>Revise o documento cuidadosamente</li>
                        <li style='margin: 8px 0;'>Aprove ou reprove com justificativa</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/pops-e-its' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Acessar POPs e ITs
                    </a>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>
                        <strong>Nota:</strong> Você recebeu este email porque está configurado como aprovador de POPs e ITs no sistema. 
                        Para alterar suas preferências, entre em contato com o administrador do sistema.
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
     * Send POP/IT aprovado notification
     */
    public function sendPopItsAprovadoNotification(string $email, string $tipo, string $titulo, string $versao, $registroId): bool
    {
        if (empty($email)) {
            return false;
        }

        $subject = "SGQ - {$tipo} Aprovado ✅";
        $body = $this->buildPopItsAprovadoTemplate($tipo, $titulo, $versao, $registroId);
        
        $altBody = "SGQ OTI DJ - POPs e ITs\n\n";
        $altBody .= "Parabéns! Seu {$tipo} foi aprovado!\n\n";
        $altBody .= "Título: {$titulo}\n";
        $altBody .= "Versão: v{$versao}\n\n";
        $altBody .= "O documento já está disponível para visualização no sistema.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/pops-e-its";
        
        return $this->send([$email], $subject, $body, $altBody);
    }

    private function buildPopItsAprovadoTemplate(string $tipo, string $titulo, string $versao, $registroId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$tipo} Aprovado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>✅ {$tipo} Aprovado!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - POPs e ITs</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #D1FAE5; border-left: 4px solid #10B981; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #065F46;'>
                        <strong>🎉 Parabéns! Seu documento foi aprovado e já está disponível no sistema!</strong>
                    </p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Tipo:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$tipo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Título:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$titulo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Versão:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>v{$versao}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Status:</strong></td>
                            <td style='padding: 10px 0;'><span style='background: #10B981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;'>APROVADO</span></td>
                        </tr>
                    </table>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/pops-e-its' style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Visualizar no Sistema
                    </a>
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
     * Send POP/IT reprovado notification
     */
    public function sendPopItsReprovadoNotification(string $email, string $tipo, string $titulo, string $versao, string $motivo, $registroId): bool
    {
        if (empty($email)) {
            return false;
        }

        $subject = "SGQ - {$tipo} Reprovado ❌";
        $body = $this->buildPopItsReprovadoTemplate($tipo, $titulo, $versao, $motivo, $registroId);
        
        $altBody = "SGQ OTI DJ - POPs e ITs\n\n";
        $altBody .= "Seu {$tipo} foi reprovado.\n\n";
        $altBody .= "Título: {$titulo}\n";
        $altBody .= "Versão: v{$versao}\n";
        $altBody .= "Motivo: {$motivo}\n\n";
        $altBody .= "Você pode editar o documento e enviar novamente para aprovação.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/pops-e-its";
        
        return $this->send([$email], $subject, $body, $altBody);
    }

    private function buildPopItsReprovadoTemplate(string $tipo, string $titulo, string $versao, string $motivo, $registroId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$tipo} Reprovado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>❌ {$tipo} Reprovado</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - POPs e ITs</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #FEE2E2; border-left: 4px solid #EF4444; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #991B1B;'>
                        <strong>⚠️ Seu documento foi reprovado e precisa de ajustes.</strong>
                    </p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Tipo:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$tipo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Título:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$titulo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Versão:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>v{$versao}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Status:</strong></td>
                            <td style='padding: 10px 0;'><span style='background: #EF4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;'>REPROVADO</span></td>
                        </tr>
                    </table>
                </div>
                
                <div style='background: #FFF7ED; border: 2px solid #F59E0B; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #92400E; margin: 0 0 10px 0; font-size: 16px;'>📝 Motivo da Reprovação:</h3>
                    <p style='margin: 0; color: #78350F; font-size: 14px; line-height: 1.6;'>{$motivo}</p>
                </div>

                <div style='background: #EBF8FF; border: 2px solid #3B82F6; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #1E40AF; margin: 0 0 10px 0; font-size: 16px;'>🔄 Próximos Passos:</h3>
                    <ul style='color: #1E40AF; margin: 0; padding-left: 20px; font-size: 14px;'>
                        <li style='margin: 8px 0;'>Acesse a aba <strong>Meus Registros</strong></li>
                        <li style='margin: 8px 0;'>Clique em <strong>Editar</strong> no registro reprovado</li>
                        <li style='margin: 8px 0;'>Faça as correções necessárias</li>
                        <li style='margin: 8px 0;'>Envie novamente para aprovação</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/pops-e-its' style='background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        ✏️ Editar Documento
                    </a>
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
     * Send exclusão aprovada notification
     */
    public function sendExclusaoAprovadaNotification(string $email, string $titulo, int $protocoloId, string $observacoes = ''): bool
    {
        if (empty($email)) {
            return false;
        }

        $subject = "SGQ - Solicitação de Exclusão Aprovada ✅";
        $body = $this->buildExclusaoAprovadaTemplate($titulo, $protocoloId, $observacoes);
        
        $altBody = "SGQ OTI DJ - POPs e ITs\n\n";
        $altBody .= "Sua solicitação de exclusão foi aprovada!\n\n";
        $altBody .= "Protocolo: #{$protocoloId}\n";
        $altBody .= "Documento: {$titulo}\n\n";
        $altBody .= "O registro foi removido do sistema.\n";
        
        return $this->send([$email], $subject, $body, $altBody);
    }

    private function buildExclusaoAprovadaTemplate(string $titulo, int $protocoloId, string $observacoes): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        $obsHtml = '';
        if (!empty($observacoes)) {
            $obsHtml = "
                <div style='background: #F3F4F6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'><strong>Observações do Avaliador:</strong></p>
                    <p style='margin: 10px 0 0 0; color: #333; font-size: 14px;'>{$observacoes}</p>
                </div>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Exclusão Aprovada</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>✅ Solicitação Aprovada!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - POPs e ITs</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #D1FAE5; border-left: 4px solid #10B981; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #065F46;'>
                        <strong>🎉 Sua solicitação de exclusão foi aprovada e o registro foi removido do sistema!</strong>
                    </p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Protocolo:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>#{$protocoloId}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Documento:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$titulo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Status:</strong></td>
                            <td style='padding: 10px 0;'><span style='background: #10B981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;'>APROVADO</span></td>
                        </tr>
                    </table>
                </div>
                
                {$obsHtml}

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/pops-e-its' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        📋 Acessar POPs e ITs
                    </a>
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
     * Send exclusão reprovada notification
     */
    public function sendExclusaoReprovadaNotification(string $email, string $titulo, int $protocoloId, string $motivo): bool
    {
        if (empty($email)) {
            return false;
        }

        $subject = "SGQ - Solicitação de Exclusão Reprovada ❌";
        $body = $this->buildExclusaoReprovadaTemplate($titulo, $protocoloId, $motivo);
        
        $altBody = "SGQ OTI DJ - POPs e ITs\n\n";
        $altBody .= "Sua solicitação de exclusão foi reprovada.\n\n";
        $altBody .= "Protocolo: #{$protocoloId}\n";
        $altBody .= "Documento: {$titulo}\n";
        $altBody .= "Motivo: {$motivo}\n\n";
        $altBody .= "O registro permanece no sistema.\n";
        
        return $this->send([$email], $subject, $body, $altBody);
    }

    private function buildExclusaoReprovadaTemplate(string $titulo, int $protocoloId, string $motivo): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Exclusão Reprovada</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>❌ Solicitação Reprovada</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - POPs e ITs</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #FEE2E2; border-left: 4px solid #EF4444; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #991B1B;'>
                        <strong>⚠️ Sua solicitação de exclusão foi reprovada. O registro permanece no sistema.</strong>
                    </p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Protocolo:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>#{$protocoloId}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Documento:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$titulo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Status:</strong></td>
                            <td style='padding: 10px 0;'><span style='background: #EF4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;'>REPROVADO</span></td>
                        </tr>
                    </table>
                </div>
                
                <div style='background: #FFF7ED; border: 2px solid #F59E0B; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #92400E; margin: 0 0 10px 0; font-size: 16px;'>📝 Motivo da Reprovação:</h3>
                    <p style='margin: 0; color: #78350F; font-size: 14px; line-height: 1.6;'>{$motivo}</p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/pops-e-its' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        📋 Acessar POPs e ITs
                    </a>
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
    /**
     * Send Fluxogramas pendente notification
     */
    public function sendFluxogramasPendenteNotification(array $emails, string $titulo, string $mensagem, $registroId = null): bool
    {
        if (empty($emails)) {
            return false;
        }

        $subject = "SGQ - Novo Fluxograma Pendente de Aprovação 📋";
        $body = $this->buildFluxogramasPendenteEmailTemplate($titulo, $mensagem, $registroId);
        
        $altBody = "SGQ OTI DJ - Fluxogramas\n\n";
        $altBody .= "$titulo\n\n";
        $altBody .= "$mensagem\n\n";
        $altBody .= "Acesse o sistema para revisar e aprovar/reprovar o registro.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/fluxogramas";
        
        return $this->send($emails, $subject, $body, $altBody);
    }

    private function buildFluxogramasPendenteEmailTemplate(string $titulo, string $mensagem, $registroId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Novo Fluxograma Pendente</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📋 Novo Fluxograma Pendente!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Fluxogramas</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #92400E;'>
                        <strong>⏳ Atenção: Há um novo registro aguardando sua aprovação!</strong>
                    </p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>$titulo</h2>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #374151; font-size: 15px;'>
                        $mensagem
                    </p>
                </div>
                
                <div style='background: #EBF8FF; border: 2px solid #3B82F6; border-radius: 10px; padding: 25px; margin: 25px 0;'>
                    <h3 style='color: #1E40AF; margin: 0 0 15px 0; font-size: 18px;'>🔍 Próximos Passos:</h3>
                    <ul style='color: #1E40AF; margin: 0; padding-left: 20px; font-size: 14px;'>
                        <li style='margin: 8px 0;'>Acesse o sistema SGQ OTI DJ</li>
                        <li style='margin: 8px 0;'>Navegue até <strong>Fluxogramas → Pendente Aprovação</strong></li>
                        <li style='margin: 8px 0;'>Revise o documento cuidadosamente</li>
                        <li style='margin: 8px 0;'>Aprove ou reprove com justificativa</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/fluxogramas' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Acessar Fluxogramas
                    </a>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>
                        <strong>Nota:</strong> Você recebeu este email porque está configurado como aprovador de Fluxogramas no sistema. 
                        Para alterar suas preferências, entre em contato com o administrador do sistema.
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
     * Send Fluxograma aprovado notification
     */
    public function sendFluxogramasAprovadoNotification(string $email, string $titulo, string $versao, $registroId): bool
    {
        if (empty($email)) {
            return false;
        }

        $subject = "SGQ - Fluxograma Aprovado ✅";
        $body = $this->buildFluxogramasAprovadoTemplate($titulo, $versao, $registroId);
        
        $altBody = "SGQ OTI DJ - Fluxogramas\n\n";
        $altBody .= "Parabéns! Seu Fluxograma foi aprovado!\n\n";
        $altBody .= "Título: {$titulo}\n";
        $altBody .= "Versão: v{$versao}\n\n";
        $altBody .= "O documento já está disponível para visualização no sistema.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/fluxogramas";
        
        return $this->send([$email], $subject, $body, $altBody);
    }

    private function buildFluxogramasAprovadoTemplate(string $titulo, string $versao, $registroId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Fluxograma Aprovado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>✅ Fluxograma Aprovado!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Fluxogramas</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #D1FAE5; border-left: 4px solid #10B981; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #065F46;'>
                        <strong>🎉 Parabéns! Seu documento foi aprovado e já está disponível no sistema!</strong>
                    </p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Título:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$titulo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Versão:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>v{$versao}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Status:</strong></td>
                            <td style='padding: 10px 0;'><span style='background: #10B981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;'>APROVADO</span></td>
                        </tr>
                    </table>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/fluxogramas' style='background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Visualizar no Sistema
                    </a>
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
     * Send Fluxograma reprovado notification
     */
    public function sendFluxogramasReprovadoNotification(string $email, string $titulo, string $versao, string $motivo, $registroId): bool
    {
        if (empty($email)) {
            return false;
        }

        $subject = "SGQ - Fluxograma Reprovado ❌";
        $body = $this->buildFluxogramasReprovadoTemplate($titulo, $versao, $motivo, $registroId);
        
        $altBody = "SGQ OTI DJ - Fluxogramas\n\n";
        $altBody .= "Seu Fluxograma foi reprovado.\n\n";
        $altBody .= "Título: {$titulo}\n";
        $altBody .= "Versão: v{$versao}\n\n";
        $altBody .= "Motivo: {$motivo}\n\n";
        $altBody .= "Por favor, revise e envie uma nova versão.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/fluxogramas";
        
        return $this->send([$email], $subject, $body, $altBody);
    }

    private function buildFluxogramasReprovadoTemplate(string $titulo, string $versao, string $motivo, $registroId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Fluxograma Reprovado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>❌ Fluxograma Reprovado</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Fluxogramas</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #FEE2E2; border-left: 4px solid #EF4444; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #991B1B;'>
                        <strong>⚠️ Seu documento foi reprovado e precisa ser revisado.</strong>
                    </p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Título:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$titulo}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Versão:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>v{$versao}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>Status:</strong></td>
                            <td style='padding: 10px 0;'><span style='background: #EF4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;'>REPROVADO</span></td>
                        </tr>
                    </table>
                </div>
                
                <div style='background: #FEE2E2; border: 2px solid #EF4444; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #991B1B; margin: 0 0 10px 0; font-size: 16px;'>📝 Motivo da Reprovação:</h3>
                    <p style='margin: 0; color: #7F1D1D; font-size: 14px; line-height: 1.6;'>
                        {$motivo}
                    </p>
                </div>
                
                <div style='background: #EBF8FF; border: 2px solid #3B82F6; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #1E40AF; margin: 0 0 10px 0; font-size: 16px;'>🔄 Próximos Passos:</h3>
                    <ul style='color: #1E40AF; margin: 0; padding-left: 20px; font-size: 14px;'>
                        <li style='margin: 8px 0;'>Revise o documento com base no motivo da reprovação</li>
                        <li style='margin: 8px 0;'>Faça as correções necessárias</li>
                        <li style='margin: 8px 0;'>Envie uma nova versão através do sistema</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/fluxogramas' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        📝 Acessar Sistema
                    </a>
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
     * Send RC novo notification para administradores
     */
    public function sendRcNovoNotification(array $emails, string $numeroRegistro, array $rcData): bool
    {
        if (empty($emails)) {
            return false;
        }

        $subject = "SGQ - Nova Reclamação Cadastrada 📋 {$numeroRegistro}";
        $body = $this->buildRcNovoEmailTemplate($numeroRegistro, $rcData);
        
        $altBody = "SGQ OTI DJ - Registro de Reclamação\n\n";
        $altBody .= "Nova Reclamação Cadastrada: {$numeroRegistro}\n\n";
        $altBody .= "Data: " . date('d/m/Y', strtotime($rcData['data_abertura'])) . "\n";
        $altBody .= "Origem: {$rcData['origem']}\n";
        $altBody .= "Cliente: {$rcData['cliente_nome']}\n";
        $altBody .= "Categoria: {$rcData['categoria']}\n\n";
        $altBody .= "Acesse o sistema para mais detalhes.\n";
        $altBody .= "Link: " . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "/controle-de-rc";
        
        return $this->send($emails, $subject, $body, $altBody);
    }

    private function buildRcNovoEmailTemplate(string $numeroRegistro, array $rcData): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Nova Reclamação Cadastrada</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📋 Nova Reclamação Cadastrada!</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Registro de Reclamação</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='background: #EBF8FF; border-left: 4px solid #3B82F6; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; font-size: 16px; color: #1E40AF;'>
                        <strong>🔔 Um novo Registro de Reclamação (RC) foi cadastrado no sistema!</strong>
                    </p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>{$numeroRegistro}</h2>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>📅 Data de Abertura:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>" . date('d/m/Y', strtotime($rcData['data_abertura'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>📍 Origem:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['origem']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>👤 Cliente/Empresa:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['cliente_nome']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>📂 Categoria:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['categoria']}</td>
                        </tr>";
        
        if (!empty($rcData['qual_produto'])) {
            $return .= "
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>📦 Produto:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['qual_produto']}</td>
                        </tr>";
        }
        
        if (!empty($rcData['numero_serie'])) {
            $return .= "
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>🔢 Nº Série:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['numero_serie']}</td>
                        </tr>";
        }
        
        if (!empty($rcData['fornecedor_nome'])) {
            $return .= "
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>🏢 Fornecedor:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['fornecedor_nome']}</td>
                        </tr>";
        }
        
        $return .= "
                        <tr>
                            <td style='padding: 10px 0; color: #666; font-size: 14px;'><strong>👨‍💼 Cadastrado por:</strong></td>
                            <td style='padding: 10px 0; color: #333; font-size: 14px;'>{$rcData['usuario_nome']}</td>
                        </tr>
                    </table>
                </div>";
        
        if (!empty($rcData['detalhamento'])) {
            $return .= "
                <div style='background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                    <p style='margin: 0; color: #92400E; font-size: 14px;'>
                        <strong>📝 Detalhamento:</strong><br>
                        " . nl2br(htmlspecialchars($rcData['detalhamento'])) . "
                    </p>
                </div>";
        }
        
        $return .= "
                <div style='background: #DBEAFE; border: 2px solid #3B82F6; border-radius: 10px; padding: 25px; margin: 25px 0;'>
                    <h3 style='color: #1E40AF; margin: 0 0 15px 0; font-size: 18px;'>🔍 Ações Necessárias:</h3>
                    <ul style='color: #1E40AF; margin: 0; padding-left: 20px; font-size: 14px;'>
                        <li style='margin: 8px 0;'>Acesse o sistema SGQ OTI DJ</li>
                        <li style='margin: 8px 0;'>Navegue até <strong>Gestão da Qualidade → Controle de RC</strong></li>
                        <li style='margin: 8px 0;'>Revise os detalhes do registro</li>
                        <li style='margin: 8px 0;'>Acompanhe o status e evidências</li>
                    </ul>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/controle-de-rc' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Acessar Registro de Reclamações
                    </a>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>
                        <strong>Nota:</strong> Você recebeu este email porque está configurado como administrador do sistema. 
                        Esta é uma notificação automática para manter você informado sobre novas reclamações cadastradas.
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
        
        return $return;
    }
    
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
