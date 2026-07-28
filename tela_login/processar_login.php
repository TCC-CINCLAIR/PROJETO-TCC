<?php
$email = strtolower(trim($_POST['email']));
$senha = $_POST['senha'];

try {
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn -> setAttribute (
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $sql = "SELECT email, senha FROM usuarios WHERE email = :email";

    $stmt = $conn -> prepare ($sql);

    $stmt -> execute ([
        'email' => $email,
    ]);

    $usuario = $stmt -> fetch (PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        header ("Location: ../agenda_tarefas/index.html");
        exit;
    }
    else {
        header ("Location: ./index.html?aviso=dados-invalidos");
        exit;
    }
}
catch (Exception $e) {
    echo 'Erro encontrado!', $e -> getMessage(), "\n";
}
$conn = null;