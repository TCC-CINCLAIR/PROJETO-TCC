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
    require __DIR__ . '/../../conexao.php';

    $perguntas = $_POST['perguntas'] ?? '';
    $usuario_id = $_SESSION['sessao_id'];

    if (!is_string($perguntas)) {
        header("Location: index.php");
        exit;
    }

    $perguntas = trim($perguntas);

    if (!in_array($perguntas, ['quando_util', 'quando_pedir', 'nunca'], true)) {
        header("Location: index.php");
        exit;
    }

    $sql = "UPDATE preferencias_usuario SET pergunta_chat = :perguntas, atualizado_em = NOW() WHERE usuario_id = :sessao_id";

    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'perguntas' => $perguntas,
    ]);

    if ($stmt -> rowCount() === 0) {
        header("Location: ../pergunta1/index.php");
        exit;
    }

    if ($result) {
        header("Location: ../pergunta6/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>
