<?php
ini_set('display_errors', '0');
session_start();
header('Cache-Control: private, no-store');
if (!isset($_SESSION['sessao_id'])) { header('Location: ../tela_login/index.html?aviso=acesso-negado'); exit; }
require_once __DIR__ . '/servico.php';
$relatorios = []; $preferencia = null; $erro = '';
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
try {
    require_once __DIR__ . '/../conexao.php';
    $preferencia = preferenciaRelatorio($conn, $_SESSION['sessao_id']);
    $stmt = $conn->prepare('SELECT id, inicio_periodo, fim_periodo FROM relatorios_semanais WHERE usuario_id = :usuario ORDER BY fim_periodo DESC LIMIT 11 OFFSET :offset');
    $stmt->bindValue('usuario', (int) $_SESSION['sessao_id'], PDO::PARAM_INT);
    $stmt->bindValue('offset', ($pagina - 1) * 10, PDO::PARAM_INT);
    $stmt->execute(); $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $erro = 'Não foi possível carregar seus relatórios. Tente novamente mais tarde.'; }
$haProxima = count($relatorios) > 10;
$relatorios = array_slice($relatorios, 0, 10);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios — Cinclair</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-interna pagina-configuracao">
    <header>
        <nav class="top-menu" aria-label="Navegação principal">
            <a href="../tela_dashboard/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Config.png" alt="">Dashboard</a>
            <a href="../tela_chatbot/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Chat.png" alt="">Chatbot</a>
            <a href="../tela_perfil/index.php" class="menu-btn-active" aria-current="page"><img src="../imagens/imagens_navbar/User.png" alt="">Perfil</a>
            <a href="../agenda_tarefas/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Calendário.png" alt="">Agenda</a>
            <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt="">Consultas</a>
        </nav>
    </header>
    <main>
        <section class="relatorios-box">
            <div class="titulo"><img src="../imagens/imagens_perfil/img-docs.png" alt=""><h1>Central de Relatórios</h1></div>
            <div class="resumo-agendamento">
                <p>Seus acontecimentos e sentimentos importantes, reunidos para a consulta.</p>
                <?php if ($preferencia): ?>
                <p>Dia escolhido: <strong><?= diasRelatorio()[(int) $preferencia['dia_semana']] ?></strong>.<br>Próxima geração: <?= dataRelatorio($preferencia['proxima_geracao'])->format('d/m/Y') ?>, a partir de 0h (Brasília).</p>
                <?php elseif (!$erro): ?>
                <p>Escolha em Preferências o dia para começar a receber seu relatório semanal.</p>
                <?php endif; ?>
                <a href="../tela_preferencias/index.php">Configurar em Preferências →</a>
            </div>
            <section class="lista-relatorios" aria-label="Relatórios salvos">
                <?php if ($erro): ?><p role="alert"><?= $erro ?></p>
                <?php elseif (!$relatorios): ?>
                <div class="vazio-relatorios"><h2>Nenhum relatório salvo ainda</h2><p>Quando seu resumo semanal estiver pronto, ele aparecerá aqui. Na conversa, selecione os relatos que deseja incluir.</p></div>
                <?php endif; ?>
                <?php foreach ($relatorios as $r): $fim = dataRelatorio($r['fim_periodo']); ?>
                <article class="relatorio">
                    <div class="data"><strong><?= $fim->format('d') ?></strong><span><?= $fim->format('m/Y') ?></span></div>
                    <div class="informacoes"><strong>Resumo semanal</strong><p><?= dataRelatorio($r['inicio_periodo'])->format('d/m/Y H:i') ?> até <?= $fim->format('d/m/Y H:i') ?></p></div>
                    <a class="download" href="download.php?id=<?= (int) $r['id'] ?>" aria-label="Baixar PDF do relatório de <?= $fim->format('d/m/Y') ?>">↓ <span>PDF</span></a>
                </article>
                <?php endforeach; ?>
            </section>
            <nav class="paginacao" aria-label="Páginas de relatórios">
                <?php if ($pagina > 1): ?><a href="?pagina=<?= $pagina - 1 ?>">← Anteriores</a><?php endif; ?>
                <?php if ($haProxima): ?><a href="?pagina=<?= $pagina + 1 ?>">Próximos →</a><?php endif; ?>
            </nav>
            <a href="../tela_perfil/index.php" class="voltar">← Voltar para o perfil</a>
        </section>
    </main>
    <script src="../modo_escuro.js"></script>
</body>
</html>
