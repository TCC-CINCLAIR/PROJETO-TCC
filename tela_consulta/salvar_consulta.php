<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'] ?? $_SESSION['sessao_id'] ?? $_SESSION['id_usuario'] ?? null; 
    $profissional = $_POST['profissional'] ?? '';
    $dataConsulta = $_POST['data'] ?? $_POST['data_consulta'] ?? ''; 
    $horario = $_POST['horario'] ?? ''; 
    $observacoes = $_POST['observacoes'] ?? '';

    if (!$id_usuario || !$profissional || !$dataConsulta || !$horario) {
        echo json_encode([
            "sucesso" => false, 
            "mensagem" => "Preencha todos os campos obrigatórios."
        ]);
        exit;
    }

    $accountId = 'cCRrC1mUSyC8EHm8NRDMzA';
    $clientId = 'WQtHlL3fT1OfZ4aeq_g4w';
    $clientSecret = 'Uerhj08OzEHfB1UtXA99L49v4hwOwcZB';

    $zoomLink = '';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=" . $accountId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'] ?? null;

    if ($accessToken) {
        $dataIso = date('c', strtotime("$dataConsulta $horario"));

        $dadosZoom = [
            "topic" => "Consulta Cinclair - " . $profissional,
            "type" => 2,
            "start_time" => $dataIso,
            "duration" => 60,
            "timezone" => "America/Sao_Paulo",
            "settings" => [
                "host_video" => true,
                "participant_video" => true,
                "join_before_host" => false
            ]
        ];

        $chZoom = curl_init();
        curl_setopt($chZoom, CURLOPT_URL, "https://api.zoom.us/v2/users/me/meetings");
        curl_setopt($chZoom, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chZoom, CURLOPT_POST, true);
        curl_setopt($chZoom, CURLOPT_POSTFIELDS, json_encode($dadosZoom));
        curl_setopt($chZoom, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json"
        ]);
        $responseZoom = curl_exec($chZoom);
        curl_close($chZoom);

        $zoomResult = json_decode($responseZoom, true);
        $zoomLink = $zoomResult['join_url'] ?? '';
    }

    if (empty($zoomLink)) {
        $zoomLink = "Link não gerado";
    }

    try {
        $sql = "INSERT INTO marcar_consulta (id_usuario, profissional, data_consulta, horario, observacoes, zoom_link, email_enviado) 
                VALUES (:id_usuario, :profissional, :data_consulta, :horario, :observacoes, :zoom_link, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':profissional' => $profissional,
            ':data_consulta' => $dataConsulta,
            ':horario' => $horario,
            ':observacoes' => $observacoes,
            ':zoom_link' => $zoomLink
        ]);

        
        $stmtUser = $conn->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
        $stmtUser->execute([$id_usuario]);
        $usuarioData = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($usuarioData && !empty($usuarioData['email'])) {
            $nomeUsuario = $usuarioData['usuario'];
            $emailUsuario = $usuarioData['email'];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'cinclairzoom@gmail.com'; 
                $mail->Password = 'dnlqbeuqbmzcyasy'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('cinclairzoom@gmail.com', 'Cinclair');
                $mail->addAddress($emailUsuario, $nomeUsuario);

                $mail->isHTML(true);
                $mail->Subject = 'Consulta Agendada com Sucesso - Cinclair';
                $mail->Body    = "Olá, <b>{$nomeUsuario}</b>.<br><br>"
                               . "Sua consulta com <b>{$profissional}</b> foi agendada com sucesso para o dia <b>" . date('d/m/Y', strtotime($dataConsulta)) . "</b> às <b>{$horario}</b>.<br><br>"
                               . "Acesse o link do Zoom para participar no horário marcado: <br>"
                               . "<a href='{$zoomLink}' target='_blank'>{$zoomLink}</a><br><br>"
                               . "Atenciosamente,<br>Equipe Cinclair";

                $mail->send();
            } catch (Exception $e) {
            }
        }

        echo json_encode(["sucesso" => true, "mensagem" => "Consulta agendada com sucesso e e-mail enviado!"]);

    } catch (PDOException $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro no Banco de Dados: " . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro Geral: " . $e->getMessage()]);
    }
}
?>