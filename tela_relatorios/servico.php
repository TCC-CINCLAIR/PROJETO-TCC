<?php

function diasRelatorio(): array
{
    return ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
}

function dataRelatorio(string $data): DateTimeImmutable
{
    return (new DateTimeImmutable($data))->setTimezone(new DateTimeZone('America/Sao_Paulo'));
}

function proximoDiaRelatorio(int $dia, DateTimeImmutable $depois): DateTimeImmutable
{
    if ($dia < 0 || $dia > 6) throw new InvalidArgumentException('Dia inválido.');
    $data = $depois->setTimezone(new DateTimeZone('America/Sao_Paulo'))->setTime(0, 0);
    do { $data = $data->modify('+1 day'); } while ((int) $data->format('w') !== $dia);
    return $data;
}

function preferenciaRelatorio(PDO $conn, int|string $usuario): ?array
{
    $stmt = $conn->prepare('SELECT * FROM relatorio_preferencias WHERE usuario_id = :usuario');
    $stmt->execute(['usuario' => $usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function salvarDiaRelatorio(PDO $conn, int|string $usuario, int $dia): void
{
    if ($dia < 0 || $dia > 6) throw new InvalidArgumentException('Dia inválido.');
    $agora = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
    $conn->beginTransaction();
    try {

        $stmt = $conn->prepare('SELECT pg_advisory_xact_lock(1128878675, CAST(:usuario AS INTEGER))');
        $stmt->execute(['usuario' => $usuario]);
        $p = preferenciaRelatorio($conn, $usuario);
        $inicio = $p ? dataRelatorio($p['inicio_periodo']) : $agora;

        $minimo = $p ? $agora : $agora->modify('+7 days');
        if ($p) {
            $stmt = $conn->prepare('SELECT EXISTS (SELECT 1 FROM relatorios_semanais WHERE usuario_id = :usuario)');
            $stmt->execute(['usuario' => $usuario]);
            if (!$stmt->fetchColumn()) $minimo = max($agora, $inicio->modify('+7 days'));
        }
        $proximo = proximoDiaRelatorio($dia, $minimo);
        if ($p && ((int) $p['dia_semana'] === $dia || dataRelatorio($p['proxima_geracao']) <= $agora)) {
            $proximo = dataRelatorio($p['proxima_geracao']);
        }
        $stmt = $conn->prepare('INSERT INTO relatorio_preferencias (usuario_id, dia_semana, inicio_periodo, proxima_geracao)
            VALUES (:usuario, :dia, :inicio, :proximo) ON CONFLICT (usuario_id) DO UPDATE
            SET dia_semana = EXCLUDED.dia_semana, proxima_geracao = EXCLUDED.proxima_geracao');
        $stmt->execute(['usuario' => $usuario, 'dia' => $dia, 'inicio' => $inicio->format(DATE_ATOM), 'proximo' => $proximo->format(DATE_ATOM)]);
        $conn->commit();
    } catch (Throwable $e) { if ($conn->inTransaction()) $conn->rollBack(); throw $e; }
}


function gerarRelatorioPendente(PDO $conn, int|string $usuario, callable $resumir): bool
{
    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare('SELECT pg_try_advisory_xact_lock(1128878675, CAST(:usuario AS INTEGER))');
        $stmt->execute(['usuario' => $usuario]);
        if (!$stmt->fetchColumn()) { $conn->commit(); return false; }
        $stmt = $conn->prepare('SELECT * FROM relatorio_preferencias WHERE usuario_id = :usuario AND proxima_geracao <= NOW() FOR UPDATE SKIP LOCKED');
        $stmt->execute(['usuario' => $usuario]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) { $conn->commit(); return false; }
        $stmt = $conn->prepare('SELECT m.*, (d.mensagem_id IS NOT NULL) AS destaque FROM chat_mensagens m
            JOIN chat_conversas c ON c.id = m.conversa_id
            LEFT JOIN relatorio_destaques d ON d.mensagem_id = m.id AND d.usuario_id = c.usuario_id
            WHERE c.usuario_id = :usuario AND m.criado_em >= :inicio AND m.criado_em < :fim ORDER BY m.criado_em, m.id');
        $stmt->execute(['usuario' => $usuario, 'inicio' => $p['inicio_periodo'], 'fim' => $p['proxima_geracao']]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $conn->prepare('SELECT titulo, resumo, mensagem_ids, data_relato, criado_em FROM relatorio_acontecimentos
            WHERE usuario_id = :usuario AND criado_em >= :inicio ORDER BY criado_em, id');
        $stmt->execute(['usuario' => $usuario, 'inicio' => $p['inicio_periodo']]);
        $conteudo = comporRelatorioAcontecimentos($mensagens, $stmt->fetchAll(PDO::FETCH_ASSOC), $p['proxima_geracao'], $resumir);
        if (!is_string($conteudo) || trim($conteudo) === '') throw new RuntimeException('Resumo vazio.');
        $stmt = $conn->prepare('INSERT INTO relatorios_semanais (usuario_id, inicio_periodo, fim_periodo, conteudo) VALUES (:usuario, :inicio, :fim, :conteudo)');
        $stmt->execute(['usuario' => $usuario, 'inicio' => $p['inicio_periodo'], 'fim' => $p['proxima_geracao'], 'conteudo' => $conteudo]);
        $proximo = proximoDiaRelatorio((int) $p['dia_semana'], dataRelatorio($p['proxima_geracao']));
        $stmt = $conn->prepare('UPDATE relatorio_preferencias SET inicio_periodo = proxima_geracao, proxima_geracao = :proximo WHERE usuario_id = :usuario');
        $stmt->execute(['usuario' => $usuario, 'proximo' => $proximo->format(DATE_ATOM)]);
        $conn->commit();
        return true;
    } catch (Throwable $e) { if ($conn->inTransaction()) $conn->rollBack(); throw $e; }
}

function comporRelatorioAcontecimentos(array $mensagens, array $acontecimentos, string $fim, callable $resumir): string
{
    $cobertos = []; $envios = []; $confirmados = [];
    foreach ($acontecimentos as $a) {
        foreach (json_decode($a['mensagem_ids'], true, 32, JSON_THROW_ON_ERROR) as $id) $cobertos[(int) $id] = true;

        if (dataRelatorio($a['criado_em']) < dataRelatorio($fim)) {
            $confirmados[] = $a['titulo'] . ' — relato em ' . dataRelatorio($a['data_relato'])->format('d/m/Y H:i') . "\n" . $a['resumo'];
        }
    }
    foreach ($mensagens as $m) {
        if (isset($cobertos[(int) $m['id']]) && isset($m['envio_id'])) $envios[$m['envio_id']] = true;
    }
    $restantes = array_values(array_filter($mensagens, static fn($m) => !isset($cobertos[(int) $m['id']]) && !isset($envios[$m['envio_id'] ?? ''])));
    $temRelatos = (bool) array_filter($restantes, static fn($m) => $m['remetente'] === 'user');
    $partes = [];
    if ($confirmados) $partes[] = "Acontecimentos guardados por você\n\n" . implode("\n\n", $confirmados);
    if ($temRelatos) $partes[] = $resumir($restantes);
    return $partes ? implode("\n\n", $partes) : 'Não houve outros acontecimentos neste período.';
}
