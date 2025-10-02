<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'djsgqoti@sgqoti.com.br';
    $mail->Password   = 'Pandora@1989';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('djsgqoti@sgqoti.com.br', 'SGQ OTI DJ');
    $mail->addAddress('seuemail@gmail.com'); // troque pelo seu email

    $mail->isHTML(true);
    $mail->Subject = 'Teste PHPMailer + Hostinger';
    $mail->Body    = 'Funcionou! Se você recebeu este email, está OK.';

    $mail->send();
    echo '✅ E-mail enviado com sucesso!';
} catch (Exception $e) {
    echo "❌ Erro ao enviar: {$mail->ErrorInfo}";
}
