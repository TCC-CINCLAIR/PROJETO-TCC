(() => {
    const lista = document.getElementById('upcoming-tasks');
    const vazio = document.getElementById('tasks-empty');
    const aviso = document.getElementById('storage-warning');
    const dataAtual = () => new Intl.DateTimeFormat('sv-SE', { timeZone: 'America/Sao_Paulo' }).format(new Date());

    function renderizar() {
        let tarefas;
        try {
            if (erroTarefasDashboard) throw new Error('Erro ao carregar tarefas.');
            tarefas = tarefasDashboard;
            aviso.hidden = true;
        } catch (erro) {
            lista.hidden = true;
            vazio.hidden = true;
            aviso.hidden = false;
            document.getElementById('task-count').textContent = '—';
            return;
        }
        const hoje = dataAtual();
        const proximas = tarefas.filter(tarefa => !tarefa.concluida && tarefa.data >= hoje)
            .sort((a, b) => a.data.localeCompare(b.data)).slice(0, 3);
        lista.replaceChildren();
        proximas.forEach((tarefa, indice) => {
            const item = document.createElement('li');
            const numero = document.createElement('span');
            numero.className = 'task-number';
            numero.textContent = String(indice + 1).padStart(2, '0');
            numero.setAttribute('aria-hidden', 'true');
            const detalhes = document.createElement('div');
            const titulo = document.createElement('h3');
            titulo.textContent = tarefa.titulo;
            const data = document.createElement('time');
            data.dateTime = tarefa.data;
            const [ano, mes, dia] = tarefa.data.split('-');
            data.textContent = `${dia}/${mes}/${ano}`;
            detalhes.append(titulo, data);
            const status = document.createElement('span');
            status.className = 'status-badge';
            status.textContent = tarefa.data === hoje ? 'Hoje' : 'Pendente';
            item.append(numero, detalhes, status);
            lista.appendChild(item);
        });
        lista.hidden = proximas.length === 0;
        vazio.hidden = proximas.length > 0;
        document.getElementById('task-count').textContent = `${proximas.length}/3`;
    }
    renderizar();
    window.addEventListener('pageshow', renderizar);

})();
