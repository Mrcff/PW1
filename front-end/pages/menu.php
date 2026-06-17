<header class="site-header">

    <a href="index.php" class="header-logo">
        Café do TADS
    </a>

    <nav class="header-nav">
        <a href="../index.php">Início</a>
        <a href="tutorial.php">Tutorial</a>
        <a href="game.php">Jogo</a>
        <a href="liga.php">Liga</a>
    </nav>

    <div class="header-right">
        <?php if (isset($_SESSION["usuario_id"])): ?>
            <span>Olá, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?></span>
            <a href="../back-end/login/logout.php">Sair</a>
        <?php else: ?>
            <a href="../back-end/login/login.php">Login</a>
        <?php endif; ?>
    </div>

</header>