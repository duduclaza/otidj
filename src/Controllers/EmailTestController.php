<?php

namespace App\Controllers;

use App\Services\EmailService;

class EmailTestController
{
    public function test(): void
    {
        // Garantir JSON puro
        while (ob_get_level()) { ob_end_clean(); }
        ini_set('display_errors', '0');
        error_reporting(0);
        header('Content-Type: application/json');

        try {
            $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';
            if ($to === '') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Parâmetro ?to= é obrigatório (email de destino)'
                ]);
                exit;
            }

            $service = new EmailService();

            $subject = 'Teste SMTP - SGQ';
            $body = '<strong>Teste de envio SMTP</strong><br>Se você recebeu este email, a configuração está OK.';
            $altBody = 'Teste de envio SMTP - Se você recebeu este email, a configuração está OK.';

            $ok = $service->send($to, $subject, $body, $altBody);

            if ($ok) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Email enviado com sucesso para: ' . $to
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Falha ao enviar email',
                    'error' => $service->getLastError()
                ]);
            }
            exit;
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro inesperado ao enviar email',
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
}
