<?php

/**
 * Model para controle de faltas e presenças
 */
class Attendance extends BaseModel {
    
    public function __construct() {
        parent::__construct('attendance.json');
    }
    
    /**
     * Registra presença
     */
    public function registerPresence($atendidoId, $data) {
        $record = [
            'id' => uniqid('presence_'),
            'atendido_id' => $atendidoId,
            'tipo' => 'presenca',
            'data' => $data['data'] ?? date('Y-m-d'),
            'atividade' => $data['atividade'] ?? 'Atendimento',
            'observacao' => $data['observacao'] ?? '',
            'registrado_por' => $_SESSION['user_id'] ?? null,
            'registrado_em' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($record);
    }
    
    /**
     * Registra falta
     */
    public function registerAbsence($atendidoId, $data) {
        $record = [
            'id' => uniqid('absence_'),
            'atendido_id' => $atendidoId,
            'tipo' => 'falta',
            'data' => $data['data'] ?? date('Y-m-d'),
            'atividade' => $data['atividade'] ?? 'Atendimento',
            'justificada' => isset($data['justificativa']) && !empty($data['justificativa']),
            'justificativa' => $data['justificativa'] ?? '',
            'observacao' => $data['observacao'] ?? '',
            'registrado_por' => $_SESSION['user_id'] ?? null,
            'registrado_em' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($record);
    }
    
    /**
     * Busca registros de um atendido
     */
    public function getByAtendido($atendidoId) {
        return $this->findBy('atendido_id', $atendidoId);
    }
    
    /**
     * Conta presenças de um atendido
     */
    public function countPresences($atendidoId, $startDate = null, $endDate = null) {
        $records = $this->getByAtendido($atendidoId);
        $count = 0;
        
        foreach ($records as $record) {
            if ($record['tipo'] === 'presenca') {
                if ($startDate && $record['data'] < $startDate) continue;
                if ($endDate && $record['data'] > $endDate) continue;
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Conta faltas de um atendido
     */
    public function countAbsences($atendidoId, $justificadas = null, $startDate = null, $endDate = null) {
        $records = $this->getByAtendido($atendidoId);
        $count = 0;
        
        foreach ($records as $record) {
            if ($record['tipo'] === 'falta') {
                if ($justificadas !== null && $record['justificada'] !== $justificadas) continue;
                if ($startDate && $record['data'] < $startDate) continue;
                if ($endDate && $record['data'] > $endDate) continue;
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Obtém última atividade de um atendido
     */
    public function getLastActivity($atendidoId) {
        $records = $this->getByAtendido($atendidoId);
        
        if (empty($records)) {
            return null;
        }
        
        // Ordenar por data decrescente
        usort($records, function($a, $b) {
            return strcmp($b['data'], $a['data']);
        });
        
        return $records[0];
    }
    
    /**
     * Obtém estatísticas de um atendido
     */
    public function getStatistics($atendidoId, $startDate = null, $endDate = null) {
        $totalPresencas = $this->countPresences($atendidoId, $startDate, $endDate);
        $faltasJustificadas = $this->countAbsences($atendidoId, true, $startDate, $endDate);
        $faltasNaoJustificadas = $this->countAbsences($atendidoId, false, $startDate, $endDate);
        $totalFaltas = $faltasJustificadas + $faltasNaoJustificadas;
        $totalRegistros = $totalPresencas + $totalFaltas;
        
        $percentualPresenca = $totalRegistros > 0 
            ? round(($totalPresencas / $totalRegistros) * 100, 2) 
            : 0;
        
        return [
            'total_presencas' => $totalPresencas,
            'faltas_justificadas' => $faltasJustificadas,
            'faltas_nao_justificadas' => $faltasNaoJustificadas,
            'total_faltas' => $totalFaltas,
            'total_registros' => $totalRegistros,
            'percentual_presenca' => $percentualPresenca,
            'ultima_atividade' => $this->getLastActivity($atendidoId)
        ];
    }
    
    /**
     * Remove registro
     */
    public function removeRecord($recordId) {
        return $this->delete($recordId);
    }
    
    /**
     * Atualiza justificativa de falta
     */
    public function updateJustification($recordId, $justificativa) {
        $record = $this->findById($recordId);
        
        if (!$record || $record['tipo'] !== 'falta') {
            return false;
        }
        
        $record['justificativa'] = $justificativa;
        $record['justificada'] = !empty($justificativa);
        $record['atualizado_em'] = date('Y-m-d H:i:s');
        
        return $this->update($recordId, $record);
    }
}
