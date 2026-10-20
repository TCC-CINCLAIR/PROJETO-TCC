<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

function enviarEmailRecuperacao(string $destinatario, string $codigo): void
{
    $pastaXampp = dirname(__DIR__, 3);
    $config = require $pastaXampp . '/config-tcc/email.php';

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['email'];
    $mail->Password = $config['senha_app'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Timeout = 20;
    $mail->CharSet = 'UTF-8';

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ];

    if (!empty($config['cafile'])) {
        $mail->SMTPOptions['ssl']['cafile'] = $config['cafile'];
    }

    $mail->setFrom($config['email'], 'Cinclair');
    $mail->addAddress($destinatario);

    $mail->Subject = 'Código para redefinir sua senha';
    $mail->Body =
        "Seu código de recuperação é: {$codigo}\n\n" .
        "Ele expira em 10 minutos.\n\n" .
        "Se você não solicitou a redefinição, ignore este e-mail.";

    $mail->send();
}