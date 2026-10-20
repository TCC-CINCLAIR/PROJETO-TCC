<?php
session_start();

header('Cache-Control: no-store');

$erroCodigo = $_SESSION['erro_codigo'] ?? '';
unset($_SESSION['erro_codigo']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redefinição de Senha</title>

  <!-- fonte usada no título e nos textos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
</head>
<body class="pagina-acesso">

  <div class="card">

    <!-- ícone da chave -->
    <div class="icone-chave">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
      </svg>
    </div>

    <h1>Redefinição de senha</h1>
    <p class="subtitulo">Informe o código enviado no email</p>

    <form action="validar_codigo.php" method="post">
    <!-- campo de código -->
    <div class="campo">
      <label for="codigo">Codigo de Verificação</label>

      <div class="campo-codigo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
        </svg>
        <input
          type="text"
          id="codigo"
          name="codigo"
          placeholder="Código"
          inputmode="numeric"
          autocomplete="one-time-code"
          pattern="[0-9]{6}"
          maxlength="6"
          required
        >
      </div>
    </div>

      <button type="submit" class="btn-validar">Validar código</button>
    </form>

  </div>

    <script src="../modo_escuro.js"></script>
  <script>
  const campoCodigo = document.getElementById("codigo");

  const erroCodigo = <?= json_encode(
      $erroCodigo,
      JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
  ) ?>;

  // Remove o aviso anterior quando a pessoa começa a corrigir.
  campoCodigo.addEventListener("input", function () {
    campoCodigo.setCustomValidity("");
  });

  // Mostra o aviso com o visual nativo do navegador.
  if (erroCodigo) {
    campoCodigo.setCustomValidity(erroCodigo);

    window.addEventListener("load", function () {
      campoCodigo.focus();
      campoCodigo.reportValidity();
    });
  }
  </script>

</body>
</html>
