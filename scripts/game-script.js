//obj:jogoEstado
// Estados: "inicio" | "salao" | "pedindo" | "pausado" | "entre-levels" | "gameover"
const jogoEstado = {
    level: 1,
    score: 0,
    scorePorLevel: [],
    scoreInicioLevel: 0,
    modo: "inicio",
    pedidoAtual: "",
    mesaAtual: null,
    mesaAtualCancelavel: null,
    segurandoPedido: false,
    pedidosCompletos: 0,
    pedidosTotais: 0,
    clientesNaFila: 0,
    estrelasRestaurante: 5,
    parametrosLevel: null,
    garcom: {
        xRatio: 0.1,
        yRatio: 0.6,
        x: 84,
        y: 275,
        speed: 4
    },
    keys: new Set(),
    mesas: [],
    dialogoTimers: [],
    spawnAgendados: [],
    spawnInterval: null,
    loopRodando: false
};

//====================
// VARIÁVEIS GLOBAIS
//====================

const jogoHud = document.getElementById("jogo-hud");
const telaInicio = document.getElementById("tela-inicio");
const btnJogar = document.getElementById("btn-jogar");
const cenarioSala = document.getElementById("cenario-sala");
const cenarioPedido = document.getElementById("cenario-pedido");
const chao = document.getElementById("chao-restaurante");
const mesaLayer = document.getElementById("mesa-layer");
const garcom = document.getElementById("doutor");
const interactBtn = document.getElementById("interact-btn");
const cozinha = document.getElementById("cozinha");
const dialogoBox = document.getElementById("dialogo");
const pedidoInput = document.getElementById("pedido-input");
const pedidoAnotado = document.getElementById("pedido-anotado");
const feedbackAnotacao = document.getElementById("feedback-anotacao");
const scoreValue = document.getElementById("hud-score");
const levelValue = document.getElementById("hud-level");
const pedidosValue = document.getElementById("hud-pedidos");
const hudStars = document.querySelectorAll(".star");
const gameOverModal = document.getElementById("game-over-modal");
const gameOverScoreVal = document.getElementById("game-over-score-val");
const btnReiniciar = document.getElementById("btn-reiniciar");
const pausaModal = document.getElementById("pausa-modal");
const btnContinuar = document.getElementById("btn-continuar");
const btnReiniciarLevel = document.getElementById("btn-reiniciar-level");
const btnSair = document.getElementById("btn-sair");
const entreLevelModal = document.getElementById("entre-level-modal");
const entreLevelTitulo = document.getElementById("entre-level-titulo");
const entreLevelScore = document.getElementById("entre-level-score");
const btnProximaFase = document.getElementById("btn-proxima-fase");
const btnTentarNovamente = document.getElementById("btn-tentar-novamente");

//================
// FUNÇÕES
//================

//f:pegarObjetosJogo
function pegarObjetosJogo() {
    return window.ObjetosJogo || {
        falas: [{ cumprimento: ["Boa noite!"], fala: ["Pode anotar?"] }],
        pedidos: [{ opcoes: ["Um café"] }],
        avaliacoes: [{ estrelas: 2, comentarios: ["Pedido entregue."] }]
    };
}

//f:pegarRandom
function pegarRandom(items) {
    return items[Math.floor(Math.random() * items.length)];
}

//f:pegarParametrosLevel
function pegarParametrosLevel(level) {
    function lerp(a, b, t) { return a + (b - a) * Math.max(0, Math.min(1, t)); }

    if (level <= 5) {
        const t = (level - 1) / 4;
        return {
            numMesas: level <= 3 ? 2 : 3,
            maxClientesSimultaneos: 2,
            numPedidos: [2, 3, 3, 4, 5][level - 1],
            cooldownSpawnMs: Math.round(lerp(5000, 3000, t)),
            chanceRude: 0,
            pacienciaNormalMs: 10000,
            pacienciaRudeMs: 6000
        };
    }
    if (level <= 10) {
        const t = (level - 6) / 4;
        return {
            numMesas: [3, 3, 4, 4, 5][level - 6],
            maxClientesSimultaneos: 3,
            numPedidos: [5, 6, 7, 8, 10][level - 6],
            cooldownSpawnMs: Math.round(lerp(3000, 2000, t)),
            chanceRude: 0,
            pacienciaNormalMs: 10000,
            pacienciaRudeMs: 6000
        };
    }
    if (level <= 15) {
        const t = (level - 11) / 4;
        return {
            numMesas: Math.random() < 0.5 ? 4 : 5,
            maxClientesSimultaneos: 4,
            numPedidos: 10 + Math.floor(Math.random() * 3),
            cooldownSpawnMs: Math.round(lerp(2000, 1500, t)),
            chanceRude: lerp(0.10, 0.25, t),
            pacienciaNormalMs: 10000,
            pacienciaRudeMs: 6000
        };
    }
    if (level <= 20) {
        const t = (level - 16) / 4;
        const fp = lerp(1.0, 0.80, t);
        return {
            numMesas: Math.random() < 0.5 ? 4 : 5,
            maxClientesSimultaneos: 4,
            numPedidos: 10 + Math.floor(Math.random() * 6),
            cooldownSpawnMs: Math.round(lerp(1500, 1000, t)),
            chanceRude: lerp(0.25, 0.35, t),
            pacienciaNormalMs: Math.round(10000 * fp),
            pacienciaRudeMs: Math.round(6000 * fp)
        };
    }
    // Níveis 21+
    const blocos = Math.floor((level - 21) / 5);
    const fp = Math.max(0.80, 1.0 - blocos * 0.05);
    return {
        numMesas: Math.random() < 0.5 ? 5 : 6,
        maxClientesSimultaneos: 4,
        numPedidos: 12 + Math.floor(Math.random() * 4),
        cooldownSpawnMs: 1000,
        chanceRude: 0.35,
        pacienciaNormalMs: Math.round(10000 * fp),
        pacienciaRudeMs: Math.round(6000 * fp)
    };
}

