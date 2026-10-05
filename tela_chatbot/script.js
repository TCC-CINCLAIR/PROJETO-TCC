const formulario = document.getElementById('chat-form');
const campoMensagem = document.getElementById('mensagem');
const mensagens = document.getElementById('mensagens');
const botaoEmoji = document.getElementById('emoji-btn');
const seletorEmoji = document.getElementById('emoji-picker');
const botaoEnviar = formulario.querySelector('button[type="submit"]');
const aviso = document.getElementById('chat-aviso');
let enviando = false;

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

// Usa textContent para exibir mensagens como texto, sem executar HTML.
function adicionarMensagem(texto, remetente) {
    const mensagem = document.createElement('div');
    mensagem.className = `mensagem mensagem-${remetente}`;
    const autor = document.createElement('span');
    autor.className = 'sr-only';
    autor.textContent = remetente === 'usuario' ? 'Você: ' : 'Clarice: ';
    const conteudo = document.createElement('p');
    conteudo.textContent = texto;
    mensagem.append(autor, conteudo);
    mensagens.appendChild(mensagem);
    mensagens.scrollTop = mensagens.scrollHeight;
    return mensagem;
}

formulario.addEventListener('submit', async (evento) => {
    // Evita que o formulário recarregue a página.
    evento.preventDefault();
    if (enviando) return;

    const texto = campoMensagem.value.trim();
    if (!texto) {
        campoMensagem.focus();
        return;
    }

    enviando = true;
    botaoEnviar.disabled = true;
    botaoEmoji.disabled = true;
    campoMensagem.disabled = true;
    formulario.setAttribute('aria-busy', 'true');
    fecharEmojis();
    aviso.classList.remove('erro');
    aviso.textContent = 'Aguardando resposta…';
    aviso.hidden = false;
    const mensagemEnviada = adicionarMensagem(texto, 'usuario');
    campoMensagem.value = '';

    const controle = new AbortController();
    const tempoLimite = setTimeout(() => controle.abort(), 35000);

    try {
        // O navegador chama nosso PHP. A chave da API fica no servidor.
        const retorno = await fetch(formulario.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': formulario.dataset.csrf,
            },
            body: JSON.stringify({ mensagem: texto }),
            signal: controle.signal,
        });
        const dados = await retorno.json();
        if (!retorno.ok) {
            throw new Error(typeof dados?.erro === 'string' ? dados.erro : 'Não foi possível enviar sua mensagem.');
        }
        if (typeof dados?.resposta !== 'string' || !dados.resposta.trim()) {
            throw new Error('A resposta não chegou completa. Tente novamente.');
        }

        adicionarMensagem(dados.resposta, 'clarice');
        aviso.hidden = true;
    } catch (erro) {
        // Devolve o texto ao campo para tentar novamente sem duplicar a mensagem.
        mensagemEnviada.remove();
        campoMensagem.value = texto;
        aviso.classList.add('erro');
        aviso.textContent = erro.name === 'AbortError'
            ? 'A resposta demorou demais. Tente novamente.'
            : (erro instanceof TypeError || erro instanceof SyntaxError)
                ? 'Não foi possível conectar ao chat. Confira sua conexão e tente novamente.'
                : erro.message;
        aviso.hidden = false;
    } finally {
        clearTimeout(tempoLimite);
        enviando = false;
        botaoEnviar.disabled = false;
        botaoEmoji.disabled = false;
        campoMensagem.disabled = false;
        formulario.removeAttribute('aria-busy');
        campoMensagem.focus();
    }
});
