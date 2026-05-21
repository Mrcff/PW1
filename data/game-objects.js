// ===========================
// OBJETOS DE FALA E PEDIDOS
// ===========================
//Esta página contém objetos com as falas, pedidos e comentários de avaliações dos clientes

// Use CTRL+F //obj:falas para atualizar os vetores de falas dos clientes
// Use CTRL+F //obj:pedido para atualizar o vetor de pedidos
// Use CTRL+F //obj:availiacoes para atualizar o objeto de avaliações

//obj:falas
const falas = [
  {
    "cumprimento": [
      "Boa noite!",
      "Oi, garçom!",
      "Tudo bem por aí?",
      "Pode anotar?",
      "Cheguei com fome."
    ],
    "fala": [
      "Vou pedir rapidinho.",
      "Anota com carinho, por favor.",
      "Hoje eu quero algo bem simples.",
      "Se não for incomodo, quero o seguinte.",
      "Minha mesa já decidiu o pedido."
    ]
  }
];

//obj:pedido
const pedidos = [
  {
    "opcoes": [
      "Dois sucos de uva e um pastel",
      "Um café com leite e uma coxinha",
      "Três pães de queijo e um chá gelado",
      "Uma limonada e dois mistos quentes",
      "Um bolo de cenoura e um cappuccino"
    ]
  }
];

//obj:availiacoes
const avaliacoes = [
  {
    "estrelas": 1,
    "comentarios": [
      "Faltou tômpero.",
      "O pedido chegou, mas quase perdi a fome.",
      "Dá para melhorar o atendimento.",
      "Foi meio confuso, hein.",
      "Anotou certo, mas sem muita pressa."
    ]
  },
  {
    "estrelas": 2,
    "comentarios": [
      "Bom atendimento.",
      "Gostei, foi bem organizado.",
      "Pedido certo e clima agradável.",
      "Atendeu direitinho.",
      "Voltaria para pedir de novo."
    ]
  },
  {
    "estrelas": 3,
    "comentarios": [
      "Servico excelente!",
      "Anotação perfeita e entrega rápida.",
      "Esse garçom merece aplausos.",
      "Muito bom, virei cliente fiél.",
      "Atendimento de cinco estrelas, mesmo valendo três."
    ]
  }
];

window.ObjetosJogo = {
  falas,
  pedidos,
  avaliacoes
};