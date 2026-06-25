<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../back-end/login/login.php");
    exit;
}

require_once __DIR__ . "/../../back-end/banco/conexao.php";
require_once __DIR__ . "/../../back-end/liga/liga-oficial.php";

// Garante que o usuario atual exista na Liga Oficial.
$usuario_id = $_SESSION["usuario_id"];
$ligaJogoId = garantirLigaOficial($conexao);
incluirUsuarioNaLigaOficial($conexao, $ligaJogoId, $usuario_id);
$mensagem   = "";
$erro       = "";

// Cria uma liga e adiciona automaticamente o criador como primeiro membro.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "criar") {

    $nome         = trim($_POST["nome"] ?? "");
    $palavraChave = trim($_POST["palavraChave"] ?? "");
    $dataFim      = trim($_POST["dataFim"] ?? "");

    if (empty($nome) || empty($palavraChave)) {
        $erro = "Nome e palavra-chave são obrigatórios.";
    } elseif (!empty($dataFim) && $dataFim < date("Y-m-d")) {
        // Evita criar uma liga que ja nasceria encerrada.
        $erro = "A data de encerramento nao pode estar no passado.";
    } else {
        // Salva a liga. Data vazia vira NULL, ou seja, sem encerramento.
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
            // Registra a entrada do criador; dataEntrada sera usada no ranking.
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

// Adiciona o usuario a uma liga existente usando a palavra-chave.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"]) && $_POST["acao"] === "entrar") {

    $palavraChave = trim($_POST["palavraChave"] ?? "");

    if (empty($palavraChave)) {
        $erro = "Informe a palavra-chave da liga.";
    } else {
        // Localiza a liga e sua data de encerramento pela palavra-chave.
        $stmt = mysqli_prepare($conexao, "SELECT id, nome, dataFim FROM liga WHERE palavraChave = ?");
        mysqli_stmt_bind_param($stmt, "s", $palavraChave);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $liga   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$liga) {
            $erro = "Palavra-chave inválida.";
        } elseif ($liga["dataFim"] !== null && $liga["dataFim"] < date("Y-m-d")) {
            $erro = "Esta liga foi encerrada.";
        } else {
            // Evita inserir o mesmo usuario duas vezes na mesma liga.
            $stmtCheck = mysqli_prepare($conexao,
                "SELECT 1 FROM ligaUsuario WHERE liga_id = ? AND usuario_id = ?"
            );
            mysqli_stmt_bind_param($stmtCheck, "ii", $liga["id"], $usuario_id);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);

            if (mysqli_num_rows($resultCheck) > 0) {
                $erro = "Você já participa dessa liga.";
            } else {
                // A dataEntrada recebe o horario atual pelo valor padrao da tabela.
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

// Busca as ligas do usuario; a Oficial fica no topo da lista.
$stmtLigas = mysqli_prepare($conexao,
    "SELECT l.id, l.nome, l.dataCriacao, l.dataFim
     FROM liga l
     INNER JOIN ligaUsuario lu ON lu.liga_id = l.id
     WHERE lu.usuario_id = ?
     ORDER BY (l.id = $ligaJogoId) DESC, l.dataCriacao DESC"
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
// Mantem o modal de criacao aberto para exibir erros de validacao.
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
    <link rel="stylesheet" href="../css/pages.css">
    <script src="../scripts/pages-script.js" defer></script>
</head>
<body>

<?php require_once __DIR__ . "/../components/menu.php"; ?>

<h1 class="liga-page-titulo">Ligas</h1>

<?php if ($erro): ?>
    <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<?php if ($mensagem): ?>
    <p class="msg-ok"><?= htmlspecialchars($mensagem) ?></p>
<?php endif; ?>

<!-- Botão que abre o modal -->
<div class="acoes-topo">
    <a href="../index.php" class="btn-primario">Voltar para Inicial</a>
    <a href="historicoRelatorio.php" class="btn-primario">Histórico e relatório</a>
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
<div class="liga-card">
    <div class="card-entrar">
        <form class="form-entrar" method="post" action="liga.php">
            <input type="hidden" name="acao" value="entrar">
            <div class="campo">
                <label for="palavraChaveEntrar">Entrar em uma liga — palavra-chave</label>
                <input type="text" id="palavraChaveEntrar" name="palavraChave"
                    placeholder="Digite a palavra-chave" required>
            </div>
            <button type="submit" class="btn-primario">Entrar</button>
        </form>
    </div>
</div>
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

        <?php // A Oficial usa ranking global; as criadas respeitam a entrada do membro. ?>
        <?php $ehLigaDoJogo = (int) $liga["id"] === $ligaJogoId; ?>
        <!-- Ranking geral -->
        <?php
        if ($ehLigaDoJogo) {
            // Liga Oficial: melhor pontuacao entre todos os jogadores.
            $stmtRank = mysqli_prepare($conexao,
                "SELECT u.nome, COALESCE(MAX(p.pontuacao), 0) AS total
                 FROM usuarios u
                 LEFT JOIN partida p ON p.usuario_id = u.id
                 GROUP BY u.id, u.nome
                 ORDER BY total DESC"
            );
        } else {
            // Liga criada: melhor pontuacao somente dentro do periodo valido do membro.
            $stmtRank = mysqli_prepare($conexao,
                "SELECT u.nome, COALESCE(MAX(p.pontuacao), 0) AS total
                 FROM ligaUsuario lu
                 INNER JOIN liga l ON l.id = lu.liga_id
                 INNER JOIN usuarios u ON u.id = lu.usuario_id
                 LEFT JOIN partida p ON p.usuario_id = lu.usuario_id
                     -- Resultados anteriores a entrada ou posteriores ao fim nao contam.
                     AND p.dataPartida >= lu.dataEntrada
                     AND (l.dataFim IS NULL OR p.dataPartida < DATE_ADD(l.dataFim, INTERVAL 1 DAY))
                 WHERE lu.liga_id = ?
                 GROUP BY u.id, u.nome
                 ORDER BY total DESC"
            );
            mysqli_stmt_bind_param($stmtRank, "i", $liga["id"]);
        }
        mysqli_stmt_execute($stmtRank);
        $resRank = mysqli_stmt_get_result($stmtRank);
        ?>

        <!-- Cada jogador aparece pela sua melhor partida, sem somar tentativas. -->
        <p class="ranking-titulo">Ranking geral</p>
        <table>
            <thead>
                <tr><th>#</th><th>Jogador</th><th>Melhor pontuacao</th></tr>
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
        if ($ehLigaDoJogo) {
            // Liga Oficial: melhor resultado obtido nos ultimos sete dias.
            $stmtSem = mysqli_prepare($conexao,
                "SELECT u.nome, COALESCE(MAX(p.pontuacao), 0) AS total
                 FROM usuarios u
                 LEFT JOIN partida p ON p.usuario_id = u.id
                     AND p.dataPartida >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY u.id, u.nome
                 ORDER BY total DESC"
            );
        } else {
            // Liga criada: aplica semana, entrada do membro e encerramento da liga.
            $stmtSem = mysqli_prepare($conexao,
                "SELECT u.nome, COALESCE(MAX(p.pontuacao), 0) AS total
                 FROM ligaUsuario lu
                 INNER JOIN liga l ON l.id = lu.liga_id
                 INNER JOIN usuarios u ON u.id = lu.usuario_id
                 LEFT JOIN partida p ON p.usuario_id = lu.usuario_id
                     AND p.dataPartida >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     -- A semana tambem respeita a entrada do jogador e o fim da liga.
                     AND p.dataPartida >= lu.dataEntrada
                     AND (l.dataFim IS NULL OR p.dataPartida < DATE_ADD(l.dataFim, INTERVAL 1 DAY))
                 WHERE lu.liga_id = ?
                 GROUP BY u.id, u.nome
                 ORDER BY total DESC"
            );
            mysqli_stmt_bind_param($stmtSem, "i", $liga["id"]);
        }
        mysqli_stmt_execute($stmtSem);
        $resSem = mysqli_stmt_get_result($stmtSem);
        ?>

        <!-- No ranking semanal, a melhor partida e limitada aos ultimos sete dias. -->
        <p class="ranking-titulo">Ranking semanal</p>
        <table>
            <thead>
                <tr><th>#</th><th>Jogador</th><th>Melhor pontuacao</th></tr>
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
