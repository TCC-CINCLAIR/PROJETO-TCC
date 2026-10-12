<?php
session_start();
if (!isset($_SESSION['sessao_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}
require_once "../conexao.php";
$usuario_id = $_SESSION['sessao_id'];
$id = $_POST['id'];
$sql = "DELETE FROM agenda_tarefas
        WHERE id = :id AND id_usuario = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    'id' => $id,
    'usuario_id' => $usuario_id
]);
echo json_encode(['sucesso' => $stmt->rowCount() > 0]);
?>