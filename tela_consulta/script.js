const overlay = document.getElementById("overlay");
let ultimoFoco = null;

function mostrarAviso() {
    ultimoFoco = document.activeElement;
    overlay.style.display = "flex";
    document.body.classList.add("dialog-open");
    overlay.querySelector("button").focus();
}

function fecharAviso() {
    overlay.style.display = "none";
    document.body.classList.remove("dialog-open");
    ultimoFoco?.focus();
}

overlay.addEventListener("click", function (event) {
    if (event.target === overlay) {
        fecharAviso();
    }
});

document.addEventListener("keydown", function (event) {
    if (overlay.style.display !== "flex") return;
    if (event.key === "Escape") fecharAviso();
    if (event.key === "Tab") {
        event.preventDefault();
        overlay.querySelector("button").focus();
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

    const dados = new FormData();
    dados.append("profissional", profissional);
    dados.append("data", data);
    dados.append("horario", horario);
    dados.append("observacoes", observacoes);

    fetch("salvar_consulta.php", {
        method: "POST",
        body: dados
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error("Resposta do servidor não é um JSON válido: " + text);
        }
    })
    .then(resultado => {
        if (resultado.sucesso) {
            alert(resultado.mensagem);

            document.getElementById("profissional").selectedIndex = 0;
            document.getElementById("dataConsulta").value = "";
            document.getElementById("horario").selectedIndex = 0;
            document.getElementById("observacoes").value = "";
        } else {
            alert("Erro: " + resultado.mensagem);
        }
    })
    .catch((error) => {
        alert("Erro no processamento: " + error.message);
    });
}