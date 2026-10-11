// Mostrar ou ocultar a senha.
document.querySelectorAll(".alternar-senha").forEach(function (botao) {
  botao.addEventListener("click", function () {
    const campo = document.getElementById(botao.dataset.target);
    const mostrar = campo.type === "password";

    campo.type = mostrar ? "text" : "password";
    botao.setAttribute(
      "aria-label",
      mostrar ? "Ocultar senha" : "Mostrar senha"
    );
  });
});

// Impede o envio apenas quando os campos são inválidos.
document
  .getElementById("form-redefinicao")
  .addEventListener("submit", function (evento) {
    const senha = document.getElementById("nova-senha").value;
    const confirmacao = document.getElementById("confirma-senha").value;

    if (senha !== confirmacao) {
      evento.preventDefault();
      alert("As senhas não coincidem.");
      return;
    }

    if (senha.length < 8) {
      evento.preventDefault();
      alert("A senha deve ter no mínimo 8 caracteres.");
    }
  });