<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}

try {
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn -> setAttribute(
      PDO::ATTR_ERRMODE,
      PDO::ERRMODE_EXCEPTION  
    );

    $sql = "SELECT usuario, email FROM usuarios WHERE id = :id";
    $stmt = $conn -> prepare($sql);
    $stmt -> execute([
        'id' => $_SESSION['usuario_id']
    ]);
    $dadosUsuario = $stmt -> fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die($e);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuário</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="top-menu">

            <a href="" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</button></a>

            <a href="" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</button></a>

            <a href="" class="menu-btn-active"><button ><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</button></a>

            <a href="../agenda_tarefas/index.php" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</button></a>
      
            <a href="../tela_consulta/index.html" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</button></a>

        </nav>
    </header>
    <main>
        <section class="info-usuario">
            <section class="saudacoes">
                <div class="img-perfil"><img src="../imagens/imagens_navbar/User.png" alt="Imagem Usuário"></div>
                <p>Olá, <?= htmlspecialchars($dadosUsuario['usuario'], ENT_QUOTES, 'UTF-8') ?></p>
            </section>
            <section class="dados-usuario">
                <label for="nome">Nome</label><input type="text" value="<?= htmlspecialchars($dadosUsuario['usuario'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="nome">Email</label><input type="email" value="<?= htmlspecialchars($dadosUsuario['email'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="nome">Senha</label><input type="password" value="&bull">
                <div class="btn-redefinir">
                    <a href="../Redefinir_senha/index.html"><button class="btn-redefinir-senha">Redefinir Senha</button></a>
                </div>
            </section>
        </section>
        <section class="opcoes-sistema">
            <h2>Configurações</h2>
            <section class="aba-botoes">
                <a href="#">Relatórios</a>
                <a href="#">Preferências</a>
                <a href="#">Notificações</a>
                <a href="#">Privacidade de Segurança</a>
            </section>
            <div class="logout">
                <a href="../tela_login/index.html"><button class="btn-logout"> &rarr;]  Sair da conta</button></a>
            </div>
        </section>
    </main>
