/*
 * Catálogo visual dos clientes.
 *
 * Cada sprite de movimento é uma folha 3x3: a primeira linha contém os
 * frames frontais. O jogo usa o frame central (50% 0%) enquanto o cliente
 * estiver sentado na mesa.
 */
window.PersonagensJogo = {
  gordinho: {
    nome: "Gordinho",
    sprite: "../assets/images/personagens/GORDINHO-M/Gordinho-movimentos.png",
    retrato: "../assets/images/personagens/GORDINHO-M/gordinho-LFRosto.jpg",
  },
  gotica: {
    nome: "Gótica",
    sprite: "../assets/images/personagens/GOTICA-M/Gotica-movimentos.png",
    retrato: "../assets/images/personagens/GOTICA-M/GOTICA-LFPRosto.png",
  },
  valentao: {
    nome: "Valentão",
    sprite: "../assets/images/personagens/VALENTÃO-M/valentão-movimentos.png",
    retrato: "../assets/images/personagens/VALENTÃO-M/VALENTÃO-LFP.jpg",
  },
  velho: {
    nome: "Velho",
    sprite: "../assets/images/personagens/VELHO-M/Velho-movimentos.png",
    retrato: "../assets/images/personagens/VELHO-M/velho-front-r.png",
  },
};

window.AtendenteJogo = {
  sprite: "../assets/images/personagens/ATENDENTE-M/Atendente-movimentos.png",
};