//f:cooldownSpawn
function cooldownSpawn(baseMs) {
    const delta = Math.floor(baseMs * 0.20);
    return baseMs - delta + Math.floor(Math.random() * (delta * 2 + 1));
}

//f:criarLevel
function criarLevel(level) {
    limparTimersLevel();
    const params = pegarParametrosLevel(level);
    jogoEstado.parametrosLevel = params;
    jogoEstado.mesaAtual = null;
    jogoEstado.mesaAtualCancelavel = null;
    jogoEstado.segurandoPedido = false;
    jogoEstado.pedidosCompletos = 0;
    jogoEstado.pedidosTotais = params.numPedidos;
    jogoEstado.clientesNaFila = params.numPedidos;
    const numMesas = Math.max(params.numMesas, params.maxClientesSimultaneos);
    mesaLayer.innerHTML = "";
    jogoEstado.mesas = [];

    criarMesasFisicas(numMesas).forEach((mesa) => {
        const el = document.createElement("button");
        el.className = "mesa-restaurante";
        el.type = "button";
        el.innerHTML = `
            <span class="mesa-topo"></span>
            <span class="cadeira cadeira-left"></span>
            <span class="cadeira cadeira-right"></span>
            <span class="cliente hidden"></span>
            <span class="review hidden"></span>
            <span class="paciencia-bar hidden"><span class="paciencia-fill"></span></span>
        `;
        mesaLayer.appendChild(el);
        jogoEstado.mesas.push({ ...mesa, element: el });
    });

    posicaoMesas();
    atualizarHUD();
    cozinha.classList.remove("entregue");

    agendarSpawn(tentarSpawnProximo, 1200);
}

//f:tentarSpawnProximo
function tentarSpawnProximo() {
    if (jogoEstado.clientesNaFila <= 0) return;
    if (jogoEstado.modo === "gameover" || jogoEstado.modo === "entre-levels") return;
    const params = jogoEstado.parametrosLevel;

    const ativos = jogoEstado.mesas.filter((m) => {
        const cl = m.element.querySelector(".cliente");
        return !cl.classList.contains("hidden") && !m.entregue;
    }).length;

    if (ativos >= params.maxClientesSimultaneos) {
        agendarSpawn(tentarSpawnProximo, cooldownSpawn(params.cooldownSpawnMs));
        return;
    }

    const mesaLivre = jogoEstado.mesas.find((m) => {
        const cl = m.element.querySelector(".cliente");
        return cl.classList.contains("hidden") && !m.ocupada;
    });

    if (!mesaLivre) {
        agendarSpawn(tentarSpawnProximo, cooldownSpawn(params.cooldownSpawnMs));
        return;
    }

    mesaLivre.pedidoRecebido = false;
    mesaLivre.entregue = false;
    mesaLivre.servido = false;
    mesaLivre.review = null;
    mesaLivre.ocupada = true;

    jogoEstado.clientesNaFila -= 1;
    spawnCliente(mesaLivre, params);

    if (jogoEstado.clientesNaFila > 0) {
        agendarSpawn(tentarSpawnProximo, cooldownSpawn(params.cooldownSpawnMs));
    }
}

//f:limparTimersLevel
function limparTimersLevel() {
    jogoEstado.spawnAgendados.forEach((s) => window.clearTimeout(s.timerId));
    jogoEstado.spawnAgendados = [];
    if (jogoEstado.spawnInterval) {
        window.clearInterval(jogoEstado.spawnInterval);
        jogoEstado.spawnInterval = null;
    }
    jogoEstado.mesas.forEach((m) => {
        if (m.pacienciaTimer) window.clearInterval(m.pacienciaTimer);
    });
}

