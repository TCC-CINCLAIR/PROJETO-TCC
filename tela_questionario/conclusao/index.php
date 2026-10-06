<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
    header("Location: ../../tela_login/index.html?aviso=acesso-negado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário concluído | Cinclair</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../assets/css/responsivo.css">
    <link rel="stylesheet" href="../../modo_escuro.css">
</head>
<body class="pagina-conclusao">
    <main class="caixa-questionario" aria-labelledby="titulo-conclusao">
        <div class="informacoes-progresso">
            <p>Questionário concluído</p>
            <span>100% completo</span>
        </div>
        <div class="barra-progresso" role="progressbar" aria-label="Progresso do questionário" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100">
            <div class="progresso-7"></div>
        </div>
        <section class="mensagem-conclusao">
            <div class="icone-conclusao" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" focusable="false">
                    <path d="M13 24L21 32L36 17" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <p class="etiqueta">Tudo pronto para continuar</p>
            <h1 id="titulo-conclusao">Seu jeito de conversar<br>começa aqui.</h1>
            <p class="dica">Obrigado por compartilhar suas preferências. Você concluiu o questionário e deu mais um passo na sua jornada com a Cinclair.</p>
        </section>
        <div class="proximo-passo">
            <h2>E agora?</h2>
            <p>Acesse seu perfil para continuar explorando a plataforma, organizar suas tarefas e agendar consultas.</p>
        </div>
        <div class="botoes">
            <a class="btn-proxima" href="../../tela_perfil/index.php">Ir para meu perfil <span aria-hidden="true">&rarr;</span></a>
        </div>
        <p class="rodape">Um passo de cada vez, no seu ritmo.</p>
    </main>
    <script src="../../modo_escuro.js"></script>
</body>
</html>
