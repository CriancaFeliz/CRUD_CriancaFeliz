<?php

/**
 * Model para gerenciar Logs do Sistema
 * Acesso exclusivo para Administradores
 */
class LogDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('log', 'id_log');
    }
    
    /**
     * Obter todos os logs com paginação
     */
    public function getAllLogs($page = 1, $perPage = 50) {
        return $this->paginate($page, $perPage, null, [], 'data_alteracao DESC');
    }
    
    /**
     * Buscar logs por tabela afetada
     */
    public function getLogsByTable($table, $page = 1, $perPage = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE tabela_afetada = ? ORDER BY data_alteracao DESC";
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare($sql . " LIMIT $perPage OFFSET $offset");
        $stmt->execute([$table]);
        $data = $stmt->fetchAll();
        
        $total = $this->count('tabela_afetada = ?', [$table]);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
    
    /**
     * Buscar logs por ação (INSERT, UPDATE, DELETE)
     */
    public function getLogsByAction($action, $page = 1, $perPage = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE acao = ? ORDER BY data_alteracao DESC";
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare($sql . " LIMIT $perPage OFFSET $offset");
        $stmt->execute([$action]);
        $data = $stmt->fetchAll();
        
        $total = $this->count('acao = ?', [$action]);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
    
    /**
     * Buscar logs por usuário
     */
    public function getLogsByUser($userId, $page = 1, $perPage = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE id_usuario = ? ORDER BY data_alteracao DESC";
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare($sql . " LIMIT $perPage OFFSET $offset");
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll();
        
        $total = $this->count('id_usuario = ?', [$userId]);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
    
    /**
     * Buscar logs por ID de registro (para rastrear um atendido, ficha, etc)
     */
    public function getLogsByRegistroId($registroId, $page = 1, $perPage = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE id_registro = ? ORDER BY data_alteracao DESC";
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare($sql . " LIMIT $perPage OFFSET $offset");
        $stmt->execute([$registroId]);
        $data = $stmt->fetchAll();
        
        $total = $this->count('id_registro = ?', [$registroId]);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
    
    /**
     * Buscar logs por período (data inicial e final)
     */
    public function getLogsByDateRange($startDate, $endDate, $page = 1, $perPage = 50) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE DATE(data_alteracao) >= ? AND DATE(data_alteracao) <= ? 
                ORDER BY data_alteracao DESC";
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare($sql . " LIMIT $perPage OFFSET $offset");
        $stmt->execute([$startDate, $endDate]);
        $data = $stmt->fetchAll();
        
        $total = $this->count("DATE(data_alteracao) >= ? AND DATE(data_alteracao) <= ?", [$startDate, $endDate]);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
    
    /**
     * Busca avançada com múltiplos filtros
     */
    public function searchAdvanced($filters = [], $page = 1, $perPage = 50) {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['tabela'])) {
            $conditions[] = "tabela_afetada = ?";
            $params[] = $filters['tabela'];
        }
        
        if (!empty($filters['acao'])) {
            $conditions[] = "acao = ?";
            $params[] = $filters['acao'];
        }
        
        if (!empty($filters['usuario_id'])) {
            $conditions[] = "id_usuario = ?";
            $params[] = $filters['usuario_id'];
        }
        
        if (!empty($filters['data_inicio'])) {
            $conditions[] = "DATE(data_alteracao) >= ?";
            $params[] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $conditions[] = "DATE(data_alteracao) <= ?";
            $params[] = $filters['data_fim'];
        }
        
        if (!empty($filters['busca'])) {
            $conditions[] = "(registro_alt LIKE ? OR valor_anterior LIKE ? OR valor_atual LIKE ?)";
            $search = '%' . $filters['busca'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY data_alteracao DESC";
        
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        if (!empty($conditions)) {
            $countSql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $countResult = $countStmt->fetch();
        $total = $countResult['total'];
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
    
    /**
     * Obter estatísticas dos logs
     */
    public function getStatistics() {
        $stats = [];
        
        // Total de logs
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total_logs'] = $stmt->fetch()['total'];
        
        // Logs por ação
        $stmt = $this->pdo->query("SELECT acao, COUNT(*) as total FROM {$this->table} GROUP BY acao");
        $stats['por_acao'] = $stmt->fetchAll();
        
        // Logs por tabela
        $stmt = $this->pdo->query("SELECT tabela_afetada, COUNT(*) as total FROM {$this->table} GROUP BY tabela_afetada ORDER BY total DESC");
        $stats['por_tabela'] = $stmt->fetchAll();
        
        // Usuários mais ativos
        $stmt = $this->pdo->query("
            SELECT l.id_usuario, u.nome, COUNT(*) as total 
            FROM {$this->table} l 
            LEFT JOIN usuario u ON l.id_usuario = u.idusuario 
            WHERE l.id_usuario IS NOT NULL 
            GROUP BY l.id_usuario, u.nome 
            ORDER BY total DESC 
            LIMIT 10
        ");
        $stats['usuarios_ativos'] = $stmt->fetchAll();
        
        // Últimos 7 dias
        $stmt = $this->pdo->query("
            SELECT DATE(data_alteracao) as data, COUNT(*) as total 
            FROM {$this->table} 
            WHERE data_alteracao >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            GROUP BY DATE(data_alteracao) 
            ORDER BY data DESC
        ");
        $stats['ultimos_7_dias'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Obter histórico completo de um atendido
     */
    public function getHistoricoAtendido($idAtendido) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE id_registro = ? OR registro_alt LIKE ?
            ORDER BY data_alteracao DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAtendido, '%' . $idAtendido . '%']);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Exportar logs em formato CSV
     */
    public function exportToCSV($filters = []) {
        $result = $this->searchAdvanced($filters, 1, 10000);
        
        $csv = "ID,Data,Ação,Tabela,Registro,Valor Anterior,Valor Atual,Usuário,IP\n";
        
        foreach ($result['data'] as $log) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,\"%s\"\n",
                $log['id_log'],
                $log['data_alteracao'],
                $log['acao'],
                $log['tabela_afetada'],
                str_replace('"', '""', $log['registro_alt']),
                str_replace('"', '""', substr($log['valor_anterior'], 0, 100)),
                str_replace('"', '""', substr($log['valor_atual'], 0, 100)),
                $log['id_usuario'] ?? 'N/A',
                $log['ip_usuario'] ?? 'N/A'
            );
        }
        
        return $csv;
    }
    
    /**
     * Limpar logs antigos (mais de X dias)
     * Apenas para administrador
     */
    public function deleteOldLogs($days = 90) {
        $sql = "DELETE FROM {$this->table} WHERE data_alteracao < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$days]);
    }
    
    /**
     * Registrar log manualmente
     * Útil para ações que não são capturadas por triggers
     */
    public function logAction($acao, $tabela, $registro_alt, $valor_anterior = null, $valor_atual = null, $idRegistro = null, $usuarioId = null, $ipUsuario = null) {
        $data = [
            'data_alteracao' => date('Y-m-d H:i:s'),
            'acao' => $acao,
            'tabela_afetada' => $tabela,
            'registro_alt' => $registro_alt,
            'valor_anterior' => $valor_anterior,
            'valor_atual' => $valor_atual,
            'id_registro' => $idRegistro,
            'id_usuario' => $usuarioId ?? ($_SESSION['user_id'] ?? null),
            'ip_usuario' => $ipUsuario ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            'campo_alterado' => 'MANUAL'
        ];
        
        return $this->create($data);
    }
    
    /**
     * Sobrescrever método paginate para ordenação padrão por data DESC
     */
    public function paginate($page = 1, $perPage = 50, $where = null, $params = [], $orderBy = 'data_alteracao DESC') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $sql .= " ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        $total = $this->count($where, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }
}
