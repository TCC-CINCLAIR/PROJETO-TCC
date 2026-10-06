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
    <title>Pergunta 2 | Cinclair</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../assets/css/responsivo.css">
    <link rel="stylesheet" href="../../modo_escuro.css">
</head>
<body class="pagina-questionario">
    <div class="caixa-questionario">
        <!-- Progresso desta página -->
        <div class="informacoes-progresso">
            <p>Pergunta 2 de 7</p>
            <span>14% completo</span>
        </div>
        <div class="barra-progresso">
            <div class="progresso-1"></div>
        </div>

        <!-- Envia a resposta para o processamento -->
        <form id="questionario" action="processar_pergunta.php" method="post">
            <h1 id="titulo-pergunta">Qual é sua faixa etária?</h1>
            <div class="opcoes" role="group" aria-labelledby="titulo-pergunta">
                <label class="opcao">
                    <input type="radio" name="faixa_etaria" value="menos_12" required>
                    <span>Menos de 12 anos</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="faixa_etaria" value="12_17" required>
                    <span>De 12 a 17 anos</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="faixa_etaria" value="18_29" required>
                    <span>De 18 a 29 anos</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="faixa_etaria" value="30_59" required>
                    <span>De 30 a 59 anos</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="faixa_etaria" value="60_mais" required>
                    <span>60 anos ou mais</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="faixa_etaria" value="nao_informar" required>
                    <span>Prefiro não informar</span>
                </label>
            </div>

            <p id="aviso" class="mensagem-erro" role="alert"></p>
            <div class="botoes">
                <a class="btn-voltar" href="../pergunta1/index.php">Voltar</a>
                <button class="btn-proxima" type="submit">Próxima</button>
            </div>
        </form>
        <noscript>Ative o JavaScript para guardar suas respostas ao mudar de página.</noscript>
    </div>
    <script src="../../modo_escuro.js"></script>
</body>
</html>
