<?php
require_once __DIR__ . '/personalidade_clarice.php';

function buscarPreferenciasUsuario(PDO $conexao, int|string $usuarioId): array
{
    // Apelido e quatro escolhas de conversa. Não lê idade ou observações.
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
    $arquivo = __DIR__ . '/config.banco.local.php';
    if (!is_file($arquivo)) {
        throw new RuntimeException('Configuração local do banco ausente.');
    }
    $config = require $arquivo;
    return new PDO($config['dsn'], $config['usuario'], $config['senha'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function carregarPreferenciasUsuario(int|string $usuarioId): array
{
    try {
        return buscarPreferenciasUsuario(conectarBancoPreferencias(), $usuarioId);
    } catch (Throwable $erro) {
        // Em falha do banco, a conversa continua com o padrão. O log não inclui
        // credenciais nem valores de preferências.
        error_log('Clarice: preferências indisponíveis; usando padrão. Tipo: ' . get_class($erro));
        return normalizarPreferenciasClarice([]);
    }
}
