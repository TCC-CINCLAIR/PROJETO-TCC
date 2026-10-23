<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['sessao_id'])) {
    http_response_code(401);
    exit(json_encode(['sucesso' => false]));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['sucesso' => false]));
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit(json_encode(['sucesso' => false]));
}

try {
    require __DIR__ . '/../conexao.php';
    $stmt = $conn->prepare('UPDATE public.marcar_consulta
        SET concluida_agenda = TRUE
        WHERE id = :id AND id_usuario = :usuario');
    $stmt->execute(['id' => $id, 'usuario' => $_SESSION['sessao_id']]);
    echo json_encode(['sucesso' => $stmt->rowCount() > 0]);
} catch (Throwable $erro) {
    error_log('Erro ao concluir consulta: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode(['sucesso' => false]);
}
