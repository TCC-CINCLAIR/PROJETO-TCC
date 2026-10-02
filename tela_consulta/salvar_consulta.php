<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}

require_once "../conexao.php";

$usuario_id = $_SESSION['usuario_id'];
$profissional = $_POST['profissional'] ?? '';
$data = $_POST['data'] ?? '';
$horario = $_POST['horario'] ?? '';
$observacoes = $_POST['observacoes'] ?? '';

if ($profissional === '' || $data === '' || $horario === '') {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Preencha todos os campos obrigatórios.'
    ]);
    exit;
}

try {
    $sql = "INSERT INTO marcar_consulta
            (id_usuario, profissional, data_consulta, horario, observacoes)
            VALUES
            (:id_usuario, :profissional, :data_consulta, :horario, :observacoes)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        'id_usuario' => $usuario_id,
        'profissional' => $profissional,
        'data_consulta' => $data,
        'horario' => $horario,
        'observacoes' => $observacoes
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Consulta agendada com sucesso!'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao salvar a consulta.'
    ]);
}
?>