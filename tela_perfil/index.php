<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
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
        'id' => $_SESSION['sessao_id'],
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
    <title>Perfil de Usuário</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <link rel="stylesheet" href="../modo_escuro.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/responsivo.css?v=<?= filemtime(__DIR__ . '/../assets/css/responsivo.css') ?>">
</head>
<body class="pagina-interna pagina-perfil">
    <header>
        <nav class="top-menu" aria-label="Navegação principal">

            <a href="" class="menu-btn"><img src="../imagens/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</a>

            <a href="../tela_chatbot/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</a>

            <a href="" class="menu-btn-active" aria-current="page"><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</a>

            <a href="../agenda_tarefas/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</a>
      
            <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</a>

        </nav>
    </header>
    <main>
        <section class="info-usuario">
            <section class="saudacoes">
                <div class="img-perfil"><img src="../imagens/imagens_navbar/User.png" alt="Imagem Usuário"></div>
                <p>Olá, <?= htmlspecialchars($dadosUsuario['usuario'], ENT_QUOTES, 'UTF-8') ?></p>
            </section>
            <section class="dados-usuario">
                <label for="nome">Nome</label><input type="text" value="<?= htmlspecialchars($dadosUsuario['usuario'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="nome">Email</label><input type="email" value="<?= htmlspecialchars($dadosUsuario['email'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="nome">Senha</label><input type="password" value="&bull">
                <div class="btn-redefinir">
                    <a href="../Redefinir_senha/index.html"><button class="btn-redefinir-senha">Redefinir Senha</button></a>
                </div>
            </section>
        </section>
        <section class="opcoes-sistema">
            <h2>Configurações</h2>
            <section class="aba-botoes">
                <div class="btn-relatorios">
                    <a href="../tela_relatorios/index.php"><button><img src="../imagens/imagens_perfil/img-docs.png" alt=""><span>Relatórios <small>Revise seu histórico</small></span></button></a>
                </div>
                <div class="btn-preferencias">    
                    <a href="../tela_preferencias/index.php"><button><img src="../imagens/imagens_perfil/img-config.png" alt=""><span>Preferências <small>Personalize sua experiência</small></span></button></a>
                </div>
                <div class="btn-notificacao">    
                    <a href="#"><button><img src="../imagens/imagens_perfil/img-sino.png" alt=""><span>Notificações <small>Gerencia seus alertas</small></span></button></a>
                </div>
                <div class="btn-privacidade">    
                    <a href="../tela_privacidade/index.php"><button><img src="../imagens/imagens_perfil/img-escudo.png" alt=""><span>Privacidade de Segurança <small>Controle seus dados</small></span></button></a>
                </div>
            </section>
            <div class="logout">
                <a href="../tela_login/index.html"><button class="btn-logout"> &rarr;]  Sair da conta</button></a>
            </div>
            <div class="contato">
                <a target="_blank" href="https://www.instagram.com/cinclaair"><p>Fale Conosco</p></a>
            </div>
        </section>
    </main>
    <script src="../modo_escuro.js"></script>

    </body>
</html>
