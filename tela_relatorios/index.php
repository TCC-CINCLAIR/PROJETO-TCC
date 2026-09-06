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
    <title>Perfil de Usuário</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="top-menu">

            <a href="" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</button></a>

            <a href="" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</button></a>

            <a href="" class="menu-btn-active"><button ><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</button></a>

            <a href="../agenda_tarefas/index.php" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</button></a>
      
            <a href="../tela_consulta/index.php" class="menu-btn"><button ><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</button></a>

        </nav>
    </header>
    <main>
        <section class="relatorios-box">
            <div class="titulo">
                <img src="../imagens/imagens_perfil/img-docs.png" alt="Relatórios">
                <h1>Central de Relatórios</h1>
            </div>

            <section class="lista-relatorios">
                <div class="relatorio">
                    <div class="data">
                        <strong>30</strong>
                        <span>jul.</span>
                    </div>

                    <div class="informacoes">
                        <strong>
                            Título que dá uma prévia do que foi conversado
                        </strong>
                        <p>30/07/2026 - 13:45</p>
                    </div>
                    <button class="download">↓</button>
                </div>
            
                <div class="relatorio">
                    <div class="data">
                        <strong>10</strong>
                        <span>jun.</span>
                    </div>

                    <div class="informacoes">
                        <strong>
                            Título que dá uma prévia do que foi conversado
                        </strong>
                        <p>10/06/2026 - 11:20</p>
                    </div>
                    <button class="download">↓</button>
                </div>

                <div class="relatorio">
                    <div class="data">
                        <strong>09</strong>
                        <span>mar.</span>
                    </div>

                    <div class="informacoes">
                        <strong>
                            Título que dá uma prévia do que foi conversado
                        </strong>
                        <p>09/03/2026 - 18:45</p>
                    </div>
                    <button class="download">↓</button>
                </div>

                <div class="relatorio">
                    <div class="data">
                        <strong>16</strong>
                        <span>jan.</span>
                    </div>

                    <div class="informacoes">
                        <strong>
                            Título que dá uma prévia do que foi conversado
                        </strong>
                        <p>16/01/2026 - 20:15</p>
                    </div>
                    <button class="download">↓</button>
                </div>
            </section>

            <a href="../tela_perfil/index.php" class="voltar">
                ← Voltar para o perfil
            </a>
        </section>
    </main>
</body>
</html>

