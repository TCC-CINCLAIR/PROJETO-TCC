//BOTÃO NOVA TAREFA

const tasksContainer = document.getElementById("tasksContainer");

var listaTarefas = [];

function openModal() {
    document.getElementById("taskModal").style.display = "flex";
}

function closeModal(){
    document.getElementById("taskModal").style.display = "none";
}

function addTask(){
    const title = document.getElementById("taskTitle").value;
    const date = document.getElementById("taskDate").value;

    if(title === "" || date === "") {
        alert("Preencha todos os campos!");
        return;
    }

    const formattedDate = formatDate(date);
    const mesDaTarefa = getMes(date);

    var novaTarefa = {
        titulo: title,
        mes: mesDaTarefa,
        concluida: false
    };
    listaTarefas.push(novaTarefa);

    const card = document.createElement("div");
    card.classList.add("task-card");
    card.innerHTML = `
        <div class="task-header">
            ${formattedDate}
        </div>

        <div class="task-list">
            <div class="task-item">
                <input type="checkbox">
                <span>${title}</span>
            </div>
        </div>
    `;

    const checkbox = card.querySelector("input[type='checkbox']");
    checkbox.onchange = function() {
        novaTarefa.concluida = checkbox.checked;
        pendencias();
    };

    tasksContainer.appendChild(card);

    document.getElementById("taskTitle").value = "";
    document.getElementById("taskDate").value = "";

    closeModal();
    pendencias();
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

///////////////////////////////////////////////////////////////////////
//BOTAO DO HAMBURGUINHO

var menu = document.getElementById("sideMenu");

function openMenu(){
    menu.classList.add("active-menu");
}

function closeMenu() {
    menu.classList.remove("active-menu");
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
            tarefas.innerHTML += "<div class='month-task-item'>" + status + " " + tarefasDoMes[j].titulo + "</div>";
        }
    }

    tarefas.style.display = "block";
}

function pendencias(){
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
    if (event.target.type === "checkbox") {
        const task = event.target.closest(".task-item");
        const card = event.target.closest(".task-card");

        if (event.target.checked && task) {
            task.remove();

            const taskList = card.querySelector(".task-list");

            if (taskList.children.length === 0) {
                card.remove();
            }

            pendencias();
        }
    }
});