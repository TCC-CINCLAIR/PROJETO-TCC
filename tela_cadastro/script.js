const campoSenha = document.getElementById("senha");
const botaoExibir = document.getElementById("exibir-senha");

botaoExibir.addEventListener ("click", function() {
    if (campoSenha.type === "password") {
        campoSenha.type = "text";
    }
    else {
        campoSenha.type = "password";
    }
});

const botaoCadastrar = document.getElementById("btn-cadastrar");

botaoCadastrar.form.addEventListener ("submit", function(evento){
    const confirmar = confirm("Verifique se os dados foram preenchidos corretamente");
    if (!confirmar){
        evento.preventDefault();
        return;
    }
    botaoCadastrar.disabled = true;
    botaoCadastrar.textContent = "Cadastrando…";
});

// Restaura o formulário se a pessoa voltar pelo histórico do navegador.
window.addEventListener("pageshow", function () {
    botaoCadastrar.disabled = false;
    botaoCadastrar.textContent = "Cadastrar";
});
