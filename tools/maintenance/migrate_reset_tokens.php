<?php

require_once __DIR__ . '/../../app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

$tokensFile = DATA_PATH . '/reset_tokens.json';
if (!file_exists($tokensFile)) {
    echo "Nenhum arquivo reset_tokens.json encontrado." . PHP_EOL;
    exit(0);
}

$tokens = json_decode(file_get_contents($tokensFile), true);
if (!is_array($tokens)) {
    echo "Arquivo reset_tokens.json inválido." . PHP_EOL;
    exit(1);
}

$model = new PasswordResetToken();
$imported = 0;
$skipped = 0;

foreach ($tokens as $tokenHash => $tokenData) {
    if (!is_string($tokenHash) || strlen($tokenHash) !== 64) {
        $skipped++;
        continue;
    }

    if (!empty($tokenData['used']) || empty($tokenData['email']) || empty($tokenData['expiry']) || $tokenData['expiry'] < time()) {
        $skipped++;
        continue;
    }

    try {
        $model->createToken($tokenData['email'], $tokenHash, date('Y-m-d H:i:s', (int)$tokenData['expiry']));
        $imported++;
    } catch (Exception $e) {
        $skipped++;
    }
}

echo "Importados: {$imported}" . PHP_EOL;
echo "Ignorados: {$skipped}" . PHP_EOL;
