<?php

/**
 * Service para lógica de negócio das fichas socioeconômicas
 */
class SocioeconomicoService {
    private $socioeconomicoModel;
    
    public function __construct() {
        $this->socioeconomicoModel = App::getSocioeconomicoModel();
    }
    
    /**
     * Lista todas as fichas com paginação
     */
    public function listFichas($page = 1, $perPage = 10) {
        // Chamar o método específico do SocioeconomicoDB
        if (method_exists($this->socioeconomicoModel, 'listFichas')) {
            return $this->socioeconomicoModel->listFichas($page, $perPage);
        }
        // Fallback para método genérico
        return $this->socioeconomicoModel->paginate($page, $perPage);
    }
    
    /**
     * Busca ficha por ID
     */
    public function getFicha($id) {
        // Usar método getFicha do model que já faz JOIN
        if (method_exists($this->socioeconomicoModel, 'getFicha')) {
            $ficha = $this->socioeconomicoModel->getFicha($id);
        } else {
            $ficha = $this->socioeconomicoModel->findById($id);
        }
        
        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }
        
        // Adicionar dados calculados
        if (!isset($ficha['idade'])) {
            $ficha['idade'] = $this->socioeconomicoModel->calculateAge($ficha['data_nascimento'] ?? '');
        }
        
        if (!isset($ficha['renda_familiar'])) {
            $ficha['renda_familiar'] = floatval($ficha['renda_familiar'] ?? 0);
        }
        
        if (!isset($ficha['numero_membros'])) {
            $ficha['numero_membros'] = intval($ficha['qtd_pessoas'] ?? 1);
        }
        
        if (!isset($ficha['situacao_economica'])) {
            $ficha['situacao_economica'] = $this->categorizeSituacao(
                $ficha['renda_familiar'], 
                $ficha['numero_membros']
            );
        }
        
