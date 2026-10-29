const formulario = document.getElementById('chat-form');
const campoMensagem = document.getElementById('mensagem');
const mensagens = document.getElementById('mensagens');
const botaoEmoji = document.getElementById('emoji-btn');
const seletorEmoji = document.getElementById('emoji-picker');
const botaoEnviar = formulario.querySelector('button[type="submit"]');
const aviso = document.getElementById('chat-aviso');
const botaoAnteriores = document.getElementById('historico-anteriores');
let enviando = false;
let carregandoHistorico = false;
let envioPendente = null;
requestAnimationFrame(() => { mensagens.scrollTop = mensagens.scrollHeight; });

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


function criarMensagem(texto, remetente, id = null) {
    const mensagem = document.createElement('div');
    mensagem.className = `mensagem mensagem-${remetente}`;
    const autor = document.createElement('span');
    autor.className = 'sr-only';
    autor.textContent = remetente === 'usuario' ? 'Você: ' : 'Clarice: ';
    const conteudo = document.createElement('p');
    conteudo.textContent = texto;
    mensagem.append(autor, conteudo);
    if (id !== null) mensagem.dataset.id = String(id);

    return mensagem;
}

function adicionarMensagem(texto, remetente) {
    const mensagem = criarMensagem(texto, remetente);
    mensagens.appendChild(mensagem);
    mensagens.scrollTop = mensagens.scrollHeight;
    return mensagem;
}

botaoAnteriores.addEventListener('click', async () => {
    if (carregandoHistorico || enviando) return;
    carregandoHistorico = true;
    botaoAnteriores.disabled = true;
    botaoAnteriores.textContent = 'Carregando…';
    const controle = new AbortController();
    const limite = setTimeout(() => controle.abort(), 15000);
    try {
        const url = new URL('carregar_historico.php', window.location.href);
        url.searchParams.set('antes', botaoAnteriores.dataset.antes);
        const retorno = await fetch(url, { credentials: 'same-origin', signal: controle.signal });
        const dados = await retorno.json();
        if (!retorno.ok || !Array.isArray(dados.mensagens)) throw new Error('Falha no histórico.');
        const alturaAntes = mensagens.scrollHeight;
        const fragmento = document.createDocumentFragment();
        const ids = new Set(Array.from(mensagens.querySelectorAll('[data-id]'), (m) => m.dataset.id));
        for (const m of dados.mensagens) {
            if (!ids.has(String(m.id))) {
                fragmento.appendChild(criarMensagem(m.conteudo, m.remetente === 'user' ? 'usuario' : 'clarice', m.id));
            }
        }
        botaoAnteriores.after(fragmento);
        botaoAnteriores.dataset.antes = String(dados.antes || 0);
        botaoAnteriores.hidden = !dados.ha_anteriores;
        mensagens.scrollTop += mensagens.scrollHeight - alturaAntes;
        botaoAnteriores.textContent = 'Carregar mensagens anteriores';
    } catch (erro) {
        botaoAnteriores.textContent = 'Não foi possível carregar. Tentar novamente';
    } finally {
        clearTimeout(limite);
        carregandoHistorico = false;
        botaoAnteriores.disabled = false;
    }
});

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

    evento.preventDefault();
    if (enviando || carregandoHistorico || formulario.dataset.historicoPronto !== 'true') return;

    const texto = campoMensagem.value.trim();
    if (!texto) {
        campoMensagem.focus();
        return;
    }



    if (!envioPendente || envioPendente.texto !== texto) {
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        envioPendente = { texto, id: Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('') };
    }

    enviando = true;
    botaoEnviar.disabled = true;
    botaoEmoji.disabled = true;
    campoMensagem.disabled = true;
    botaoAnteriores.disabled = true;
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

        const retorno = await fetch(formulario.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': formulario.dataset.csrf,
            },
            body: JSON.stringify({ mensagem: texto, envio_id: envioPendente.id }),
            signal: controle.signal,
        });
        const dados = await retorno.json();
        if (!retorno.ok) {
            throw new Error(typeof dados?.erro === 'string' ? dados.erro : 'Não foi possível enviar sua mensagem.');
        }
        if (typeof dados?.resposta !== 'string' || !dados.resposta.trim()) {
            throw new Error('A resposta não chegou completa. Tente novamente.');
        }




        clearTimeout(tempoLimite);
        const tempoMinimo = Math.min(3000, 1600 + dados.resposta.length * 8);
        const tempoRestante = tempoMinimo - (performance.now() - inicioResposta);
        if (tempoRestante > 0) {
            await new Promise((resolver) => setTimeout(resolver, tempoRestante));
        }
        balaoDigitacao.remove();
        mensagemEnviada.dataset.id = String(dados.usuario_id_mensagem);

        const resposta = adicionarMensagem(dados.resposta, 'clarice');
        resposta.dataset.id = String(dados.ia_id_mensagem);
        envioPendente = null;
        aviso.hidden = true;
        document.dispatchEvent(new CustomEvent('clarice:mensagem-enviada', { detail: { texto } }));
    } catch (erro) {

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
        botaoAnteriores.disabled = false;
        formulario.removeAttribute('aria-busy');
        campoMensagem.focus();
    }
});
