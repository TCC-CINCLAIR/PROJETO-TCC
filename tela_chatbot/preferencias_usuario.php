<?php
require_once __DIR__ . '/personalidade_clarice.php';

function buscarPreferenciasUsuario(PDO $conexao, int|string $usuarioId): array
{

    $consulta = $conexao->prepare(
        'SELECT apelido, tom_conversa, reacao_chat, pergunta_chat, inicio_chat
         FROM preferencias_usuario
         WHERE usuario_id = :usuario_id
         ORDER BY atualizado_em DESC NULLS LAST
         LIMIT 1'
    );
    $consulta->execute(['usuario_id' => $usuarioId]);
    return normalizarPreferenciasClarice($consulta->fetch(PDO::FETCH_ASSOC) ?: []);
}

function conectarBancoPreferencias(): PDO
{
    require __DIR__ . '/../conexao.php';

    return $conn;
}

function carregarPreferenciasUsuario(int|string $usuarioId): array
{
    try {
        return buscarPreferenciasUsuario(conectarBancoPreferencias(), $usuarioId);
    } catch (Throwable $erro) {

        error_log('Clarice: preferências indisponíveis; usando padrão. Tipo: ' . get_class($erro));
        return normalizarPreferenciasClarice([]);
    }
}
