<?php

namespace App\Controllers;

use App\Services\EmailService;

class EmailController
{
    private EmailService $emailService;
    
    public function __construct()
    {
        $this->emailService = new EmailService();
    }
    
    /**
     * Test email configuration
     */
    public function testConnection()
    {
        header('Content-Type: application/json');
        
        $result = $this->emailService->testConnection();
        echo json_encode($result);
    }
    
    /**
     * Send test email
     */
    public function sendTest()
    {
        header('Content-Type: application/json');
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email é obrigatório'
            ]);
            return;
        }
        
        $subject = 'Teste de Email - SGQ OTI DJ';
        $body = $this->buildTestEmailTemplate();
        
        $success = $this->emailService->send($email, $subject, $body);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Email de teste enviado com sucesso!' : 'Erro ao enviar email de teste'
        ]);
    }
    
    private function buildTestEmailTemplate(): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Teste de Email</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>SGQ OTI DJ</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>Sistema de Gestão da Qualidade</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: #10B981; color: white; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 16px;'>
                        ✅ TESTE DE EMAIL
                    </div>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Configuração de Email Funcionando!</h2>
                
                <p style='font-size: 16px; margin: 20px 0;'>
                    Parabéns! Se você está recebendo este email, significa que a configuração SMTP do sistema SGQ OTI DJ está funcionando corretamente.
                </p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #333; margin-top: 0;'>Configurações Utilizadas:</h3>
                    <ul style='color: #666;'>
                        <li><strong>Servidor SMTP:</strong> smtp.hostinger.com</li>
                        <li><strong>Porta:</strong> 465</li>
                        <li><strong>Criptografia:</strong> SSL</li>
                        <li><strong>Email:</strong> djsgqoti@sgqoti.com.br</li>
                    </ul>
                </div>
                
                <p style='color: #666; font-size: 14px; margin: 20px 0;'>
                    O sistema agora pode enviar notificações automáticas para:
                </p>
                
                <ul style='color: #666; font-size: 14px;'>
                    <li>Aprovação/Reprovação de amostragens</li>
                    <li>Registro de novos retornados</li>
                    <li>Alertas e notificações do sistema</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . ($_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br') . "' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                        Acessar Sistema SGQ
                    </a>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='margin: 0; color: #666; font-size: 12px;'>
                    © " . date('Y') . " SGQ OTI DJ - Sistema de Gestão da Qualidade<br>
                    Email de teste enviado em " . date('d/m/Y H:i:s') . "
                </p>
            </div>
        </body>
        </html>";
    }
}
