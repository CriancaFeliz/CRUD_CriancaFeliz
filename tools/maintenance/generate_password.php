<?php
// Script para gerar hash da senha
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Senha: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "<br>";
echo "VerificaÃ§Ã£o: " . (password_verify($password, $hash) ? 'OK' : 'ERRO') . "<br>";
?>


