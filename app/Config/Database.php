<?php

/**
 * Configurações do Banco de Dados
 */
class Database {

    // Instância PDO (singleton)
    private static $pdo = null;
    
    /**
     * Obter conexão PDO (singleton)
     */
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $host = trim((string) (getenv('DB_HOST') ?: ''));
                $port = (int) (getenv('DB_PORT') ?: 3306);
                $dbname = trim((string) (getenv('DB_NAME') ?: ''));
                $username = trim((string) (getenv('DB_USER') ?: ''));
                $password = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '';
                $charset = trim((string) (getenv('DB_CHARSET') ?: 'utf8mb4'));

                if ($host === '' || $dbname === '' || $username === '') {
                    throw new RuntimeException('Banco de dados não configurado. Defina DB_HOST, DB_NAME e DB_USER.');
                }

                if (!preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
                    throw new RuntimeException('DB_HOST inválido.');
                }

                if (!preg_match('/^[A-Za-z0-9_]+$/', $dbname)) {
                    throw new RuntimeException('DB_NAME inválido.');
                }

                if ($port < 1 || $port > 65535) {
                    throw new RuntimeException('DB_PORT inválido.');
                }

                if (!in_array($charset, ['utf8mb4', 'utf8'], true)) {
                    throw new RuntimeException('DB_CHARSET inválido.');
                }

                if (!extension_loaded('pdo_mysql')) {
                    throw new Exception('A extensao pdo_mysql nao esta habilitada neste PHP.');
                }

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ];

                if (defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
                    $options[constant('Pdo\Mysql::ATTR_INIT_COMMAND')] = "SET NAMES utf8mb4";
                } elseif (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                    $options[constant('PDO::MYSQL_ATTR_INIT_COMMAND')] = "SET NAMES utf8mb4";
                }
                
                self::$pdo = new PDO($dsn, $username, $password, $options);
                
            } catch (Throwable $e) {
                debugLog('Falha ao conectar ao banco de dados', [
                    'exception' => get_class($e),
                    'code' => (string) $e->getCode()
                ]);
                throw new RuntimeException('Não foi possível conectar ao banco de dados. Verifique a configuração do ambiente.');
            }
        }
        
        return self::$pdo;
    }
    
    /**
     * Verificar se banco de dados está disponível
     */
    public static function isAvailable() {
        try {
            self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Executar query diretamente
     */
    public static function query($sql, $params = []) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            debugLog('Falha ao executar consulta no banco de dados', [
                'code' => (string) $e->getCode()
            ]);
            throw new RuntimeException('Não foi possível concluir a operação no banco de dados.');
        }
    }
    
    /**
     * Iniciar transação
     */
    public static function beginTransaction() {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transação
     */
    public static function commit() {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transação
     */
    public static function rollback() {
        return self::getConnection()->rollBack();
    }
    
    /**
     * Obter último ID inserido
     */
    public static function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Definir usuário logado para triggers
     */
    public static function setLoggedUser($userId) {
        try {
            self::query("SET @usuario_id = ?", [$userId]);
        } catch (Exception $e) {
            debugLog('Falha ao preparar o contexto de auditoria', [
                'exception' => get_class($e)
            ]);
        }
    }
}
