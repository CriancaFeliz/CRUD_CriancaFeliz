<?php

/**
 * Helper para preparar variáveis de sessão MySQL para logs
 * Deve ser chamado antes de qualquer operação que gere logs
 */
class LogHelper {
    private const MASKED_VALUE = '[valor sensivel mascarado]';

    private const SENSITIVE_FIELD_PATTERN = '/(senha|password|token|secret|segredo|api[_-]?key|apikey|authorization|cookie|session|csrf|credencial|credential|hash)/i';
    
    /**
     * Preparar variáveis de sessão MySQL para capturar ID do usuário nos triggers
     */
    public static function prepareLogVariables() {
        try {
            $pdo = Database::getConnection();
            
            // Obter ID do usuário da sessão
            $userId = $_SESSION['user_id'] ?? null;
            $ipUsuario = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            
            // Definir variáveis de sessão MySQL para os triggers
            if ($userId) {
                $pdo->exec("SET @usuario_id = " . intval($userId));
            } else {
                $pdo->exec("SET @usuario_id = NULL");
            }

            $stmt = $pdo->prepare("SET @ip_usuario = ?");
            $stmt->execute([$ipUsuario]);
            
        } catch (Exception $e) {
            // Log silencioso - não interrompe a execução
            error_log("LogHelper: Erro ao preparar variáveis de log: " . $e->getMessage());
        }
    }

    public static function isSensitiveField($field) {
        return is_string($field) && preg_match(self::SENSITIVE_FIELD_PATTERN, $field) === 1;
    }

    public static function maskSensitiveValue($value, $field = '') {
        if ($value === null || $value === '') {
            return $value;
        }

        if (self::isSensitiveField((string) $field)) {
            return self::MASKED_VALUE;
        }

        if (is_array($value)) {
            return self::maskSensitiveArray($value);
        }

        $text = (string) $value;
        $trimmed = trim($text);

        if ($trimmed !== '' && in_array($trimmed[0], ['{', '['], true)) {
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode(
                    self::maskSensitiveArray($decoded),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        return self::maskSensitiveText($text);
    }

    public static function maskLogRow(array $log) {
        $masked = $log;
        $field = $log['campo_alterado'] ?? '';

        foreach (['registro_alt', 'valor_anterior', 'valor_atual'] as $key) {
            if (array_key_exists($key, $masked)) {
                $masked[$key] = self::maskSensitiveValue($masked[$key], $field);
            }
        }

        return self::maskSensitiveArray($masked);
    }

    public static function neutralizeSpreadsheetFormula($value) {
        $text = (string) ($value ?? '');

        if (preg_match('/^\s*[=+\-@]/', $text) === 1) {
            return "'" . $text;
        }

        return $text;
    }

    private static function maskSensitiveArray(array $data) {
        $masked = [];

        foreach ($data as $key => $value) {
            if (self::isSensitiveField((string) $key)) {
                $masked[$key] = self::MASKED_VALUE;
                continue;
            }

            $masked[$key] = is_array($value)
                ? self::maskSensitiveArray($value)
                : self::maskSensitiveValue($value);
        }

        return $masked;
    }

    private static function maskSensitiveText($text) {
        return preg_replace_callback(
            '/((?:senha|password|token|secret|segredo|api[_-]?key|apikey|authorization|cookie|session|csrf|credencial|credential|hash)\s*[:=]\s*)(["\']?)([^,;}\]\s"\']+)(["\']?)/i',
            function ($matches) {
                return $matches[1] . $matches[2] . self::MASKED_VALUE . $matches[4];
            },
            $text
        );
    }
}
