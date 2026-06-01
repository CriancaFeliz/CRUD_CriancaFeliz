<?php

abstract class TestCase {
    private $assertions = 0;

    protected function assertTrue($condition, $message = 'Expected condition to be true') {
        $this->assertions++;
        if ($condition !== true) {
            throw new Exception($message);
        }
    }

    protected function assertFalse($condition, $message = 'Expected condition to be false') {
        $this->assertions++;
        if ($condition !== false) {
            throw new Exception($message);
        }
    }

    protected function assertSame($expected, $actual, $message = 'Values are not the same') {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new Exception($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
        }
    }

    protected function assertNotEmpty($value, $message = 'Expected value not to be empty') {
        $this->assertions++;
        if (empty($value)) {
            throw new Exception($message);
        }
    }

    protected function assertThrows(callable $callback, $exceptionClass = Exception::class, $message = 'Expected exception was not thrown') {
        $this->assertions++;
        try {
            $callback();
        } catch (Throwable $exception) {
            if ($exception instanceof $exceptionClass) {
                return;
            }

            throw new Exception('Unexpected exception class: ' . get_class($exception));
        }

        throw new Exception($message);
    }

    public function assertionCount() {
        return $this->assertions;
    }
}
