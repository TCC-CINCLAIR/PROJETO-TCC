<?php
session_start();

if (!isset($_SESSION['sessao_id'])) {
  header("Location: ../tela_login/index.html?aviso=acesso-negado");
  exit;
}
require_once "../conexao.php";

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT profissional, data_consulta, horario, observacoes
        FROM marcar_consulta
        WHERE id_usuario = :usuario_id
        ORDER BY data_consulta, horario";

$stmt = $conn->prepare($sql);
$stmt->execute(['usuario_id' => $usuario_id]);

$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Agenda de Tarefas</title>

  <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/responsivo.css?v=<?= filemtime(__DIR__ . '/../assets/css/responsivo.css') ?>">
  <link rel="stylesheet" href="../modo_escuro.css">
</head>

<body class="pagina-interna pagina-agenda">

  <header>
    <nav class="top-menu" aria-label="Navegação principal">

      <a href="#" class="menu-btn"> <img src="../agenda_tarefas/imagens_navbar/Config.png" alt="IMG dashboard">Dashboard</a>

      <a href="../tela_chatbot/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Chat.png" alt="IMG Chat">Chatbot</a>

      <a href="../tela_perfil/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/User.png" alt="IMG Perfil">Perfil</a>

      <a href="#" class="menu-btn-active" aria-current="page"><img src="../imagens/imagens_navbar/Calendário.png" alt="IMG Agenda">Agenda</a>
      
      <a href="../tela_consulta/index.php" class="menu-btn"><img src="../imagens/imagens_navbar/Relógio.png" alt="IMG Consultas">Consultas</a>

    </nav>
  </header>

  <main class="container">    
    <div class="agenda-box">

      <div class="top-area">

        <div class="title">
          <h1>Agenda de Tarefas</h1>
          <p>Organize suas atividades diárias</p>
        </div>
        <div class="agenda-actions">
          <button class="new-task" onclick="openModal()"><span aria-hidden="true">+</span> Nova tarefa</button>
          <button type="button" class="menu-icon" onclick="openMenu()" aria-controls="sideMenu" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 6h12M9 12h12M9 18h12M3 6h1M3 12h1M3 18h1" stroke-linecap="round"/></svg>
            Pendências
          </button>
        </div>

      </div>

      <section class="tasks-section" aria-labelledby="tasks-heading">
        <h2 id="tasks-heading">Suas tarefas</h2>
        <div class="tasks-grid" id="tasksContainer"></div>
        <div class="empty-state" id="emptyTasks">
          <div class="empty-icon" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="11" width="30" height="29" rx="5"/><path d="M16 7v8m16-8v8M9 21h30m-23 8 5 5 10-10" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
          <h3>Um dia de cada vez</h3>
          <p>Suas tarefas aparecerão aqui.<br>Toque em <strong>Nova tarefa</strong> para começar.</p>
        </div>
      </section>

    </div>

  </main>


  <div class="modal" id="taskModal" role="dialog" aria-modal="true" aria-labelledby="task-modal-title">

    <div class="modal-content">

      <div class="dialog-heading">
        <h2 id="task-modal-title">Nova tarefa</h2>
        <button class="close-dialog" type="button" onclick="closeModal()" aria-label="Fechar nova tarefa">&times;</button>
      </div>
      <p class="dialog-description">Reserve um momento para o que importa.</p>

      <div class="form-group">

        <label for="taskTitle">Título da tarefa</label>

        <input 
          type="text" 
          id="taskTitle"
          placeholder="Ex: Meditação Matinal"
        >

      </div>

      <div class="form-group">

        <label for="taskDate">Data</label>

        <input 
          type="date"
          id="taskDate"
        >

      </div>

      <div class="modal-buttons">

        <button class="add-btn" onclick="addTask()">
          Adicionar
        </button>

        <button class="cancel-btn" onclick="closeModal()">
          Cancelar
        </button>

      </div>

    </div>

  </div>

  <div class="menu-backdrop" id="menuBackdrop" onclick="closeMenu()"></div>
  <div class="side-menu" id="sideMenu" role="dialog" aria-modal="true" aria-labelledby="pending-title">

    <div class="side-header">

      <h2 id="pending-title">Pendências</h2>

      <button class="close-menu" onclick="closeMenu()" aria-label="Fechar pendências">
        ✕
      </button>

    </div>

    <div class="month-item" onclick="toggleMonthTasks('Janeiro')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Janeiro"></div>

    <span>Janeiro</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Janeiro"></div>



<div class="month-item" onclick="toggleMonthTasks('Fevereiro')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Fevereiro"></div>

    <span>Fevereiro</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Fevereiro"></div>



<div class="month-item" onclick="toggleMonthTasks('Março')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Março"></div>

    <span>Março</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Março"></div>


<div class="month-item" onclick="toggleMonthTasks('Abril')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Abril"></div>

    <span>Abril</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Abril"></div>



<div class="month-item" onclick="toggleMonthTasks('Maio')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Maio"></div>

    <span>Maio</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Maio"></div>



<div class="month-item" onclick="toggleMonthTasks('Junho')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Junho"></div>

    <span>Junho</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Junho"></div>



<div class="month-item" onclick="toggleMonthTasks('Julho')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Julho"></div>

    <span>Julho</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Julho"></div>



<div class="month-item" onclick="toggleMonthTasks('Agosto')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Agosto"></div>

    <span>Agosto</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Agosto"></div>



<div class="month-item" onclick="toggleMonthTasks('Setembro')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Setembro"></div>

    <span>Setembro</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Setembro"></div>



<div class="month-item" onclick="toggleMonthTasks('Outubro')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Outubro"></div>

    <span>Outubro</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Outubro"></div>



<div class="month-item" onclick="toggleMonthTasks('Novembro')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Novembro"></div>

    <span>Novembro</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Novembro"></div>



<div class="month-item" onclick="toggleMonthTasks('Dezembro')">

  <div class="month-left">

    <div class="pending-dot" id="dot-Dezembro"></div>

    <span>Dezembro</span>

  </div>

  <span>›</span>

</div>

<div class="month-tasks" id="tasks-Dezembro"></div>
  </div>
  <script>
    const consultasBanco = <?= json_encode($consultas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  </script>

  <script src="script.js"></script>
  <script src="../modo_escuro.js"></script>

</body>
</html>
