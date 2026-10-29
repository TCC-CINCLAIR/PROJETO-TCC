<?php
session_start();
if (!isset($_SESSION['sessao_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}
require_once __DIR__ . '/../conexao.php';
$usuario_id = $_SESSION['sessao_id'];
$titulo = trim($_POST['titulo']);
$data = $_POST['data'];
$sql = "INSERT INTO agenda_tarefas (id_usuario, titulo_tarefa, data)
        VALUES (:usuario_id, :titulo, :data)
        RETURNING id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    'usuario_id' => $usuario_id,
    'titulo' => $titulo,
    'data' => $data
]);
$id = $stmt->fetchColumn();
echo json_encode(['sucesso' => true, 'id' => $id]);
?>
