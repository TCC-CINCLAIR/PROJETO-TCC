<?php
$email = $_POST['email'];
$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

try{
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn->setAttribute(
      PDO::ATTR_ERRMODE,
      PDO::ERRMODE_EXCEPTION  
    );
    $sql = "INSERT INTO usuarios (email, usuario, senha) VALUES (:email, :usuario, :senha)";

    $stmt = $conn->prepare($sql);

    $result = $stmt->execute([
        'email' => $email,
        'usuario' => $usuario,
        'senha' => $senha
    ]);
    if ($result){
        header("Location: ../tela_bemvindo/index.html");
    }
}
catch(Exception $e){
    echo 'Possível erro de Excessão: ', $e->getMessage(), "\n";
}

$conn=null;