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
        'id' => $_SESSION['sessao_id']
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
    <title>Privacidade e Segurança</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-interna pagina-configuracao">
    <header>
        <nav class="top-menu" aria-label="Navegação principal">
            <a href="../tela_dashboard/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</a>
            <a href="" class="menu-btn"><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</a>
            <a href="../tela_perfil/index.php" class="menu-btn-active" aria-current="page"><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</a>
            <a href="../agenda_tarefas/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</a>
            <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</a>
        </nav>
    </header>
    <main>
        <section class="privacidade-box">
            <div class="titulo">
                <img src="../imagens/imagens_perfil/img-escudo.png" alt="Privacidade e Segurança">
                <h1>Privacidade e Segurança</h1>
            </div>
        <section class="texto-privacidade">
            <p>A sua privacidade é importante para nós. É política do Cinclair respeitar a sua privacidade em relação a qualquer informação sua que possamos coletar no site Cinclair, e outros sites que possuímos e operamos.</p>
            <p>Solicitamos informações pessoais apenas quando realmente precisamos delas para lhe fornecer um serviço. Fazemo-lo por meios justos e legais, com o seu conhecimento e consentimento. Também informamos por que estamos coletando e como será usado.</p>
            <p>Apenas retemos as informações coletadas pelo tempo necessário para fornecer o serviço solicitado. Quando armazenamos dados, protegemos dentro de meios comercialmente aceitáveis ​​para evitar perdas e roubos, bem como acesso, divulgação, cópia, uso ou modificação não autorizados.</p>
            <p>Não compartilhamos informações de identificação pessoal publicamente ou com terceiros, exceto quando exigido por lei.</p>
            <p>Você é livre para recusar a nossa solicitação de informações pessoais, entendendo que talvez não possamos fornecer alguns dos serviços desejados.</p>
            <h2>Mais informações</h2>
            <p>Esperemos que esteja esclarecido e, como mencionado anteriormente, se houver algo que você não tem certeza se precisa ou não, geralmente é mais seguro deixar os cookies ativados, caso interaja com um dos recursos que você usa em nosso site.</p>
        </section>
            <a href="../tela_perfil/index.php" class="voltar">← Voltar para o perfil</a>
        </section>
    </main>
    <script src="../modo_escuro.js"></script>
</body>
</html>
