//BOTÃO NOVA TAREFA

const tasksContainer = document.getElementById("tasksContainer");

var listaTarefas = [];
let ultimoFoco = null;



function carregarConsultas(){
    for(var i = 0; i < consultasBanco.length; i++){
        var consulta = consultasBanco[i];

        var titulo = "Consulta com " + consulta.profissional + " - " + consulta.horario.substring(0,5);
        var mesDaConsulta = getMes(consulta.data_consulta);

        var consultaTarefa = {
            titulo: titulo,
            mes: mesDaConsulta,
            concluida: false
        };

        listaTarefas.push(consultaTarefa);

        var card = document.createElement("div");
        card.classList.add("task-card");

        card.innerHTML = `
            <div class="task-header">
                ${formatDate(consulta.data_consulta)}
            </div>
            <div class="task-list">
                <div class="task-item">
                    <input type="checkbox">
                    <span>${titulo}</span>
                </div>
            </div>
        `;

        var checkbox = card.querySelector("input[type='checkbox']");

        checkbox.onchange = function(){
            consultaTarefa.concluida = checkbox.checked;
            pendencias();
        };

        tasksContainer.appendChild(card);
    }

    pendencias();
}

carregarConsultas();

function openModal() {
    ultimoFoco = document.activeElement;
    document.getElementById("taskModal").style.display = "flex";
    document.body.classList.add("dialog-open");
    document.querySelector("#taskModal .close-dialog").focus();
}

function closeModal(){
    document.getElementById("taskModal").style.display = "none";
    document.body.classList.remove("dialog-open");
    ultimoFoco?.focus();
}

function addTask(){
    const title = document.getElementById("taskTitle").value.trim();
    const date = document.getElementById("taskDate").value;

    if (title === "" || date === "") {
        alert("Preencha todos os campos!");
        return;
    }

    const dados = new FormData();
    dados.append("titulo", title);
    dados.append("data", date);

    fetch("salvar_tarefa.php", {
        method: "POST",
        body: dados
    })
    .then(response => response.json())
    .then(resultado => {
        if (!resultado.sucesso) {
            alert("Não foi possível salvar a tarefa.");
            return;
        }

        criarCardTarefa(resultado.id, title, date);

        document.getElementById("taskTitle").value = "";
        document.getElementById("taskDate").value = "";
        closeModal();
        pendencias();
    })
    .catch(() => alert("Erro ao salvar a tarefa."));
}

function formatDate(dateString){
    const partes = dateString.split("-");
    const ano = Number(partes[0]);
    const mesNumero = Number(partes[1]);
    const dia = Number(partes[2]);

    const data = new Date(ano, mesNumero - 1, dia);

    const dias = [
        "Domingo","Segunda-feira","Terça-feira","Quarta-feira",
        "Quinta-feira","Sexta-feira","Sábado"
    ];

    const meses = [
        "Janeiro","Fevereiro","Março","Abril","Maio","Junho",
        "Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"
    ];

    const diaSemana = dias[data.getDay()];
    const mes = meses[data.getMonth()];

    return `${diaSemana}, ${dia} de ${mes}`;
}

function getMes(dateString){
    const partes = dateString.split("-");
    const mesNumero = Number(partes[1]);

    const meses = [
        "Janeiro","Fevereiro","Março","Abril","Maio","Junho",
        "Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"
    ];

    return meses[mesNumero - 1];
}


function criarCardTarefa(id, titulo, data) {
    const mesDaTarefa = getMes(data);

    listaTarefas.push({
    id: Number(id),
    titulo: titulo,
    mes: mesDaTarefa,
    concluida: false,
    tipo: "tarefa"
});

    const card = document.createElement("div");
    card.classList.add("task-card");
    card.dataset.id = id;
    card.dataset.tipo = "tarefa";
    card.innerHTML = `
        <div class="task-header">${formatDate(data)}</div>
        <div class="task-list">
            <label class="task-item">
                <input type="checkbox">
                <span></span>
            </label>
        </div>
    `;

    card.querySelector(".task-item span").textContent = titulo;
    tasksContainer.appendChild(card);
}

function carregarTarefas() {
    for (const tarefa of tarefasBanco) {
        criarCardTarefa(tarefa.id, tarefa.titulo_tarefa, tarefa.data);
    }

    pendencias();
}

carregarTarefas();

///////////////////////////////////////////////////////////////////////
//BOTAO DO HAMBURGUINHO

var menu = document.getElementById("sideMenu");

function openMenu(){
    ultimoFoco = document.activeElement;
    menu.classList.add("active-menu");
    document.getElementById("menuBackdrop").style.display = "block";
    document.querySelector(".menu-icon").setAttribute("aria-expanded", "true");
    document.body.classList.add("dialog-open");
    menu.querySelector(".close-menu").focus();
}

