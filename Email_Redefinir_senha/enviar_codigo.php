<?php

session_start();

require_once __DIR__ . '/../enviar_email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$email = $_POST['email'] ?? '';

if (!is_string($email)) {
    http_response_code(400);
    exit('E-mail inválido.');
}

$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Informe um e-mail válido.');
}

// Evita envios repetidos nesta sessão.
$ultimoEnvio = $_SESSION['recuperacao_ultimo_envio'] ?? 0;

if (time() - $ultimoEnvio < 60) {
    http_response_code(429);
    exit('Aguarde 1 minuto antes de solicitar outro código.');
}

$_SESSION['recuperacao_ultimo_envio'] = time();

// Remove qualquer autorização de recuperação anterior.
unset(
    $_SESSION['recuperacao_id'],
    $_SESSION['recuperacao_autorizada'],
    $_SESSION['recuperacao_expira']
);

try {
    require __DIR__ . '/../conexao.php';

    $conn->beginTransaction();

    // Bloqueia o registro durante a criação da solicitação.
    $consulta = $conn->prepare(
        'SELECT id
         FROM public.usuarios
         WHERE LOWER(email) = :email
         FOR UPDATE'
    );

    $consulta->execute(['email' => $email]);
    $usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Só envia quando há exatamente uma conta com esse e-mail.
    if (count($usuarios) === 1) {
        $usuarioId = (int) $usuarios[0]['id'];

        // Limite por conta, mesmo usando outra sessão.
        $limite = $conn->prepare(
            "SELECT
                COUNT(*) AS quantidade,
                COUNT(*) FILTER (
                    WHERE criado_em > NOW() - INTERVAL '1 minute'
                ) AS recentes
             FROM public.redefinicoes_senha
             WHERE usuario_id = :usuario_id
               AND criado_em > NOW() - INTERVAL '1 hour'"
        );

        $limite->execute(['usuario_id' => $usuarioId]);
        $envios = $limite->fetch(PDO::FETCH_ASSOC);

        if (
            (int) $envios['quantidade'] < 5 &&
            (int) $envios['recentes'] === 0
        ) {
            $codigo = (string) random_int(100000, 999999);
            $codigoHash = password_hash($codigo, PASSWORD_DEFAULT);

            // Invalida códigos anteriores dessa conta.
            $invalidar = $conn->prepare(
                'UPDATE public.redefinicoes_senha
                 SET utilizado_em = NOW()
                 WHERE usuario_id = :usuario_id
                   AND utilizado_em IS NULL'
            );

            $invalidar->execute(['usuario_id' => $usuarioId]);

            // Guarda o hash, nunca o código original.
            $inserir = $conn->prepare(
                "INSERT INTO public.redefinicoes_senha
                    (usuario_id, codigo_hash, expira_em)
                 VALUES
                    (:usuario_id, :codigo_hash,
                     NOW() + INTERVAL '10 minutes')
                 RETURNING id"
            );

            $inserir->execute([
                'usuario_id' => $usuarioId,
                'codigo_hash' => $codigoHash,
            ]);

            $solicitacaoId = (int) $inserir->fetchColumn();

            enviarEmailRecuperacao($email, $codigo);

            $conn->commit();

            session_regenerate_id(true);
            $_SESSION['recuperacao_id'] = $solicitacaoId;
        } else {
            $conn->commit();
        }
    } else {
        $conn->commit();
    }

    // Mesmo destino para não revelar se o e-mail está cadastrado.
    header('Location: ../codigo_redefinir_senha/index.php');
    exit;
} catch (Throwable $erro) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Erro na recuperação de senha: ' . $erro->getMessage());

    http_response_code(500);
    exit('Não foi possível concluir a solicitação. Tente novamente mais tarde.');
}