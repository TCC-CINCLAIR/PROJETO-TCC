<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuário</title>
    <link rel="stylesheet" href="./style.css">
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
            <div class="img-perfil"><img src="../imagens/imagens_navbar/User.png" alt="Imagem Usuário"></div>
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
