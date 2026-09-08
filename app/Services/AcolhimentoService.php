<?php

/**
 * Service para lógica de negócio das fichas de acolhimento
 */
class AcolhimentoService {
    private $acolhimentoModel;
    
    public function __construct() {
        $this->acolhimentoModel = new Acolhimento();
    }
    
    /**
     * Lista todas as fichas com paginação
     */
    public function listFichas($page = 1, $perPage = 10, $filters = []) {
        // Usar a listagem específica que já retorna os campos mapeados (id, nome_completo, etc.)
        if (method_exists($this->acolhimentoModel, 'listFichas')) {
            return $this->acolhimentoModel->listFichas($page, $perPage, $filters);
        }
        // Fallback: paginate (menos ideal pois não mapeia campos), mantido por segurança
        return $this->acolhimentoModel->paginate($page, $perPage);
    }
    
    /**
     * Busca ficha por ID
     */
    public function getFicha($id) {
        // Usar método especializado que já retorna campos mapeados (id, nome_completo, etc.) e datas formatadas
        if (method_exists($this->acolhimentoModel, 'getFicha')) {
            $ficha = $this->acolhimentoModel->getFicha($id);
        } else {
            $ficha = $this->acolhimentoModel->findById($id);
        }

        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }

        // Adicionar dados calculados (idempotente)
        $ficha['idade'] = $this->acolhimentoModel->calculateAge($ficha['data_nascimento'] ?? '');
        $ficha['categoria'] = $this->acolhimentoModel->categorizeByAge($ficha['idade']);

