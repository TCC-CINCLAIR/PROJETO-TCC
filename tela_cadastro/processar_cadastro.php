<?php
session_start();
$email = strtolower(trim($_POST['email']));
$usuario = mb_convert_case(trim($_POST['usuario']), MB_CASE_TITLE, "UTF-8");
$senha = $_POST['senha'];
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);



if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("ERRO: Email inválido");
}


try {
    require __DIR__ . '/../conexao.php';

    $sqlSelect = "SELECT email FROM usuarios WHERE email = :email";
    $stmt = $conn -> prepare ($sqlSelect);
    $stmt -> execute ([
        'email' => $email
    ]);
    $emailExiste = $stmt ->  fetch (PDO::FETCH_ASSOC);
    if ($emailExiste) {
        header("Location: ../tela_login/index.html?aviso=email-existente");
        exit;
    }

    $sqlInsert = "INSERT INTO usuarios (email, usuario, senha) VALUES (:email, :usuario, :senha) RETURNING id";

    $stmt = $conn -> prepare($sqlInsert);

    $result = $stmt -> execute([
        'email' => $email,
        'usuario' => $usuario,
        'senha' => $senhaHash
    ]);
    if ($result) {
        $usuarioId = $stmt->fetchColumn();
        if ($usuarioId === false) {
            throw new RuntimeException('Cadastro sem identificador de usuário.');
        }

        session_regenerate_id(true);
        $_SESSION = ['sessao_id' => $usuarioId];
        session_write_close();
        header ("Location: ../tela_bemvindo/index.php");
        exit;
        }      
} catch (Exception $e) {
    echo 'Possível erro de Excessão: ', $e -> getMessage(), "\n";
}
$conn = null;
?>
