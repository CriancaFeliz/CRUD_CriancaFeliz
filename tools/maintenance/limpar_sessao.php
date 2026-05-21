<?php
/**
 * Script temporÃ¡rio para limpar a sessÃ£o
 */
session_start();
$_SESSION = [];
session_destroy();
echo "SessÃ£o limpa! <a href='index.php'>Fazer login novamente</a>";


