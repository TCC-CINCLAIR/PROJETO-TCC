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

    $preferencias = $_POST['preferencias'] ?? '';
    $usuario_id = $_SESSION['sessao_id'];

    if (!is_string($preferencias)) {
        header("Location: index.php");
        exit;
    }

    $preferencias = trim($preferencias);

    if (mb_strlen($preferencias, 'UTF-8') > 1000) {
        header("Location: index.php");
        exit;
    }

    $sql = "UPDATE preferencias_usuario SET observacao = :preferencias, atualizado_em = NOW() WHERE usuario_id = :sessao_id";

    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'preferencias' => $preferencias,
    ]);

    if ($stmt -> rowCount() === 0) {
        header("Location: ../pergunta1/index.php");
        exit;
    }

    if ($result) {
        header("Location: ../conclusao/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>
