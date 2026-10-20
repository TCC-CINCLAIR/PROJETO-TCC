const fonteAdaptativa = document.getElementById("fonteAdaptativa");

function aplicarFonteAdaptativa() {
    const ativada = localStorage.getItem("fonteAdaptativa") === "true";

    document.body.classList.toggle("fonte-adaptativa", ativada);

    if (fonteAdaptativa) {
        fonteAdaptativa.checked = ativada;
    }
}

aplicarFonteAdaptativa();

if (fonteAdaptativa) {
    fonteAdaptativa.addEventListener("change", function () {
        localStorage.setItem("fonteAdaptativa", fonteAdaptativa.checked);
        aplicarFonteAdaptativa();
    });
}