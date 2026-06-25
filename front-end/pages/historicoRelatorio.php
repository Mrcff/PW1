<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../back-end/login/login.php");
    exit;
}

require_once __DIR__ . "/../../back-end/banco/conexao.php";

$usuarioId = (int) $_SESSION["usuario_id"];

function formatarPontos($valor) {
    return number_format((float) $valor, 0, ",", ".");
}

$estatisticas = [
    "total_partidas" => 0,
    "pontuacao_total" => 0,
    "media_pontos" => 0,
    "maior_pontuacao" => 0,
    "menor_pontuacao" => 0,
];

//primeira parte do relatorio
$stmt = mysqli_prepare(
    $conexao,
    "SELECT COUNT(*) AS total_partidas,
            COALESCE(SUM(pontuacao), 0) AS pontuacao_total,
            COALESCE(AVG(pontuacao), 0) AS media_pontos,
            COALESCE(MAX(pontuacao), 0) AS maior_pontuacao,
            COALESCE(MIN(pontuacao), 0) AS menor_pontuacao
     FROM partida
     WHERE usuario_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $usuarioId);
mysqli_stmt_execute($stmt);
$estatisticas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

//tabelas de historico
$historico = [];
$stmt = mysqli_prepare(
    $conexao,
    "SELECT dataPartida, nivel, pontuacao
     FROM partida
     WHERE usuario_id = ?
     ORDER BY dataPartida DESC, id DESC"
);
mysqli_stmt_bind_param($stmt, "i", $usuarioId);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
while ($partida = mysqli_fetch_assoc($resultado)) {
    $historico[] = $partida;
}
mysqli_stmt_close($stmt);

//calculo da melhor partida
$melhorPartida = null;
$stmt = mysqli_prepare(
    $conexao,
    "SELECT dataPartida, nivel, pontuacao
     FROM partida
     WHERE usuario_id = ?
     ORDER BY pontuacao DESC, nivel DESC, dataPartida DESC
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $usuarioId);
mysqli_stmt_execute($stmt);
$melhorPartida = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

//registro em cada nível
$partidasPorNivel = [];
$stmt = mysqli_prepare(
    $conexao,
    "SELECT nivel, COUNT(*) AS quantidade
     FROM partida
     WHERE usuario_id = ?
     GROUP BY nivel
     ORDER BY nivel"
);
mysqli_stmt_bind_param($stmt, "i", $usuarioId);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
while ($linha = mysqli_fetch_assoc($resultado)) {
    $partidasPorNivel[(int) $linha["nivel"]] = (int) $linha["quantidade"];
}
mysqli_stmt_close($stmt);

//7 ultimos dias da semama
$resumoSemanal = [];
$stmt = mysqli_prepare(
    $conexao,
    "SELECT DATE_SUB(DATE(dataPartida), INTERVAL WEEKDAY(dataPartida) DAY) AS inicio_semana,
            COUNT(*) AS partidas,
            SUM(pontuacao) AS pontuacao
     FROM partida
     WHERE usuario_id = ?
       AND dataPartida >= DATE_SUB(CURDATE(), INTERVAL 7 WEEK)
     GROUP BY inicio_semana
     ORDER BY inicio_semana"
);
mysqli_stmt_bind_param($stmt, "i", $usuarioId);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
while ($linha = mysqli_fetch_assoc($resultado)) {
    $resumoSemanal[$linha["inicio_semana"]] = $linha;
}
mysqli_stmt_close($stmt);

$semanas = [];
$segundaAtual = new DateTimeImmutable("monday this week");
for ($i = 7; $i >= 0; $i--) {
    $inicio = $segundaAtual->modify("-$i weeks");
    $chave = $inicio->format("Y-m-d");
    $semanas[] = [
        "inicio" => $inicio,
        "partidas" => (int) ($resumoSemanal[$chave]["partidas"] ?? 0),
        "pontuacao" => (int) ($resumoSemanal[$chave]["pontuacao"] ?? 0),
    ];
}
$maiorPontuacaoSemanal = max(array_column($semanas, "pontuacao")) ?: 1;

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico e Relatório</title>
    <link rel="stylesheet" href="../css/pages.css">
    <link rel="stylesheet" href="../css/historicoRelatorio.css">
    <script src="../scripts/pages-script.js" defer></script>

</head>
<body>

