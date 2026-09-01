<?php

/**
 * Helper para preparar variáveis de sessão MySQL para logs
 * Deve ser chamado antes de qualquer operação que gere logs
 */
class LogHelper {
    
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
            
            $ipQuoted = $pdo->quote($ipUsuario);
            $pdo->exec("SET @ip_usuario = " . $ipQuoted);
            
        } catch (Exception $e) {
            // Log silencioso - não interrompe a execução
            error_log("LogHelper: Erro ao preparar variáveis de log: " . $e->getMessage());
        }
    }
}
