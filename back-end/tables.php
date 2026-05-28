<?php
    require_once __DIR__ . "/conexao.php";

    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($conexao, $sql);

    if (mysqli_error($conexao)) {
        echo "Erro ao criar tabela: " . mysqli_error($conexao);
    } else {
        echo "Tabela 'usuarios' pronta!";
    }

    mysqli_close($conexao);
?>