        return $ficha;
    }
    
    /**
     * Categoriza situação econômica
     */
    private function categorizeSituacao($rendaFamiliar, $numeroMembros = 1) {
        $rendaPerCapita = $rendaFamiliar / max($numeroMembros, 1);
        $salarioMinimo = 1320;
        
        if ($rendaPerCapita < $salarioMinimo * 0.5) {
            return 'Extrema Pobreza';
        } elseif ($rendaPerCapita < $salarioMinimo) {
            return 'Pobreza';
        } elseif ($rendaPerCapita < $salarioMinimo * 3) {
            return 'Baixa Renda';
        } elseif ($rendaPerCapita < $salarioMinimo * 6) {
            return 'Média Renda';
        } else {
            return 'Alta Renda';
        }
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
        $ficha = $this->socioeconomicoModel->createFicha($data);
        
        // Log da ação
        $this->logAction('create', $ficha['id'], 'Ficha socioeconômica criada');
        
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
        $ficha = $this->socioeconomicoModel->updateFicha($id, $data);
        
        // Log da ação
        $this->logAction('update', $id, 'Ficha socioeconômica atualizada');
        
        return $ficha;
    }
    
    /**
     * Exclui ficha
     */
    public function deleteFicha($id) {
        $ficha = $this->socioeconomicoModel->findById($id);
        
        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }
        
        $result = $this->socioeconomicoModel->delete($id);
        
        if ($result) {
            // Log da ação
            $this->logAction('delete', $id, 'Ficha socioeconômica excluída');
        }
        
        return $result;
    }
    
    /**
     * Busca avançada
     */

    
    public function searchFichas($query, $filters = []) {
    $results = $this->socioeconomicoModel->searchByName($query);
        
        // Aplicar filtros adicionais
        if (!empty($filters)) {
            $results = $this->applyFilters($results, $filters);
        }
        
        // Adicionar dados calculados
        foreach ($results as &$ficha) {
            $ficha['idade'] = $this->socioeconomicoModel->calculateAge($ficha['data_nascimento'] ?? '');
            $ficha['renda_familiar'] = $this->socioeconomicoModel->calculateRendaFamiliar($ficha);
            $ficha['situacao_economica'] = $this->socioeconomicoModel->categorizeSituacao(
                $ficha['renda_familiar'], 
                intval($ficha['numero_membros'] ?? 1)
            );
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
        // Validar CPF
        if (!empty($data['cpf'])) {
            $cpf = preg_replace('/\D+/', '', $data['cpf']);
            if (!$this->isValidCPF($cpf)) {
                throw new Exception('CPF inválido');
            }
        }
        
        // Validar datas
        if (!empty($data['data_nascimento'])) {
            if (!$this->isValidDate($data['data_nascimento'])) {
                throw new Exception('Data de nascimento inválida');
            }
        }
        
        // Validar CEP
        if (!empty($data['cep'])) {
            $cep = preg_replace('/\D+/', '', $data['cep']);
            if (strlen($cep) !== 8) {
                throw new Exception('CEP deve ter 8 dígitos');
            }
        }
        
        // Validar telefones
        $telefoneFields = ['telefone', 'celular', 'contato_emergencia'];
        foreach ($telefoneFields as $field) {
            if (!empty($data[$field])) {
                $telefone = preg_replace('/\D+/', '', $data[$field]);
                if (strlen($telefone) < 10 || strlen($telefone) > 11) {
                    throw new Exception("$field deve ter 10 ou 11 dígitos");
                }
            }
        }
        
        // Validar valores monetários
        $rendaFields = [];
        for ($i = 1; $i <= 10; $i++) {
            $rendaFields[] = "renda_membro_$i";
        }
        
        foreach ($rendaFields as $field) {
            if (!empty($data[$field])) {
                $valor = str_replace(['.', ','], ['', '.'], $data[$field]);
                if (!is_numeric($valor) || floatval($valor) < 0) {
                    throw new Exception("$field deve ser um valor monetário válido");
                }
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
        return $this->socioeconomicoModel->getStatistics();
    }
    
    /**
     * Gera relatório socioeconômico
     */
    public function generateReport($filters = []) {
        $fichas = $this->socioeconomicoModel->findAll();
        
        if (!empty($filters)) {
            $fichas = $this->applyFilters($fichas, $filters);
        }
        
        $report = [
            'total_fichas' => count($fichas),
            'situacoes' => [
                'Extrema Pobreza' => 0,
                'Pobreza' => 0,
                'Baixa Renda' => 0,
                'Média Renda' => 0,
                'Alta Renda' => 0
            ],
            'faixa_etaria' => [
                'Criança (0-11)' => 0,
                'Adolescente (12-17)' => 0,
                'Adulto (18+)' => 0
            ],
            'renda_media' => 0,
            'membros_media' => 0
        ];
        
        $rendaTotal = 0;
        $membrosTotal = 0;
        $contadorRenda = 0;
        
        foreach ($fichas as $ficha) {
            // Situação econômica
            $rendaFamiliar = $this->socioeconomicoModel->calculateRendaFamiliar($ficha);
            $numeroMembros = intval($ficha['numero_membros'] ?? 1);
            $situacao = $this->socioeconomicoModel->categorizeSituacao($rendaFamiliar, $numeroMembros);
            
            if (isset($report['situacoes'][$situacao])) {
                $report['situacoes'][$situacao]++;
            }
            
            // Faixa etária
            $idade = $this->socioeconomicoModel->calculateAge($ficha['data_nascimento'] ?? '');
            if ($idade !== null) {
                if ($idade < 12) {
                    $report['faixa_etaria']['Criança (0-11)']++;
                } elseif ($idade < 18) {
                    $report['faixa_etaria']['Adolescente (12-17)']++;
                } else {
                    $report['faixa_etaria']['Adulto (18+)']++;
                }
            }
            
            // Médias
            if ($rendaFamiliar > 0) {
                $rendaTotal += $rendaFamiliar;
                $contadorRenda++;
            }
            
            $membrosTotal += $numeroMembros;
        }
        
        if ($contadorRenda > 0) {
            $report['renda_media'] = $rendaTotal / $contadorRenda;
        }
        
        if (count($fichas) > 0) {
            $report['membros_media'] = $membrosTotal / count($fichas);
        }
        
        return $report;
    }
    
    /**
     * Exporta fichas para CSV
     */
    public function exportToCSV($filters = []) {
        $fichas = $this->socioeconomicoModel->findAll();
        
        if (!empty($filters)) {
            $fichas = $this->applyFilters($fichas, $filters);
        }
        
        $csv = "Nome,CPF,RG,Data Nascimento,Idade,Renda Familiar,Situação Econômica,Membros Família,Status\n";
        
        foreach ($fichas as $ficha) {
            $idade = $this->socioeconomicoModel->calculateAge($ficha['data_nascimento'] ?? '');
            $rendaFamiliar = $this->socioeconomicoModel->calculateRendaFamiliar($ficha);
            $situacao = $this->socioeconomicoModel->categorizeSituacao(
                $rendaFamiliar, 
                intval($ficha['numero_membros'] ?? 1)
            );
            
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%.2f","%s","%s","%s"' . "\n",
                $ficha['nome_completo'] ?? '',
                $ficha['cpf'] ?? '',
                $ficha['rg'] ?? '',
                $ficha['data_nascimento'] ?? '',
                $idade ?? '',
                $rendaFamiliar,
                $situacao,
                $ficha['numero_membros'] ?? '',
                $ficha['status'] ?? ''
            );
        }
        
        return $csv;
    }
    
    /**
     * Log de ações
     */
    private function logAction($action, $fichaId, $description) {
        $logFile = DATA_PATH . '/socioeconomico_log.json';
        
        if (!file_exists($logFile)) {
            file_put_contents($logFile, json_encode([]));
        }
        
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
        
        $logs[] = [
            'id' => uniqid(),
            'action' => $action,
            'ficha_id' => $fichaId,
            'description' => $description,
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? 'Sistema',
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Manter apenas os últimos 1000 logs
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Obtém logs de ações
     */
    public function getLogs($limit = 50) {
        $logFile = DATA_PATH . '/socioeconomico_log.json';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
        
        // Retornar os mais recentes
        return array_slice(array_reverse($logs), 0, $limit);
    }
}
