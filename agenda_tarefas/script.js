const tasksContainer = document.getElementById("tasksContainer");

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
    tasksContainer.appendChild(card);

    document.getElementById("taskTitle").value = "";
    document.getElementById("taskDate").value = "";

    closeModal();
}

function formatDate(dateString){
    const data = new Date(dateString);
    const dias = [
        "Domingo","Segunda-feira","Terça-feira","Quarta-feira",
        "Quinta-feira","Sexta-feira","Sábado"
    ];

    const meses = [
        "Janeiro","Fevereiro","Março","Abril","Maio","Junho",
        "Julho","Agosto","Stembro","Outubro","Novembro","Dezembro"
    ];

    const diaSemana = dias[data.getDay()];
    const dia = data.getDate();
    const mes = meses[data.getMonth()];

    return `${diaSemana}, ${dia} de ${mes}`;
}