<main class="relatorio-container">
    <div class="relatorio-topo">
        <div>
            <p class="relatorio-etiqueta">Acompanhamento do jogador</p>
            <h1>Histórico e Relatório</h1>
            <p class="relatorio-intro">Consulte suas partidas e acompanhe a evolução do seu desempenho.</p>
        </div>
        <a href="liga.php" class="botao-voltar">Voltar para ligas</a>
    </div>

    <section aria-labelledby="titulo-resumo">
        <h2 id="titulo-resumo">Relatório de desempenho</h2>
        <div class="cards-estatisticas">
            <article class="card-estatistica"><span>Partidas realizadas</span><strong><?= formatarPontos($estatisticas["total_partidas"]) ?></strong></article>
            <article class="card-estatistica"><span>Pontuação acumulada</span><strong><?= formatarPontos($estatisticas["pontuacao_total"]) ?></strong></article>
            <article class="card-estatistica"><span>Média por partida</span><strong><?= formatarPontos($estatisticas["media_pontos"]) ?></strong></article>
            <article class="card-estatistica"><span>Maior pontuação</span><strong><?= formatarPontos($estatisticas["maior_pontuacao"]) ?></strong></article>
            <article class="card-estatistica"><span>Menor pontuação</span><strong><?= formatarPontos($estatisticas["menor_pontuacao"]) ?></strong></article>
        </div>
    </section>

    <div class="grade-relatorio">
        <section class="painel" aria-labelledby="titulo-melhor-partida">
            <h2 id="titulo-melhor-partida">Melhor partida</h2>
            <?php if ($melhorPartida): ?>
                <p class="destaque-pontos"><?= formatarPontos($melhorPartida["pontuacao"]) ?> pontos</p>
                <dl class="lista-detalhes">
                    <div><dt>Data</dt><dd><?= date("d/m/Y H:i", strtotime($melhorPartida["dataPartida"])) ?></dd></div>
                    <div><dt>Nível máximo</dt><dd><?= (int) $melhorPartida["nivel"] ?></dd></div>
                </dl>
            <?php else: ?>
                <p class="estado-vazio">Jogue uma partida para visualizar seu melhor resultado.</p>
            <?php endif; ?>
        </section>

        <section class="painel" aria-labelledby="titulo-niveis">
            <h2 id="titulo-niveis">Desempenho por nível</h2>
            <p class="legenda-painel">Taxa de alcance: percentual de partidas que chegaram a cada nível.</p>
            <?php if ($partidasPorNivel): ?>
                <div class="lista-niveis">
                    <?php
                    $totalPartidas = (int) $estatisticas["total_partidas"];
                    $acumulado = 0;
                    $maximoNivel = max(array_keys($partidasPorNivel));
                    for ($nivel = $maximoNivel; $nivel >= 1; $nivel--) {
                        $acumulado += $partidasPorNivel[$nivel] ?? 0;
                        $taxa = $totalPartidas > 0 ? ($acumulado / $totalPartidas) * 100 : 0;
                    ?>
                        <div class="linha-nivel">
                            <span>Nível <?= $nivel ?></span>
                            <div class="barra-progresso" aria-label="<?= number_format($taxa, 0) ?>% das partidas alcançaram o nível <?= $nivel ?>"><i style="width: <?= $taxa ?>%"></i></div>
                            <b><?= number_format($taxa, 0) ?>%</b>
                        </div>
                    <?php } ?>
                </div>
            <?php else: ?>
                <p class="estado-vazio">Ainda não há níveis registrados.</p>
            <?php endif; ?>
        </section>
    </div>

    <section class="painel painel-semanal" aria-labelledby="titulo-semanal">
        <div class="cabecalho-painel">
            <div><h2 id="titulo-semanal">Resumo semanal</h2><p class="legenda-painel">Pontuação acumulada nas últimas oito semanas.</p></div>
        </div>
        <div class="grafico-semanal" role="img" aria-label="Gráfico da pontuação acumulada por semana">
            <?php foreach ($semanas as $semana): ?>
                <?php $altura = ($semana["pontuacao"] / $maiorPontuacaoSemanal) * 100; ?>
                <div class="coluna-semana">
                    <span class="valor-semana"><?= formatarPontos($semana["pontuacao"]) ?></span>
                    <div class="trilho-barra"><i style="height: <?= $altura ?>%"></i></div>
                    <span class="rotulo-semana"><?= $semana["inicio"]->format("d/m") ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="painel" aria-labelledby="titulo-historico">
        <div class="cabecalho-painel"><div><h2 id="titulo-historico">Histórico pessoal</h2><p class="legenda-painel">Todas as partidas realizadas, da mais recente para a mais antiga.</p></div></div>
        <?php if ($historico): ?>
            <div class="tabela-responsiva"><table>
                <thead><tr><th>Data</th><th>Nível máximo</th><th>Pontuação</th></tr></thead>
                <tbody>
                <?php foreach ($historico as $partida): ?>
                    <tr><td><?= date("d/m/Y H:i", strtotime($partida["dataPartida"])) ?></td><td><?= (int) $partida["nivel"] ?></td><td><?= formatarPontos($partida["pontuacao"]) ?> pontos</td></tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php else: ?>
            <p class="estado-vazio">Nenhuma partida foi registrada até o momento.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
