<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// O CLI do XAMPP pode não ter permissão para gravar na pasta de sessões usada
// pelo Apache. O utilitário não depende de sessão, mas o bootstrap a inicializa.
$sessionPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'criancafeliz_cli_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}
ini_set('session.save_path', $sessionPath);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$name = trim((string) (getenv('INITIAL_ADMIN_NAME') ?: 'Administrador'));
$email = trim((string) (getenv('INITIAL_ADMIN_EMAIL') ?: ''));
$password = (string) (getenv('INITIAL_ADMIN_PASSWORD') ?: '');

if ($email === '' || !validateEmail($email)) {
    fwrite(STDERR, "Defina INITIAL_ADMIN_EMAIL com um email válido.\n");
    exit(1);
}

if (!PasswordHelper::isValid($password)) {
    fwrite(STDERR, PasswordHelper::validationError($password) . "\n");
    exit(1);
}

$users = new User();
$existing = $users->findByEmail($email);

if ($existing) {
    fwrite(STDERR, "Já existe um usuário com o email informado.\n");
    exit(1);
}

$created = $users->createUser([
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'role' => 'admin',
    'status' => 'Ativo'
]);

fwrite(STDOUT, "Administrador criado com o ID " . ($created['id'] ?? '') . ".\n");
