<?php
session_start();

try {
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn -> setAttribute (
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
    );

    $apelido = trim($_POST['apelido'] ?? '');
    $usuario_id = $_SESSION['sessao_id'];

    if ($apelido == '') {
        header("Location: index.php");
        exit;
    }
    
    $sql = "INSERT INTO preferencias_usuario (usuario_id, apelido) VALUES (:sessao_id, :apelido)";
 
    $stmt = $conn -> prepare($sql);

    $result = $stmt -> execute([
        'sessao_id' => $usuario_id,
        'apelido' => $apelido,
    ]);
    
    if ($result) {
        header("Location: ../pergunta2/index.php");
        exit;
    }
}catch (Exception $e){
    die($e);
}
$conn = null;
?>