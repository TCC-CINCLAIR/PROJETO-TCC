<?php
// Execute pelo terminal. --banco usa somente tabelas temporárias, sem dados reais.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
set_exception_handler(static function (Throwable $erro): void {
    // Não imprime rastros de conexão, que podem incluir configuração privada.
    fwrite(STDERR, 'Teste interrompido: ' . ($erro instanceof PDOException ? 'falha na conexão ou consulta ao banco.' : $erro->getMessage()) . "\n");
    exit(1);
});
require_once __DIR__ . '/historico_chat.php';
$total = 0;
function conferir(bool $condicao, string $descricao): void {
    global $total;
    if (!$condicao) throw new RuntimeException($descricao);
    $total++;
    echo "OK: $descricao\n";
}
$historico = [];
for ($i = 0; $i < 8; $i++) {
    $historico[] = ['role' => 'user', 'content' => "Pergunta $i"];
    $historico[] = ['role' => 'assistant', 'content' => "Resposta $i"];
}
$contexto = limitarContextoChat($historico);
conferir(count($contexto) === 12 && $contexto[0]['content'] === 'Pergunta 2', 'Contexto conserva as seis trocas mais recentes');
conferir(limitarContextoChat([['role' => 'system', 'content' => 'regra'], ['role' => 'assistant', 'content' => 'solta']]) === [], 'Descarta regras e respostas sem pergunta');
$parLongo = [['role' => 'user', 'content' => str_repeat('á', 3000)], ['role' => 'assistant', 'content' => str_repeat('é', 3000)]];
conferir(count(limitarContextoChat($parLongo)) === 2, 'Limite conta caracteres UTF-8');
conferir(count(limitarContextoChat(array_merge($parLongo, array_slice($historico, -2)))) === 2, 'Não corta uma troca ao atingir 6.000 caracteres');
if (!in_array('--banco', $argv, true)) { echo "$total verificações concluídas. Use --banco para testar persistência.\n"; exit; }
require_once __DIR__ . '/preferencias_usuario.php';
$banco = conectarBancoPreferencias();
try {
    $banco->exec('CREATE TEMP TABLE chat_conversas (id SERIAL PRIMARY KEY, usuario_id INTEGER NOT NULL UNIQUE, criado_em TIMESTAMPTZ DEFAULT NOW(), atualizado_em TIMESTAMPTZ DEFAULT NOW())');
    $banco->exec("CREATE TEMP TABLE chat_mensagens (id SERIAL PRIMARY KEY, conversa_id INTEGER NOT NULL REFERENCES pg_temp.chat_conversas(id), envio_id VARCHAR(36) NOT NULL, remetente VARCHAR(10) NOT NULL CHECK (remetente IN ('user', 'assistant')), conteudo TEXT NOT NULL, criado_em TIMESTAMPTZ DEFAULT NOW(), UNIQUE (conversa_id, envio_id, remetente))");
    // Confere a resolução antes de executar qualquer função de persistência.
    conferir((bool) $banco->query("SELECT 'chat_conversas'::regclass = 'pg_temp.chat_conversas'::regclass AND 'chat_mensagens'::regclass = 'pg_temp.chat_mensagens'::regclass")->fetchColumn(), 'Consultas usam exclusivamente as tabelas temporárias');
    $usuario = 1900000011;
    $outro = 1900000012;
    $envio = str_repeat('a', 32);
    $primeira = processarTrocaChat($banco, $usuario, $envio, 'Primeira pergunta', static function ($texto, $contexto) {
        conferir($contexto === [], 'Primeiro envio não recebe histórico');
        return 'Primeira resposta';
    });
    conferir(count(carregarHistoricoChat($banco, $usuario)['mensagens']) === 2, 'Mensagem e resposta persistem juntas');
    $repetida = processarTrocaChat($banco, $usuario, $envio, 'Primeira pergunta', static function () { throw new RuntimeException('Não deveria chamar IA novamente'); });
    conferir($repetida === $primeira, 'Reenvio retorna a mesma resposta sem duplicar ou chamar IA');
    try {
        processarTrocaChat($banco, $usuario, $envio, 'Texto diferente', static fn() => 'Erro');
        throw new LogicException('Deveria rejeitar conflito');
    } catch (RuntimeException $erro) { conferir($erro->getCode() === 409, 'Rejeita reutilização do identificador com outro texto'); }
    processarTrocaChat($banco, $usuario, str_repeat('b', 32), 'Segunda pergunta', static function ($texto, $contexto) {
        conferir(count($contexto) === 2 && $contexto[0]['content'] === 'Primeira pergunta', 'Próximo envio recebe a troca anterior');
        return 'Segunda resposta';
    });
    try {
        processarTrocaChat($banco, $usuario, str_repeat('c', 32), 'Falha simulada', static function () { throw new RuntimeException('Falha simulada', 503); });
        throw new LogicException('Deveria falhar');
    } catch (RuntimeException $erro) { conferir($erro->getCode() === 503, 'Falha da IA chega ao chamador'); }
    conferir(count(carregarHistoricoChat($banco, $usuario)['mensagens']) === 4 && !$banco->inTransaction(), 'Falha desfaz a transação sem deixar mensagem isolada');
    processarTrocaChat($banco, $outro, $envio, 'Outro usuário', static function ($texto, $contexto) {
        conferir($contexto === [], 'Outra conta não recebe o contexto da primeira');
        return 'Resposta de outra conta';
    });
    conferir(count(carregarHistoricoChat($banco, $outro)['mensagens']) === 2, 'Histórico de outra conta é separado');
    $segundaConexao = conectarBancoPreferencias();
    processarTrocaChat($banco, $usuario, str_repeat('d', 32), 'Teste de concorrência', static function () use ($segundaConexao, $usuario, $outro) {
        $segundaConexao->beginTransaction();
        try {
            $consulta = $segundaConexao->prepare('SELECT pg_try_advisory_xact_lock(1128878674, CAST(:usuario AS INTEGER))');
            $consulta->execute(['usuario' => $usuario]);
            conferir(!in_array($consulta->fetchColumn(), [true, 1, '1', 't'], true), 'Envios simultâneos da mesma conta ficam bloqueados');
            $consulta->execute(['usuario' => $outro]);
            conferir(in_array($consulta->fetchColumn(), [true, 1, '1', 't'], true), 'Outra conta pode enviar ao mesmo tempo');
        } finally { $segundaConexao->rollBack(); }
        return 'Concorrência conferida';
    });
    for ($i = 0; $i < 19; $i++) {
        processarTrocaChat($banco, $usuario, str_pad(dechex($i), 32, '0', STR_PAD_LEFT), "Pergunta $i", static fn() => 'Resposta de paginação');
    }
    $pagina = carregarHistoricoChat($banco, $usuario);
    $anteriores = carregarHistoricoChat($banco, $usuario, $pagina['antes']);
    conferir(count($pagina['mensagens']) === 40 && $pagina['ha_anteriores'], 'Carrega as 40 mensagens mais recentes');
    conferir(count($anteriores['mensagens']) === 4 && !$anteriores['ha_anteriores'], 'Página anterior recupera o restante');
    conferir((int) end($anteriores['mensagens'])['id'] < (int) $pagina['mensagens'][0]['id'], 'Paginação mantém a ordem sem sobreposição');
    echo "$total verificações concluídas.\n";
} finally {
    if ($banco->inTransaction()) $banco->rollBack();
    $banco->exec('DROP TABLE IF EXISTS pg_temp.chat_mensagens');
    $banco->exec('DROP TABLE IF EXISTS pg_temp.chat_conversas');
}
