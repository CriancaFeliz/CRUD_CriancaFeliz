<?php

require_once __DIR__ . '/../../app/bootstrap.php';

$expectedTables = [
    'Atendido',
    'Usuario',
    'Responsavel',
    'Ficha_Socioeconomico',
    'Familia',
    'Despesas',
    'Frequencia_Dia',
    'Frequencia_Oficina',
    'Oficina',
    'Desligamento',
    'documento',
    'anotacao_psicologica',
    'log'
];

try {
    $pdo = Database::getConnection();
    $rows = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
    $actualTables = array_map(function ($row) {
        return $row[0];
    }, $rows);

    $actualLowerMap = [];
    foreach ($actualTables as $table) {
        $actualLowerMap[strtolower($table)][] = $table;
    }

    $issues = [];
    foreach ($expectedTables as $expected) {
        $lower = strtolower($expected);
        if (!isset($actualLowerMap[$lower])) {
            $issues[] = [
                'table' => $expected,
                'status' => 'missing',
                'actual' => '-'
            ];
            continue;
        }

        if (!in_array($expected, $actualLowerMap[$lower], true)) {
            $issues[] = [
                'table' => $expected,
                'status' => 'case_mismatch',
                'actual' => implode(', ', $actualLowerMap[$lower])
            ];
        }
    }

    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }

    if (empty($issues)) {
        echo "OK: nomes de tabelas esperados foram encontrados com a caixa exata." . PHP_EOL;
        exit(0);
    }

    echo "ATENCAO: divergencias de nomes de tabelas encontradas." . PHP_EOL;
    foreach ($issues as $issue) {
        echo "- {$issue['table']}: {$issue['status']} (encontrado: {$issue['actual']})" . PHP_EOL;
    }
    exit(1);
} catch (Throwable $exception) {
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo "Erro ao diagnosticar tabelas: " . $exception->getMessage() . PHP_EOL;
    exit(1);
}
