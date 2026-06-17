<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../../back-end/banco/conexao.php";

$usuario_id = $_SESSION["usuario_id"];
$mensagem   = "";
$erro       = "";


// criar liga
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "criar") {

    $nome         = trim($_POST["nome"] ?? "");
    $palavraChave = trim($_POST["palavraChave"] ?? "");
    $dataFim      = trim($_POST["dataFim"] ?? "");

    if (empty($nome) || empty($palavraChave)) {
        $erro = "Nome e palavra-chave são obrigatórios.";

    } else {
        $stmt = mysqli_prepare($conexao,
            "INSERT INTO liga (nome, palavraChave, criador_id, dataFim)
             VALUES (?, ?, ?, ?)"
        );
        $dataFimFinal = empty($dataFim) ? null : $dataFim;
        mysqli_stmt_bind_param($stmt, "ssis", $nome, $palavraChave, $usuario_id, $dataFimFinal);

        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conexao) === 1062) {
                $erro = "Já existe uma liga com esse nome ou palavra-chave.";
            } else {
                $erro = "Erro ao criar liga.";
            }
        } else {
            $liga_id = mysqli_insert_id($conexao);
            // Criador entra automaticamente
            $stmt2 = mysqli_prepare($conexao,
                "INSERT INTO ligaUsuario (liga_id, usuario_id) VALUES (?, ?)"
            );
            mysqli_stmt_bind_param($stmt2, "ii", $liga_id, $usuario_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            $mensagem = "Liga criada com sucesso!";
        }
        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ligas</title>
    <link rel="stylesheet" href="../css/pages-styles.css">
</head>
<body>

<h1>Ligas</h1>

<?php if ($erro): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<?php if ($mensagem): ?>
    <p style="color: green;"><?= htmlspecialchars($mensagem) ?></p>
<?php endif; ?>

<!-- form criar liga -->
<h2>Criar nova liga</h2>
<form method="post" action="liga.php">
    <input type="hidden" name="acao" value="criar">

    <label for="nome">Nome da liga:</label><br>
    <input type="text" id="nome" name="nome" required><br><br>

    <label for="palavraChave">Palavra-chave (para convidar membros):</label><br>
    <input type="text" id="palavraChave" name="palavraChave" required><br><br>

    <label for="dataFim">Data de encerramento (opcional):</label><br>
    <input type="date" id="dataFim" name="dataFim"><br><br>

    <button type="submit">Criar liga</button>
</form>
</body>
</html>