//f:agendarSpawn
function agendarSpawn(fn, delayMs) {
    const entrada = { timerId: null, disparoEm: Date.now() + delayMs, fn };
    entrada.timerId = window.setTimeout(() => {
        jogoEstado.spawnAgendados = jogoEstado.spawnAgendados.filter((s) => s !== entrada);
        fn();
    }, delayMs);
    jogoEstado.spawnAgendados.push(entrada);
}

//f:pausarSpawns
function pausarSpawns() {
    const agora = Date.now();
    jogoEstado.spawnAgendados.forEach((s) => {
        window.clearTimeout(s.timerId);
        s.timerId = null;
        s.restanteMs = Math.max(0, s.disparoEm - agora);
    });
}

//f:retornarSpawns
function retornarSpawns() {
    const pendentes = [...jogoEstado.spawnAgendados];
    jogoEstado.spawnAgendados = [];
    pendentes.forEach((s) => {
        const delay = s.restanteMs ?? 0;
        const entrada = { timerId: null, disparoEm: Date.now() + delay, fn: s.fn };
        entrada.timerId = window.setTimeout(() => {
            jogoEstado.spawnAgendados = jogoEstado.spawnAgendados.filter((x) => x !== entrada);
            s.fn();
        }, delay);
        jogoEstado.spawnAgendados.push(entrada);
    });
}

//f:spawnCliente
function spawnCliente(mesa, params) {
    const p = params || jogoEstado.parametrosLevel;
    const tipo = Math.random() < p.chanceRude ? "rude" : "normal";
    mesa.tipo = tipo;
    mesa.estrelasCliente = 3;
    mesa.pacienciaCicloMs = tipo === "normal" ? p.pacienciaNormalMs : p.pacienciaRudeMs;

    const clienteEl = mesa.element.querySelector(".cliente");
    const barEl = mesa.element.querySelector(".paciencia-bar");
    const fillEl = mesa.element.querySelector(".paciencia-fill");

    clienteEl.classList.remove("hidden");
    clienteEl.classList.remove("saindo");
    clienteEl.classList.toggle("rude", tipo === "rude");
    barEl.classList.remove("hidden");

    mesa.pacienciaInicio = Date.now();
    mesa.pacienciaPausadoEm = null;

    mesa.pacienciaTimer = window.setInterval(() => {
        if (jogoEstado.modo === "pedindo" || jogoEstado.modo === "pausado") return;
        if (mesa.pedidoRecebido || mesa.entregue) {
            window.clearInterval(mesa.pacienciaTimer);
            barEl.classList.add("hidden");
            return;
        }
        const decorrido = (mesa.pacienciaPausadoEm ?? 0) + (Date.now() - mesa.pacienciaInicio);
        const proporcao = Math.max(0, 1 - decorrido / mesa.pacienciaCicloMs);
        fillEl.style.width = `${proporcao * 100}%`;
        fillEl.style.background = `hsl(${Math.round(proporcao * 110)}, 72%, 48%)`;

        if (proporcao <= 0) {
            mesa.estrelasCliente -= 1;
            if (mesa.estrelasCliente <= 0) {
                window.clearInterval(mesa.pacienciaTimer);
                barEl.classList.add("hidden");
                mesa.pedidoRecebido = true;
                avaliacaoClienteInsatisfeito(mesa);
                penalizarRestaurante();
                registrarPedidoConcluido();
            } else {
                mesa.pacienciaInicio = Date.now();
                mesa.pacienciaPausadoEm = 0;
                fillEl.style.width = "100%";
            }
        }
    }, 80);
}

//f:registrarPedidoConcluido
function registrarPedidoConcluido() {
    jogoEstado.pedidosCompletos += 1;
    atualizarHUD();
    if (jogoEstado.pedidosCompletos >= jogoEstado.pedidosTotais) {
        window.setTimeout(finalizarLevel, 1800);
    }
}

//f:pausarBarras
function pausarBarras() {
    jogoEstado.mesas.forEach((m) => {
        if (m.pacienciaTimer && !m.pedidoRecebido && !m.entregue) {
            m.pacienciaPausadoEm = (m.pacienciaPausadoEm ?? 0) + (Date.now() - m.pacienciaInicio);
        }
    });
}

//f:retornarBarras
function retornarBarras() {
    jogoEstado.mesas.forEach((m) => {
        if (m.pacienciaTimer && !m.pedidoRecebido && !m.entregue) {
            m.pacienciaInicio = Date.now();
        }
    });
}

//f:penalizarRestaurante
function penalizarRestaurante() {
    jogoEstado.estrelasRestaurante = Math.max(0, jogoEstado.estrelasRestaurante - 1);
    atualizarHUD();
    if (jogoEstado.estrelasRestaurante <= 0) window.setTimeout(gameOver, 600);
}

