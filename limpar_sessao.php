<?php
/**
 * Script temporário para limpar a sessão
 */
session_start();
$_SESSION = [];
session_destroy();
echo "Sessão limpa! <a href='index.php'>Fazer login novamente</a>";
