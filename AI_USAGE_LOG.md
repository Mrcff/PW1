# Relatório de Uso de Inteligência Artificial Generativa

Este documento registra todas as interações significativas com ferramentas de IA generativa (como Gemini, ChatGPT, Copilot, etc.) durante o desenvolvimento deste projeto. O objetivo é promover o uso ético e transparente da IA como ferramenta de apoio, e não como substituta para a compreensão dos conceitos fundamentais.

## Política de Uso
O uso de IA foi permitido para as seguintes finalidades:
- Geração de ideias e brainstorming de algoritmos.
- Explicação de conceitos complexos.
- Geração de código boilerplate (ex: estrutura de classes, leitura de arquivos).
- Sugestões de refatoração e otimização de código.
- Debugging e identificação de causas de erros.
- Geração de casos de teste.

É proibido submeter código gerado por IA sem compreendê-lo completamente e sem adaptá-lo ao projeto. Todo trecho de código influenciado pela IA deve ser referenciado neste log.

---

## Registro de Interações

*Copie e preencha o template abaixo para cada interação relevante.*

### Interação 1

- **Data:** 20/05/2026
- **Etapa do Projeto:** 2 - Desenvolvimento do Script de Jogo
- **Ferramenta de IA Utilizada:** GPT Codex 5.5
- **Objetivo da Consulta:** Eu estava com dificuldades para entender como gerenciar o dicionário do algoritmo LZW quando ele atinge o tamanho máximo. Precisava de uma estratégia para lidar com isso.

- **Prompt(s) Utilizado(s):**
  1. "No algoritmo de compressão LZW, o que acontece quando o dicionário atinge o tamanho máximo? Quais são as estratégias mais comuns para lidar com isso?"
  2. "Pode me dar um exemplo em Python de como implementar a estratégia de 'resetar o dicionário' no LZW?"

- **Resumo da Resposta da IA:**
  A IA explicou três estratégias: 1) parar de adicionar novas entradas, 2) resetar o dicionário para o estado inicial, e 3) usar uma política de descarte, como LRU (Least Recently Used), que é mais complexa. A IA forneceu um pseudocódigo para a estratégia de reset, que parecia a mais simples e eficaz para este projeto.

- **Análise e Aplicação:**
  A resposta da IA foi extremamente útil para clarear as opções. Optei por implementar a estratégia de resetar o dicionário. O código fornecido pela IA não foi usado diretamente, pois estava muito simplificado e não se encaixava na minha arquitetura de classes. No entanto, a lógica de verificar o tamanho do dicionário e invocar uma função `reset_dictionary()` foi a base para a minha implementação. Isso me poupou tempo de pesquisa em artigos e livros.

- **Referência no Código:**
  A lógica inspirada por esta interação foi implementada no arquivo `compressor/lzw.py`, especificamente na função `compress()`, por volta da linha 85.

---

### Interação 2

- Data: 22/06/2026
- Etapa do Projeto: 2 - Desenvolvimento dos Sprites/personagens
- Ferramenta de IA Utilizada: GPT Codex 5.5
- Objetivo da Consulta: Entender qual seria a melhor forma de renderizar personagens usando uma imagem PNG em formato de sprite sheet 3x3, especialmente diferenciando o atendente animado dos clientes estáticos.

- Prompt(s) Utilizado(s):
  1. “Qual a melhor tecnologia para aplicar personagens de animação com PNG definidos por 3x3? Podemos planejar uma implementação para essa aplicação.”
  2. “Pode me explicar como posso usar sprite sheet no game.css e game.js, trocando as frames apenas do atendente?”

- Resumo da Resposta da IA:
  A IA explicou que, para este projeto, a melhor abordagem seria usar sprite sheet com CSS e JavaScript, em vez de várias imagens separadas ou canvas. Como a imagem PNG estava organizada em uma grade 3x3, a IA sugeriu controlar o frame exibido por meio da propriedade `background-position`. Também explicou que o CSS deveria usar `background-size: 300% 300%`, pois a imagem possui três colunas e três linhas. No JavaScript, a IA orientou a mapear os frames por direção, usando objetos com `coluna` e `linha`, e alternar os frames conforme o personagem se movimenta.

- Análise e Aplicação:
  A explicação ajudou a organizar a implementação dos personagens no jogo. Foi definido que o atendente teria animação, pois ele se movimenta pelo cenário, enquanto os clientes seriam renderizados de forma mais estática nas mesas. No caso do atendente, o JavaScript controla a direção e o frame atual com base no movimento do jogador. Já o CSS é responsável por exibir apenas uma parte da imagem do sprite sheet.

  Com a recomendação da IA, foi possível reaproveitar o PNG já definido anteriormente, sem precisar separar manualmente cada frame em arquivos diferentes. A solução também permitiu manter os clientes como sprites estáticos, enquanto apenas o atendente trocade frame durante a movimentação.

- Referência no Código:
  A lógica foi aplicada nos arquivos:
    - `front-end/css/game.css`, na classe `.garcom-sprite`, onde o sprite sheet é configurado com `background-size: 300% 300%`, `background-position` e `image-rendering: pixelated`.
    - `front-end/scripts/game.js`, nas constantes `FRAMES_GARCOM` e `INTERVALO_FRAME_GARCOM_MS`, e nas funções `configurarSpriteGarcom()`, `renderizarSpriteGarcom()` e `atualizarAnimacaoGarcom()`.

---