//f:gameOver
function gameOver() {
    jogoEstado.modo = "gameover";
    jogoEstado.keys.clear();
    limparTimersLevel();
    gameOverScoreVal.textContent = jogoEstado.score;
    gameOverModal.classList.remove("hidden");
}

//f:finalizarLevel
function finalizarLevel() {
    jogoEstado.scorePorLevel[jogoEstado.level - 1] = jogoEstado.score;
    jogoEstado.modo = "entre-levels";
    limparTimersLevel();

    const pontuacaoLevel = jogoEstado.score - jogoEstado.scoreInicioLevel;
    entreLevelTitulo.textContent = `Nível ${jogoEstado.level} concluído!`;
    entreLevelScore.innerHTML =
        `Pontuação desta fase: <strong>+${pontuacaoLevel}</strong><br>Total: <strong>${jogoEstado.score}</strong>`;

    btnProximaFase.textContent = `Nível ${jogoEstado.level + 1} →`;
    btnProximaFase.classList.remove("hidden");

    entreLevelModal.classList.remove("hidden");
}

//f:irProximaFase
function irProximaFase() {
    entreLevelModal.classList.add("hidden");
    jogoEstado.level += 1;
    jogoEstado.scoreInicioLevel = jogoEstado.score;
    jogoEstado.garcom.x = 84;
    jogoEstado.garcom.y = 275;
    jogoEstado.garcom.xRatio = 0.1;
    jogoEstado.garcom.yRatio = 0.6;
    jogoEstado.modo = "salao";
    criarLevel(jogoEstado.level);
    atualizarPosicaoGarcom();
    atualizarBotao();
}

//f:tentarNovamenteFase
function tentarNovamenteFase() {
    entreLevelModal.classList.add("hidden");
    jogoEstado.score = jogoEstado.scoreInicioLevel;
    jogoEstado.garcom.x = 84;
    jogoEstado.garcom.y = 275;
    jogoEstado.garcom.xRatio = 0.1;
    jogoEstado.garcom.yRatio = 0.6;
    jogoEstado.modo = "salao";
    criarLevel(jogoEstado.level);
    atualizarPosicaoGarcom();
    atualizarBotao();
}

//f:criarMesasFisicas
function criarMesasFisicas(quant) {
    const posicoes = [];
    const distanciaMin = 0.24;
    let tentativas = 0;
    while (posicoes.length < quant && tentativas < 800) {
        const c = { xRatio: 0.08 + Math.random() * 0.78, yRatio: 0.12 + Math.random() * 0.66 };
        if (posicoes.every((p) => Math.hypot(p.xRatio - c.xRatio, p.yRatio - c.yRatio) >= distanciaMin)) posicoes.push(c);
        tentativas++;
    }
    while (posicoes.length < quant) {
        const i = posicoes.length;
        posicoes.push({ xRatio: 0.06 + (i % 4) * 0.24, yRatio: 0.12 + Math.floor(i / 4) * 0.3 });
    }
    return posicoes.map((pos, i) => ({
        id: `mesa-${jogoEstado.level}-${i + 1}`,
        ...pos,
        ocupada: false, pedidoRecebido: false, entregue: false, servido: false,
        review: null, tipo: null,
        estrelasCliente: 3, pacienciaCicloMs: 10000,
        pacienciaTimer: null, pacienciaInicio: 0, pacienciaPausadoEm: null
    }));
}

//f:posicaoMesas
// Dimensões fixas da .mesa-restaurante (CSS: width:146px height:112px)
const MESA_W = 146;
const MESA_H = 112;
function posicaoMesas() {
    const r = chao.getBoundingClientRect();
    // Se o chão estiver oculto (cenario-pedido ativo), r.width/height são 0 — aborta
    if (r.width === 0 || r.height === 0) return;
    jogoEstado.mesas.forEach((mesa) => {
        mesa.x = garimpar(r.width  * mesa.xRatio, 16, r.width  - MESA_W - 16);
        mesa.y = garimpar(r.height * mesa.yRatio, 16, r.height - MESA_H - 16);
        mesa.element.style.left = `${mesa.x}px`;
        mesa.element.style.top  = `${mesa.y}px`;
    });
}

//f:garimpar
function garimpar(valor, min, max) { return Math.max(min, Math.min(max, valor)); }

