<?php

// Teste manual pelo terminal. O navegador não pode disparar chamadas por aqui.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
ini_set('display_errors', '0');
require_once __DIR__ . '/servico_ia.php';

try {
    // Usa uma frase fictícia, sem informações pessoais.
    $resposta = consultarIA('Responda apenas: Conexão funcionando.');
    echo 'Resposta da IA: ' . $resposta . PHP_EOL;
} catch (Throwable $erro) {
    // Não imprime rastreamentos, configurações ou a chave da API.
    echo 'Teste não concluído: ' . $erro->getMessage() . PHP_EOL;
    exit(1);
}
