<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["erro" => "Não autenticado"]);
    exit;
}

header("Content-Type: application/json");
require_once __DIR__ . "/../../back-end/banco/conexao.php";
require_once __DIR__ . "/../../back-end/banco/liga-oficial.php";

$dados = json_decode(file_get_contents("php://input"), true);

$pontuacao = intval($dados["pontuacao"] ?? 0);
$nivel     = intval($dados["nivel"]     ?? 1);

if ($pontuacao <= 0) {
    echo json_encode(["erro" => "Pontuação inválida"]);
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$ligaJogoId = garantirLigaOficial($conexao);
incluirUsuarioNaLigaOficial($conexao, $ligaJogoId, $usuario_id);

$stmt = mysqli_prepare($conexao,
    "INSERT INTO partida (usuario_id, pontuacao, nivel) VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "iii", $usuario_id, $pontuacao, $nivel);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["ok" => true, "partida_id" => mysqli_insert_id($conexao)]);
} else {
    echo json_encode(["erro" => "Erro ao salvar partida"]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);
?>