//f:atualizarPosicaoGarcom
function atualizarPosicaoGarcom() {
    const r = chao.getBoundingClientRect();
    const gr = garcom.getBoundingClientRect();
    const maxX = r.width  - gr.width;
    const maxY = r.height - gr.height;
    jogoEstado.garcom.x = garimpar(jogoEstado.garcom.x, 0, maxX);
    jogoEstado.garcom.y = garimpar(jogoEstado.garcom.y, 0, maxY);
    if (maxX > 0) jogoEstado.garcom.xRatio = jogoEstado.garcom.x / maxX;
    if (maxY > 0) jogoEstado.garcom.yRatio = jogoEstado.garcom.y / maxY;
    garcom.style.transform = `translate(${jogoEstado.garcom.x}px, ${jogoEstado.garcom.y}px)`;
}

//f:recalcularPosicaoGarcomAposResize
// Dimensões fixas do .doutor (CSS: width:48px height:68px)
const GARCOM_W = 48;
const GARCOM_H = 68;
function recalcularPosicaoGarcomAposResize() {
    const r = chao.getBoundingClientRect();
    if (r.width === 0 || r.height === 0) return;
    const maxX = r.width  - GARCOM_W;
    const maxY = r.height - GARCOM_H;
    jogoEstado.garcom.x = garimpar(jogoEstado.garcom.xRatio * maxX, 0, maxX);
    jogoEstado.garcom.y = garimpar(jogoEstado.garcom.yRatio * maxY, 0, maxY);
    garcom.style.transform = `translate(${jogoEstado.garcom.x}px, ${jogoEstado.garcom.y}px)`;
}

//f:distanciaGarcom
function distanciaGarcom(tx, ty) {
    return Math.hypot((jogoEstado.garcom.x + 24) - tx, (jogoEstado.garcom.y + 24) - ty);
}

//f:distanciaGarcomElemento
function distanciaGarcomElemento(el) {
    const cr = chao.getBoundingClientRect();
    const er = el.getBoundingClientRect();
    return distanciaGarcom(er.left - cr.left + er.width / 2, er.top - cr.top + er.height / 2);
}

//f:pegarAcaoPerto
function pegarAcaoPerto() {
    if (jogoEstado.modo !== "salao") return null;

    if (jogoEstado.segurandoPedido) {
        if (distanciaGarcomElemento(cozinha) < 145)
            return { label: "Entregar na cozinha", action: entregarPedido };
        return null;
    }

    const mesaAberta = jogoEstado.mesas.find((m) => {
        if (m.element.querySelector(".cliente").classList.contains("hidden")) return false;
        if (m.pedidoRecebido || m.entregue) return false;
        return distanciaGarcom(m.x + 72, m.y + 52) < 95;
    });

    if (mesaAberta) return { label: "Anotar pedido", action: () => iniciarPedido(mesaAberta) };
    return null;
}

//f:atualizarBotao
function atualizarBotao() {
    const acao = pegarAcaoPerto();
    if (!acao) { interactBtn.classList.add("hidden"); interactBtn.onclick = null; return; }
    interactBtn.textContent = acao.label;
    interactBtn.classList.remove("hidden");
    interactBtn.onclick = acao.action;
}

//f:controlesJogo
function controlesJogo() {
    if (jogoEstado.modo === "salao") {
        if (jogoEstado.keys.has("arrowup") || jogoEstado.keys.has("w")) jogoEstado.garcom.y -= jogoEstado.garcom.speed;
        if (jogoEstado.keys.has("arrowdown") || jogoEstado.keys.has("s")) jogoEstado.garcom.y += jogoEstado.garcom.speed;
        if (jogoEstado.keys.has("arrowleft") || jogoEstado.keys.has("a")) jogoEstado.garcom.x -= jogoEstado.garcom.speed;
        if (jogoEstado.keys.has("arrowright") || jogoEstado.keys.has("d")) jogoEstado.garcom.x += jogoEstado.garcom.speed;
        atualizarPosicaoGarcom();
        atualizarBotao();
    }
    if (jogoEstado.loopRodando) requestAnimationFrame(controlesJogo);
}

//f:iniciarLoop
function iniciarLoop() {
    if (jogoEstado.loopRodando) return; // já está rodando, não duplica
    jogoEstado.loopRodando = true;
    requestAnimationFrame(controlesJogo);
}

//f:renderizarDialogo
function renderizarDialogo(line) {
    const p = document.createElement("p");
    p.textContent = line;
    dialogoBox.appendChild(p);
    while (dialogoBox.children.length > 3) dialogoBox.removeChild(dialogoBox.firstElementChild);
}

//f:rolarDialogo
function rolarDialogo(textoPedido) {
    jogoEstado.dialogoTimers.forEach((t) => window.clearTimeout(t));
    jogoEstado.dialogoTimers = [];
    const falas = pegarObjetosJogo().falas[0];
    [
        `Cliente: ${pegarRandom(falas.cumprimento)}`,
        pegarRandom(falas.fala),
        `Pedido: ${textoPedido}`
    ].forEach((line, i) => {
        jogoEstado.dialogoTimers.push(window.setTimeout(() => renderizarDialogo(line), i * 900));
    });
}

