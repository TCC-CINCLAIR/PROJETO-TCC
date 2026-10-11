(() => {
    const dialogo = document.getElementById('previa-acontecimento');
    const abrir = document.getElementById('guardar-acontecimento');
    const form = document.getElementById('form-acontecimento');
    const titulo = document.getElementById('acontecimento-titulo');
    const resumo = document.getElementById('acontecimento-resumo');
    const campos = document.getElementById('previa-campos');
    const status = document.getElementById('previa-status');
    const guardar = document.getElementById('confirmar-acontecimento');
    const editar = document.getElementById('editar-acontecimento');
    const cancelar = document.getElementById('cancelar-acontecimento');
    let token = null;
    let salvando = false;
    let versao = 0;

    async function pedido(dados) {
        const controle = new AbortController();
        const limite = setTimeout(() => controle.abort(), 35000);
        try {
            const r = await fetch('../tela_relatorios/acontecimento.php', {
                method: 'POST', credentials: 'same-origin', signal: controle.signal,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': formulario.dataset.csrf },
                body: JSON.stringify(dados),
            });
            const resposta = await r.json();
            if (!r.ok) throw new Error(resposta.erro || 'Não foi possível concluir. Tente novamente.');
            return resposta;
        } finally { clearTimeout(limite); }
    }
    function erroTexto(e) {
        return e.name === 'AbortError' ? 'Demorou um pouco mais. Tente novamente; o acontecimento não será duplicado.'
            : e instanceof TypeError || e instanceof SyntaxError ? 'Não foi possível conectar. Tente novamente.' : e.message;
    }
    async function preparar() {
        if (dialogo.open || enviando || carregandoHistorico) return;
        const atual = ++versao;
        token = null; titulo.value = ''; resumo.value = '';
        titulo.readOnly = true; resumo.readOnly = true;
        campos.hidden = true; editar.hidden = true; guardar.hidden = true;
        status.textContent = 'A Clarice está reunindo os detalhes do último acontecimento…'; status.hidden = false;
        dialogo.showModal();
        try {
            const dados = await pedido({ acao: 'preparar' });
            if (atual !== versao || !dialogo.open) { pedido({ acao: 'cancelar', token: dados.token }).catch(() => {}); return; }
            token = dados.token; titulo.value = dados.titulo; resumo.value = dados.resumo;
            document.getElementById('previa-quantidade').textContent = `${dados.quantidade} relato(s) reunido(s). Nada é guardado antes da sua confirmação.`;
            campos.hidden = false; editar.hidden = false; guardar.hidden = false; status.hidden = true;
            guardar.focus();
        } catch (e) { if (atual === versao) { status.textContent = erroTexto(e); status.hidden = false; } }
    }
    function fechar() {
        if (salvando) return;
        ++versao;
        if (token) pedido({ acao: 'cancelar', token }).catch(() => {});
        token = null; dialogo.close(); abrir.focus();
    }
    abrir.addEventListener('click', preparar);
    cancelar.addEventListener('click', fechar);
    dialogo.addEventListener('cancel', (e) => { e.preventDefault(); fechar(); });
    editar.addEventListener('click', () => {
        titulo.readOnly = false; resumo.readOnly = false; resumo.focus();
        status.textContent = 'Ajuste o texto como preferir e clique em Guardar.'; status.hidden = false;
    });
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!token || salvando) return;
        salvando = true; guardar.disabled = true; editar.disabled = true; cancelar.disabled = true;
        titulo.readOnly = true; resumo.readOnly = true;
        status.textContent = 'Guardando acontecimento…'; status.hidden = false;
        try {
            await pedido({ acao: 'guardar', token, titulo: titulo.value, resumo: resumo.value });
            token = null; dialogo.close(); abrir.focus();
            aviso.classList.remove('erro'); aviso.textContent = 'Acontecimento guardado! Ele entrará como um único item no relatório.'; aviso.hidden = false;
        } catch (e) { status.textContent = erroTexto(e); }
        finally { salvando = false; guardar.disabled = false; editar.disabled = false; cancelar.disabled = false; }
    });
    document.addEventListener('clarice:mensagem-enviada', (e) => {
        const texto = e.detail.texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const negacao = /\b(nao|nunca|nem|cancela|cancelar)\b/.test(texto);
        const pedidoExplicito = /\b(inclu\w*|coloc\w*|guard\w*|salv\w*|adicion\w*|registr\w*|apare\w*)\b/.test(texto) && /\b(relatorio|acontecimento)\b/.test(texto);
        if (!negacao && pedidoExplicito) setTimeout(preparar, 0);
    });
})();