        return $ficha;
    }
    
    /**
     * Cria nova ficha
     */
    public function createFicha($data) {
        // Sanitizar dados
        $data = sanitizeInput($data);
        
        // Validações específicas
        $this->validateFichaData($data);
        
        // Criar ficha
        $ficha = $this->acolhimentoModel->createFicha($data);
        
        // Log da ação
        $this->logAction('create', $ficha['id'], 'Ficha de acolhimento criada');
        
        return $ficha;
    }
    
    /**
     * Atualiza ficha existente
     */
    public function updateFicha($id, $data) {
        // Sanitizar dados
        $data = sanitizeInput($data);
        
        // Validações específicas
        $this->validateFichaData($data, $id);
        
        // Atualizar ficha
        $ficha = $this->acolhimentoModel->updateFicha($id, $data);
        
        // Log da ação
        $this->logAction('update', $id, 'Ficha de acolhimento atualizada');
        
        return $ficha;
    }
    
    /**
     * Exclui ficha
     */
    public function deleteFicha($id) {
        $ficha = $this->acolhimentoModel->findById($id);
        
        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }
        
        $result = $this->acolhimentoModel->delete($id);
        
        if ($result) {
            // Log da ação
            $this->logAction('delete', $id, 'Ficha de acolhimento excluída');
        }
        
        return $result;
    }
    
    /**
     * Busca avançada
     */
    public function searchFichas($query, $filters = []) {
        // Usar searchAdvanced que aceita filtros
        if (method_exists($this->acolhimentoModel, 'searchAdvanced')) {
            return $this->acolhimentoModel->searchAdvanced($query, $filters);
        }
        
        // Fallback: busca simples
        if (method_exists($this->acolhimentoModel, 'searchByName')) {
            $results = $this->acolhimentoModel->searchByName($query);
        } else {
            // Último fallback: filtrar em memória
            $all = $this->acolhimentoModel->findAll();
            $results = array_filter($all, function($r) use ($query) {
                return stripos($r['nome_completo'] ?? $r['nome'] ?? '', $query) !== false;
            });
        }
        
        // Aplicar filtros adicionais se houver
        if (!empty($filters)) {
            $results = $this->applyFilters($results, $filters);
        }
        
        return $results;
    }

    
    /**
     * Aplica filtros aos resultados
     */
    private function applyFilters($results, $filters) {
        return array_filter($results, function($ficha) use ($filters) {
            foreach ($filters as $field => $value) {
                if (!empty($value) && isset($ficha[$field])) {
                    if (stripos($ficha[$field], $value) === false) {
                        return false;
                    }
                }
            }
            return true;
        });
    }
    
    /**
     * Valida dados da ficha
     */
    private function validateFichaData($data, $excludeId = null) {
        // Validar Nome Completo (proibir números, emojis e caracteres especiais para segurança)
        if (!isset($data['nome_completo']) || trim($data['nome_completo']) === '') {
            throw new Exception('Nome completo é obrigatório');
        }

        $nomeCompleto = trim($data['nome_completo']);
        if (mb_strlen($nomeCompleto, 'UTF-8') < 3) {
            throw new Exception('Nome completo deve ter pelo menos 3 caracteres');
        }

        if (preg_match('/[0-9]/', $nomeCompleto)) {
            throw new Exception('O nome completo não pode conter números');
        }

        if (!preg_match('/^[\p{L}\s]+$/u', $nomeCompleto)) {
            throw new Exception('O nome completo deve conter apenas letras e espaços (sem números, emojis ou caracteres especiais)');
        }

        // Validar Encaminhado por (proibir números, emojis e caracteres especiais para segurança)
        if (!empty($data['encaminha_por']) && trim($data['encaminha_por']) !== '') {
            $encaminhaPor = trim($data['encaminha_por']);
            if (preg_match('/[0-9]/', $encaminhaPor)) {
                throw new Exception('O campo Encaminhado por não pode conter números');
            }

            if (!preg_match('/^[\p{L}\s]+$/u', $encaminhaPor)) {
                throw new Exception('O campo Encaminhado por deve conter apenas letras e espaços (sem números, emojis ou caracteres especiais)');
            }
        }

        // Validar CPF
        if (!empty($data['cpf'])) {
            $cpf = preg_replace('/\D+/', '', $data['cpf']);
            if (!$this->isValidCPF($cpf)) {
                throw new Exception('CPF inválido');
            }
        }
        
        // Validar CPF do responsável
        if (!empty($data['cpf_responsavel'])) {
            $cpf = preg_replace('/\D+/', '', $data['cpf_responsavel']);
            if (!$this->isValidCPF($cpf)) {
                throw new Exception('CPF do responsável inválido');
            }
        }
        
        // Validar datas
        if (!empty($data['data_nascimento'])) {
            if (!$this->isValidDate($data['data_nascimento'])) {
                throw new Exception('Data de nascimento inválida');
            }
        }
        
        if (!empty($data['data_acolhimento'])) {
            if (!$this->isValidDate($data['data_acolhimento'])) {
                throw new Exception('Data de acolhimento inválida');
            }
        }
        
        // Validar CEP
        if (!empty($data['cep'])) {
            $cep = preg_replace('/\D+/', '', $data['cep']);
            if (strlen($cep) !== 8) {
                throw new Exception('CEP deve ter 8 dígitos');
            }
        }
        
        // Validar telefone
        if (!empty($data['contato_1'])) {
            $telefone = preg_replace('/\D+/', '', $data['contato_1']);
            if (strlen($telefone) < 10 || strlen($telefone) > 11) {
                throw new Exception('Telefone deve ter 10 ou 11 dígitos');
            }
        }
    }
    
    /**
     * Valida CPF
     */
    private function isValidCPF($cpf) {
        $cpf = preg_replace('/\D+/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Verificar sequências iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        return true; // Validação simplificada
    }
    
    /**
     * Valida data no formato dd/mm/aaaa
     */
    private function isValidDate($date) {
        $parts = explode('/', $date);
        if (count($parts) !== 3) {
            return false;
        }
        
        $day = intval($parts[0]);
        $month = intval($parts[1]);
        $year = intval($parts[2]);
        
        return checkdate($month, $day, $year);
    }
    
    /**
     * Obtém estatísticas
     */
    public function getStatistics() {
        return $this->acolhimentoModel->getStatistics();
    }
    
    /**
     * Exporta fichas para CSV
     */
    public function exportToCSV($filters = []) {
        $fichas = $this->acolhimentoModel->findAll();
        
        if (!empty($filters)) {
            $fichas = $this->applyFilters($fichas, $filters);
        }
        
        $csv = "Nome,CPF,RG,Data Nascimento,Idade,Categoria,Responsável,Contato,Status\n";
        
        foreach ($fichas as $ficha) {
            $idade = $this->acolhimentoModel->calculateAge($ficha['data_nascimento'] ?? '');
            $categoria = $this->acolhimentoModel->categorizeByAge($idade);
            
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $ficha['nome_completo'] ?? '',
                $ficha['cpf'] ?? '',
                $ficha['rg'] ?? '',
                $ficha['data_nascimento'] ?? '',
                $idade ?? '',
                $categoria,
                $ficha['nome_responsavel'] ?? '',
                $ficha['contato_1'] ?? '',
                $ficha['status'] ?? ''
            );
        }
        
        return $csv;
    }
    
    /**
     * Log de ações
     */
    private function logAction($action, $fichaId, $description) {
        require_once APP_PATH . '/Models/Log.php';
        $logModel = new Log();
        
        $acaoBd = 'UPDATE';
        if ($action === 'create') $acaoBd = 'INSERT';
        if ($action === 'delete') $acaoBd = 'DELETE';
        
        $logModel->logAction(
            $acaoBd,
            'ficha_acolhimento',
            "Ficha ID: $fichaId - $description",
            null,
            null,
            $fichaId
        );
    }
    
    /**
     * Obtém logs de ações
     */
    public function getLogs($limit = 50) {
        require_once APP_PATH . '/Models/Log.php';
        $logModel = new Log();
        $result = $logModel->getLogsByTable('ficha_acolhimento', 1, $limit);
        return $result['data'] ?? [];
    }
}
