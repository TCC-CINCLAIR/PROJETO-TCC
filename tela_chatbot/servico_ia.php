<?php

require_once __DIR__ . '/personalidade_clarice.php';
require_once __DIR__ . '/historico_chat.php';



function consultarIA(string $mensagem, array $preferencias = [], array $historico = []): string
{

    $mensagem = trim($mensagem);
    if ($mensagem === '' || !mb_check_encoding($mensagem, 'UTF-8')
        || preg_match('/^\s*$/u', $mensagem) || mb_strlen($mensagem, 'UTF-8') > 2000) {
        throw new InvalidArgumentException('Escreva uma mensagem de até 2.000 caracteres.');
    }

    return chamarModeloClarice(array_merge([
        ['role' => 'system', 'content' => instrucoesClarice($preferencias)],
    ], limitarContextoChat($historico), [
        ['role' => 'user', 'content' => $mensagem],
    ]), 700);
}


function chamarModeloClarice(array $mensagens, int $limiteTokens): string
{

    $arquivoConfig = __DIR__ . '/config.local.php';
    if (!is_file($arquivoConfig)) {
        throw new RuntimeException('Configure a conexão com a IA no arquivo config.local.php.');
    }
    $config = require $arquivoConfig;
    $chave = is_array($config) ? ($config['api_key'] ?? '') : '';
    if (!is_string($chave) || trim($chave) === '') {
        throw new RuntimeException('Preencha a chave da Groq em config.local.php.');
    }
    $chave = trim($chave);
    if (preg_match('/[\r\n]/', $chave)) {
        throw new RuntimeException('A chave da Groq deve estar em uma única linha.');
    }
    if (!extension_loaded('curl')) {
        throw new RuntimeException('Ative a extensão cURL no PHP para conectar à IA.');
    }



    $modelo = $config['model'] ?? 'qwen/qwen3.8-27b';
    if (!is_string($modelo) || trim($modelo) === '') {
        throw new RuntimeException('Informe um modelo válido na configuração.');
    }
    $dados = [
        'model' => trim($modelo),
        'messages' => $mensagens,
        'max_completion_tokens' => $limiteTokens,
        'stream' => false,
    ];
    if (str_starts_with($modelo, 'qwen/')) {
        $dados['reasoning_effort'] = 'none';
        $dados['reasoning_format'] = 'hidden';
    }


    $curl = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $chave,
        ],
        CURLOPT_POSTFIELDS => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    ]);
    $corpo = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);


    if ($corpo === false) {
        throw new RuntimeException('Não foi possível conectar à Groq. Confira a conexão e a configuração HTTPS do PHP.');
    }
    if ($status === 429) {
        throw new RuntimeException('O limite de uso da IA foi atingido. Aguarde antes de tentar novamente.', 429);
    }
    if ($status === 401 || $status === 403) {
        throw new RuntimeException('A Groq recusou o acesso. Confira sua chave e as permissões da conta.');
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('A Groq não concluiu a solicitação (HTTP ' . $status . '). Confira o modelo configurado ou tente mais tarde.');
    }


    $retorno = json_decode($corpo, true);
    $escolha = $retorno['choices'][0] ?? [];
    $resposta = $escolha['message']['content'] ?? null;
    if (!is_string($resposta) || trim($resposta) === '' || ($escolha['finish_reason'] ?? '') !== 'stop') {
        throw new RuntimeException('A IA não retornou uma resposta completa. Tente novamente.');
    }
    return trim($resposta);
}
