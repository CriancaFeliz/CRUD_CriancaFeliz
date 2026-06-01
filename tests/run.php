<?php

$root = dirname(__DIR__);
require_once $root . '/app/bootstrap.php';
require_once __DIR__ . '/automated/TestCase.php';

$testFiles = glob(__DIR__ . '/automated/*Test.php') ?: [];
$failures = [];
$totalTests = 0;
$totalAssertions = 0;

foreach ($testFiles as $file) {
    require_once $file;
    $className = basename($file, '.php');

    if (!class_exists($className)) {
        $failures[] = $className . ': classe de teste nao encontrada';
        continue;
    }

    $test = new $className();
    foreach (get_class_methods($test) as $method) {
        if (strpos($method, 'test') !== 0) {
            continue;
        }

        $totalTests++;
        try {
            $before = $test->assertionCount();
            $test->$method();
            $totalAssertions += ($test->assertionCount() - $before);
            echo '.';
        } catch (Throwable $exception) {
            echo 'F';
            $failures[] = $className . '::' . $method . ' - ' . $exception->getMessage();
        }
    }
}

echo PHP_EOL;

if (!empty($failures)) {
    echo "Falhas:" . PHP_EOL;
    foreach ($failures as $failure) {
        echo "- " . $failure . PHP_EOL;
    }
    exit(1);
}

echo "OK: {$totalTests} testes, {$totalAssertions} assercoes." . PHP_EOL;
