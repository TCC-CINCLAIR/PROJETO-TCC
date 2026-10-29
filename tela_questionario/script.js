
const formulario = document.getElementById('questionario');
const campos = formulario.querySelectorAll('input, textarea');
const aviso = document.getElementById('aviso');
const chave = 'cinclair_questionario_preferencias';
let respostas = {};


try {
    const salvas = JSON.parse(localStorage.getItem(chave));
    if (salvas && typeof salvas === 'object' && !Array.isArray(salvas)) {
        respostas = salvas;
    }
} catch {
    aviso.textContent = 'Não foi possível recuperar as respostas anteriores.';
}


campos.forEach(function (campo) {
    const resposta = respostas[campo.name];
    if (typeof resposta === 'string') {
        if (campo.type === 'radio') {
            campo.checked = campo.value === resposta;
        } else {
            campo.value = resposta;
        }
    }
});


function salvarResposta() {
    campos.forEach(function (campo) {
        if (campo.type !== 'radio' || campo.checked) {
            respostas[campo.name] = campo.value.trim();
        }
    });

    try {
        localStorage.setItem(chave, JSON.stringify(respostas));
        aviso.textContent = '';
        return true;
    } catch {
        aviso.textContent = 'O navegador não permitiu salvar. Libere o armazenamento deste site para continuar.';
        return false;
    }
}

formulario.addEventListener('input', salvarResposta);


formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (salvarResposta()) {

        window.location.href = formulario.action;
    }
});
