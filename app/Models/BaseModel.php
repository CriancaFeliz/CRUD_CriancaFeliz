<?php

/**
 * Classe base para todos os Models
 * Fornece funcionalidades básicas de CRUD com arquivos JSON
 */
class BaseModel {
    protected $dataFile;
    protected $data = [];
    
    public function __construct($dataFile) {
        $this->dataFile = DATA_PATH . '/' . $dataFile;
        $this->loadData();
    }
    
    /**
     * Carrega dados do arquivo JSON
     */
    protected function loadData() {
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
        
        $json = file_get_contents($this->dataFile);
        $this->data = json_decode($json, true) ?: [];
    }
    
    /**
     * Salva dados no arquivo JSON
     */
    protected function saveData() {
        file_put_contents(
            $this->dataFile, 
            json_encode(array_values($this->data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
    
    /**
     * Busca todos os registros
     */
    public function findAll() {
        return $this->data;
    }
    
    /**
     * Alias para findAll() - busca todos os registros
     */
    public function getAll() {
        return $this->data;
    }
    
    /**
     * Busca registro por ID
     */
    public function findById($id) {
        foreach ($this->data as $record) {
            if ($record['id'] === $id) {
                return $record;
            }
        }
        return null;
    }
    
    /**
     * Busca registros por critério
     */
    public function findBy($field, $value) {
        $results = [];
        foreach ($this->data as $record) {
            if (isset($record[$field]) && $record[$field] === $value) {
                $results[] = $record;
            }
        }
        return $results;
    }
    
    /**
     * Busca registros com filtros múltiplos
     */
    public function findWhere($criteria) {
        $results = [];
        foreach ($this->data as $record) {
            $match = true;
            foreach ($criteria as $field => $value) {
                if (!isset($record[$field]) || $record[$field] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $record;
            }
        }
        return $results;
    }
    
    /**
     * Cria novo registro
     */
    public function create($data) {
        $data['id'] = $this->generateId();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->data[] = $data;
        $this->saveData();
        
        return $data;
    }
    
    /**
     * Atualiza registro existente
     */
    public function update($id, $data) {
        foreach ($this->data as $key => $record) {
            if ($record['id'] === $id) {
                $data['id'] = $id;
                $data['created_at'] = $record['created_at'] ?? date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                
                $this->data[$key] = $data;
                $this->saveData();
                
                return $data;
            }
        }
        return null;
    }
    
    /**
     * Exclui registro
     */
    public function delete($id) {
        foreach ($this->data as $key => $record) {
            if ($record['id'] === $id) {
                unset($this->data[$key]);
                $this->saveData();
                return true;
            }
        }
        return false;
    }
    
    /**
     * Conta total de registros
     */
    public function count() {
        return count($this->data);
    }
    
    /**
     * Busca com paginação
     */
    public function paginate($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $items = array_slice($this->data, $offset, $perPage);
        
        return [
            'data' => $items,
            'total' => count($this->data),
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil(count($this->data) / $perPage)
        ];
    }
    
    /**
     * Gera ID único
     */
    protected function generateId() {
        return uniqid('', true);
    }
    
    /**
     * Busca por texto em múltiplos campos
     */
    public function search($query, $fields = []) {
        $results = [];
        $query = strtolower($query);
        
        foreach ($this->data as $record) {
            foreach ($fields as $field) {
                if (isset($record[$field])) {
                    $value = strtolower($record[$field]);
                    if (strpos($value, $query) !== false) {
                        $results[] = $record;
                        break;
                    }
                }
            }
        }
        
        return $results;
    }
}
