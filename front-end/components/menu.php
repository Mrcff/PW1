<?php
/**
 * session-data.php (antigo menu.php)
 * 
 * Inclua este arquivo no <head> de cada página PHP, ANTES do pages-script.js.
 * Ele emite uma única variável JS com os dados de sessão necessários.
 * O header em si é montado inteiramente pelo pages-script.js.
 * 
 * Uso:
 *   <?php
 *     session_start();
 *     require_once "../includes/session-data.php";
 *   ?>
 */

// Garante que a sessão está ativa sem iniciá-la duas vezes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<script>
  window.usuarioLogado = <?= isset($_SESSION["usuario_id"])
    ? json_encode([
        "id"   => (int)  $_SESSION["usuario_id"],
        "nome" => htmlspecialchars((string) $_SESSION["usuario_nome"], ENT_QUOTES, "UTF-8"),
      ])
    : "null" ?>;
</script>