//f:iniciarPedido
function iniciarPedido(mesa) {
    jogoEstado.modo = "pedindo";
    jogoEstado.keys.clear();
    jogoEstado.mesaAtual = mesa;
    jogoEstado.mesaAtualCancelavel = mesa;
    jogoEstado.pedidoAtual = pegarRandom(pegarObjetosJogo().pedidos[0].opcoes);
    mesa.pedidoRecebido = true;

    if (mesa.pacienciaTimer) {
        window.clearInterval(mesa.pacienciaTimer);
        mesa.element.querySelector(".paciencia-bar").classList.add("hidden");
    }
    pausarBarras();

    interactBtn.classList.add("hidden");
    interactBtn.onclick = null;
    cenarioSala.classList.add("hidden");
    cenarioPedido.classList.remove("hidden");
    pedidoAnotado.textContent = "";
    pedidoInput.value = "";
    feedbackAnotacao.textContent = "Digite o pedido exatamente como aparecer. [Esc para cancelar]";
    dialogoBox.innerHTML = "";
    rolarDialogo(jogoEstado.pedidoAtual);
    window.setTimeout(() => pedidoInput.focus(), 120);
}

//f:cancelarPedido
function cancelarPedido() {
    const mesa = jogoEstado.mesaAtualCancelavel;
    if (!mesa) return;

    mesa.pedidoRecebido = false;

    const clienteEl = mesa.element.querySelector(".cliente");
    const barEl = mesa.element.querySelector(".paciencia-bar");
    const fillEl = mesa.element.querySelector(".paciencia-fill");

    if (!clienteEl.classList.contains("hidden")) {
        barEl.classList.remove("hidden");
        mesa.pacienciaInicio = Date.now();

        mesa.pacienciaTimer = window.setInterval(() => {
            if (jogoEstado.modo === "pedindo" || jogoEstado.modo === "pausado") return;
            if (mesa.pedidoRecebido || mesa.entregue) {
                window.clearInterval(mesa.pacienciaTimer);
                barEl.classList.add("hidden");
                return;
            }
            const decorrido = (mesa.pacienciaPausadoEm ?? 0) + (Date.now() - mesa.pacienciaInicio);
            const proporcao = Math.max(0, 1 - decorrido / mesa.pacienciaCicloMs);
            fillEl.style.width = `${proporcao * 100}%`;
            fillEl.style.background = `hsl(${Math.round(proporcao * 110)}, 72%, 48%)`;
            if (proporcao <= 0) {
                mesa.estrelasCliente -= 1;
                if (mesa.estrelasCliente <= 0) {
                    window.clearInterval(mesa.pacienciaTimer);
                    barEl.classList.add("hidden");
                    mesa.pedidoRecebido = true;
                    avaliacaoClienteInsatisfeito(mesa);
                    penalizarRestaurante();
                    registrarPedidoConcluido();
                } else {
                    mesa.pacienciaInicio = Date.now();
                    mesa.pacienciaPausadoEm = 0;
                    fillEl.style.width = "100%";
                }
            }
        }, 80);
    }

    jogoEstado.mesaAtual = null;
    jogoEstado.mesaAtualCancelavel = null;
    jogoEstado.modo = "salao";
    retornarBarras();
    cenarioPedido.classList.add("hidden");
    cenarioSala.classList.remove("hidden");
    if (jogoEstado.resizePendente) {
        jogoEstado.resizePendente = false;
        posicaoMesas();
        recalcularPosicaoGarcomAposResize();
    }
    atualizarBotao();
}

//f:finalizarPedido
function finalizarPedido() {
    jogoEstado.modo = "salao";
    jogoEstado.segurandoPedido = true;
    jogoEstado.score += 100;
    jogoEstado.mesaAtualCancelavel = null;
    cenarioPedido.classList.add("hidden");
    cenarioSala.classList.remove("hidden");
    feedbackAnotacao.textContent = "Pedido anotado.";
    retornarBarras();
    if (jogoEstado.resizePendente) {
        jogoEstado.resizePendente = false;
        posicaoMesas();
        recalcularPosicaoGarcomAposResize();
    }
    atualizarHUD();
    atualizarBotao();
}

//f:entregarPedido
function entregarPedido() {
    if (!jogoEstado.mesaAtual) return;
    const mesa = jogoEstado.mesaAtual;
    mesa.entregue = true;
    jogoEstado.segurandoPedido = false;
    jogoEstado.score += 150;
    cozinha.classList.add("entregue");
    interactBtn.classList.add("hidden");
    interactBtn.onclick = null;
    atualizarHUD();
    avaliacaoClienteEntregue(mesa);
    jogoEstado.mesaAtual = null;
    registrarPedidoConcluido();
}

