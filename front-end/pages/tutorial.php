<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tutorial</title>
        <link rel="stylesheet" href="../css/pages.css">
        <script src="../scripts/pages-script.js" defer></script>
    </head>
    <body class="tutorial-page">
        <?php require_once __DIR__ . "/../components/menu.php"; ?>
        <main class="tutorial-shell">
            <section class="tutorial-hero">
                <p class="tutorial-kicker">COMECE POR AQUI</p>
                <h1>Como jogar</h1>
                <p>Atenda os clientes rapidamente, registre os pedidos e entregue-os no balcão antes que a paciência acabe.</p>
            </section>
            <section class="tutorial-steps" aria-label="Etapas do jogo">
                <article class="tutorial-step"><span>1</span><h2>Movimente-se</h2><p>Use WASD ou as setas para caminhar pelo salão.</p></article>
                <article class="tutorial-step"><span>2</span><h2>Anote pedidos</h2><p>Aproxime-se de uma mesa ocupada e interaja usando a Barra de Espaço para registrar o pedido.</p></article>
                <article class="tutorial-step"><span>3</span><h2>Entregue no balcão</h2><p>Com o pedido em mãos, vá até o balcão da cozinha. Apenas uma mesa pode ser atendida por vez.</p></article>
                <article class="tutorial-step"><span>4</span><h2>Suba na Liga</h2><p>Cada fase concluída envia seus pontos para o ranking.</p></article>
            </section>
            <a class="tutorial-play" href="game.php">Ir para o jogo</a>
        </main>
    </body>
</html>
