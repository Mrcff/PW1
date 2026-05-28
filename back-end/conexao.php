<?php
    require_once __DIR__ . "/config.php";

    $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if (mysqli_connect_error()) {
        die("Erro de conexão: " . mysqli_connect_error());
    }

    mysqli_set_charset($conexao, "utf8mb4");
?>
