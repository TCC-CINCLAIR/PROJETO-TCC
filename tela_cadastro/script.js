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

botaoCadastrar.addEventListener ("click", function(evento){
    const confirmar = confirm("Verifique se os dados foram preenchidos corretamente");
    if (!confirmar){
        evento.preventDefault();
    }
});
