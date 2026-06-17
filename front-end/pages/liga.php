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
            "INSERT INTO liga (nome, palavraChave, criador_id, dataFim) VALUES (?, ?, ?, ?)"
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

// entrar na liga
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "entrar") {

    $palavraChave = trim($_POST["palavraChave"] ?? "");

    if (empty($palavraChave)) {
        $erro = "Informe a palavra-chave da liga.";
    } else {
        $stmt = mysqli_prepare($conexao, "SELECT id, nome FROM liga WHERE palavraChave = ?");
        mysqli_stmt_bind_param($stmt, "s", $palavraChave);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $liga   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$liga) {
            $erro = "Palavra-chave inválida.";
        } else {
            $stmtCheck = mysqli_prepare($conexao,
                "SELECT 1 FROM ligaUsuario WHERE liga_id = ? AND usuario_id = ?"
            );
            mysqli_stmt_bind_param($stmtCheck, "ii", $liga["id"], $usuario_id);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);

            if (mysqli_num_rows($resultCheck) > 0) {
                $erro = "Você já participa dessa liga.";
            } else {
                $stmt2 = mysqli_prepare($conexao,
                    "INSERT INTO ligaUsuario (liga_id, usuario_id) VALUES (?, ?)"
                );
                mysqli_stmt_bind_param($stmt2, "ii", $liga["id"], $usuario_id);
                mysqli_stmt_execute($stmt2);
                $mensagem = "Você entrou na liga " . htmlspecialchars($liga["nome"]) . "!";
                mysqli_stmt_close($stmt2);
            }
            mysqli_stmt_close($stmtCheck);
        }
    }
}

// busca ligas do usuário
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

// reabre modal se houve erro ao criar
$reabrirModal = ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["acao"])
    && $_POST["acao"] === "criar"
    && !empty($erro));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ligas</title>
    <link rel="stylesheet" href="../css/liga.css">
</head>
<body>

<h1>Ligas</h1>

<?php if ($erro): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<?php if ($mensagem): ?>
    <p style="color: green;"><?= htmlspecialchars($mensagem) ?></p>
<?php endif; ?>

<!-- Botão que abre o modal -->
<div class="acoes-topo">
    <a href="../index.html" class="btn-primario">Voltar para Inicial</a>
    <button class="btn-primario" onclick="abrirModal()">+ Criar liga</button>
</div>

<!-- ── MODAL CRIAR LIGA ── -->
<div class="modal-overlay <?= $reabrirModal ? 'aberto' : '' ?>" id="modalCriar">
    <div class="modal">
        <button class="modal-fechar" onclick="fecharModal()" aria-label="Fechar">&times;</button>
        <p class="modal-titulo">Criar nova liga</p>

        <form method="post" action="liga.php">
            <input type="hidden" name="acao" value="criar">

            <div class="campo">
                <label for="nome">Nome da liga</label>
                <input type="text" id="nome" name="nome"
                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label for="palavraChave">Palavra-chave para convidar membros</label>
                <input type="text" id="palavraChave" name="palavraChave"
                       value="<?= htmlspecialchars($_POST['palavraChave'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label for="dataFim">Data de encerramento <span style="font-weight:400;text-transform:none">(opcional)</span></label>
                <input type="date" id="dataFim" name="dataFim"
                       value="<?= htmlspecialchars($_POST['dataFim'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-primario">Criar liga</button>
        </form>
    </div>
</div>

<hr>

<!-- ── SUAS LIGAS ── -->
<p class="secao-titulo">Suas ligas</p>

<?php if (empty($ligas)): ?>
    <p class="vazio">Você ainda não participa de nenhuma liga.</p>
<?php else: ?>

    <?php foreach ($ligas as $liga): ?>
    <div class="liga-card">

        <p class="liga-nome"><?= htmlspecialchars($liga["nome"]) ?></p>
        <p class="liga-info">Criada em: <?= $liga["dataCriacao"] ?></p>
        <?php if ($liga["dataFim"]): ?>
            <p class="liga-info">Encerra em: <?= $liga["dataFim"] ?></p>
        <?php endif; ?>

        <!-- Entrar em uma liga (inline) -->
        <form class="form-entrar" method="post" action="liga.php">
            <input type="hidden" name="acao" value="entrar">
            <div class="campo">
                <label for="palavraChaveEntrar">Entrar em outra liga — palavra-chave</label>
                <input type="text" id="palavraChaveEntrar" name="palavraChave"
                       placeholder="Digite a palavra-chave" required>
            </div>
            <button type="submit" class="btn-primario">Entrar</button>
        </form>

        <!-- Ranking geral -->
        <?php
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

        <p class="ranking-titulo">Ranking geral</p>
        <table>
            <thead>
                <tr><th>#</th><th>Jogador</th><th>Pontos</th></tr>
            </thead>
            <tbody>
                <?php $pos = 1; while ($row = mysqli_fetch_assoc($resRank)): ?>
                <tr>
                    <td><?= $pos++ ?></td>
                    <td><?= htmlspecialchars($row["nome"]) ?></td>
                    <td><?= $row["total"] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php mysqli_stmt_close($stmtRank); ?>

        <!-- Ranking semanal -->
        <?php
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

        <p class="ranking-titulo">Ranking semanal</p>
        <table>
            <thead>
                <tr><th>#</th><th>Jogador</th><th>Pontos</th></tr>
            </thead>
            <tbody>
                <?php $pos = 1; while ($row = mysqli_fetch_assoc($resSem)): ?>
                <tr>
                    <td><?= $pos++ ?></td>
                    <td><?= htmlspecialchars($row["nome"]) ?></td>
                    <td><?= $row["total"] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php mysqli_stmt_close($stmtSem); ?>

    </div>
    <?php endforeach; ?>
    <?php mysqli_close($conexao); ?>

<?php endif; ?>

<script>
    const overlay = document.getElementById('modalCriar');

    function abrirModal() {
        overlay.classList.add('aberto');
        document.body.style.overflow = 'hidden';
    }

    function fecharModal() {
        overlay.classList.remove('aberto');
        document.body.style.overflow = '';
    }

    // fecha clicando fora do modal
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) fecharModal();
    });

    // fecha com Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') fecharModal();
    });
</script>

</body>
</html>