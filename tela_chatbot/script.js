const formulario = document.getElementById('chat-form');
const campoMensagem = document.getElementById('mensagem');
const mensagens = document.getElementById('mensagens');
const botaoEmoji = document.getElementById('emoji-btn');
const seletorEmoji = document.getElementById('emoji-picker');

function fecharEmojis() {
    seletorEmoji.hidden = true;
    botaoEmoji.setAttribute('aria-expanded', 'false');
}

botaoEmoji.addEventListener('click', () => {
    seletorEmoji.hidden = !seletorEmoji.hidden;
    botaoEmoji.setAttribute('aria-expanded', String(!seletorEmoji.hidden));
    if (!seletorEmoji.hidden) seletorEmoji.querySelector('button').focus();
});

seletorEmoji.addEventListener('click', (evento) => {
    const botao = evento.target.closest('button');
    if (!botao) return;
    const inicio = campoMensagem.selectionStart;
    const fim = campoMensagem.selectionEnd;
    if (campoMensagem.value.length - (fim - inicio) + botao.textContent.length <= campoMensagem.maxLength) {
        campoMensagem.setRangeText(botao.textContent, inicio, fim, 'end');
    }
    fecharEmojis();
    campoMensagem.focus();
});

document.addEventListener('click', (evento) => {
    if (!evento.target.closest('.emoji-container')) fecharEmojis();
});

document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && !seletorEmoji.hidden) {
        fecharEmojis();
        botaoEmoji.focus();
    }
});

formulario.addEventListener('submit', (evento) => {
    evento.preventDefault();
    const texto = campoMensagem.value.trim();
    if (!texto) {
        campoMensagem.focus();
        return;
    }

    // Prévia da interface: nenhuma mensagem é enviada ou armazenada.
    const mensagem = document.createElement('div');
    mensagem.className = 'mensagem mensagem-usuario';
    const autor = document.createElement('span');
    autor.className = 'sr-only';
    autor.textContent = 'Você: ';
    const conteudo = document.createElement('p');
    conteudo.textContent = texto;
    mensagem.append(autor, conteudo);
    mensagens.appendChild(mensagem);
    document.getElementById('aviso-previa').hidden = false;
    campoMensagem.value = '';
    fecharEmojis();
    mensagens.scrollTop = mensagens.scrollHeight;
    campoMensagem.focus();
});
