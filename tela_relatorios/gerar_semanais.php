<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('display_errors', '0');
require_once __DIR__ . '/resumo.php';
$resumir = 'resumirPeriodoSemanal';
try {
    require_once __DIR__ . '/../conexao.php';
    $usuarios = $conn->query('SELECT usuario_id FROM relatorio_preferencias WHERE proxima_geracao <= NOW() ORDER BY proxima_geracao')->fetchAll(PDO::FETCH_COLUMN);
    $total = 0; $falhas = 0;
    foreach ($usuarios as $usuario) {
        try {
            // Recupera períodos atrasados em ordem, sem misturar semanas ou gerar duplicatas.
            for ($i = 0; $i < 52 && gerarRelatorioPendente($conn, $usuario, $resumir); $i++) $total++;
        } catch (Throwable $e) {
            $falhas++;
            // Registra apenas tipo/código; não imprime relatos, credenciais ou resposta da IA.
            fwrite(STDERR, 'Falha em um período: ' . get_class($e) . ' (código ' . (int) $e->getCode() . ").\n");
        }
    }
    echo "$total relatório(s) gerado(s); $falhas conta(s) com falha.\n";
    exit($falhas ? 1 : 0);
} catch (Throwable $e) { fwrite(STDERR, "Falha na conexão ou consulta dos relatórios.\n"); exit(1); }
