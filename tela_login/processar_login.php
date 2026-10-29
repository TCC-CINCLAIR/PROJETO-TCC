<?php
session_start();

$email = strtolower(trim($_POST['email']));
$senha = $_POST['senha'];

try {
    require __DIR__ . '/../conexao.php';

    $sql = "SELECT id, email, senha FROM usuarios WHERE email = :email";

    $stmt = $conn -> prepare ($sql);

    $stmt -> execute ([
        'email' => $email,
    ]);

    $usuario = $stmt -> fetch (PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['sessao_id'] = $usuario['id'];
        header ("Location: ../tela_perfil/index.php");
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
?>