<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
session_start();
if (!isset($_SESSION['sessao_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sua sessão expirou. Entre novamente.']);
    exit;
}
$usuarioId = $_SESSION['sessao_id'];
session_write_close();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['erro' => 'Método inválido.']);
    exit;
}
$antes = filter_input(INPUT_GET, 'antes', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($antes === false || $antes === null) {
    http_response_code(400);
    echo json_encode(['erro' => 'Informe uma página válida do histórico.']);
    exit;
}
try {
    require_once __DIR__ . '/preferencias_usuario.php';
    require_once __DIR__ . '/historico_chat.php';
    echo json_encode(carregarHistoricoChat(conectarBancoPreferencias(), $usuarioId, $antes), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $erro) {
    http_response_code(503);
    echo json_encode(['erro' => 'Não foi possível carregar as mensagens anteriores. Tente novamente.']);
}
