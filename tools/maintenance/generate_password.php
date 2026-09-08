<?php
// Script para gerar hash da senha
$password = getenv('INITIAL_ADMIN_PASSWORD') ?: 'AlterarEstaSenha!2026';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "Senha: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "<br>";
echo "VerificaÃ§Ã£o: " . (password_verify($password, $hash) ? 'OK' : 'ERRO') . "<br>";
?>


