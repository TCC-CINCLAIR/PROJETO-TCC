const overlay = document.getElementById("overlay");

function mostrarAviso() {
    overlay.style.display = "flex";
}

overlay.addEventListener("click", function (event) {
    if (event.target === overlay) {
        overlay.style.display = "none";
    }
});

function confirmarConsulta() {
    const profissional = document.getElementById("profissional").value;
    const data = document.getElementById("dataConsulta").value;
    const horario = document.getElementById("horario").value;
    const observacoes = document.getElementById("observacoes").value;

    if (profissional === "") {
        alert("Selecione um profissional.");
        return;
    }

    if (data === "") {
        alert("Selecione uma data.");
        return;
    }

    if (horario === "") {
        alert("Selecione um horário.");
        return;
    }

    const partes = data.split("-");
    const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;

    alert(
`Consulta agendada com sucesso!

Profissional: ${profissional}

Data: ${dataFormatada}

Horário: ${horario}`
    );

    document.getElementById("profissional").selectedIndex = 0;
    document.getElementById("dataConsulta").value = "";
    document.getElementById("horario").selectedIndex = 0;
    document.getElementById("observacoes").value = "";
}