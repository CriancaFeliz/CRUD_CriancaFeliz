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
                $host = trim((string) ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost'));
                $port = (int) ($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
                $dbname = trim((string) ($_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? $_SERVER['DB_NAME'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: '')));
                $username = trim((string) ($_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USER'] ?? $_SERVER['DB_USERNAME'] ?? getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: '')));
                $password = isset($_ENV['DB_PASS']) ? (string)$_ENV['DB_PASS'] : (isset($_ENV['DB_PASSWORD']) ? (string)$_ENV['DB_PASSWORD'] : (isset($_SERVER['DB_PASS']) ? (string)$_SERVER['DB_PASS'] : (isset($_SERVER['DB_PASSWORD']) ? (string)$_SERVER['DB_PASSWORD'] : (getenv('DB_PASS') !== false ? (string)getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? (string)getenv('DB_PASSWORD') : '')))));
                $charset = trim((string) ($_ENV['DB_CHARSET'] ?? $_SERVER['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4'));

                if ($dbname === '' || $username === '') {
                    throw new RuntimeException('Banco de dados não configurado no .env. Defina DB_HOST, DB_NAME (ou DB_DATABASE) e DB_USER (ou DB_USERNAME).');
                }

                if (!preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
                    throw new RuntimeException("DB_HOST inválido: '{$host}'");
                }

                if ($port < 1 || $port > 65535) {
                    throw new RuntimeException("DB_PORT inválido: {$port}");
                }

                if (!extension_loaded('pdo_mysql')) {
                    throw new Exception('A extensão PHP pdo_mysql não está habilitada neste servidor.');
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
                    'message' => $e->getMessage(),
                    'code' => (string) $e->getCode()
                ]);
                throw new RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
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
                'message' => $e->getMessage(),
                'code' => (string) $e->getCode()
            ]);
            throw new RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
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
