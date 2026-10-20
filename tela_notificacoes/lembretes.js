(() => {
    if (window.lembretesCinclairCarregados) return;
    window.lembretesCinclairCarregados = true;

    const enderecoScript = document.currentScript.src;
    const api = new URL("api.php", enderecoScript);

    const interruptor = document.getElementById("notificacoes-consultas");
    const status = document.getElementById("status-notificacoes");

    let token = "";
    let ocupado = false;
    let carregado = false;

    function mostrarHistorico(historico) {
        const lista = document.getElementById("lista-notificacoes");

        if (!lista) return;

        lista.replaceChildren();

        if (historico.length === 0) {
            const mensagem = document.createElement("p");
            mensagem.textContent = "Você ainda não tem lembretes.";
            lista.appendChild(mensagem);
            return;
        }

        for (const item of historico) {
            const cartao = document.createElement("article");
            cartao.className = "item-notificacao";

            const mensagem = document.createElement("p");
            mensagem.textContent = item.mensagem;

            const data = document.createElement("time");
            data.dateTime = item.criado_em;
            data.textContent = new Date(item.criado_em).toLocaleString(
                "pt-BR",
                { timeZone: "America/Sao_Paulo" }
            );

            cartao.append(mensagem, data);
            lista.appendChild(cartao);
        }
    }

    function informar(mensagem) {
        if (status) status.textContent = mensagem;
    }

    async function lerDados() {
        const resposta = await fetch(api, {
            credentials: "same-origin",
            cache: "no-store",
        });

        if (resposta.status === 401) return null;

        const dados = await resposta.json();

        if (!resposta.ok) {
            throw new Error(dados.erro || "Falha ao carregar.");
        }

        return dados;
    }

    function mostrarLembretes(dados) {
        if (!dados.ativado) return;
        if (!("Notification" in window)) return;
        if (Notification.permission !== "granted") return;

        for (const consulta of dados.lembretes) {
            const chave = [
                "cinclair-lembrete",
                dados.usuario,
                consulta.data,
                consulta.horario,
                consulta.profissional,
            ].join("|");

            // Evita repetir o aviso neste navegador após atualizar a página.
            if (localStorage.getItem(chave)) continue;

            const minutos = Math.max(1, Number(consulta.minutos));

            const aviso = new Notification("Lembrete de consulta", {
                body:
                    `Sua consulta começa em ${minutos} ` +
                    (minutos === 1 ? "minuto!" : "minutos!"),
                tag: chave,
            });

            localStorage.setItem(chave, "mostrado");

            aviso.onclick = function () {
                window.focus();
                aviso.close();
            };
        }
    }

    async function verificar() {
        if (ocupado) return;

        ocupado = true;

        try {
            const dados = await lerDados();

            if (!dados) return;

            token = dados.csrf;

            if (interruptor) {
                interruptor.checked = dados.ativado;
                interruptor.disabled = false;
            }

            if (!carregado) {
                informar(
                    dados.ativado
                        ? "Lembretes ativados. Mantenha uma página do site aberta."
                        : "Lembretes desativados."
                );

                carregado = true;
            }
            mostrarHistorico(dados.historico || []);
            mostrarLembretes(dados);
        } catch (erro) {
            informar(erro.message);
            console.warn("Lembretes:", erro.message);
        } finally {
            ocupado = false;
        }
    }

    if (interruptor) {
        interruptor.disabled = true;

        interruptor.addEventListener("change", async function () {
            const anterior = !interruptor.checked;
            const ativar = interruptor.checked;

            ocupado = true;
            interruptor.disabled = true;

            try {
                if (ativar) {
                    if (!window.isSecureContext || !("Notification" in window)) {
                        throw new Error(
                            "Abra pelo localhost em um navegador de computador compatível."
                        );
                    }

                    const permissao = await Notification.requestPermission();

                    if (permissao !== "granted") {
                        throw new Error(
                            "Permita as notificações nas configurações deste site no navegador."
                        );
                    }
                }

                const resposta = await fetch(api, {
                    method: "POST",
                    credentials: "same-origin",
                    body: new URLSearchParams({
                        csrf: token,
                        ativar: ativar ? "1" : "0",
                    }),
                });

                const resultado = await resposta.json();

                if (!resposta.ok) {
                    throw new Error(resultado.erro || "Falha ao salvar.");
                }

                informar(
                    ativar
                        ? "Lembretes ativados. Mantenha uma página do site aberta."
                        : "Lembretes desativados."
                );
            } catch (erro) {
                interruptor.checked = anterior;
                informar(erro.message);
            } finally {
                interruptor.disabled = false;
                ocupado = false;
            }
        });
    }

    verificar();
    setInterval(verificar, 30000);
    window.addEventListener("focus", verificar);
})();