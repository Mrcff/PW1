const jogoEstado = {
    level: 1,
    score: 0,
    modo: "salao",
    levelMax: 5,
    pedidoAtual: "",
    mesaAtual: null,
    segurandoPedido: false,
    pedidosCompletos: 0,
    pedidosTotais: 2,
    garcom: {
        x: 84,
        y: 275,
        speed: 3
    },
    keys: new Set(),
    mesas: [],
    dialogoTimers: []
};

//====================
// VARIÁVEIS GLOBAIS
//====================

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

//================
// FUNÇÕES
//================

//f:pegarObjetosJogo
function pegarObjetosJogo() {
    return window.ObjetosJogo || {
        falas: [{ cumprimento: ["Boa noite!"], fala: ["Pode anotar?"] }],
        pedidos: [{ opcoes: ["um cafe"] }],
        avaliacoes: [{ estrelas: 2, comentarios: ["Pedido entregue."] }]
    };
}

//f:pegarRandom
function pegarRandom(items) {
    return items[Math.floor(Math.random() * items.length)];
}

//f:pegarNumeroPedidos
function pegarNumeroPedidos(level) {
    return Math.min(level, jogoEstado.levelMax) * 2;
}

//f:criarLevel
function criarLevel(level) {
    mesaLayer.innerHTML = "";
    jogoEstado.mesas= [];
    jogoEstado.mesaAtual = null;
    jogoEstado.segurandoPedido = false;
    jogoEstado.pedidosCompletos = 0;
    jogoEstado.pedidosTotais = pegarNumeroPedidos(level);

    mesasLevel = criarMesas(jogoEstado.pedidosTotais);

    mesasLevel.forEach((mesa) => {
        const mesaElemento = document.createElement("button");
        mesaElemento.className = "mesa-restaurante";
        mesaElemento.type = "button";
        mesaElemento.innerHTML = `
            <span class="mesa-topo"></span>
            <span class="cadeira cadeira-left"></span>
            <span class="cadeira cadeira-right"></span>
            <span class="cliente ${mesa.cliente ? "" : "hidden"}"></span>
            <span class="review hidden"></span>
        `;
        mesaLayer.appendChild(mesaElemento);
        jogoEstado.mesas.push({ ...mesa, element: mesaElemento});
    });
    posicaoMesas();
    atualizarHUD();
    cozinha.classList.remove("entregue");
}

//f:criarMesas
function criarMesas(quant) {
    const posicoes = [];
    const distanciaMin = 0.24;
    let tentativas = 0;

    while (posicoes.length < quant && tentativas <800) {
        const candidato = {
            xRatio: 0.08 + Math.random() * 0.78,
            yRatio: 0.12 + Math.random() * 0.66
        };
        const temEspaco = posicoes.every((posicao) => {
            return Math.hypot(posicao.xRatio - candidato.xRatio, posicao.yRatio - candidato.yRatio) >= distanciaMin;
        });
        if (temEspaco) {
            posicoes.push(candidato);
        }
        tentativas +=1;
    }
    while (posicoes.length < quant) {
        const index = posicoes.length;
        posicoes.push({
            xRatio: 0.06 + (index %4) * 0.24,
            yRatio: 0.12 + Math.floor(index / 4) *0.3
        });
    }
    return posicoes.map((posicao, index) => ({
        id: `mesa-${jogoEstado.level}-${index +1}`,
        ...posicao,
        cliente: true,
        servido: false,
        pedidoRecebido: false,
        review: null
    }));
}

//f:posicaoMesas
function posicaoMesas() {
    const chaoRect = chao.getBoundingClientRect();

    jogoEstado.mesas.forEach((mesa) => {
        const mesaRect = mesa.element.getBoundingClientRect();
        mesa.x = garimpar(chaoRect.width * mesa.xRatio, 16, chaoRect.width - mesaRect.width - 16);
        mesa.y = garimpar(chaoRect.height * mesa.yRatio, 16, chaoRect.height - mesaRect.height - 16);
        mesa.element.style.left = `${mesa.x}px`;
        mesa.element.style.top = `${mesa.y}px`;
    });
}

//f:garimpar
function garimpar(valor, min, max) {
  return Math.max(min, Math.min(max, valor));
}

