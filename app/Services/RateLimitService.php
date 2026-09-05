<?php

/**
 * Limitador persistente para operações sensíveis.
 *
 * Email e IP são armazenados somente como hashes, evitando que a proteção
 * crie uma nova fonte de dados pessoais.
 */
class RateLimitService {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function isAllowed($action, $identifier, $maxAttempts, $windowSeconds) {
        [$action, $identifierHash, $maxAttempts, $windowSeconds] = $this->normalize(
            $action,
            $identifier,
            $maxAttempts,
            $windowSeconds
        );

        $stmt = $this->pdo->prepare(
            'SELECT attempts, window_started_at, blocked_until
               FROM auth_rate_limits
              WHERE action = ? AND identifier_hash = ?
              LIMIT 1'
        );
        $stmt->execute([$action, $identifierHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return true;
        }

        $now = time();
        $blockedUntil = !empty($row['blocked_until']) ? strtotime($row['blocked_until']) : false;
        if ($blockedUntil !== false && $blockedUntil > $now) {
            return false;
        }

        $windowStarted = strtotime($row['window_started_at']);
        if ($windowStarted === false || ($now - $windowStarted) >= $windowSeconds) {
            return true;
        }

        return (int) $row['attempts'] < $maxAttempts;
    }

    public function hit($action, $identifier, $maxAttempts, $windowSeconds, $blockSeconds) {
        [$action, $identifierHash, $maxAttempts, $windowSeconds] = $this->normalize(
            $action,
            $identifier,
            $maxAttempts,
            $windowSeconds
        );
        $blockSeconds = max(1, min(86400, (int) $blockSeconds));
        $startedTransaction = !$this->pdo->inTransaction();

        try {
            if ($startedTransaction) {
                $this->pdo->beginTransaction();
            }

            $stmt = $this->pdo->prepare(
                'SELECT id, attempts, window_started_at
                   FROM auth_rate_limits
                  WHERE action = ? AND identifier_hash = ?
                  FOR UPDATE'
            );
            $stmt->execute([$action, $identifierHash]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $now = time();
            $attempts = 1;
            $windowStartedAt = date('Y-m-d H:i:s', $now);

            if ($row) {
                $existingWindow = strtotime($row['window_started_at']);
                if ($existingWindow !== false && ($now - $existingWindow) < $windowSeconds) {
                    $attempts = (int) $row['attempts'] + 1;
                    $windowStartedAt = $row['window_started_at'];
                }
            }

            $blockedUntil = $attempts >= $maxAttempts
                ? date('Y-m-d H:i:s', $now + $blockSeconds)
                : null;

            if ($row) {
                $update = $this->pdo->prepare(
                    'UPDATE auth_rate_limits
                        SET attempts = ?, window_started_at = ?, blocked_until = ?, last_attempt_at = NOW()
                      WHERE id = ?'
                );
                $update->execute([$attempts, $windowStartedAt, $blockedUntil, $row['id']]);
            } else {
                $insert = $this->pdo->prepare(
                    'INSERT INTO auth_rate_limits
                        (action, identifier_hash, attempts, window_started_at, blocked_until, last_attempt_at)
                     VALUES (?, ?, ?, ?, ?, NOW())'
                );
                $insert->execute([$action, $identifierHash, $attempts, $windowStartedAt, $blockedUntil]);
            }

            if ($startedTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($startedTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function clear($action, $identifier) {
        [$action, $identifierHash] = $this->normalize($action, $identifier, 1, 1);
        $stmt = $this->pdo->prepare(
            'DELETE FROM auth_rate_limits WHERE action = ? AND identifier_hash = ?'
        );
        $stmt->execute([$action, $identifierHash]);
    }

    private function normalize($action, $identifier, $maxAttempts, $windowSeconds) {
        $action = strtolower(trim((string) $action));
        $identifier = strtolower(trim((string) $identifier));
        $maxAttempts = max(1, min(100, (int) $maxAttempts));
        $windowSeconds = max(1, min(86400, (int) $windowSeconds));

        if (!preg_match('/^[a-z0-9_.-]{1,50}$/', $action) || $identifier === '') {
            throw new InvalidArgumentException('Identificador de limite inválido.');
        }

        return [$action, hash('sha256', $action . '|' . $identifier), $maxAttempts, $windowSeconds];
    }
}
