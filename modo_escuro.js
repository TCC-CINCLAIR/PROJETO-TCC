const modoEscuro = document.getElementById("modoEscuro");

if(localStorage.getItem("modoEscuro") === "ativado"){
    document.body.classList.add("modo-escuro");
    if(modoEscuro){
        modoEscuro.checked = true;
    }
}

if(modoEscuro){
    modoEscuro.addEventListener("change", function(){
        if(modoEscuro.checked){
            document.body.classList.add("modo-escuro");
            localStorage.setItem("modoEscuro", "ativado");
        }else{
            document.body.classList.remove("modo-escuro");
            localStorage.setItem("modoEscuro", "desativado");
        }
    });
}