//f:atualizarPosicaoGarcom
function atualizarPosicaoGarcom() {
    const chaoRect = chao.getBoundingClientRect();
    const garcomRect = garcom.getBoundingClientRect();
    const maxX = chaoRect.width - garcomRect.width;
    const maxY = chaoRect.height - garcomRect.height;

    jogoEstado.garcom.x = garimpar(jogoEstado.garcom.x, 0, maxX);
    jogoEstado.garcom.y = garimpar(jogoEstado.garcom.y, 0, maxY);

    garcom.style.transform = `translate(${jogoEstado.garcom.x}px, ${jogoEstado.garcom.y}px)`;
}

//f:distanciaGarcom
function distanciaGarcom(targetX, targetY) {
    const garcomCenterX = jogoEstado.garcom.x + 24;
    const garcomCenterY = jogoEstado.garcom.y + 24;
    return Math.hypot(garcomCenterX - targetX, garcomCenterY - targetY);
}

//f:distanciaGarcomElemento
function distanciaGarcomElemento(elemento) {
    const chaoRect = chao.getBoundingClientRect();
    const elementRect = elemento.getBoundingClientRect();
    const targetX = elementRect.left - chaoRect.left + elementRect.width / 2;
    const targetY = elementRect.top - chaoRect.top + elementRect.height / 2;
    return distanciaGarcom(targetX, targetY);
}

//f:pegarAcaoPerto
function pegarAcaoPerto() {
    if (jogoEstado.modo !== "salao") {
        return null
    }
    if (jogoEstado.segurandoPedido) {
        const proximoCozinha = distanciaGarcomElemento(cozinha) < 145;
        if(proximoCozinha) {
            return {
                label: "Entregar na cozinha",
                action: entregarPedido
            };
        }
        return null;
    }

    const mesaAberta = jogoEstado.mesas.find((mesa) => {
        if (mesa.pedidoRecebido || mesa.entregue) {
            return false;
        }
        return distanciaGarcom(mesa.x + 72, mesa.y + 52) <95;
    });

    if (mesaAberta) {
        return {
            label: "Anotar pedido",
            action: () => iniciarPedido(mesaAberta)
        };
    }
    return null;
}

//f:atualizarBotao
function atualizarBotao() {
    const acao = pegarAcaoPerto();
    if(!acao) {
        interactBtn.classList.add("hidden");
        interactBtn.onclick = null;
        return;
    }

    interactBtn.textContent = acao.label;
    interactBtn.classList.remove("hidden");
    interactBtn.onclick = acao.action;
}

//f:controlesJogo
function controlesJogo() {
  if (jogoEstado.modo === "salao") {
    if (jogoEstado.keys.has("arrowup") || jogoEstado.keys.has("w")) {
      jogoEstado.garcom.y -= jogoEstado.garcom.speed;
    }
    if (jogoEstado.keys.has("arrowdown") || jogoEstado.keys.has("s")) {
      jogoEstado.garcom.y += jogoEstado.garcom.speed;
    }
    if (jogoEstado.keys.has("arrowleft") || jogoEstado.keys.has("a")) {
      jogoEstado.garcom.x -= jogoEstado.garcom.speed;
    }
    if (jogoEstado.keys.has("arrowright") || jogoEstado.keys.has("d")) {
      jogoEstado.garcom.x += jogoEstado.garcom.speed;
    }

    atualizarPosicaoGarcom();
    atualizarBotao();
  }
  requestAnimationFrame(controlesJogo);
}

//f:renderizarDialogo
function renderizarDialogo(line) {
  const linha = document.createElement("p");
  linha.textContent = line;
  dialogoBox.appendChild(linha);

  while (dialogoBox.children.length > 3) {
    dialogoBox.removeChild(dialogoBox.firstElementChild);
  }
}

//f:rolarDialogo
function rolarDialogo(textoPedido) {
  jogoEstado.dialogoTimers.forEach((timer) => window.clearTimeout(timer));
  jogoEstado.dialogoTimers = [];

  const gameObjects = pegarObjetosJogo();
  const falasCliente = gameObjects.falas[0];
  const lines = [
    `Cliente: ${pegarRandom(falasCliente.cumprimento)}`,
    pegarRandom(falasCliente.fala),
    `Pedido: ${textoPedido}`
  ];
  dialogoBox.innerHTML = "";

  lines.forEach((line, index) => {
    const timer = window.setTimeout(() => renderizarDialogo(line), index * 900);
    jogoEstado.dialogoTimers.push(timer);
  });
}

