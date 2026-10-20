<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
    header('Location: ../tela_login/index.html?aviso=acesso-negado');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notificações</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="pagina-interna pagina-notificacoes">

    <header>
        <nav class="top-menu" aria-label="Navegação principal">
            <a href="#" class="menu-btn">
                <img src="../imagens/imagens_navbar/Config.png" alt="">
                Dashboard
            </a>
            <a href="../tela_chatbot/index.php" class="menu-btn">
                <img src="../imagens/imagens_navbar/Chat.png" alt="">
                Chatbot
            </a>

            <a href="../tela_perfil/index.php" class="menu-btn-active">
                <img src="../imagens/imagens_navbar/User.png" alt="">
                Perfil
            </a>

            <a href="../agenda_tarefas/index.php" class="menu-btn">
                <img src="../imagens/imagens_navbar/Calendário.png" alt="">
                Agenda
            </a>

            <a href="../tela_consulta/index.php" class="menu-btn">
                <img src="../imagens/imagens_navbar/Relógio.png" alt="">
                Consultas
            </a>
        </nav>
    </header>

    <main class="moldura-notificacoes">
        <section class="cartao-notificacoes" aria-labelledby="titulo">
            <a
                href="../tela_perfil/index.php"
                class="fechar"
                aria-label="Voltar ao perfil"
            >
                <span aria-hidden="true">×</span>
            </a>

            <h1 id="titulo">Notificações</h1>

            <div class="opcao-notificacao">
                <div class="icone-consulta" aria-hidden="true">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 6v6l4 3"></path>
                    </svg>
                </div>
            

                <div class="texto-notificacao">
                    <label for="notificacoes-consultas">Consultas</label>
                    <p id="descricao-consultas">
                        Receba um lembrete 10 minutos antes da consulta.
                    </p>
                </div>

                <label class="interruptor">
                    <input
                        type="checkbox"
                        id="notificacoes-consultas"
                        role="switch"
                        aria-describedby="descricao-consultas"
                    >
                    <span class="trilho" aria-hidden="true"></span>
                </label>
            </div>
            <p
                id="status-notificacoes"
                class="orientacao"
                role="status"
                aria-live="polite"
            >
                Carregando preferência...
            </p>

            <p class="orientacao">
                No horário do lembrete, confira o e-mail da consulta
                para acessar o link do Zoom.
            </p>
            <section class="historico-notificacoes" aria-labelledby="titulo-historico">
                <h2 id="titulo-historico">Seus lembretes</h2>

                <div id="lista-notificacoes">
                    <p>Carregando lembretes...</p>
                </div>
            </section>
            
        </section>
    </main>

    <script src="../modo_escuro.js"></script>
    <script src="../lembretes.js?v=1"></script>
</body>
</html>