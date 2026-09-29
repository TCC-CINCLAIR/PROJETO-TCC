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
    <title>Pergunta 4 | Cinclair</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../assets/css/responsivo.css">
</head>
<body class="pagina-questionario">
    <div class="caixa-questionario">
        <!-- Progresso desta página -->
        <div class="informacoes-progresso">
            <p>Pergunta 4 de 7</p>
            <span>43% completo</span>
        </div>
        <div class="barra-progresso">
            <div class="progresso-3"></div>
        </div>

        <!-- Envia a resposta para o processamento -->
        <form id="questionario" action="processar_pergunta.php" method="post">
            <h1 id="titulo-pergunta">Depois que você contar um acontecimento, o que prefere?</h1>
            <div class="opcoes" role="group" aria-labelledby="titulo-pergunta">
                <label class="opcao">
                    <input type="radio" name="apoio" value="breve" required>
                    <span>Uma breve mensagem de apoio</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="apoio" value="confirmacao" required>
                    <span>Apenas uma confirmação de que o relato foi registrado</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="apoio" value="sem_preferencia" required>
                    <span>Não tenho preferência</span>
                </label>
            </div>

            <p id="aviso" class="mensagem-erro" role="alert"></p>
            <div class="botoes">
                <a class="btn-voltar" href="../pergunta3/index.php">Voltar</a>
                <button class="btn-proxima" type="submit">Próxima</button>
            </div>
        </form>
        <noscript>Ative o JavaScript para guardar suas respostas ao mudar de página.</noscript>
    </div>
</body>
</html>
