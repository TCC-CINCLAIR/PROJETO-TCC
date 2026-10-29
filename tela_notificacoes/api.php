<?php

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['sessao_id'])) {
    responder(['erro' => 'Faça login novamente.'], 401);
}

$usuarioId = (int) $_SESSION['sessao_id'];

if (empty($_SESSION['notificacoes_csrf'])) {
    $_SESSION['notificacoes_csrf'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['notificacoes_csrf'];
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    $ativar = $_POST['ativar'] ?? '';

    if (!is_string($csrf) || !hash_equals($token, $csrf)) {
        responder(['erro' => 'Atualize a página e tente novamente.'], 403);
    }

    if (!in_array($ativar, ['0', '1'], true)) {
        responder(['erro' => 'Opção inválida.'], 400);
    }
} elseif ($metodo !== 'GET') {
    responder(['erro' => 'Método inválido.'], 405);
}

session_write_close();

try {
    require __DIR__ . '/../conexao.php';

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($metodo === 'POST') {
        $salvar = $conn->prepare(
            'INSERT INTO public.preferencias_notificacoes
                (usuario_id, consultas)
             VALUES (:id, CAST(:ativo AS boolean))
             ON CONFLICT (usuario_id)
             DO UPDATE SET consultas = EXCLUDED.consultas'
        );

        $salvar->execute([
            'id' => $usuarioId,
            'ativo' => $ativar === '1' ? 'true' : 'false',
        ]);

        responder(['sucesso' => true]);
    }

    $consulta = $conn->prepare(
        'SELECT CASE WHEN consultas THEN 1 ELSE 0 END
         FROM public.preferencias_notificacoes
         WHERE usuario_id = :id'
    );

    $consulta->execute(['id' => $usuarioId]);
    $ativado = (int) $consulta->fetchColumn() === 1;

    $lembretes = [];

    if ($ativado) {
        $consulta = $conn->prepare(
            "SELECT
                data_consulta::date AS data,
                horario::time AS horario,
                profissional,
                CEIL(
                    EXTRACT(EPOCH FROM (
                        (
                            (data_consulta::date + horario::time)
                            AT TIME ZONE 'America/Sao_Paulo'
                        ) - NOW()
                    )) / 60
                ) AS minutos
             FROM public.marcar_consulta
             WHERE id_usuario = :id
               AND (
                   (data_consulta::date + horario::time)
                   AT TIME ZONE 'America/Sao_Paulo'
               ) > NOW()
               AND (
                   (data_consulta::date + horario::time)
                   AT TIME ZONE 'America/Sao_Paulo'
               ) <= NOW() + INTERVAL '10 minutes'"
        );

        $consulta->execute(['id' => $usuarioId]);
        $lembretes = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $gravar = $conn->prepare(
            'INSERT INTO public.historico_notificacoes
                (usuario_id, chave, mensagem)
             VALUES (:usuario, :chave, :mensagem)
             ON CONFLICT (usuario_id, chave) DO NOTHING'
        );

        foreach ($lembretes as $lembrete) {

            $chave = hash(
                'sha256',
                $lembrete['data'] . '|' .
                $lembrete['horario'] . '|' .
                $lembrete['profissional']
            );

            $minutos = max(1, (int) $lembrete['minutos']);
            $unidade = $minutos === 1 ? 'minuto' : 'minutos';

            $dataFormatada = DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $lembrete['data']
            )->format('d/m/Y');

            $horaFormatada = substr($lembrete['horario'], 0, 5);

            $mensagem =
                "Sua consulta começa em {$minutos} {$unidade}! " .
                "Agendada para {$dataFormatada} às {$horaFormatada}.";

            $gravar->execute([
                'usuario' => $usuarioId,
                'chave' => $chave,
                'mensagem' => $mensagem,
            ]);
        }
    }


    $consulta = $conn->prepare(
        'SELECT id, mensagem, criado_em
         FROM public.historico_notificacoes
         WHERE usuario_id = :id
         ORDER BY criado_em DESC, id DESC
         LIMIT 30'
    );

    $consulta->execute(['id' => $usuarioId]);
    $historico = $consulta->fetchAll(PDO::FETCH_ASSOC);

    foreach ($historico as &$item) {
        $item['criado_em'] = (
            new DateTimeImmutable($item['criado_em'])
        )->format(DateTimeInterface::ATOM);
    }
    unset($item);

    responder([
        'usuario' => $usuarioId,
        'csrf' => $token,
        'ativado' => $ativado,
        'lembretes' => $lembretes,
        'historico' => $historico,
    ]);
} catch (Throwable $erro) {
    error_log('Erro nas notificações: ' . $erro->getMessage());

    responder([
        'erro' => 'Não foi possível carregar ou salvar os lembretes.',
    ], 500);
}