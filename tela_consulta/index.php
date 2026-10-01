<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
  header("Location: ../tela_login/index.html?aviso=acesso-negado");
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcar Consulta</title>

    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/responsivo.css?v=<?= filemtime(__DIR__ . '/../assets/css/responsivo.css') ?>">
</head>

<body class="pagina-interna pagina-consulta">

<header>

    <nav class="top-menu" aria-label="Navegação principal">

        <a href="#" class="menu-btn">
            <img src="imagens_navbar/Config.png">
            <span>Dashboard</span>
        </a>

        <a href="../tela_chatbot/index.php" class="menu-btn">
            <img src="imagens_navbar/Chat.png">
            <span>Chatbot</span>
        </a>

        <a href="../tela_perfil/index.php" class="menu-btn">
            <img src="imagens_navbar/User.png">
            <span>Perfil</span>
        </a>

        <a href="../agenda_tarefas/index.php" class="menu-btn">
            <img src="imagens_navbar/Calendário.png">
            <span>Agenda</span>
        </a>

        <a href="#" class="menu-btn active" aria-current="page">
            <img src="imagens_navbar/Relógio.png">
            <span>Consultas</span>
        </a>
    </nav>

</header>

<main class="container">
    <section class="consulta-box">
        <div class="top-area">
            <div class="title">
                <h1>Marcar Consulta</h1>
                <p>Agende sua próxima consulta</p>
            </div>
            <button class="info-btn" onclick="mostrarAviso()" aria-label="Ver política de cancelamento">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1" stroke-linecap="round"/></svg>
            </button>

        </div>

        <div class="consulta-content">
            <div class="consulta-card">
                <div class="form-group form-full">
                    <label for="profissional"><img src="imagens_consulta/img_user_preto.png" alt="" class="input-icon">Profissional</label>
                    <select id="profissional">
                        <option value="">Selecione um profissional</option>
                        <option>Dr. Miguel Vasconcelos</option>
                        <option>Dra. Carla Vascovi</option>
                        <option>Dr. Carlos Alberto</option>
                        <option>Dra. Camila Carvalho</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dataConsulta"><img src="imagens_consulta/img_calendario_preto.png" alt="" class="input-icon">Data</label>
                    <input type="date" id="dataConsulta">
                </div>

                <div class="form-group">
                    <label for="horario"><img src="imagens_consulta/img_relógio_preto.png" alt="" class="input-icon">Horário</label>
                    <select id="horario">
                        <option>09:00</option>
                        <option>10:00</option>
                        <option>11:00</option>
                        <option>13:00</option>
                        <option>14:00</option>
                        <option>15:00</option>
                        <option>16:00</option>
                        <option>17:00</option>
                    </select>
                </div>

                <div class="form-group form-full">
                    <label for="observacoes">Observações <span class="optional">(opcional)</span></label>
                    <textarea id="observacoes"
                        placeholder="Algo que o profissional precisa saber?"></textarea>
                </div>

                <button class="confirmar-btn" onclick="confirmarConsulta()">
                    Confirmar Agendamento
                </button>
            </div>
        </div>
    </section>
</main>

<div class="overlay" id="overlay" role="dialog" aria-modal="true" aria-labelledby="aviso-title">
    <div class="aviso-box">
        <h2 id="aviso-title">Política de cancelamento</h2>
        <p>Cancelamentos devem ser feitos com pelo menos 24 horas de antecedência.</p>
        <button class="confirmar-btn" type="button" onclick="fecharAviso()">Entendi</button>
    </div>
</div>

<script src="script.js?v=<?= filemtime(__DIR__ . '/script.js') ?>"></script>

</body>

</html>
