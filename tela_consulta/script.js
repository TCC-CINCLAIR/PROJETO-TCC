const overlay = document.getElementById("overlay");
let ultimoFoco = null;
let campoAposAviso = null;
let enviandoConsulta = false;

function mostrarAviso(
    titulo = "Política de cancelamento",
    mensagem = "Cancelamentos devem ser feitos com pelo menos 24 horas de antecedência.",
    tipo = "info",
    campo = null
) {
    ultimoFoco = document.activeElement;
    campoAposAviso = campo;
    document.getElementById("aviso-title").textContent = titulo;
    document.getElementById("aviso-mensagem").textContent = mensagem;
    overlay.querySelector(".aviso-box").dataset.tipo = tipo;
    overlay.querySelector("button").textContent = campo === "horario" ? "Escolher outro horário" : "Entendi";
    overlay.style.display = "flex";
    document.body.classList.add("dialog-open");
    overlay.querySelector("button").focus();
}

function fecharAviso() {
    overlay.style.display = "none";
    document.body.classList.remove("dialog-open");
    const destino = campoAposAviso ? document.getElementById(campoAposAviso) : ultimoFoco;
    destino?.focus();
    campoAposAviso = null;
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
    if (enviandoConsulta) return;
    const profissional = document.getElementById("profissional").value;
    const data = document.getElementById("dataConsulta").value;
    const horario = document.getElementById("horario").value;
    const observacoes = document.getElementById("observacoes").value;

    if (profissional === "") {
        mostrarAviso("Selecione um profissional", "Escolha com quem você deseja agendar sua consulta.", "atencao", "profissional");
        return;
    }

    if (data === "") {
        mostrarAviso("Selecione uma data", "Escolha o dia da sua consulta para continuar.", "atencao", "dataConsulta");
        return;
    }

    if (horario === "") {
        mostrarAviso("Selecione um horário", "Escolha um horário para a sua consulta.", "atencao", "horario");
        return;
    }

    const dados = new FormData();
    dados.append("profissional", profissional);
    dados.append("data", data);
    dados.append("horario", horario);
    dados.append("observacoes", observacoes);

    const botao = document.querySelector(".consulta-card .confirmar-btn");
    enviandoConsulta = true;
    botao.disabled = true;
    botao.textContent = "Agendando...";

    fetch("salvar_consulta.php", {
        method: "POST",
        body: dados
    })
    .then(response => response.json())
    .then(resultado => {
        if (resultado.sucesso) {
            mostrarAviso("Consulta agendada!", resultado.mensagem, "sucesso", "profissional");

            document.getElementById("profissional").selectedIndex = 0;
            document.getElementById("dataConsulta").value = "";
            document.getElementById("horario").selectedIndex = 0;
            document.getElementById("observacoes").value = "";
        } else {
            const horarioOcupado = resultado.codigo === "horario_indisponivel";
            mostrarAviso(
                horarioOcupado ? "Horário indisponível" : "Não foi possível agendar",
                resultado.mensagem || "Tente novamente em instantes.",
                horarioOcupado ? "atencao" : "erro",
                horarioOcupado ? "horario" : null
            );
        }
    })
    .catch(() => {
        mostrarAviso("Não foi possível confirmar", "Não conseguimos confirmar o agendamento agora. Confira sua agenda antes de tentar novamente.", "erro");
    })
    .finally(() => {
        enviandoConsulta = false;
        botao.disabled = false;
        botao.textContent = "Confirmar Agendamento";
    });
}
