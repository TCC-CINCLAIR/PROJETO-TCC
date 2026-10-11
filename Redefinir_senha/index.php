<?php
session_start();

header('Cache-Control: no-store');
$sucesso = $_SESSION['senha_redefinida_sucesso'] ?? false;
unset($_SESSION['senha_redefinida_sucesso']);

$autorizacao = $_SESSION['recuperacao_autorizada'] ?? null;
$expira = $_SESSION['recuperacao_expira'] ?? 0;

if (!$sucesso && (!$autorizacao || time() >= $expira)) {
    unset(
        $_SESSION['recuperacao_id'],
        $_SESSION['recuperacao_autorizada'],
        $_SESSION['recuperacao_expira'],
        $_SESSION['recuperacao_csrf']
    );

    header('Location: ../Email_Redefinir_senha/index.html');
    exit;
}

if (empty($_SESSION['recuperacao_csrf'])) {
    $_SESSION['recuperacao_csrf'] = bin2hex(random_bytes(32));
}
?>


<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Redefinição de Senha</title>

    <link rel="stylesheet" href="style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
      <link rel="stylesheet" href="../assets/css/responsivo.css">
    <link rel="stylesheet" href="../modo_escuro.css">
</head>
  <body class="pagina-acesso">
    <div class="moldura-cartao">
      <div class="cartao">
        <div class="circulo-cadeado">
          <svg viewBox="0 0 24 24">
            <path
              d="M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5zm-3 8V6a3 3 0 0 1 6 0v3zm3 4a2 2 0 0 1 1 3.73V19h-2v-2.27A2 2 0 0 1 12 13z"
            />
          </svg>
        </div>

        <?php if ($sucesso): ?>

          <h1>Senha alterada!</h1>

          <p class="subtitulo">
            Sua senha foi redefinida com sucesso.
            Entre com sua nova senha.
          </p>

          
          
          <a
          href="../tela_login/index.html"
          style="display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 48px;
            box-sizing: border-box;
            background-color: #990000;
            color: white;
            border-radius: 50px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            "
          >
            Ir para o login
          </a>



        <?php else: ?>

        <h1>Redefinição de senha</h1>
        <p class="subtitulo">Informe a nova senha</p>

        <form id="form-redefinicao" action="salvar_senha.php" method="post">
          <input
            type="hidden"
            name="csrf"
            value="<?= htmlspecialchars($_SESSION['recuperacao_csrf'], ENT_QUOTES, 'UTF-8') ?>"
          >
          <div class="Grupo-nova-senha">
            <label for="nova-senha">Nova Senha</label>
            <div class="caixa-input-1">
              <svg class="icone-cadeado" viewBox="0 0 24 24">
                <rect x="5" y="10" width="14" height="10" rx="2" />
                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
              </svg>

              <input
                type="password"
                id="nova-senha"
                name="nova-senha"
                placeholder="Digite a nova senha"
                required
                minlength="8"
                autocomplete="new-password"
              />

              <button
                type="button"
                class="alternar-senha"
                data-target="nova-senha"
                aria-label="Mostrar senha"
              >
                <svg class="icone-olho" viewBox="0 0 24 24">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
            </div>
          </div>

          <div class="Grupo-confirmar-senha">
            <label for="confirma-senha">Confirme a senha</label>
            <div class="caixa-input-2">
              <svg class="icone-cadeado" viewBox="0 0 24 24">
                <rect x="5" y="10" width="14" height="10" rx="2" />
                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
              </svg>

              <input
                type="password"
                id="confirma-senha"
                name="confirma-senha"
                placeholder="Confirme a nova senha"
                required
                minlength="8"
                autocomplete="new-password"
              />

              <button
                type="button"
                class="alternar-senha"
                data-target="confirma-senha"
                aria-label="Mostrar senha"
              >
                <svg class="icone-olho" viewBox="0 0 24 24">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="botao-confirmar">Confirmar</button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$sucesso): ?>
      <script src="script.js?v=2"></script>
    <?php endif; ?>
    
      <script src="../modo_escuro.js"></script>
</body>
</html>
