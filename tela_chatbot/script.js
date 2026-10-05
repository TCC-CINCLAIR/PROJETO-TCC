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

function mostrarDigitacao() {
    const balao = document.createElement('div');
    balao.className = 'mensagem mensagem-clarice mensagem-digitando';
    const descricao = document.createElement('span');
    descricao.className = 'sr-only';
    descricao.textContent = 'Clarice está preparando uma resposta…';
    balao.appendChild(descricao);

    for (let i = 0; i < 3; i++) {
        const ponto = document.createElement('span');
        ponto.className = 'ponto-digitacao';
        ponto.setAttribute('aria-hidden', 'true');
        balao.appendChild(ponto);
    }
    mensagens.appendChild(balao);
    mensagens.scrollTop = mensagens.scrollHeight;
    return balao;
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
    aviso.textContent = '';
    aviso.hidden = true;
    const mensagemEnviada = adicionarMensagem(texto, 'usuario');
    const balaoDigitacao = mostrarDigitacao();
    const inicioResposta = performance.now();
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

        // Mostra os pontos por 1,6 a 3 segundos, conforme o tamanho da resposta.
        // O tempo que a API já levou conta nessa pausa, evitando espera extra
        // quando a conexão estiver lenta. Falhas não recebem atraso artificial.
        clearTimeout(tempoLimite);
        const tempoMinimo = Math.min(3000, 1600 + dados.resposta.length * 8);
        const tempoRestante = tempoMinimo - (performance.now() - inicioResposta);
        if (tempoRestante > 0) {
            await new Promise((resolver) => setTimeout(resolver, tempoRestante));
        }
        balaoDigitacao.remove();
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
        balaoDigitacao.remove();
        enviando = false;
        botaoEnviar.disabled = false;
        botaoEmoji.disabled = false;
        campoMensagem.disabled = false;
        formulario.removeAttribute('aria-busy');
        campoMensagem.focus();
    }
});
