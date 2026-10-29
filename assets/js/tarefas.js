
window.TarefasCinclair = (() => {
    const usuario = document.body.dataset.usuarioId;
    const chave = `cinclair:tarefas:${usuario}`;
    function carregar() {
        if (!usuario) throw new Error('Usuário não identificado.');
        const tarefas = JSON.parse(localStorage.getItem(chave) || '[]');
        if (!Array.isArray(tarefas)) throw new Error('Lista de tarefas inválida.');
        return tarefas.filter(tarefa => tarefa && typeof tarefa.titulo === 'string'
            && typeof tarefa.data === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(tarefa.data)
            && typeof tarefa.concluida === 'boolean');
    }
    function salvar(tarefas) {
        if (!usuario) throw new Error('Usuário não identificado.');
        localStorage.setItem(chave, JSON.stringify(tarefas));
    }
    return { carregar, salvar, chave };
})();
