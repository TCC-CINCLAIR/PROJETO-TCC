<?php

session_start();
function avisarErroCodigo(string $mensagem): void
{
    $_SESSION['erro_codigo'] = $mensagem;

    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$solicitacaoId = $_SESSION['recuperacao_id'] ?? null;
$codigo = $_POST['codigo'] ?? '';

if (!$solicitacaoId) {
    avisarErroCodigo('Solicite um novo código pela tela de recuperação.');
}

if (!is_string($codigo) || !preg_match('/^[0-9]{6}$/', $codigo)) {
    avisarErroCodigo('Digite um código de 6 números.');
}

// A autorização só será concedida depois de validar o código.
unset(
    $_SESSION['recuperacao_autorizada'],
    $_SESSION['recuperacao_expira']
);

try {
    require __DIR__ . '/../conexao.php';

    $conn->beginTransaction();

    $consulta = $conn->prepare(
        'SELECT *,
                EXTRACT(EPOCH FROM expira_em) AS expira_timestamp,
                CASE WHEN expira_em > NOW() THEN 1 ELSE 0 END AS vigente
         FROM public.redefinicoes_senha
         WHERE id = :id
         FOR UPDATE'
    );

    $consulta->execute(['id' => $solicitacaoId]);
    $solicitacao = $consulta->fetch(PDO::FETCH_ASSOC);

    if (
        !$solicitacao ||
        (int) $solicitacao['vigente'] !== 1 ||
        $solicitacao['utilizado_em'] !== null ||
        $solicitacao['validado_em'] !== null ||
        (int) $solicitacao['tentativas'] >= 5
    ) {
        $conn->rollBack();
        unset($_SESSION['recuperacao_id']);

        avisarErroCodigo('Código expirado ou indisponível. Solicite um novo código.');
    }

    if (!password_verify($codigo, $solicitacao['codigo_hash'])) {
        $atualizar = $conn->prepare(
            'UPDATE public.redefinicoes_senha
             SET tentativas = tentativas + 1
             WHERE id = :id'
        );

        $atualizar->execute(['id' => $solicitacaoId]);
        $conn->commit();

        $restantes = 5 - ((int) $solicitacao['tentativas'] + 1);

        if ($restantes === 0) {
            unset($_SESSION['recuperacao_id']);
            avisarErroCodigo('Limite de tentativas atingido. Solicite um novo código.');
        }

        avisarErroCodigo(
            "Código incorreto. Restam {$restantes} tentativas."
        );
    }

    $validar = $conn->prepare(
        'UPDATE public.redefinicoes_senha
         SET validado_em = NOW()
         WHERE id = :id'
    );

    $validar->execute(['id' => $solicitacaoId]);
    $conn->commit();

    session_regenerate_id(true);

    $_SESSION['recuperacao_autorizada'] = $solicitacaoId;
    $_SESSION['recuperacao_expira'] =
        (int) $solicitacao['expira_timestamp'];

    header('Location: ../Redefinir_senha/index.php');
    exit;
} catch (Throwable $erro) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Erro ao validar recuperação: ' . $erro->getMessage());

    avisarErroCodigo(
        'Não foi possível validar o código. Tente novamente mais tarde.'
    );
}