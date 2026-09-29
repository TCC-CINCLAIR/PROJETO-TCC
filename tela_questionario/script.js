// Este arquivo só guarda e recupera respostas. Cada pergunta está no seu HTML.
const formulario = document.getElementById('questionario');
const campos = formulario.querySelectorAll('input, textarea');
const aviso = document.getElementById('aviso');
const chave = 'cinclair_questionario_preferencias';
let respostas = {};

// 1. Ler as respostas que já existem neste navegador.
try {
    const salvas = JSON.parse(localStorage.getItem(chave));
    if (salvas && typeof salvas === 'object' && !Array.isArray(salvas)) {
        respostas = salvas;
    }
} catch {
    aviso.textContent = 'Não foi possível recuperar as respostas anteriores.';
}

// 2. Preencher os campos desta página.
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

// 3. Guardar a resposta quando a pessoa preencher ou trocar uma opção.
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

// 4. O HTML valida os campos obrigatórios antes deste evento.
formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (salvarResposta()) {
        // Navegar sem colocar as respostas pessoais no endereço da página.
        window.location.href = formulario.action;
    }
});