//f:avaliacaoClienteEntregue
function avaliacaoClienteEntregue(mesa) {
    let estrelas = mesa.estrelasCliente;
    if (mesa.tipo === "rude" && estrelas > 1 && Math.random() < 0.30) estrelas = 1;
    const objs = pegarObjetosJogo();
    const compat = objs.avaliacoes.filter((a) => a.estrelas === estrelas);
    const av = compat.length > 0 ? pegarRandom(compat) : { estrelas, comentarios: ["Pedido entregue."] };
    mesa.review = { stars: estrelas, comentario: pegarRandom(av.comentarios) };
    renderizarAvaliacao(mesa, estrelas, mesa.review.comentario);
}

//f:avaliacaoClienteInsatisfeito
function avaliacaoClienteInsatisfeito(mesa) {
    const objs = pegarObjetosJogo();
    const compat = objs.avaliacoes.filter((a) => a.estrelas === 0);
    const av = compat.length > 0 ? pegarRandom(compat) : { estrelas: 0, comentarios: ["O cliente foi embora."] };
    mesa.review = { stars: 0, comentario: pegarRandom(av.comentarios) };
    renderizarAvaliacao(mesa, 0, mesa.review.comentario);
}

//f:renderizarAvaliacao
function renderizarAvaliacao(mesa, estrelas, comentario) {
    const cliente = mesa.element.querySelector(".cliente");
    const review = mesa.element.querySelector(".review");
    const estrelasStr = estrelas > 0 ? "★".repeat(estrelas) : "☆";

    window.setTimeout(() => {
        cliente.classList.add("saindo");
        review.textContent = `${estrelasStr} ${comentario}`;
        review.classList.remove("hidden");
    }, 900);

    window.setTimeout(() => {
        cliente.classList.add("hidden");
        mesa.ocupada = false;
    }, 1800);

    window.setTimeout(() => review.classList.add("sumindo"), 3500);
    window.setTimeout(() => {
        review.classList.add("hidden");
        review.classList.remove("sumindo");
        review.textContent = "";
    }, 4100);
}

//f:atualizarHUD
function atualizarHUD() {
    levelValue.textContent = jogoEstado.level;
    scoreValue.textContent = jogoEstado.score;
    pedidosValue.textContent = `${jogoEstado.pedidosCompletos}/${jogoEstado.pedidosTotais}`;
    hudStars.forEach((star, i) => star.classList.toggle("active", i < jogoEstado.estrelasRestaurante));
}

//f:digitacao
function digitacao() {
    const anotado = pedidoInput.value.toLowerCase();
    const esperado = jogoEstado.pedidoAtual.toLowerCase();
    pedidoAnotado.textContent = pedidoInput.value;
    if (!esperado.startsWith(anotado)) {
        feedbackAnotacao.textContent = "Ops, confira a anotação antes que o cliente reclame.";
        return;
    }
    feedbackAnotacao.textContent = "Continue anotando... [Esc para cancelar]";
    if (anotado === esperado) finalizarPedido();
}

//f:bloquearCola
function bloquearCola() {
    const bloqueio = (e) => e.preventDefault();
    pedidoInput.addEventListener("paste", bloqueio);
    pedidoInput.addEventListener("copy", bloqueio);
    pedidoInput.addEventListener("cut",  bloqueio);
    pedidoInput.addEventListener("contextmenu", bloqueio);
    pedidoInput.addEventListener("keydown", (e) => {
        if ((e.ctrlKey || e.metaKey) && ["c","v","x","a"].includes(e.key.toLowerCase())) e.preventDefault();
    });
}

//f:abrirPausa
function abrirPausa() {
    jogoEstado.modo = "pausado";
    jogoEstado.keys.clear();
    pausarBarras();
    pausarSpawns();
    pausaModal.classList.remove("hidden");
}

//f:fecharPausa
function fecharPausa() {
    pausaModal.classList.add("hidden");
    jogoEstado.modo = "salao";
    retornarBarras();
    retornarSpawns();
}

//f:reiniciarLevel
function reiniciarLevel() {
    pausaModal.classList.add("hidden");
    limparTimersLevel();
    jogoEstado.modo = "salao";
    jogoEstado.score = jogoEstado.scoreInicioLevel;
    jogoEstado.garcom.x = 84;
    jogoEstado.garcom.y = 275;
    jogoEstado.garcom.xRatio = 0.1;
    jogoEstado.garcom.yRatio = 0.6;
    jogoEstado.garcom.speed = 4;
    criarLevel(jogoEstado.level);
    atualizarPosicaoGarcom();
    atualizarBotao();
}

