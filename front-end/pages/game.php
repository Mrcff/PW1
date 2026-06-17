<?php
session_start();
require_once "menu.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Game</title>
        <link rel="stylesheet" href="../css/pages-styles.css">
        <link rel="stylesheet" href="../css/game-styles.css">
        <script src="../scripts/pages.js" defer></script>
        <script src="../data/game-objects.js" defer></script>
        <script src="../scripts/game.js" defer></script>
    </head>
    <body>
        <main class="tela-jogo">
<!-- HUD =================================================== -->
            <section class="jogo-hud">
                <div>
                    <span class ="hud-label">Nível</span>
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
            <section class="cenario-sala" id="cenario-sala">
                <div class="cozinha" id="cozinha">
                    <span>Cozinha</span>
                </div>
                <div class="chao-restaurante" id="chao-restaurante">
                    <div class="mesa-layer" id="mesa-layer"></div>
                    <div class="doutor" id="doutor"></div>
                </div>
                <button class="interact-btn hidden" id="interact-btn" type="button">Interagir</button>
                <p class="dica">Use WASD ou as setas para andar.</p>
            </section>
<!-- JOGO: CENÁRIO ANOTAR PEDIDO =========================== -->
            <section class="cenario-pedido hidden" id="cenario-pedido">
                <div class="dialogo" id="dialogo"></div>
                <div class="cliente-view">
                    <div class="cliente-head"></div>
                    <div class="cliente-body"></div>
                    <div class="mesa-pedido"></div>
                </div>
                <div class="caderneta">
                    <div class="caderneta-header">Caderneta</div>
                    <div class="pedido-anotado" id="pedido-anotado"></div>
                    <input type="text" id="pedido-input">
                    <p id="feedback-anotacao">Digite o pedido exatamente como aparecer.</p>
                </div>
            </section>
        </main>
    </body>
</html>