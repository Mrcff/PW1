<?php
    require_once __DIR__ . "/../conexao.php";

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
        echo "Tabela 'usuarios' pronta!<br>";
    }

    $tabelas = [
        //tabela que armazena a pontuação de cada jogador em uma tentativa de jogo
       "partida" => "CREATE TABLE IF NOT EXISTS partida(
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            pontuacao INT NOT NULL,
            nivel INT NOT NULL,
            dataPartida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "liga" => "CREATE TABLE IF NOT EXISTS liga(
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL UNIQUE,
            palavraChave VARCHAR(30) NOT NULL UNIQUE,
            criador_id INT,
            dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
            dataFim DATE,
            FOREIGN KEY (criador_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "ligaUsuario" => "CREATE TABLE IF NOT EXISTS ligaUsuario (
            liga_id INT,
            usuario_id INT,
            dataEntrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(liga_id, usuario_id),
            FOREIGN KEY (liga_id) REFERENCES liga(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tabelas as $nome => $sql) {
        mysqli_query($conexao, $sql);
        if (mysqli_error($conexao)) {
            echo "Erro ao criar tabela $nome: " . mysqli_error($conexao) . "<br>";
        } else {
            echo "Tabela '$nome' pronta!<br>";
        }
    }

    mysqli_close($conexao);

?>
