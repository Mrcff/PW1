<?php
$base = "/PW1/front-end/";
?>
<header class="site-header">

    <a href="<?= $base ?>index.php" class="header-logo">
        Café sem Fronteiras
    </a>

    <nav class="header-nav">
        <a href="<?= $base ?>index.php">Início</a>
        <a href="<?= $base ?>pages/tutorial.php">Tutorial</a>
        <a href="<?= $base ?>pages/game.php">Jogo</a>
        <a href="<?= $base ?>pages/liga.php">Liga</a>
    </nav>

    <div class="header-right">
        <?php if (isset($_SESSION["usuario_id"])): ?>
            <span>Olá, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?></span>
            <a href="/trabalhoWeb/PW1/back-end/login/logout.php">Sair</a>
        <?php else: ?>
            <a href="/trabalhoWeb/PW1/back-end/login/login.php">Login</a>
        <?php endif; ?>
    </div>

</header>