//f:sairParaInicio
function sairParaInicio() {
    pausaModal.classList.add("hidden");
    limparTimersLevel();
    jogoEstado.modo = "inicio";
    jogoEstado.loopRodando = false;  // para o loop de animação
    jogoEstado.keys.clear();
    jogoHud.classList.add("hidden");
    cenarioSala.classList.add("hidden");
    cenarioPedido.classList.add("hidden");
    gameOverModal.classList.add("hidden");
    entreLevelModal.classList.add("hidden");
    telaInicio.classList.remove("hidden");
}

//f:iniciarJogo
function iniciarJogo() {
    telaInicio.classList.add("hidden");
    jogoHud.classList.remove("hidden");
    cenarioSala.classList.remove("hidden");
    jogoEstado.modo = "salao";
    jogoEstado.estrelasRestaurante = 5;
    jogoEstado.level = 1;
    jogoEstado.score = 0;
    jogoEstado.scoreInicioLevel = 0;
    jogoEstado.scorePorLevel = [];
    jogoEstado.garcom.x = 84;
    jogoEstado.garcom.y = 275;
    jogoEstado.garcom.xRatio = 0.1;
    jogoEstado.garcom.yRatio = 0.6;
    jogoEstado.garcom.speed = 4;
    criarLevel(jogoEstado.level);
    atualizarPosicaoGarcom();
    atualizarBotao();
    iniciarLoop();
}

//f:reiniciarJogo
function reiniciarJogo() {
    gameOverModal.classList.add("hidden");
    jogoEstado.modo = "salao";
    jogoEstado.estrelasRestaurante = 5;
    jogoEstado.level = 1;
    jogoEstado.score = 0;
    jogoEstado.scoreInicioLevel = 0;
    jogoEstado.scorePorLevel = [];
    jogoEstado.garcom.x = 84;
    jogoEstado.garcom.y = 275;
    jogoEstado.garcom.xRatio = 0.1;
    jogoEstado.garcom.yRatio = 0.6;
    jogoEstado.garcom.speed = 4;
    criarLevel(jogoEstado.level);
    atualizarPosicaoGarcom();
    atualizarBotao();
}

//========================
// EVENTOS
//========================

window.addEventListener("resize", () => {
    // Se o cenario-sala estiver oculto (ex: durante anotação de pedido),
    // os BoundingClientRect retornam zero e corrompem as posições.
    // Marca o resize como pendente e reaplicará ao voltar ao salão.
    if (cenarioSala.classList.contains("hidden")) {
        jogoEstado.resizePendente = true;
        return;
    }
    posicaoMesas();
    recalcularPosicaoGarcomAposResize();
});

window.addEventListener("keydown", (event) => {
    const key = event.key.toLowerCase();

    if (key === "escape") {
        if (jogoEstado.modo === "pedindo") cancelarPedido();
        else if (jogoEstado.modo === "salao") abrirPausa();
        else if (jogoEstado.modo === "pausado") fecharPausa();
        return;
    }

    if (key === " " || key === "spacebar") {
        // Durante anotação, não intercepta — o espaço deve chegar ao pedidoInput
        if (jogoEstado.modo === "pedindo") return;
        // Em qualquer outro modo ativo, previne scroll da página
        const modoAtivo = ["salao", "pausado", "entre-levels", "gameover"];
        if (modoAtivo.includes(jogoEstado.modo)) event.preventDefault();
        if (jogoEstado.modo === "salao" && !interactBtn.classList.contains("hidden")) {
            interactBtn.click();
        }
        return;
    }

    if (["arrowup","arrowdown","arrowleft","arrowright","w","s","a","d"].includes(key)) {
        if (jogoEstado.modo !== "salao" || event.target === pedidoInput) return;
        event.preventDefault();
        jogoEstado.keys.add(key);
    }
});

window.addEventListener("keyup", (event) => {
    jogoEstado.keys.delete(event.key.toLowerCase());
});

pedidoInput.addEventListener("input", digitacao);
btnJogar.addEventListener("click", iniciarJogo);
btnReiniciar.addEventListener("click", reiniciarJogo);
btnContinuar.addEventListener("click", fecharPausa);
btnReiniciarLevel.addEventListener("click", reiniciarLevel);
btnSair.addEventListener("click", sairParaInicio);
btnProximaFase.addEventListener("click", irProximaFase);
btnTentarNovamente.addEventListener("click", tentarNovamenteFase);

//f:iniciarCarrosselDicas
function iniciarCarrosselDicas() {
    const dicas = document.querySelectorAll(".dica");
    if (!dicas.length) return;
    let atual = 0;
    setInterval(() => {
        dicas[atual].classList.add("dica-oculta");
        atual = (atual + 1) % dicas.length;
        dicas[atual].classList.remove("dica-oculta");
    }, 4000);
}

window.addEventListener("load", () => {
    bloquearCola();
    atualizarHUD();
    iniciarCarrosselDicas();
});
