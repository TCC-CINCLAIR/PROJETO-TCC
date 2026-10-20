<?php
ini_set('display_errors', '0');
session_start();
header('Cache-Control: private, no-store');
if (!isset($_SESSION['sessao_id'])) {
    header('Location: ../tela_login/index.html?aviso=acesso-negado');
    exit;
}
$usuarioId = (int) $_SESSION['sessao_id'];
session_write_close();
$agora = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
$consulta = null;
$relatorio = null;
$erroConsulta = false;
$erroRelatorio = false;
function escaparDashboard(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
try {
    require_once __DIR__ . '/../conexao.php';
} catch (Throwable $e) {
    $erroConsulta = $erroRelatorio = true;
}
if (isset($conn)) {
    try {
        $stmt = $conn->prepare('SELECT profissional, data_consulta, horario FROM marcar_consulta
            WHERE id_usuario = :usuario AND (data_consulta > :hoje OR (data_consulta = :hoje_horario AND horario >= :horario))
            ORDER BY data_consulta, horario LIMIT 1');
        $stmt->execute(['usuario' => $usuarioId, 'hoje' => $agora->format('Y-m-d'),
            'hoje_horario' => $agora->format('Y-m-d'), 'horario' => $agora->format('H:i:s')]);
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $erroConsulta = true;
    }
    try {
        $stmt = $conn->prepare('SELECT id, inicio_periodo, fim_periodo FROM relatorios_semanais
            WHERE usuario_id = :usuario ORDER BY fim_periodo DESC, id DESC LIMIT 1');
        $stmt->execute(['usuario' => $usuarioId]);
        $relatorio = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $erroRelatorio = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Cinclair</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
</head>
<body class="pagina-interna pagina-dashboard" data-usuario-id="<?= $usuarioId ?>">
    <header>
        <nav class="top-menu" aria-label="Navegação principal">
            <a href="index.php" class="menu-btn-active" aria-current="page"><img src="../imagens/imagens_navbar/Config.png" alt="">Dashboard</a>
            <a href="../tela_chatbot/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Chat.png" alt="">Chatbot</a>
            <a href="../tela_perfil/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/User.png" alt="">Perfil</a>
            <a href="../agenda_tarefas/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Calendário.png" alt="">Agenda</a>
            <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt="">Consultas</a>
        </nav>
    </header>
    <main class="container">
        <section class="dashboard-box" aria-labelledby="dashboard-title">
            <div class="dashboard-heading">
                <div>
                    <p class="eyebrow">SEU DIA, COM MAIS CLAREZA</p>
                    <h1 id="dashboard-title">Meu dashboard</h1>
                    <p>Um espaço para acompanhar o que importa para você.</p>
                </div>
                <time class="today" datetime="<?= $agora->format('Y-m-d') ?>"><?= $agora->format('d/m/Y') ?></time>
            </div>
            <div class="dashboard-grid">
                <section class="dashboard-window tasks-window" aria-labelledby="tasks-title">
                    <div class="window-heading">
                        <span class="window-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="3"/><path d="M8 3v4m8-4v4M4 10h16m-12 5 3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <div><h2 id="tasks-title">Próximas tarefas</h2><p>Seus próximos três passos.</p></div>
                        <span class="task-count" id="task-count" aria-label="Quantidade de tarefas exibidas">0/3</span>
                    </div>
                    <ol class="upcoming-tasks" id="upcoming-tasks" hidden></ol>
                    <div class="window-empty tasks-empty" id="tasks-empty">
                        <span class="empty-symbol" aria-hidden="true">✓</span>
                        <h3>Espaço para um novo passo</h3>
                        <p>Adicione tarefas à sua agenda e acompanhe as próximas por aqui.</p>
                    </div>
                    <p class="storage-warning" id="storage-warning" role="status" hidden>Não foi possível carregar as tarefas salvas neste navegador.</p>
                    <noscript><p>Ative o JavaScript para consultar suas tarefas.</p></noscript>
                    <a class="window-link" href="../agenda_tarefas/index.php">Ver minha agenda <span aria-hidden="true">→</span></a>
                </section>
                <section class="dashboard-window" aria-labelledby="consultation-title">
                    <div class="window-heading">
                        <span class="window-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round"/></svg></span>
                        <div><h2 id="consultation-title">Próxima consulta</h2><p>Um momento para cuidar de você.</p></div>
                    </div>
                    <?php if ($erroConsulta): ?>
                    <p class="window-message" role="status">Não foi possível carregar sua consulta. Atualize a página para tentar novamente.</p>
                    <?php elseif ($consulta): ?>
                    <div class="consultation-summary">
                        <div class="date-tile"><strong><?= (new DateTimeImmutable($consulta['data_consulta']))->format('d') ?></strong><span><?= (new DateTimeImmutable($consulta['data_consulta']))->format('m/Y') ?></span></div>
                        <div><h3><?= escaparDashboard($consulta['profissional']) ?></h3><p><?= (new DateTimeImmutable($consulta['data_consulta']))->format('d/m/Y') ?> às <?= escaparDashboard(substr($consulta['horario'], 0, 5)) ?></p><span class="status-badge">Agendada</span></div>
                    </div>
                    <?php else: ?>
                    <div class="window-empty"><h3>Nenhuma consulta agendada</h3><p>Quando agendar sua próxima consulta, os detalhes aparecerão aqui.</p></div>
                    <?php endif; ?>
                    <a class="window-link" href="../tela_consulta/index.php"><?= $consulta ? 'Ir para consultas' : 'Agendar consulta' ?> <span aria-hidden="true">→</span></a>
                </section>
                <section class="dashboard-window" aria-labelledby="report-title">
                    <div class="window-heading">
                        <span class="window-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Zm0 0v6h6M8 13h8m-8 4h5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <div><h2 id="report-title">Último relatório</h2><p>Seu percurso, reunido em um só lugar.</p></div>
                    </div>
                    <?php if ($erroRelatorio): ?>
                    <p class="window-message" role="status">Não foi possível carregar seu relatório. Atualize a página para tentar novamente.</p>
                    <?php elseif ($relatorio): $fuso = new DateTimeZone('America/Sao_Paulo'); ?>
                    <div class="report-summary"><span class="status-badge">Disponível em PDF</span><h3>Resumo semanal</h3><p><?= (new DateTimeImmutable($relatorio['inicio_periodo']))->setTimezone($fuso)->format('d/m/Y') ?> a <?= (new DateTimeImmutable($relatorio['fim_periodo']))->setTimezone($fuso)->format('d/m/Y') ?></p></div>
                    <?php else: ?>
                    <div class="window-empty"><h3>Seu relatório aparecerá aqui</h3><p>Assim que um resumo semanal estiver salvo, você poderá acessá-lo por aqui.</p></div>
                    <?php endif; ?>
                    <div class="window-actions">
                        <?php if ($relatorio): ?><a class="pdf-link" href="../tela_relatorios/download.php?id=<?= (int) $relatorio['id'] ?>">Baixar PDF <span aria-hidden="true">↓</span></a><?php endif; ?>
                        <a class="window-link" href="../tela_relatorios/index.php">Ver relatórios <span aria-hidden="true">→</span></a>
                    </div>
                </section>
            </div>
        </section>
    </main>
    <script src="../assets/js/tarefas.js"></script>
    <script src="script.js"></script>
    <script src="../modo_escuro.js"></script>
</body>
</html>
