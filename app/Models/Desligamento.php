<?php

/**
 * Model para controle de desligamentos
 */
class Desligamento extends BaseModel {
    
    public function __construct() {
        parent::__construct('desligamentos.json');
    }
    
    /**
     * Registra desligamento
     */
    public function registerDesligamento($atendidoId, $data) {
        $record = [
            'id' => uniqid('deslig_'),
            'atendido_id' => $atendidoId,
            'atendido_nome' => $data['atendido_nome'] ?? '',
            'atendido_cpf' => $data['atendido_cpf'] ?? '',
            'motivo' => $data['motivo'] ?? '',
            'tipo_motivo' => $data['tipo_motivo'] ?? 'manual', // manual, idade, excesso_faltas
            'data_desligamento' => $data['data_desligamento'] ?? date('Y-m-d'),
            'observacao' => $data['observacao'] ?? '',
            'automatico' => $data['automatico'] ?? false,
            'registrado_por' => $_SESSION['user_id'] ?? null,
            'registrado_por_nome' => $_SESSION['user_name'] ?? 'Sistema',
            'registrado_em' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($record);
    }
    
    /**
     * Verifica se atendido já foi desligado
     */
    public function isDesligado($atendidoId) {
        $desligamentos = $this->findBy('atendido_id', $atendidoId);
        return !empty($desligamentos);
    }
    
    /**
     * Busca desligamento de um atendido
     */
    public function getByAtendido($atendidoId) {
        $desligamentos = $this->findBy('atendido_id', $atendidoId);
        return !empty($desligamentos) ? $desligamentos[0] : null;
    }
    
    /**
     * Lista desligamentos com filtros
     */
    public function listDesligamentos($filters = []) {
        $desligamentos = $this->findAll();
        
        if (!empty($filters['tipo_motivo'])) {
            $desligamentos = array_filter($desligamentos, function($d) use ($filters) {
                return $d['tipo_motivo'] === $filters['tipo_motivo'];
            });
        }
        
        if (!empty($filters['data_inicio'])) {
            $desligamentos = array_filter($desligamentos, function($d) use ($filters) {
                return $d['data_desligamento'] >= $filters['data_inicio'];
            });
        }
        
        if (!empty($filters['data_fim'])) {
            $desligamentos = array_filter($desligamentos, function($d) use ($filters) {
                return $d['data_desligamento'] <= $filters['data_fim'];
            });
        }
        
        // Ordenar por data decrescente
        usort($desligamentos, function($a, $b) {
            return strcmp($b['data_desligamento'], $a['data_desligamento']);
        });
        
        return array_values($desligamentos);
    }
    
    /**
     * Cancela desligamento (reativa atendido)
     */
    public function cancelDesligamento($desligamentoId) {
        return $this->delete($desligamentoId);
    }
    
    /**
     * Obtém estatísticas de desligamentos
     */
    public function getStatistics() {
        $desligamentos = $this->findAll();
        
        $stats = [
            'total' => count($desligamentos),
            'por_idade' => 0,
            'por_excesso_faltas' => 0,
            'manual' => 0,
            'automaticos' => 0
        ];
        
        foreach ($desligamentos as $d) {
            if ($d['tipo_motivo'] === 'idade') {
                $stats['por_idade']++;
            } elseif ($d['tipo_motivo'] === 'excesso_faltas') {
                $stats['por_excesso_faltas']++;
            } else {
                $stats['manual']++;
            }
            
            if ($d['automatico']) {
                $stats['automaticos']++;
            }
        }
        
        return $stats;
    }
}
