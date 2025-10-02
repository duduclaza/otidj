<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Config SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'djsgqoti@sgqoti.com.br';
    $mail->Password   = 'Pandora@1989';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port       = 465;

    // Remetente e destinatário
    $mail->setFrom('djsgqoti@sgqoti.com.br', 'SGQ OTI DJ');
    $mail->addAddress('seuemail@gmail.com'); // troque pelo seu email para teste

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Teste PHPMailer + Hostinger';
    $mail->Body    = 'Se você recebeu este e-mail, sua configuração SMTP está ok!';

    $mail->send();
    echo '✅ E-mail enviado com sucesso!';
} catch (Exception $e) {
    echo "❌ Erro ao enviar: {$mail->ErrorInfo}";
}
