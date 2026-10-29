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

    $tom = $_POST['tom'] ?? '';
    $usuario_id = $_SESSION['sessao_id'];

    if (!is_string($tom)) {
        header("Location: index.php");
        exit;
    }

    $tom = trim($tom);

    if (!in_array($tom, ['direto', 'informal', 'formal', 'sem_preferencia'], true)) {
        header("Location: index.php");
        exit;
    }

    $sql = "UPDATE preferencias_usuario SET tom_conversa = :tom, atualizado_em = NOW() WHERE usuario_id = :sessao_id";

    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'tom' => $tom,
    ]);

    if ($stmt -> rowCount() === 0) {
        header("Location: ../pergunta1/index.php");
        exit;
    }

    if ($result) {
        header("Location: ../pergunta4/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>
