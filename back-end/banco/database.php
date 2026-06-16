<?php
    require_once __DIR__ . "/config.php";

    $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASS, port:DB_PORT);

    if (mysqli_connect_error()) {
        die("Erro de conexão: " . mysqli_connect_error());
    }

    mysqli_set_charset($conexao, "utf8mb4");

    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . ";";
    mysqli_query($conexao, $sql);

    if (mysqli_error($conexao)) {
        echo "Erro ao criar banco de dados: " . mysqli_error($conexao);
    } else {
        echo "Base de dados pronta!";
    }

    mysqli_close($conexao);
?>
