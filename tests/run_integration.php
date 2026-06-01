<?php

$root = dirname(__DIR__);

if (!extension_loaded('pdo_mysql')) {
    echo "ERRO: a extensao pdo_mysql nao esta habilitada. Rode pelo Docker de teste: docker compose -f docker-compose.test.yml exec -T app php tests/run_integration.php" . PHP_EOL;
    exit(2);
}

require_once $root . '/app/bootstrap.php';
require_once __DIR__ . '/automated/TestCase.php';
require_once __DIR__ . '/integration/IntegrationTestCase.php';

$testFiles = glob(__DIR__ . '/integration/*Test.php') ?: [];
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
            if (method_exists($test, 'setUp')) {
                $test->setUp();
            }

            $before = $test->assertionCount();
            $test->$method();
            $totalAssertions += ($test->assertionCount() - $before);

            if (method_exists($test, 'tearDown')) {
                $test->tearDown();
            }

            echo '.';
        } catch (Throwable $exception) {
            echo 'F';
            $failures[] = $className . '::' . $method . ' - ' . $exception->getMessage();

            if (method_exists($test, 'tearDown')) {
                try {
                    $test->tearDown();
                } catch (Throwable $tearDownException) {
                    $failures[] = $className . '::tearDown - ' . $tearDownException->getMessage();
                }
            }
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

echo "OK: {$totalTests} testes de integracao, {$totalAssertions} assercoes." . PHP_EOL;
