<?php

/**
 * Model Base para MySQL
 * Substitui BaseModel (JSON) quando banco estiver disponível
 */
abstract class BaseModelDB {
    
    protected $table;
    protected $primaryKey = 'id';
    protected $pdo;
    
    public function __construct($table, $primaryKey = 'id') {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->pdo = Database::getConnection();
    }
    
    /**
     * Buscar todos os registros
     */
    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }
    
    /**
     * Alias para all() - compatibilidade
     */
    public function findAll() {
        return $this->all();
    }
    
    /**
     * Alias para all() - compatibilidade
     */
    public function getAll() {
        return $this->all();
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Criar novo registro
     */
   // dentro de class BaseModelDB { ... }

private function getTableColumns()
{
    // Retorna array com nomes das colunas da tabela (cache simples por instância)
    static $cache = [];
    $key = $this->table;
    if (isset($cache[$key])) return $cache[$key];

    $stmt = $this->pdo->prepare("SHOW COLUMNS FROM {$this->table}");
    $stmt->execute();
    $cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $cache[$key] = $cols;
    return $cols;
}

public function create($data)
{
    // 1) manter apenas colunas existentes na tabela
    $columns = $this->getTableColumns();
    $data = array_intersect_key($data, array_flip($columns));

    if (empty($data)) {
        throw new Exception("Nenhum dado válido para inserir em {$this->table}");
    }

    // 2) converter strings vazias para NULL para não quebrar colunas DATE/DATETIME/ENUM
    foreach ($data as $k => $v) {
        if ($v === '') $data[$k] = null;
    }

    // 3) montar SQL
    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');

    $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $this->pdo->prepare($sql);

    $ok = $stmt->execute(array_values($data));
    if (!$ok) {
        $err = $stmt->errorInfo();
        throw new Exception("Erro ao inserir em {$this->table}: " . ($err[2] ?? json_encode($err)));
    }

    $id = $this->pdo->lastInsertId();
    return $this->findById($id);
}    
    /**
     * Atualizar registro
     */
    public function update($id, $data) {
        // Filtrar apenas colunas que existem na tabela
        $columns = $this->getTableColumns();
        $data = array_intersect_key($data, array_flip($columns));
        
        if (empty($data)) {
            // Nada para atualizar, retornar registro atual
            return $this->findById($id);
        }
        
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "$field = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " 
                WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        
        return $this->findById($id);
    }
    
    /**
     * Deletar registro
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Buscar por campo
     */
    public function findBy($field, $value) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $field = ?");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar múltiplos por campo
     */
    public function findAllBy($field, $value) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $field = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Contar registros
     */
    public function count($where = null, $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Paginação
     */
    public function paginate($page = 1, $perPage = 10, $where = null, $params = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        $total = $this->count($where, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            // Compatibilidade com código antigo
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Busca avançada
     */
    public function search($query, $fields = []) {
        if (empty($fields)) {
            return [];
        }
        
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "$field LIKE ?";
            $params[] = "%$query%";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' OR ', $conditions);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Executar query customizada
     */
    protected function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
