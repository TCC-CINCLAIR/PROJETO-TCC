<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../tela_login/index.html?aviso=acesso-negado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pergunta 3 | Cinclair</title>
    <link rel="stylesheet" href="style.css">
    <script src="../script.js" defer></script>
</head>
<body>
    <div class="caixa-questionario">
        <!-- Progresso desta página -->
        <div class="informacoes-progresso">
            <p>Pergunta 3 de 7</p>
            <span>29% completo</span>
        </div>
        <div class="barra-progresso">
            <div class="progresso-2"></div>
        </div>

        <!-- O action informa qual é a próxima página -->
        <form id="questionario" action="../pergunta4/index.php">
            <h1 id="titulo-pergunta">Como você prefere que o assistente fale com você?</h1>
            <div class="opcoes" role="group" aria-labelledby="titulo-pergunta">
                <label class="opcao">
                    <input type="radio" name="tom" value="direto" required>
                    <span>De forma direta e objetiva</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="tom" value="informal" required>
                    <span>De forma leve e informal</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="tom" value="formal" required>
                    <span>De forma mais formal</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="tom" value="sem_preferencia" required>
                    <span>Não tenho preferência</span>
                </label>
            </div>

            <p id="aviso" class="mensagem-erro" role="alert"></p>
            <div class="botoes">
                <a class="btn-voltar" href="../pergunta2/index.php">Voltar</a>
                <button class="btn-proxima" type="submit">Próxima</button>
            </div>
        </form>
        <noscript>Ative o JavaScript para guardar suas respostas ao mudar de página.</noscript>
    </div>
</body>
</html>
