<?php

session_start();
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$solicitacaoId = $_SESSION['recuperacao_autorizada'] ?? null;
$expira = $_SESSION['recuperacao_expira'] ?? 0;

if (!$solicitacaoId || time() >= $expira) {
    http_response_code(403);
    exit('Autorização expirada. Solicite um novo código.');
}

$csrf = $_POST['csrf'] ?? '';
$csrfEsperado = $_SESSION['recuperacao_csrf'] ?? '';

if (
    !is_string($csrf) ||
    $csrfEsperado === '' ||
    !hash_equals($csrfEsperado, $csrf)
) {
    http_response_code(403);
    exit('Formulário inválido. Volte e atualize a página.');
}

$senha = $_POST['nova-senha'] ?? '';
$confirmacao = $_POST['confirma-senha'] ?? '';

if (!is_string($senha) || !is_string($confirmacao)) {
    http_response_code(400);
    exit('Dados inválidos.');
}

if ($senha !== $confirmacao) {
    exit('As senhas não coincidem. Volte e tente novamente.');
}


if (mb_strlen($senha, 'UTF-8') < 8 || strlen($senha) > 72) {
    exit('Use pelo menos 8 caracteres e no máximo 72 bytes.');
}

try {
    require __DIR__ . '/../conexao.php';

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $conn->beginTransaction();


    $consulta = $conn->prepare(
        'SELECT usuario_id
         FROM public.redefinicoes_senha
         WHERE id = :id'
    );

    $consulta->execute(['id' => $solicitacaoId]);
    $usuarioId = $consulta->fetchColumn();

    if ($usuarioId === false) {
        $conn->rollBack();
        exit('Solicitação inválida. Solicite um novo código.');
    }


    $bloquear = $conn->prepare(
        'SELECT id FROM public.usuarios WHERE id = :id FOR UPDATE'
    );

    $bloquear->execute(['id' => $usuarioId]);

    if ($bloquear->fetchColumn() === false) {
        $conn->rollBack();
        exit('Conta indisponível.');
    }


    $consumir = $conn->prepare(
        'UPDATE public.redefinicoes_senha
         SET utilizado_em = NOW()
         WHERE id = :id
           AND usuario_id = :usuario_id
           AND validado_em IS NOT NULL
           AND utilizado_em IS NULL
           AND expira_em > NOW()
           AND tentativas < 5
         RETURNING id'
    );

    $consumir->execute([
        'id' => $solicitacaoId,
        'usuario_id' => $usuarioId,
    ]);

    if ($consumir->fetchColumn() === false) {
        $conn->rollBack();
        exit('Solicitação expirada ou já utilizada. Solicite outro código.');
    }

    $atualizar = $conn->prepare(
        'UPDATE public.usuarios
         SET senha = :senha
         WHERE id = :id'
    );

    $atualizar->execute([
        'senha' => $senhaHash,
        'id' => $usuarioId,
    ]);


    $invalidar = $conn->prepare(
        'UPDATE public.redefinicoes_senha
         SET utilizado_em = NOW()
         WHERE usuario_id = :usuario_id
           AND utilizado_em IS NULL'
    );

    $invalidar->execute(['usuario_id' => $usuarioId]);

    $conn->commit();

    unset(
        $_SESSION['recuperacao_id'],
        $_SESSION['recuperacao_autorizada'],
        $_SESSION['recuperacao_expira'],
        $_SESSION['recuperacao_csrf'],
        $_SESSION['sessao_id']
    );

    session_regenerate_id(true);

    $_SESSION['senha_redefinida_sucesso'] = true;
    header('Location: index.php');
    exit;

} catch (Throwable $erro) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Erro ao salvar nova senha: ' . $erro->getMessage());

    http_response_code(500);
    exit('Não foi possível alterar a senha. Tente novamente mais tarde.');
}