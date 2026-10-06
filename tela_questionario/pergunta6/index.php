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
    <title>Pergunta 6 | Cinclair</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../assets/css/responsivo.css">
    <link rel="stylesheet" href="../../modo_escuro.css">
</head>
<body class="pagina-questionario">
    <div class="caixa-questionario">
        <!-- Progresso desta página -->
        <div class="informacoes-progresso">
            <p>Pergunta 6 de 7</p>
            <span>71% completo</span>
        </div>
        <div class="barra-progresso">
            <div class="progresso-5"></div>
        </div>

        <!-- Envia a resposta para o processamento -->
        <form id="questionario" action="processar_pergunta.php" method="post">
            <h1 id="titulo-pergunta">Quando não souber por onde começar, o que seria mais útil?</h1>
            <div class="opcoes" role="group" aria-labelledby="titulo-pergunta">
                <label class="opcao">
                    <input type="radio" name="inicio" value="pergunta" required>
                    <span>Uma pergunta simples sobre o meu dia</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="inicio" value="sugestoes" required>
                    <span>Sugestões de assuntos, como rotina, trabalho, estudos ou relações pessoais</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="inicio" value="sozinho" required>
                    <span>Prefiro começar por conta própria</span>
                </label>
            </div>

            <p id="aviso" class="mensagem-erro" role="alert"></p>
            <div class="botoes">
                <a class="btn-voltar" href="../pergunta5/index.php">Voltar</a>
                <button class="btn-proxima" type="submit">Próxima</button>
            </div>
        </form>
        <noscript>Ative o JavaScript para guardar suas respostas ao mudar de página.</noscript>
    </div>
    <script src="../../modo_escuro.js"></script>
</body>
</html>
