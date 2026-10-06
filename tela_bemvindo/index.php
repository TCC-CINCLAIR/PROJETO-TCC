<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
  header("Location: ../tela_login/index.html?aviso=acesso-negado");
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-Vindo!</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
</head>
<body class="pagina-bemvindo">
    
    <main>
        <header>
        <img class="img-logo" src="../imagens/imagens_cadastro/logo-login.png" alt="Logo Cinclair">
        <h2>Bem-vindo ao Cinclair</h2>
        <p>Sua jornada de autoconhecimento começa aqui.</p>
        </header>

            <div class="img-msg"><img src="../imagens/imagens_bemvindo/mensagens.svg" alt=""></div>
            <section class="txt-um">
                <h3>Funcionalidades Inteligentes</h3>
                <p>
                    Nosso chatbot personalizado está disponível 24/7 para te ajudar em sua jornada. Agende tarefas, consultas e acompanhe seu progresso através de relatórios detalhados.
                </p>
            </section>

            <div class="img-digital"><img src="../imagens/imagens_bemvindo/digital.svg" alt=""></div>
            <section class="txt-dois">
                <h3>Sobre a Empresa e Objetivos</h3>
                <p>
                    A Cinclair MindClare foi criado com o objetivo de tornar o cuidado mental mais acessível e personalizado. Combinamos tecnologia de ponta com empatia para oferecer suporte contínuo ao seu bem-estar emocional e desenvolvimento pessoal.
                </p>
            </section>
            
            <div class="proximo-passo">
                <p>
                    <strong>Próximo passo:</strong>
                    Vamos conhecer você melhor! Responda a um breve questionário sobre sua personalidade para que possamos personalizar sua experiência e oferecer o melhor suporte.
                </p>
            </div>
            
            <a class="continuar" href="../tela_questionario/pergunta1/index.php">
                Continuar &rarr;
            </a>
    </main>
    <script src="../modo_escuro.js"></script>
</body>
</html>
