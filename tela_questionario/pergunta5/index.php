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
    <title>Pergunta 5 | Cinclair</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../assets/css/responsivo.css">
    <link rel="stylesheet" href="../../modo_escuro.css">
</head>
<body class="pagina-questionario">
    <div class="caixa-questionario">

        <div class="informacoes-progresso">
            <p>Pergunta 5 de 7</p>
            <span>57% completo</span>
        </div>
        <div class="barra-progresso">
            <div class="progresso-4"></div>
        </div>


        <form id="questionario" action="processar_pergunta.php" method="post">
            <h1 id="titulo-pergunta">O assistente pode fazer perguntas para ajudar a completar seu relato?</h1>
            <div class="opcoes" role="group" aria-labelledby="titulo-pergunta">
                <label class="opcao">
                    <input type="radio" name="perguntas" value="quando_util" required>
                    <span>Sim, quando for útil, uma pergunta por vez</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="perguntas" value="quando_pedir" required>
                    <span>Apenas quando eu pedir ajuda para contar</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="perguntas" value="nunca" required>
                    <span>Prefiro registrar sem receber perguntas</span>
                </label>
            </div>

            <p id="aviso" class="mensagem-erro" role="alert"></p>
            <div class="botoes">
                <a class="btn-voltar" href="../pergunta4/index.php">Voltar</a>
                <button class="btn-proxima" type="submit">Próxima</button>
            </div>
        </form>
        <noscript>Ative o JavaScript para guardar suas respostas ao mudar de página.</noscript>
    </div>
    <script src="../../modo_escuro.js"></script>
</body>
</html>
