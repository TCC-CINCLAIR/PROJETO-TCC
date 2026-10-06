<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
session_start();

// Todas as respostas seguem o mesmo formato JSON para o JavaScript ler.
function responderJSON(int $status, array $dados): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// 1. Mantém a mesma proteção de login da página do chatbot.
if (!isset($_SESSION['sessao_id'])) {
    responderJSON(401, ['erro' => 'Sua sessão expirou. Entre novamente para conversar.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responderJSON(405, ['erro' => 'Envie sua mensagem pelo formulário do chat.']);
}
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['chat_csrf']) || !hash_equals($_SESSION['chat_csrf'], $token)) {
    responderJSON(403, ['erro' => 'Atualize a página antes de enviar sua mensagem.']);
}
// Libera a sessão enquanto aguarda a API, permitindo navegar em outras telas.
$usuarioId = $_SESSION['sessao_id'];
session_write_close();

// 2. Lê o JSON enviado pelo fetch e confere o campo mensagem.
if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/json') {
    responderJSON(415, ['erro' => 'Formato de envio inválido. Atualize a página e tente novamente.']);
}
$corpo = file_get_contents('php://input', false, null, 0, 16385);
if ($corpo === false || strlen($corpo) > 16384) {
    responderJSON(413, ['erro' => 'Sua mensagem é muito longa. Use até 2.000 caracteres.']);
}
$dados = json_decode($corpo, true);
if (!is_array($dados) || !isset($dados['mensagem']) || !is_string($dados['mensagem'])) {
    responderJSON(400, ['erro' => 'Envie uma mensagem de texto válida.']);
}
// Rejeita entradas inválidas antes de consultar o banco.
if (preg_match('/^\s*$/u', $dados['mensagem']) || mb_strlen($dados['mensagem'], 'UTF-8') > 2000) {
    responderJSON(422, ['erro' => 'Escreva uma mensagem de até 2.000 caracteres.']);
}

// 3. Usa a função do bloco 2 e devolve o resultado ao navegador.
$envioId = $dados['envio_id'] ?? null;
if (!is_string($envioId) || !preg_match('/^[a-f0-9]{32}$/D', $envioId)) {
    responderJSON(400, ['erro' => 'Atualize a página para identificar corretamente seu envio.']);
}
try {
    require_once __DIR__ . '/servico_ia.php';
    require_once __DIR__ . '/preferencias_usuario.php';
    // ID vem da sessão; não aceita ID ou preferências enviados pelo navegador.
    $preferencias = carregarPreferenciasUsuario($usuarioId);
    $banco = conectarBancoPreferencias();
    $resultado = processarTrocaChat($banco, $usuarioId, $envioId, trim($dados['mensagem']),
        static fn(string $texto, array $contexto): string => consultarIA($texto, $preferencias, $contexto));
    responderJSON(200, $resultado);
} catch (InvalidArgumentException $erro) {
    responderJSON(422, ['erro' => $erro->getMessage()]);
} catch (Throwable $erro) {
    if (in_array($erro->getCode(), [409, 429], true)) {
        responderJSON($erro->getCode(), ['erro' => $erro->getMessage()]);
    }
    // Detalhes de configuração e da API não são enviados ao navegador.
    responderJSON(502, ['erro' => 'Não foi possível obter uma resposta agora. Aguarde um pouco e tente novamente.']);
}
