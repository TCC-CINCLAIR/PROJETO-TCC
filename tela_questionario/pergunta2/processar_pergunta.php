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

    $faixa_etaria = $_POST['faixa_etaria'] ?? '';
    $usuario_id = $_SESSION['sessao_id'];

    if (!is_string($faixa_etaria)) {
        header("Location: index.php");
        exit;
    }

    $faixa_etaria = trim($faixa_etaria);

    if (!in_array($faixa_etaria, ['menos_12', '12_17', '18_29', '30_59', '60_mais', 'nao_informar'], true)) {
        header("Location: index.php");
        exit;
    }

    $sql = "UPDATE preferencias_usuario SET faixa_etaria = :faixa_etaria, atualizado_em = NOW() WHERE usuario_id = :sessao_id";

    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'faixa_etaria' => $faixa_etaria,
    ]);

    if ($stmt -> rowCount() === 0) {
        header("Location: ../pergunta1/index.php");
        exit;
    }

    if ($result) {
        header("Location: ../pergunta3/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>
