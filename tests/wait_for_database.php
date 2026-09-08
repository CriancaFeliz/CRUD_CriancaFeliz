<?php

$timeout = (int) (getenv('DB_WAIT_TIMEOUT') ?: 120);
$deadline = time() + $timeout;
$lastError = null;

while (time() <= $deadline) {
    try {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 3306;
        $dbname = getenv('DB_NAME') ?: 'criancafeliz';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdo->query('SELECT 1');
        echo "OK: banco disponivel." . PHP_EOL;
        exit(0);
    } catch (Throwable $exception) {
        $lastError = $exception->getMessage();
        sleep(2);
    }
}

echo "ERRO: banco indisponivel apos {$timeout}s. Ultimo erro: {$lastError}" . PHP_EOL;
exit(1);
