<?php
$email = strtolower(trim($_POST['email']));
$usuario = mb_convert_case(trim($_POST['usuario']), MB_CASE_TITLE, "UTF-8");
$senha = $_POST['senha'];
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

//validação de email

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("ERRO: Email inválido");
}


try {
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn->setAttribute(
      PDO::ATTR_ERRMODE,
      PDO::ERRMODE_EXCEPTION  
    );

    $sqlSelect = "SELECT email FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sqlSelect);
    $stmt->execute([
        'email' => $email
    ]);
    $emailExiste = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($emailExiste) {
        header("Location: ../tela_login/index.html?aviso=email-existente");
        exit;
    }
    
    $sqlInsert = "INSERT INTO usuarios (email, usuario, senha) VALUES (:email, :usuario, :senha)";

    $stmt = $conn->prepare($sqlInsert);

    $result = $stmt->execute([
        'email' => $email,
        'usuario' => $usuario,
        'senha' => $senhaHash
    ]);
    if ($result) {
        header("Location: ../tela_bemvindo/index.html");
        exit;
        }      
} catch (Exception $e) {
    echo 'Possível erro de Excessão: ', $e->getMessage(), "\n";
}
$conn = null;