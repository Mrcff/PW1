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

// entra na liga
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "entrar") {

    $palavraChave = trim($_POST["palavraChave"] ?? "");

    if (empty($palavraChave)) {
        $erro = "Informe a palavra-chave da liga.";
    } else {
        // Busca a liga pela palavra-chave
        $stmt = mysqli_prepare($conexao, "SELECT id, nome FROM liga WHERE palavraChave = ?");
        mysqli_stmt_bind_param($stmt, "s", $palavraChave);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $liga   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$liga) {
            $erro = "Palavra-chave inválida.";
        } else {
            // verifica se já participa da liga
            $stmtCheck = mysqli_prepare($conexao, "SELECT 1 FROM ligaUsuario WHERE liga_id = ? AND usuario_id = ?");
            mysqli_stmt_bind_param( $stmtCheck,"ii",$liga["id"],$usuario_id);

            mysqli_stmt_execute($stmtCheck);

            $resultCheck = mysqli_stmt_get_result($stmtCheck);

            if (mysqli_num_rows($resultCheck) > 0) {
                $erro = "Você já participa dessa liga.";
            } else {
                $stmt2 = mysqli_prepare( $conexao, "INSERT INTO ligaUsuario (liga_id, usuario_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt2,"ii", $liga["id"],$usuario_id);
                mysqli_stmt_execute($stmt2);
                $mensagem = "Você entrou na liga " .htmlspecialchars($liga["nome"]) . "!";
                mysqli_stmt_close($stmt2);
            }
            mysqli_stmt_close($stmtCheck);
        }
    }
}

// procura por ligas que o usuario esta
$stmtLigas = mysqli_prepare($conexao,
    "SELECT l.id, l.nome, l.dataCriacao, l.dataFim
     FROM liga l
     INNER JOIN ligaUsuario lu ON lu.liga_id = l.id
     WHERE lu.usuario_id = ?
     ORDER BY l.dataCriacao DESC"
);
mysqli_stmt_bind_param($stmtLigas, "i", $usuario_id);
mysqli_stmt_execute($stmtLigas);
$resultLigas = mysqli_stmt_get_result($stmtLigas);
$ligas = [];
while ($l = mysqli_fetch_assoc($resultLigas)) {
    $ligas[] = $l;
}
mysqli_stmt_close($stmtLigas);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ligas</title>
</head>
<body>

<h1>Ligas</h1>

<?php if ($erro): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<?php if ($mensagem): ?>
    <p style="color: green;"><?= htmlspecialchars($mensagem) ?></p>
<?php endif; ?>

<!-- CRIAR LIGA -->
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

<hr>

<!-- ENTRAR EM LIGA -->
<h2>Entrar em uma liga</h2>
<form method="post" action="liga.php">
    <input type="hidden" name="acao" value="entrar">

    <label for="palavraChaveEntrar">Palavra-chave da liga:</label><br>
    <input type="text" id="palavraChaveEntrar" name="palavraChave" required><br><br>

    <button type="submit">Entrar na liga</button>
</form>

<hr>

<!-- LIGAS DO USUÁRIO E RANKINGS -->
<h2>Suas ligas</h2>

<?php if (empty($ligas)): ?>
    <p>Você ainda não participa de nenhuma liga.</p>
<?php else: ?>
    <?php foreach ($ligas as $liga): ?>
        <h3><?= htmlspecialchars($liga["nome"]) ?></h3>
        <p>Criada em: <?= $liga["dataCriacao"] ?></p>
        <?php if ($liga["dataFim"]): ?>
            <p>Encerra em: <?= $liga["dataFim"] ?></p>
        <?php endif; ?>
        <?php
        // Ranking total (desde a criação da liga)
        $stmtRank = mysqli_prepare($conexao,
            "SELECT u.nome, COALESCE(SUM(p.pontuacao), 0) AS total
             FROM ligaUsuario lu
             INNER JOIN usuarios u ON u.id = lu.usuario_id
             LEFT JOIN partida p ON p.usuario_id = lu.usuario_id
                 AND p.dataPartida >= ?
             WHERE lu.liga_id = ?
             GROUP BY u.id, u.nome
             ORDER BY total DESC"
        );
        mysqli_stmt_bind_param($stmtRank, "si", $liga["dataCriacao"], $liga["id"]);
        mysqli_stmt_execute($stmtRank);
        $resRank = mysqli_stmt_get_result($stmtRank);
        ?>

        <h4>Ranking geral da liga</h4>
        <table border="1" cellpadding="6">
            <tr><th>#</th><th>Jogador</th><th>Pontos</th></tr>
            <?php $pos = 1; while ($row = mysqli_fetch_assoc($resRank)): ?>
                <tr>
                    <td><?= $pos++ ?></td>
                    <td><?= htmlspecialchars($row["nome"]) ?></td>
                    <td><?= $row["total"] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php mysqli_stmt_close($stmtRank); ?>

        <?php
        // Ranking semanal (últimos 7 dias)
        $stmtSem = mysqli_prepare($conexao,
            "SELECT u.nome, COALESCE(SUM(p.pontuacao), 0) AS total
             FROM ligaUsuario lu
             INNER JOIN usuarios u ON u.id = lu.usuario_id
             LEFT JOIN partida p ON p.usuario_id = lu.usuario_id
                 AND p.dataPartida >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             WHERE lu.liga_id = ?
             GROUP BY u.id, u.nome
             ORDER BY total DESC"
        );
        mysqli_stmt_bind_param($stmtSem, "i", $liga["id"]);
        mysqli_stmt_execute($stmtSem);
        $resSem = mysqli_stmt_get_result($stmtSem);
        ?>

        <h4>Ranking semanal da liga</h4>
        <table border="1" cellpadding="6">
            <tr><th>#</th><th>Jogador</th><th>Pontos</th></tr>
            <?php $pos = 1; while ($row = mysqli_fetch_assoc($resSem)): ?>
                <tr>
                    <td><?= $pos++ ?></td>
                    <td><?= htmlspecialchars($row["nome"]) ?></td>
                    <td><?= $row["total"] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php
        mysqli_stmt_close($stmtSem);
        ?>

        <hr>
    <?php endforeach; 
        mysqli_close($conexao);?>
    <?php endif; ?>

</body>
</html>