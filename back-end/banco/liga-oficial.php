<?php

const LIGA_OFICIAL_NOME = "Liga Oficial do Jogo";
const LIGA_OFICIAL_CHAVE = "__liga_oficial_pw1__";

function garantirLigaOficial($conexao) {
    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO liga (nome, palavraChave, criador_id)
         VALUES (?, ?, NULL)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
    );
    mysqli_stmt_bind_param($stmt, "ss", $nome, $chave);
    $nome = LIGA_OFICIAL_NOME;
    $chave = LIGA_OFICIAL_CHAVE;
    mysqli_stmt_execute($stmt);
    $ligaId = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    return (int) $ligaId;
}

function incluirUsuarioNaLigaOficial($conexao, $ligaId, $usuarioId) {
    $stmt = mysqli_prepare(
        $conexao,
        "INSERT IGNORE INTO ligaUsuario (liga_id, usuario_id) VALUES (?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ii", $ligaId, $usuarioId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
