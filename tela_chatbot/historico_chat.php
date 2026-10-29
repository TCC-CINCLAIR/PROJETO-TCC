<?php


function carregarHistoricoChat(PDO $banco, int|string $usuarioId, ?int $antes = null): array
{
    $sql = 'SELECT m.id, m.remetente, m.conteudo, m.criado_em
            FROM chat_mensagens m JOIN chat_conversas c ON c.id = m.conversa_id
            WHERE c.usuario_id = :usuario_id';
    $parametros = ['usuario_id' => $usuarioId];
    if ($antes !== null) {
        $sql .= ' AND m.id < :antes';
        $parametros['antes'] = $antes;
    }
    $consulta = $banco->prepare($sql . ' ORDER BY m.id DESC LIMIT 41');
    $consulta->execute($parametros);
    $linhas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $haAnteriores = count($linhas) > 40;
    $mensagens = array_reverse(array_slice($linhas, 0, 40));
    return [
        'mensagens' => $mensagens,
        'ha_anteriores' => $haAnteriores,
        'antes' => $haAnteriores ? (int) $mensagens[0]['id'] : null,
    ];
}



function limitarContextoChat(array $historico): array
{
    $pares = [];
    $usuario = null;
    foreach ($historico as $mensagem) {
        if (!is_array($mensagem) || !is_string($mensagem['content'] ?? null)
            || trim($mensagem['content']) === '') {
            $usuario = null;
            continue;
        }
        if (($mensagem['role'] ?? '') === 'user') {
            $usuario = ['role' => 'user', 'content' => $mensagem['content']];
        } elseif (($mensagem['role'] ?? '') === 'assistant' && $usuario !== null) {
            $pares[] = [$usuario, ['role' => 'assistant', 'content' => $mensagem['content']]];
            $usuario = null;
        } else {
            $usuario = null;
        }
    }
    $contexto = [];
    $tamanho = 0;
    foreach (array_reverse(array_slice($pares, -6)) as $par) {
        $caracteres = mb_strlen($par[0]['content'], 'UTF-8') + mb_strlen($par[1]['content'], 'UTF-8');
        if ($tamanho + $caracteres > 6000) break;
        $contexto = array_merge($par, $contexto);
        $tamanho += $caracteres;
    }
    return $contexto;
}



function processarTrocaChat(PDO $banco, int|string $usuarioId, string $envioId, string $texto, callable $gerarResposta): array
{
    $banco->beginTransaction();
    try {


        $banco->exec("SET LOCAL lock_timeout = '2s'");
        $trava = $banco->prepare('SELECT pg_try_advisory_xact_lock(1128878674, CAST(:usuario AS INTEGER))');
        $trava->execute(['usuario' => $usuarioId]);
        if (!in_array($trava->fetchColumn(), [true, 1, '1', 't'], true)) {
            throw new RuntimeException('Já existe uma resposta em andamento na sua conta. Aguarde e tente novamente.', 409);
        }

        $criar = $banco->prepare('INSERT INTO chat_conversas (usuario_id) VALUES (:usuario) ON CONFLICT (usuario_id) DO NOTHING');
        $criar->execute(['usuario' => $usuarioId]);
        $buscar = $banco->prepare('SELECT id FROM chat_conversas WHERE usuario_id = :usuario');
        $buscar->execute(['usuario' => $usuarioId]);
        $conversaId = $buscar->fetchColumn();



        $repetido = $banco->prepare('SELECT id, remetente, conteudo FROM chat_mensagens WHERE conversa_id = :conversa AND envio_id = :envio ORDER BY id');
        $repetido->execute(['conversa' => $conversaId, 'envio' => $envioId]);
        $anteriores = $repetido->fetchAll(PDO::FETCH_ASSOC);
        if ($anteriores) {
            if (count($anteriores) !== 2 || $anteriores[0]['remetente'] !== 'user'
                || $anteriores[1]['remetente'] !== 'assistant' || $anteriores[0]['conteudo'] !== $texto) {
                throw new RuntimeException('Esse envio não corresponde à mensagem original. Atualize a página.', 409);
            }
            $banco->commit();
            return ['resposta' => $anteriores[1]['conteudo'], 'usuario_id_mensagem' => $anteriores[0]['id'], 'ia_id_mensagem' => $anteriores[1]['id']];
        }

        $buscar = $banco->prepare('SELECT remetente, conteudo FROM chat_mensagens WHERE conversa_id = :conversa ORDER BY id DESC LIMIT 12');
        $buscar->execute(['conversa' => $conversaId]);
        $historico = array_map(static fn(array $m): array => ['role' => $m['remetente'], 'content' => $m['conteudo']], array_reverse($buscar->fetchAll(PDO::FETCH_ASSOC)));
        $resposta = $gerarResposta($texto, limitarContextoChat($historico));
        if (!is_string($resposta) || trim($resposta) === '') {
            throw new RuntimeException('Resposta vazia.');
        }

        $salvar = $banco->prepare('INSERT INTO chat_mensagens (conversa_id, envio_id, remetente, conteudo) VALUES (:conversa, :envio, :remetente, :conteudo) RETURNING id');
        $ids = [];
        foreach (['user' => $texto, 'assistant' => $resposta] as $remetente => $conteudo) {
            $salvar->execute(['conversa' => $conversaId, 'envio' => $envioId, 'remetente' => $remetente, 'conteudo' => $conteudo]);
            $ids[$remetente] = $salvar->fetchColumn();
        }
        $atualizar = $banco->prepare('UPDATE chat_conversas SET atualizado_em = NOW() WHERE id = :id');
        $atualizar->execute(['id' => $conversaId]);
        $banco->commit();
        return ['resposta' => $resposta, 'usuario_id_mensagem' => $ids['user'], 'ia_id_mensagem' => $ids['assistant']];
    } catch (Throwable $erro) {
        if ($banco->inTransaction()) $banco->rollBack();
        throw $erro;
    }
}
