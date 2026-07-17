<?php
$email = $_POST['email'];
$senha = $_POST['senha'];

try{
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn ->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $sql = "SELECT :email, :senha FROM usuarios";

    $stmt = $conn->prepare($sql);

    $result = $stmt->execute([
        'email' => $email,
        'senha' => $senha
    ]);
    if($result){
        header("Location: ../agenda_tarefas/index.html");
    }
    else{
        header("Location: ../tela_cadastro/index.html");
    }
}
catch(Exception $e){
    echo 'Erro encontrado!', $e->getMessage(), "\n";
}
$conn=null;