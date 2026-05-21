<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="../css/estilos-globais.css">
        <link rel="icon" href="../assets/icons/favicon.ico">
        <script src="../scripts/script.js" defer></script>
    </head>
    <body>
        <div class="page-header">
            <h1>Bem-vindo!</h1>
            <p>Este é login.php</p>
        </div>
        <?php 
        function sanitizarCampo(string $campo): string {
            $icampo = trim($campo);
            $campo = htmlspecialchars($campo, ENT_QUOTES, 'UTF-8');
            return $campo;
        }
        
        $id = "";
        $erros = [];
        $sucesso = false;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id_raw = $_POST["id"] ?? '';
            if (empty(trim($id_raw))) {
                $erros['id'] = "O nome de Usuário é orbigatório.";
            } else {
                $id = sanitizarCampo($id_raw);
                }
            if (empty($erros)) {
                $sucesso = true;
            }
        }

        $senha = "";
        $sucessoSenha = false;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $senha_raw = $_POST["senha"] ?? '';
            if (empty(trim($id_raw))) {
                $erros['senha'] = "A senha é obrigatória.";
            } else {
                $senha = sanitizarCampo($senha_raw);
                }
            if (empty($erros)) {
                $sucessoSenha = true;
            }
        }

        $confsenha = "";
        $sucessoConfSenha = false;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $confsenha_raw = $_POST["confsenha"] ?? '';
            if (empty(trim($confsenha_raw))) {
                $erros['confsenha'] = ".";
            } else {
                $confsenha = sanitizarCampo($confsenha_raw);
                }
            if (empty($erros)) {
                $sucesso = true;
            }
        }
        
        if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        Dados recebidos com sucesso:
                        <ul class="mb-0 mt-2">
                            <li><strong>Nome</strong>: <?= $id ?></li>
                        </ul>
                        <?php 
                            // Limpa o formulário
                            $id = ""; 
                        ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Erros no formulário.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <form id="form-login" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <label for="input-id" class="form-label">Usuário</label>
            <input type="text" class="form <?= isset($erros['nome']) ? 'is-invalid' : '' ?>" id="input-id" name="id" placeholder="Usuário" value="<?= $id ?>" required>
            
            <?php if (isset($erros['nome'])): ?>
                    <div class="invalid-feedback d-block"><?= $erros['nome'] ?></div>
            <?php endif; ?>

            <div id="erro-id" class="invalid-feedback"></div>
            
            <label for="input-senha" class="form-label">Senha</label>
            <input type="password" class="form <?= isset($erros['senha']) ? 'is-invalid' : '' ?>" id="input-senha" name="senha" value="<?= $senha ?>" required>

            <label for="input-confsenha" class="form-label">Confirmar Senha</label>
            <input type="password" class="form <?= isset($erros['confsenha']) ? 'is-invalid' : '' ?>" id="input-confsenha" name="confsenha" value="<?= $confsenha ?>" required>

            <button type="submit" class="btn">Enviar</button>

        </form>
    </body>
</html>