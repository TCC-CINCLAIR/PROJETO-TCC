<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
    header("Location: ../../tela_login/index.html?aviso=acesso-negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

try {
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn -> setAttribute (
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
    );

    $inicio = $_POST['inicio'] ?? '';
    $usuario_id = $_SESSION['sessao_id'];

    if (!is_string($inicio)) {
        header("Location: index.php");
        exit;
    }

    $inicio = trim($inicio);

    if (!in_array($inicio, ['pergunta', 'sugestoes', 'sozinho'], true)) {
        header("Location: index.php");
        exit;
    }

    $sql = "UPDATE preferencias_usuario SET inicio_chat = :inicio, atualizado_em = NOW() WHERE usuario_id = :sessao_id";

    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'inicio' => $inicio,
    ]);

    if ($stmt -> rowCount() === 0) {
        header("Location: ../pergunta1/index.php");
        exit;
    }

    if ($result) {
        header("Location: ../pergunta7/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>
