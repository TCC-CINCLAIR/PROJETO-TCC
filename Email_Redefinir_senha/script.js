// valida o email antes de ir pra tela do código
const formEmail = document.getElementById('form-email');
const inputEmail = document.getElementById('email');

formEmail.addEventListener('submit', function (e) {
    e.preventDefault();

    if (inputEmail.checkValidity() && inputEmail.value.trim() !== '') {
        // aqui depois entra a chamada pro backend enviar o código
        window.location.href = 'index(email).html';
    } else {
        // mostra o aviso padrão do navegador (email inválido/vazio)
        inputEmail.reportValidity();
    }
});
