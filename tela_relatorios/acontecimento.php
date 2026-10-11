<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
session_start();
function responderAcontecimento(int $status, array $dados): never {
    http_response_code($status); echo json_encode($dados, JSON_UNESCAPED_UNICODE); exit;
}
if (!isset($_SESSION['sessao_id'])) responderAcontecimento(401, ['erro' => 'Entre novamente para guardar um acontecimento.']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); responderAcontecimento(405, ['erro' => 'Método inválido.']); }
if (!isset($_SESSION['chat_csrf']) || !hash_equals($_SESSION['chat_csrf'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) responderAcontecimento(403, ['erro' => 'Atualize a página e tente novamente.']);
$usuario = $_SESSION['sessao_id'];
$dados = json_decode(file_get_contents('php://input', false, null, 0, 16385), true);
if (!is_array($dados)) responderAcontecimento(422, ['erro' => 'Pedido inválido.']);
$acao = $dados['acao'] ?? '';
$token = is_string($dados['token'] ?? null) ? $dados['token'] : '';
$previa = $_SESSION['previas_acontecimento'][$token] ?? null;
if ($acao === 'cancelar') { unset($_SESSION['previas_acontecimento'][$token]); responderAcontecimento(200, ['cancelado' => true]); }
session_write_close();
try {
    require_once __DIR__ . '/acontecimentos.php';
    require_once __DIR__ . '/../conexao.php';
    if ($acao === 'guardar') {
        if (!$previa || $previa['usuario'] !== $usuario || $previa['expira'] < time()) throw new InvalidArgumentException('A prévia expirou. Prepare uma nova para guardar.');
        validarTextoAcontecimento($dados['titulo'] ?? null, $dados['resumo'] ?? null);
        $id = salvarAcontecimento($conn, $usuario, $previa, $token, $dados['titulo'], $dados['resumo']);
        responderAcontecimento(200, ['id' => $id, 'guardado' => true]);
    }
    if ($acao !== 'preparar') throw new InvalidArgumentException('Pedido inválido.');
    $p = preferenciaRelatorio($conn, $usuario);
    if (!$p) throw new InvalidArgumentException('Escolha primeiro o dia do relatório em Preferências.');
    $stmt = $conn->prepare('SELECT m.id, m.remetente, m.conteudo, m.criado_em FROM chat_mensagens m JOIN chat_conversas c ON c.id = m.conversa_id
        WHERE c.usuario_id = :usuario AND m.criado_em >= :inicio ORDER BY m.id DESC LIMIT 60');
    $stmt->execute(['usuario' => $usuario, 'inicio' => $p['inicio_periodo']]);
    $mensagens = []; $tamanho = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $m) {
        $tamanho += mb_strlen($m['conteudo'], 'UTF-8');
        if ($tamanho > 24000) break;
        $mensagens[] = $m;
    }
    $previa = prepararAcontecimento(array_reverse($mensagens));
    $token = bin2hex(random_bytes(24));
    session_start();
    if (($_SESSION['sessao_id'] ?? null) !== $usuario) responderAcontecimento(401, ['erro' => 'Sua sessão mudou. Entre novamente.']);
    foreach ($_SESSION['previas_acontecimento'] ?? [] as $chave => $item) {
        if ($item['expira'] < time()) unset($_SESSION['previas_acontecimento'][$chave]);
    }
    $_SESSION['previas_acontecimento'] = array_slice($_SESSION['previas_acontecimento'] ?? [], -4, null, true);
    $_SESSION['previas_acontecimento'][$token] = $previa + ['usuario' => $usuario, 'expira' => time() + 1800];
    session_write_close();
    responderAcontecimento(200, ['token' => $token, 'titulo' => $previa['titulo'], 'resumo' => $previa['resumo'], 'quantidade' => count($previa['mensagem_ids'])]);
} catch (InvalidArgumentException $e) { responderAcontecimento(422, ['erro' => $e->getMessage()]); }
catch (Throwable $e) {
    if ($e->getCode() === 429) responderAcontecimento(429, ['erro' => 'A Clarice atingiu o limite de uso por enquanto. Aguarde um pouco e tente preparar a prévia novamente.']);
    responderAcontecimento(503, ['erro' => 'Não foi possível preparar ou guardar o acontecimento. Tente novamente.']);
}