function closeMenu() {
    menu.classList.remove("active-menu");
    document.getElementById("menuBackdrop").style.display = "none";
    document.querySelector(".menu-icon").setAttribute("aria-expanded", "false");
    document.body.classList.remove("dialog-open");
    ultimoFoco?.focus();
}

function toggleMonthTasks(mes){
    var tarefas = document.getElementById("tasks-" + mes);

    if (tarefas.style.display == "block") {
        tarefas.style.display = "none";
        return;
    }

    tarefas.innerHTML = "";

    var tarefasDoMes = [];
    for (var i = 0; i < listaTarefas.length; i++) {
        if (listaTarefas[i].mes === mes) {
            tarefasDoMes.push(listaTarefas[i]);
        }
    }

    if (tarefasDoMes.length === 0) {
        tarefas.innerHTML = "<div class='month-task-item'>Nenhuma tarefa neste mês.</div>";
    } else {
        for (var j = 0; j < tarefasDoMes.length; j++) {
            var status = tarefasDoMes[j].concluida ? "✔" : "○";
            const item = document.createElement("div");
            item.className = "month-task-item";
            item.textContent = status + " " + tarefasDoMes[j].titulo;
            tarefas.appendChild(item);
        }
    }

    tarefas.style.display = "block";
}

function pendencias(){
    document.getElementById("emptyTasks").hidden = tasksContainer.childElementCount > 0;
    var meses = [
        "Janeiro","Fevereiro","Março","Abril","Maio","Junho",
        "Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"
    ];

    for (var i = 0; i < meses.length; i++) {
        var mes = meses[i];
        var bolinha = document.getElementById("dot-" + mes);

        var temPendencia = false;

        for (var j = 0; j < listaTarefas.length; j++) {
            if (listaTarefas[j].mes === mes && listaTarefas[j].concluida === false) {
                temPendencia = true;
            }
        }

        if (temPendencia) {
            bolinha.style.display = "block";
        } else {
            bolinha.style.display = "none";
        }
    }
}

document.addEventListener("change", function (event) {
    if (event.target.type !== "checkbox" || !event.target.checked) {
        return;
    }

    const task = event.target.closest(".task-item");
    const card = event.target.closest(".task-card");

    if (!task || !card) return;

    if (card.dataset.tipo === "tarefa") {
        const dados = new FormData();
        dados.append("id", card.dataset.id);

        fetch("excluir_tarefa.php", {
        method: "POST",
        body: dados
    })
.then(response => response.text())
.then(texto => {
    let resultado;

    try {
        resultado = JSON.parse(texto);
    } catch {
        console.log("Resposta do PHP:", texto);
        throw new Error("O PHP não retornou um JSON válido.");
    }

    if (!resultado.sucesso) {
        event.target.checked = false;
        console.log("Resultado da exclusão:", resultado);
        alert("Não foi possível excluir a tarefa do banco.");
        return;
    }

    const indice = listaTarefas.findIndex(function (item) {
        return item.tipo === "tarefa" &&
            item.id === Number(card.dataset.id);
    });

    if (indice !== -1) {
        listaTarefas.splice(indice, 1);
    }

    card.remove();
    pendencias();
})
.catch(erro => {
    event.target.checked = false;
    console.error("Erro ao excluir tarefa:", erro);
    alert("Erro ao excluir a tarefa do banco. Confira o Console (F12).");
});

        return;
    }

    task.remove();

    const taskList = card.querySelector(".task-list");

    if (taskList.children.length === 0) {
        card.remove();
    }

    pendencias();
});

document.getElementById("taskModal").addEventListener("click", function (event) {
    if (event.target.id === "taskModal") closeModal();
});


document.addEventListener("keydown", function (event) {
    const modal = document.getElementById("taskModal");
    const painel = menu.classList.contains("active-menu") ? menu : modal.style.display === "flex" ? modal : null;
    if (!painel) return;
    if (event.key === "Escape") {
        painel === menu ? closeMenu() : closeModal();
    }
    if (event.key === "Tab") {
        const campos = [...painel.querySelectorAll('button, input, [tabindex="0"]')];
        const primeiro = campos[0];
        const ultimo = campos[campos.length - 1];
        if (event.shiftKey && document.activeElement === primeiro) {
            event.preventDefault();
            ultimo.focus();
        } else if (!event.shiftKey && document.activeElement === ultimo) {
            event.preventDefault();
            primeiro.focus();
        }
    }
});

document.querySelectorAll(".month-item").forEach(function (item) {
    item.setAttribute("role", "button");
    item.tabIndex = 0;
    item.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            item.click();
        }
    });
});
