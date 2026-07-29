const parametros = new URLSearchParams(window.location.search);

const aviso = parametros.get("aviso");

if (aviso === "email-existente"){
    alert("Email já cadastrado! Faça login para prosseguir.")
}

if (aviso === "dados-invalidos"){
    alert("Credenciais Inválidas! " + 'Tente novamente ou clique em "Criar Conta."');
}

if (aviso === "acesso-negado") {
    alert("Faça login para acessar a página!");
}

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
