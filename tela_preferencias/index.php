<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../tela_login/index.html?aviso=acesso-negado");
    exit;
}

try {
    $conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn -> setAttribute(
      PDO::ATTR_ERRMODE,
      PDO::ERRMODE_EXCEPTION  
    );

    $sql = "SELECT usuario, email FROM usuarios WHERE id = :id";
    $stmt = $conn -> prepare($sql);
    $stmt -> execute([
        'id' => $_SESSION['usuario_id']
    ]);
    $dadosUsuario = $stmt -> fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die($e);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferências</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="../modo_escuro.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="top-menu">
            <a href="" class="menu-btn"><button><img src="../imagens/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</button></a>
            <a href="" class="menu-btn"><button><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</button></a>
            <a href="../tela_perfil/index.php" class="menu-btn-active"><button><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</button></a>
            <a href="../agenda_tarefas/index.php" class="menu-btn"><button><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</button></a>
            <a href="../tela_consulta/index.php" class="menu-btn"><button><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</button></a>
        </nav>
    </header>
    <main>
        <section class="preferencias-box">
            <div class="titulo">
                <h1>Preferências</h1>
            </div>
            <section class="opcoes">
    <div class="opcao">
        <div>
            <strong>Ative o modo escuro</strong>
        </div>
        <label class="switch">
            <input type="checkbox" id="modoEscuro">
            <span></span>
        </label>
    </div>
    <div class="opcao">
        <div>
            <strong>Fonte adaptativa</strong>
        </div>
        <label class="switch">
            <input type="checkbox">
            <span></span>
        </label>
    </div>
</section>
            <a href="../tela_perfil/index.php" class="voltar">← Voltar para o perfil</a>
        </section>
    </main>
    <script src="../modo_escuro.js"></script>
</body>
</html>