//f:iniciarPedido
function iniciarPedido(mesa) {
  jogoEstado.modo = "pedindo";
  jogoEstado.keys.clear();
  jogoEstado.mesaAtual = mesa;
  jogoEstado.pedidoAtual = pegarRandom(pegarObjetosJogo().pedidos[0].opcoes);
  mesa.pedidoRecebido = true;
  interactBtn.classList.add("hidden");
  interactBtn.onclick = null;
  cenarioSala.classList.add("hidden");
  cenarioPedido.classList.remove("hidden");
  pedidoAnotado.textContent = "";
  pedidoInput.value = "";
  feedbackAnotacao.textContent = "Digite o pedido exatamente como aparecer.";
  rolarDialogo(jogoEstado.pedidoAtual);
  window.setTimeout(() => pedidoInput.focus(), 120);
}

//f:finalizarPedido
function finalizarPedido() {
  jogoEstado.modo = "salao";
  jogoEstado.segurandoPedido = true;
  jogoEstado.score += 100;
  cenarioPedido.classList.add("hidden");
  cenarioSala.classList.remove("hidden");
  feedbackAnotacao.textContent = "Pedido anotado.";
  atualizarHUD();
  atualizarBotao();
}

//f:entregarPedido
function entregarPedido() {
  if (!jogoEstado.mesaAtual) {
    return;
  }

  jogoEstado.mesaAtual.entregue = true;
  jogoEstado.segurandoPedido = false;
  jogoEstado.pedidosCompletos += 1;
  jogoEstado.score += 150;
  cozinha.classList.add("entregue");
  interactBtn.classList.add("hidden");
  interactBtn.onclick = null;
  atualizarHUD();
  avaliacaoCliente(jogoEstado.mesaAtual);
  jogoEstado.mesaAtual = null;

  if (jogoEstado.pedidosCompletos >= jogoEstado.pedidosTotais) {
    window.setTimeout(avancarLevel, 1800);
  }
}

//f:avaliacaoCliente
function avaliacaoCliente(mesa) {
  const cliente = mesa.element.querySelector(".cliente");
  const review = mesa.element.querySelector(".review");
  const avaliacao = pegarRandom(pegarObjetosJogo().avaliacoes);
  const comentario = pegarRandom(avaliacao.comentarios);
  mesa.review = { stars: avaliacao.estrelas, comentario };

  window.setTimeout(() => {
    cliente.classList.add("saindo");
    review.textContent = `${"★".repeat(avaliacao.estrelas)} ${comentario}`;
    review.classList.remove("hidden");
  }, 900);

  window.setTimeout(() => {
    cliente.classList.add("hidden");
  }, 1800);
}

//f:avancarLevel
function avancarLevel() {
  if (jogoEstado.level >= jogoEstado.levelMax) {
    feedbackAnotacao.textContent = "Todos os níveis foram concluídos.";
    return;
  }

  jogoEstado.level += 1;
  jogoEstado.garcom.x = 84;
  jogoEstado.garcom.y = 275;
  criarLevel(jogoEstado.level);
  atualizarPosicaoGarcom();
  atualizarBotao();
}

//f:atualizarHUD
function atualizarHUD() {
  levelValue.textContent = jogoEstado.level;
  scoreValue.textContent = jogoEstado.score;
  pedidosValue.textContent = `${jogoEstado.pedidosCompletos}/${jogoEstado.pedidosTotais}`;
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

  feedbackAnotacao.textContent = "Continue anotando...";
  if (anotado === esperado) {
    finalizarPedido();
  }
}

//f:iniciarJogo
function iniciarJogo() {
    criarLevel(jogoEstado.level);
    atualizarPosicaoGarcom();
    atualizarBotao();
    requestAnimationFrame(controlesJogo);
}

window.addEventListener("resize", () => {
    posicaoMesas();
    atualizarPosicaoGarcom();
});

window.addEventListener("keydown", (event) => {
    const key = event.key.toLowerCase();
    if (["arrowup", "arrowdown", "arrowleft", "arrowright", "w", "s", "a", "d"].includes(key)) {
        if (jogoEstado.modo !== "salao" || event.target === pedidoInput) {
            return;
        }
        event.preventDefault();
        jogoEstado.keys.add(key);
    }
});

window.addEventListener("keyup", (event) => {
    jogoEstado.keys.delete(event.key.toLowerCase());
});

pedidoInput.addEventListener("input", digitacao);
//talvez mudar isso para um botão de iniciar
window.addEventListener("load", iniciarJogo);