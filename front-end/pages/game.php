<?php 
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../back-end/login/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Game</title>
        <link rel="stylesheet" href="../css/game.css">
        <link rel="stylesheet" href="../css/pages.css">
        <script src="../data/cenarios.js" defer></script>
        <script src="../data/personagens.js" defer></script>
        <script src="../scripts/game.js" defer></script>
        <script src="../data/game-objects.js" defer></script>
    </head>
    <body>
        <?php require_once __DIR__ . "/../components/menu.php" ?>
        <main class="tela-jogo">

<!-- TELA DE INÍCIO ======================================== -->
            <section class="tela-inicio" id="tela-inicio">
                <div class="inicio-conteudo">
                    <h1 class="inicio-titulo">Título TBD!</h1>
                    <p class="inicio-desc">Anote os pedidos, entregue na cozinha e não deixe os clientes esperando.</p>
                    <div class="inicio-dicas">
                        <span>🕹️ Use WASD ou setas para andar</span>
                        <span>🎮 Use Barra de Espaço para interagir</span>
                        <span>⏯️ Use Esc para pausar ou cancelar ação</span>
                        <span>📝 Se aproxime de uma mesa e anote o pedido</span>
                        <span>🍽️ Entregue o pedido na cozinha</span>
                        <span>⭐ Não zere as estrelas do restaurante!</span>
                    </div>
                    <button class="btn-jogar" id="btn-jogar" type="button">Jogar</button>
                </div>
            </section>

<!-- HUD =================================================== -->
            <section class="jogo-hud hidden" id="jogo-hud">
                <div>
                    <span class="hud-label">Nível</span>
                    <strong id="hud-level">1</strong>
                </div>
                <div>
                    <span class="hud-label">Pontuação</span>
                    <strong id="hud-score">0</strong>
                </div>
                <div>
                    <span class="hud-label">Pedidos</span>
                    <strong id="hud-pedidos">0/2</strong>
                </div>
                <div class="stars">
                    <span class="star active"></span>
                    <span class="star active"></span>
                    <span class="star active"></span>
                    <span class="star active"></span>
                    <span class="star active"></span>
                </div>
            </section>

<!-- JOGO: CENÁRIO SALÃO DO RESTAURANTE ==================== -->
            <section class="cenario-sala hidden" id="cenario-sala">
                <p class="cenario-pais" id="cenario-pais" aria-live="polite"></p>
                <div class="cozinha" id="cozinha" aria-label="Balcão da cozinha">
                    <span>Cozinha</span>
                </div>
                <div class="chao-restaurante" id="chao-restaurante">
                    <div class="mesa-layer" id="mesa-layer"></div>
                    <img class="arvore-cenario arvore-esquerda" id="arvore-esquerda" alt="">
                    <img class="arvore-cenario arvore-direita" id="arvore-direita" alt="">
                    <div class="doutor" id="doutor" aria-label="Garçom">
                        <span class="garcom-sprite" aria-hidden="true"></span>
                    </div>
                </div>
                <button class="interact-btn hidden" id="interact-btn" type="button">Interagir</button>
                <div class="dica-carrossel" id="dica-carrossel">
                    <p class="dica" data-dica="0">Use WASD ou as setas para andar.</p>
                    <p class="dica dica-oculta" data-dica="1">Use Barra de Espaço para interagir.</p>
                    <p class="dica dica-oculta" data-dica="2">Use Esc para pausar ou cancelar ação.</p>
                    <p class="dica dica-oculta" data-dica="3">As barras de paciência restauram um pouco ao interagir.</p>
                </div>
<!-- ENTRE LEVELS (overlay sobre o cenário) =============== -->
                <div class="entre-level-overlay hidden" id="entre-level-modal">
                    <div class="entre-level-box">
                        <p class="entre-level-titulo" id="entre-level-titulo">Nível 1 concluído!</p>
                        <p class="entre-level-score" id="entre-level-score"></p>
                        <div class="entre-level-btns">
                            <button class="btn-pausa btn-pausa-secondary" id="btn-tentar-novamente" type="button">↩ Tentar novamente</button>
                            <button class="btn-pausa" id="btn-proxima-fase" type="button">Próxima fase →</button>
                        </div>
                    </div>
                </div>

<!-- PAUSA (overlay sobre o cenário) ====================== -->
                <div class="pausa-overlay hidden" id="pausa-modal">
                    <div class="pausa-box">
                        <p class="pausa-titulo">Pausado</p>
                        <button class="btn-pausa" id="btn-continuar"       type="button">Continuar</button>
                        <button class="btn-pausa btn-pausa-secondary" id="btn-reiniciar-level" type="button">Reiniciar Nível</button>
                        <button class="btn-pausa btn-pausa-danger"    id="btn-sair"            type="button">Sair</button>
                    </div>
                </div>

<!-- GAME OVER (overlay sobre o cenário) =================== -->
                <div class="game-over-overlay hidden" id="game-over-modal">
                    <div class="game-over-box">
                        <p class="game-over-titulo">Restaurante fechado!</p>
                        <p class="game-over-sub">As reclamações derrubaram todas as estrelas.</p>
                        <p class="game-over-score">Pontuação: <strong id="game-over-score-val">0</strong></p>
                        <button class="btn-jogar" id="btn-reiniciar" type="button">Tentar novamente</button>
                    </div>
                </div>
            </section>

<!-- JOGO: CENÁRIO ANOTAR PEDIDO =========================== -->
            <section class="cenario-pedido hidden" id="cenario-pedido">
                <div class="dialogo" id="dialogo"></div>
                <div class="cliente-view">
                    <img class="cliente-retrato" id="cliente-retrato" alt="">
                </div>
                <div class="caderneta">
                    <div class="caderneta-header">Caderneta</div>
                    <div class="pedido-anotado" id="pedido-anotado"></div>
                    <input type="text" id="pedido-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    <p id="feedback-anotacao">Digite o pedido exatamente como aparecer.</p>
                </div>
            </section>

        </main>
    </body>
</html>
