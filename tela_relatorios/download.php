<?php
ini_set('display_errors', '0');
session_start();
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
if (!isset($_SESSION['sessao_id'])) { header('Location: ../tela_login/index.html?aviso=acesso-negado'); exit; }
$usuario = $_SESSION['sessao_id'];
session_write_close();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); header('Allow: GET'); exit; }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) { http_response_code(404); exit('Relatório não encontrado.'); }
try {
    require_once __DIR__ . '/servico.php';
    require_once __DIR__ . '/pdf.php';
    require_once __DIR__ . '/../conexao.php';
    $stmt = $conn->prepare('SELECT * FROM relatorios_semanais WHERE id = :id AND usuario_id = :usuario');
    $stmt->execute(['id' => $id, 'usuario' => $usuario]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) { http_response_code(404); exit('Relatório não encontrado.'); }
    $periodo = dataRelatorio($r['inicio_periodo'])->format('d/m/Y H:i') . ' até ' . dataRelatorio($r['fim_periodo'])->format('d/m/Y H:i') . ' (Brasília, fim exclusivo)';
    $pdf = gerarPdfRelatorio('Cinclair — Relatório semanal', $periodo, $r['conteudo']);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="relatorio-' . dataRelatorio($r['fim_periodo'])->format('Y-m-d') . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
} catch (Throwable $e) { http_response_code(503); echo 'Não foi possível baixar o relatório. Tente novamente.'; }
