<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../tela_login/index.html?aviso=acesso-negado");
    exit;
}

// TODO: adicionar o processamento da resposta e a conexao com o banco de dados.
