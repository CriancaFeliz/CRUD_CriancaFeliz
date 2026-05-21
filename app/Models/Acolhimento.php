<?php

/**
 * Model para fichas de acolhimento - MYSQL
 */
class Acolhimento extends BaseModelDB {
    
    /**
     * Executa uma consulta SQL personalizada
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erro na consulta SQL: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function __construct() {
        parent::__construct('Atendido', 'idatendido');
    }
    
    /**
     * Cria nova ficha de acolhimento com validação
     */
    public function createFicha($data) {
        try {
            Database::beginTransaction();
            
            // Normalizar dados
            $data = $this->normalizeData($data);
            
            // 1. Criar/Buscar Responsável
            $responsavelId = $this->createOrGetResponsavel($data);
            
            // 2. Criar Atendido
            $atendidoData = [
                'nome' => $data['nome_completo'],
                'cpf' => $data['cpf'],
                'rg' => $data['rg'],
                'data_nascimento' => $this->convertDate($data['data_nascimento']),
                'data_cadastro' => date('Y-m-d'),
                'endereco' => $data['endereco'],
                'numero' => $data['numero'],
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'],
                'cidade' => $data['cidade'],
                'cep' => $data['cep'],
                'foto' => $data['foto'] ?? null,
                'status' => 'Ativo',
                'id_responsavel' => $responsavelId,
                'faixa_etaria' => $this->calculateAge($data['data_nascimento'])
            ];
            
            $atendido = $this->create($atendidoData);
            $atendidoId = $atendido['idatendido'];
            
            // 3. Criar Ficha de Acolhimento
            $fichaData = [
                'id_atendido' => $atendidoId,
                'data_acolhimento' => $this->convertDate($data['data_acolhimento']),
                'encaminha_por' => $data['encaminha_por'] ?? null,
                'queixa_principal' => $data['queixa_principal'],
                'escola' => $data['escola'] ?? null,
                'periodo' => $data['periodo'] ?? null,
                'ponto_referencia' => $data['ponto_referencia'] ?? null,
                'cras' => $data['cras'] ?? null,
                'ubs' => $data['ubs'] ?? null,
                'cad_unico' => $data['cad_unico'] ?? null,
                'acolhimento_responsavel' => $data['acolhimento_responsavel'] ?? null,
                'acolhimento_funcao' => $data['acolhimento_funcao'] ?? null,
                'carimbo' => $data['carimbo'] ?? null
            ];
            
            $this->query(
                "INSERT INTO Ficha_Acolhimento (id_atendido, data_acolhimento, encaminha_por, queixa_principal, escola, periodo, ponto_referencia, cras, ubs, cad_unico, acolhimento_responsavel, acolhimento_funcao, carimbo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array_values($fichaData)
            );
            
            Database::commit();
            
            return $this->getFicha($atendidoId);
            
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Atualiza ficha de acolhimento
     */
    public function updateFicha($id, $data) {
        $ficha = $this->findById($id);
        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }
        
        // Normalizar dados
        $data = $this->normalizeData($data);
        
        // Verificar se CPF já existe (excluindo o próprio registro)
        if (isset($data['cpf']) && $this->cpfExists($data['cpf'], $id)) {
            throw new Exception('CPF já cadastrado no sistema');
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Normaliza dados da ficha
     */
    private function normalizeData($data) {
        // Normalizar CPF (apenas números)
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D+/', '', $data['cpf']);
        }
        
        if (isset($data['cpf_responsavel'])) {
            $data['cpf_responsavel'] = preg_replace('/\D+/', '', $data['cpf_responsavel']);
        }
        
        // Normalizar RG (apenas números)
        if (isset($data['rg'])) {
            $data['rg'] = preg_replace('/\D+/', '', $data['rg']);
        }
        
        if (isset($data['rg_responsavel'])) {
            $data['rg_responsavel'] = preg_replace('/\D+/', '', $data['rg_responsavel']);
        }
        
        // Normalizar CEP (apenas números)
        if (isset($data['cep'])) {
            $data['cep'] = preg_replace('/\D+/', '', $data['cep']);
        }
        
        // Normalizar telefones (apenas números)
        if (isset($data['contato_1'])) {
            $data['contato_1'] = preg_replace('/\D+/', '', $data['contato_1']);
        }
        
        return $data;
    }
    
    /**
     * Verifica se CPF já existe
     */
    public function cpfExists($cpf, $excludeId = null) {
        $cpf = preg_replace('/\D+/', '', $cpf);
        
        foreach ($this->data as $record) {
            if ($record['cpf'] === $cpf && $record['id'] !== $excludeId) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Busca por CPF
     */
    public function findByCpf($cpf) {
    try {
        // Remove formatação do CPF informado
        $cpfBusca = preg_replace('/\D+/', '', $cpf);
        
        if (empty($cpfBusca)) {
            error_log("CPF vazio ou inválido fornecido: " . $cpf);
            return null;
        }
        
        // Primeiro tenta encontrar pelo CPF exato (com ou sem formatação)
        $sql = "SELECT * FROM Atendido WHERE 
                cpf = ? OR 
                cpf = ? OR 
                cpf = ? OR
                cpf = ? OR
                REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?";
        
        $cpfFormatado1 = substr($cpfBusca, 0, 3) . '.' . 
                        substr($cpfBusca, 3, 3) . '.' . 
                        substr($cpfBusca, 6, 3) . '-' . 
                        substr($cpfBusca, 9);
                        
        $cpfFormatado2 = substr($cpfBusca, 0, 3) . '.' . 
                        substr($cpfBusca, 3, 3) . '.' . 
                        substr($cpfBusca, 6, 3) . '/' . 
                        substr($cpfBusca, 9);
                        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $cpfBusca, 
            $cpfFormatado1,
            $cpfFormatado2,
            str_pad($cpfBusca, 14, '0', STR_PAD_LEFT),
            $cpfBusca
        ]);
        
        $atendido = $stmt->fetch();
        
        if ($atendido) {
            return $atendido;
        }
        
        // Se não encontrou, tenta buscar por similaridade
        $sql = "SELECT * FROM Atendido WHERE 
                REPLACE(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', ''), ' ', '') LIKE ? 
                LIMIT 1";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $cpfBusca . '%']);
        
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Erro ao buscar por CPF: " . $e->getMessage());
        return null;
    }
}
    
    /**
     * Busca avançada
     */
    public function searchAdvanced($query) {
        $searchFields = [
            'nome_completo',
            'cpf',
            'rg',
            'nome_responsavel',
            'cpf_responsavel',
            'endereco',
            'bairro',
            'cidade'
        ];
        
        return $this->search($query, $searchFields);
    }
    
    /**
     * Calcula idade baseada na data de nascimento
     */
    /**
     * Busca todos os pacientes com status 'Atendido'
     */
    public function getAtendidos() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE status = 'Atendido'";
            error_log("SQL: " . $sql); // Log da consulta SQL
            
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetchAll();
            
            error_log("Número de registros encontrados: " . count($result)); // Log do número de registros
            if (count($result) === 0) {
                error_log("Nenhum registro encontrado com status 'Atendido'"); // Log se não encontrar registros
                // Vamos verificar se existem registros na tabela
                $countAll = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
                error_log("Total de registros na tabela: " . $countAll);
                
                // Verificar valores de status existentes
                $this->getStatusValues();
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao buscar atendidos: " . $e->getMessage()); // Log de erro
            return [];
        }
    }
    
    /**
     * Obtém todos os valores únicos de status da tabela
     */
    public function getStatusValues() {
        try {
            $sql = "SELECT DISTINCT status FROM {$this->table}";
            $stmt = $this->pdo->query($sql);
            $statusValues = $stmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Valores de status encontrados na tabela: " . implode(", ", $statusValues));
            return $statusValues;
        } catch (PDOException $e) {
            error_log("Erro ao buscar valores de status: " . $e->getMessage());
            return [];
        }
    }
    
    public function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) {
            return null;
        }
        
        // Converter formato brasileiro para DateTime
        $parts = explode('/', $dataNascimento);
        if (count($parts) === 3) {
            $date = DateTime::createFromFormat('d/m/Y', $dataNascimento);
            if ($date) {
                $now = new DateTime();
                return $now->diff($date)->y;
            }
        }
        
        return null;
    }
    
    /**
     * Categoriza por idade
     */
    public function categorizeByAge($age) {
        if ($age === null) return 'Indefinido';
        if ($age < 12) return 'Criança';
        if ($age < 18) return 'Adolescente';
        return 'Adulto';
    }
    
    /**
     * Estatísticas das fichas
     */
    public function getStatistics() {
        $total = count($this->data);
        $ativas = count($this->findBy('status', 'Ativo'));
        $inativas = $total - $ativas;
        
        $categorias = [
            'Criança' => 0,
            'Adolescente' => 0,
            'Adulto' => 0,
            'Indefinido' => 0
        ];
        
        foreach ($this->data as $record) {
            $age = $this->calculateAge($record['data_nascimento'] ?? '');
            $categoria = $this->categorizeByAge($age);
            $categorias[$categoria]++;
        }
        
        return [
            'total' => $total,
            'ativas' => $ativas,
            'inativas' => $inativas,
            'categorias' => $categorias
        ];
    }
}