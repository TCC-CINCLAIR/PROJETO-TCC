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

    $apoio = $_POST['apoio'] ?? '';
    $usuario_id = $_SESSION['sessao_id'];

    if (!is_string($apoio)) {
        header("Location: index.php");
        exit;
    }

    $apoio = trim($apoio);

    if (!in_array($apoio, ['breve', 'confirmacao', 'sem_preferencia'], true)) {
        header("Location: index.php");
        exit;
    }

    $sql = "UPDATE preferencias_usuario SET reacao_chat = :apoio, atualizado_em = NOW() WHERE usuario_id = :sessao_id";

    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'apoio' => $apoio,
    ]);

    if ($stmt -> rowCount() === 0) {
        header("Location: ../pergunta1/index.php");
        exit;
    }

    if ($result) {
        header("Location: ../pergunta5/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>
