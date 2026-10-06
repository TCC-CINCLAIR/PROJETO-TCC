<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}

// Token da sessão: o PHP confere se o envio veio do formulário do usuário.
if (empty($_SESSION['chat_csrf'])) {
    $_SESSION['chat_csrf'] = bin2hex(random_bytes(32));
}
header('Cache-Control: no-store');
$tokenChat = $_SESSION['chat_csrf'];
$usuarioId = $_SESSION['sessao_id'];
session_write_close();
require_once __DIR__ . '/preferencias_usuario.php';
require_once __DIR__ . '/historico_chat.php';
$preferencias = carregarPreferenciasUsuario($usuarioId);
$historico = ['mensagens' => [], 'ha_anteriores' => false, 'antes' => null];
$erroHistorico = false;
try {
    $historico = carregarHistoricoChat(conectarBancoPreferencias(), $usuarioId);
} catch (Throwable $erro) {
    $erroHistorico = true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot | Clarice</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/responsivo.css?v=<?= filemtime(__DIR__ . '/../assets/css/responsivo.css') ?>">
    <link rel="stylesheet" href="../modo_escuro.css">
</head>
<body class="pagina-interna pagina-chatbot">
    <header>
        <nav class="top-menu" aria-label="Navegação principal">
            <a href="#" class="menu-btn"><img src="../imagens/imagens_navbar/Config.png" alt=""><span>Dashboard</span></a>
            <a href="index.php" class="menu-btn active" aria-current="page"><img src="../imagens/imagens_navbar/Chat.png" alt=""><span>Chatbot</span></a>
            <a href="../tela_perfil/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/User.png" alt=""><span>Perfil</span></a>
            <a href="../agenda_tarefas/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Calendário.png" alt=""><span>Agenda</span></a>
            <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt=""><span>Consultas</span></a>
        </nav>
    </header>

    <main class="container">
        <section class="chat-moldura" aria-labelledby="chat-title">
            <div class="chat-box">
                <header class="chat-header">
                    <img class="clarice-icon" src="../imagens/imagens_cadastro/logo-login.png" alt="Cinclair" width="48" height="48">
                    <div>
                        <h1 id="chat-title">Clarice</h1>
                        <p class="chat-status"><span aria-hidden="true"></span>Assistente de bem-estar</p>
                    </div>
                </header>

                <div class="chat-mensagens" id="mensagens" role="log" aria-label="Conversa com Clarice" aria-live="polite" aria-relevant="additions" tabindex="0">
                    <button type="button" class="historico-anteriores" id="historico-anteriores" data-antes="<?= (int) ($historico['antes'] ?? 0) ?>" <?= !$historico['ha_anteriores'] ? 'hidden' : '' ?>>Carregar mensagens anteriores</button>
                    <?php if (!$historico['mensagens'] && !$erroHistorico): ?>
                    <div class="mensagem mensagem-clarice">
                        <span class="sr-only">Clarice: </span>
                        <p><?= htmlspecialchars(saudacaoClarice($preferencias), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($historico['mensagens'] as $mensagem): ?>
                    <div class="mensagem <?= $mensagem['remetente'] === 'user' ? 'mensagem-usuario' : 'mensagem-clarice' ?>" data-id="<?= (int) $mensagem['id'] ?>">
                        <span class="sr-only"><?= $mensagem['remetente'] === 'user' ? 'Você: ' : 'Clarice: ' ?></span>
                        <p><?= htmlspecialchars($mensagem['conteudo'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="chat-rodape">
                    <p class="chat-aviso<?= $erroHistorico ? ' erro' : '' ?>" id="chat-aviso" role="status" <?= !$erroHistorico ? 'hidden' : '' ?>><?= $erroHistorico ? 'Não foi possível recuperar sua conversa. Atualize a página para tentar novamente.' : '' ?></p>
                    <form class="chat-form" id="chat-form" action="processar_mensagem.php" method="post" data-historico-pronto="<?= $erroHistorico ? 'false' : 'true' ?>" data-csrf="<?= htmlspecialchars($tokenChat, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="emoji-container">
                            <button class="chat-btn" id="emoji-btn" type="button" aria-label="Escolher emoji" aria-expanded="false" aria-controls="emoji-picker" <?= $erroHistorico ? 'disabled' : '' ?>>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 14a4 4 0 0 0 8 0M8 8.5h.01M16 8.5h.01" stroke-linecap="round"/></svg>
                            </button>
                            <div class="emoji-picker" id="emoji-picker" role="group" aria-label="Emojis" hidden>
                                <button type="button" aria-label="Sorriso">😊</button>
                                <button type="button" aria-label="Coração">❤️</button>
                                <button type="button" aria-label="Triste">😔</button>
                                <button type="button" aria-label="Abraço">🤗</button>
                                <button type="button" aria-label="Pensativo">🤔</button>
                                <button type="button" aria-label="Feliz">😄</button>
                            </div>
                        </div>
                        <label class="sr-only" for="mensagem">Digite sua mensagem</label>
                        <input id="mensagem" name="mensagem" type="text" placeholder="Digite sua mensagem..." maxlength="2000" autocomplete="off" required <?= $erroHistorico ? 'disabled' : '' ?>>
                        <button class="chat-btn" type="submit" aria-label="Enviar mensagem" <?= $erroHistorico ? 'disabled' : '' ?>>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 3 17 9-17 9 4-9-4-9Zm4 9h13" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                    <p class="chat-nota">As mensagens enviadas à IA e suas respostas são salvas na sua conta.</p>
                </div>
            </div>
        </section>
    </main>
    <script src="script.js?v=<?= filemtime(__DIR__ . '/script.js') ?>"></script>
    <script src="../modo_escuro.js"></script>
</body>
</html>
