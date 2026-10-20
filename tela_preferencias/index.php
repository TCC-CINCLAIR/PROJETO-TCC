<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}

require_once __DIR__ . '/../tela_relatorios/servico.php';
$_SESSION['relatorio_csrf'] ??= bin2hex(random_bytes(32));
$preferencia = null; $erroRelatorio = ''; $sucessoRelatorio = '';
try {
    require_once __DIR__ . '/../conexao.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? null;
        $dia = filter_var($_POST['dia_semana'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 6]]);
        if (!is_string($token) || !hash_equals($_SESSION['relatorio_csrf'], $token) || $dia === false || $dia === null) {
            http_response_code(422);
            $erroRelatorio = 'Atualize a página e escolha um dia válido.';
        } else {
            salvarDiaRelatorio($conn, $_SESSION['sessao_id'], $dia);
            header('Location: index.php?salvo=1'); exit;
        }
    }
    $preferencia = preferenciaRelatorio($conn, $_SESSION['sessao_id']);
    if (isset($_GET['salvo'])) $sucessoRelatorio = 'Dia do relatório atualizado.';
} catch (Throwable $e) {
    $erroRelatorio = 'A configuração dos relatórios está indisponível. Tente novamente mais tarde.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferências</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
    <link rel="stylesheet" href="../fonte_adaptativa.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-interna pagina-configuracao">
    <header>
        <nav class="top-menu" aria-label="Navegação principal">
            <a href="../tela_dashboard/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</a>
            <a href="../tela_chatbot/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</a>
            <a href="../tela_perfil/index.php" class="menu-btn-active" aria-current="page"><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</a>
            <a href="../agenda_tarefas/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</a>
            <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</a>
        </nav>
    </header>
    <main>
        <section class="preferencias-box">
            <div class="titulo">
                <h1>Preferências</h1>
            </div>
            <section class="opcoes">
    <div class="opcao">
        <div>
            <strong>Ative o modo escuro</strong>
        </div>
        <label class="switch">
            <input type="checkbox" id="modoEscuro">
            <span></span>
        </label>
    </div>
    <div class="opcao">
        <div>
            <strong>Fonte adaptativa</strong>
        </div>
        <label class="switch">
            <input type="checkbox" id="fonteAdaptativa">
            <span></span>
        </label>
    </div>
</section>
            <form method="post" class="config-relatorio">
                <h2>Seu relatório semanal</h2>
                <p>Escolha o dia em que o resumo dos seus relatos ficará disponível em PDF. A geração começa à 0h, no horário de Brasília.</p>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['relatorio_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="dia-semana">Dia da semana</label>
                <div class="acoes-relatorio">
                    <select id="dia-semana" name="dia_semana" required>
                        <option value="" <?= !$preferencia ? 'selected' : '' ?> disabled>Escolha um dia</option>
                        <?php foreach (diasRelatorio() as $dia => $nome): ?>
                        <option value="<?= $dia ?>" <?= $preferencia && (int) $preferencia['dia_semana'] === $dia ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Salvar dia</button>
                </div>
                <?php if ($preferencia): ?>
                <p>Próximo relatório: <strong><?= dataRelatorio($preferencia['proxima_geracao'])->format('d/m/Y') ?></strong>. Os relatórios anteriores continuam disponíveis.</p>
                <?php else: ?>
                <p>O primeiro relatório será gerado no dia escolhido, após pelo menos sete dias. Depois, a geração será semanal.</p>
                <?php endif; ?>
                <p>Os relatos começam a contar a partir da primeira configuração. Na conversa, use “Guardar acontecimento” para juntar os detalhes, conferir a prévia e incluir no relatório.</p>
                <p>O resumo é preparado por IA a partir das conversas do período. Revise as informações antes de levar à consulta.</p>
                <?php if ($erroRelatorio): ?><p role="alert"><?= htmlspecialchars($erroRelatorio, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <?php if ($sucessoRelatorio): ?><p role="status"><?= $sucessoRelatorio ?></p><?php endif; ?>
                <a href="../tela_relatorios/index.php">Ver meus relatórios →</a>
            </form>
            <a href="../tela_perfil/index.php" class="voltar">← Voltar para o perfil</a>
        </section>
    </main>
    <script src="../modo_escuro.js"></script>
    <script src="../fonte_adaptativa.js"></script>
</body>
</html>
