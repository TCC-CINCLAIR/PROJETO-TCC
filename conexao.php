<?php
$config = require dirname(__DIR__, 2) . '/config-tcc/banco.php';

$conn = new PDO(
    $config['dsn'],
    $config['usuario'],
    $config['senha'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);
?>