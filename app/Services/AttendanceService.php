<?php

/**
 * Service para lógica de negócio de faltas e desligamentos
 */
class AttendanceService {
    private $attendanceModel;
    private $desligamentoModel;
    private $acolhimentoModel;
    
    // Configurações
    const MAX_FALTAS_NAO_JUSTIFICADAS = 5;
    const IDADE_DESLIGAMENTO_AUTOMATICO = 18;
    
    public function __construct() {
        // Seleciona model conforme disponibilidade do banco
        $dbAvailable = false;
        if (class_exists('App') && method_exists('App', 'isDatabaseAvailable')) {
            $dbAvailable = App::isDatabaseAvailable();
        } elseif (class_exists('Database')) {
            // Tenta abrir conexão para validar
            try { Database::getConnection(); $dbAvailable = true; } catch (Exception $e) { $dbAvailable = false; }
        }

        if ($dbAvailable) {
            if (class_exists('AttendanceDB')) {
                $this->attendanceModel = new AttendanceDB();
            } else {
                $this->attendanceModel = new Attendance();
            }
        } else {
            $this->attendanceModel = new Attendance();
        }
        $this->desligamentoModel = new Desligamento();
        $this->acolhimentoModel = new Acolhimento();
    }
    
    /**
     * Registra presença
     */
    public function registerPresence($atendidoId, $data) {
        // Verificar se atendido está desligado
        if ($this->desligamentoModel->isDesligado($atendidoId)) {
            throw new Exception('Atendido já foi desligado do programa');
        }
        
        return $this->attendanceModel->registerPresence($atendidoId, $data);
    }
    
    /**
     * Registra falta
     */
    public function registerAbsence($atendidoId, $data) {
        // Verificar se atendido está desligado
        if ($this->desligamentoModel->isDesligado($atendidoId)) {
            throw new Exception('Atendido já foi desligado do programa');
        }
        
        $result = $this->attendanceModel->registerAbsence($atendidoId, $data);
        
        // Verificar se precisa alertar sobre excesso de faltas
        $this->checkExcessoFaltas($atendidoId);
        
        return $result;
    }
    
    /**
     * Verifica excesso de faltas
     */
    private function checkExcessoFaltas($atendidoId) {
        $faltasNaoJustificadas = $this->attendanceModel->countAbsences($atendidoId, false);
        
        if ($faltasNaoJustificadas >= self::MAX_FALTAS_NAO_JUSTIFICADAS) {
            // Criar alerta no sistema
            $this->createAlert($atendidoId, 'excesso_faltas', 
                "Atendido com {$faltasNaoJustificadas} faltas não justificadas");
        }
    }
    
    /**
     * Obtém estatísticas de um atendido
     */
    public function getAtendidoStatistics($atendidoId) {
        $stats = $this->attendanceModel->getStatistics($atendidoId);
        
        // Adicionar informações do atendido
        $atendido = $this->acolhimentoModel->findById($atendidoId);
        if ($atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            $nome = $atendido['nome_completo'] ?? $atendido['nome'] ?? '';
            $cpf = $atendido['cpf'] ?? '';
            $dn = $atendido['data_nascimento'] ?? '';
            $stats['atendido'] = [
                'id' => $aid,
                'nome' => $nome,
                'cpf' => $cpf,
                'data_nascimento' => $dn,
                'idade' => $this->calculateAge($dn)
            ];
            
            // Verificar alertas
            $stats['alertas'] = $this->getAlertas($atendidoId, $stats);
        }
        
        // Verificar se está desligado
        $stats['desligado'] = $this->desligamentoModel->isDesligado($atendidoId);
        if ($stats['desligado']) {
            $stats['desligamento'] = $this->desligamentoModel->getByAtendido($atendidoId);
        }
        
        return $stats;
    }
    
    /**
     * Lista todos os atendidos com estatísticas de faltas
     */
    public function listAtendidosComFaltas($page = 1, $perPage = 50) {
        $atendidos = $this->acolhimentoModel->paginate($page, $perPage);
        
        foreach ($atendidos['data'] as &$atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            if (!$aid) { continue; }
            $stats = $this->attendanceModel->getStatistics($aid);
            $atendido['faltas_justificadas'] = $stats['faltas_justificadas'] ?? 0;
            $atendido['faltas_nao_justificadas'] = $stats['faltas_nao_justificadas'] ?? 0;
            $atendido['total_presencas'] = $stats['total_presencas'] ?? 0;
            $atendido['ultima_atividade'] = $stats['ultima_atividade']['data'] ?? null;
            $atendido['idade'] = $this->calculateAge($atendido['data_nascimento'] ?? '');
            
            // Verificar alertas
            $atendido['tem_alerta'] = $this->hasAlertas($aid, $stats);
            $atendido['desligado'] = $this->desligamentoModel->isDesligado($aid);
        }
        
        return $atendidos;
    }
    
    /**
     * Busca atendidos com alertas
     */
    public function getAtendidosComAlertas() {
        $atendidos = $this->acolhimentoModel->findAll();
        $comAlertas = [];
        
        foreach ($atendidos as $atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            if (!$aid) { continue; }
            $stats = $this->attendanceModel->getStatistics($aid);
            $alertas = $this->getAlertas($aid, $stats);
            
            if (!empty($alertas)) {
                $atendido['alertas'] = $alertas;
                $atendido['stats'] = $stats;
                $comAlertas[] = $atendido;
            }
        }
        
        return $comAlertas;
    }
    
    /**
     * Verifica se atendido tem alertas
     */
    private function hasAlertas($atendidoId, $stats) {
        $alertas = $this->getAlertas($atendidoId, $stats);
        return !empty($alertas);
    }
    
    /**
     * Obtém alertas de um atendido
     */
    private function getAlertas($atendidoId, $stats) {
        $alertas = [];
        
        // Verificar se está desligado
        if ($this->desligamentoModel->isDesligado($atendidoId)) {
            return $alertas; // Não mostrar alertas para desligados
        }
        
        // Alerta de excesso de faltas não justificadas
        if ($stats['faltas_nao_justificadas'] >= self::MAX_FALTAS_NAO_JUSTIFICADAS) {
            $alertas[] = [
                'tipo' => 'excesso_faltas',
                'nivel' => 'critico',
                'mensagem' => "Excesso de faltas não justificadas ({$stats['faltas_nao_justificadas']})",
                'icone' => '⚠️',
                'acao_sugerida' => 'Considerar desligamento por excesso de faltas'
            ];
        } elseif ($stats['faltas_nao_justificadas'] >= 3) {
            $alertas[] = [
                'tipo' => 'alerta_faltas',
                'nivel' => 'atencao',
                'mensagem' => "Atenção: {$stats['faltas_nao_justificadas']} faltas não justificadas",
                'icone' => '⚡',
                'acao_sugerida' => 'Entrar em contato com responsável'
            ];
        }
        
        // Alerta de idade (próximo aos 18 anos ou já completou)
        $atendido = $this->acolhimentoModel->findById($atendidoId);
        if ($atendido) {
            $idade = $this->calculateAge($atendido['data_nascimento'] ?? '');
            
            if ($idade >= self::IDADE_DESLIGAMENTO_AUTOMATICO) {
                $alertas[] = [
                    'tipo' => 'idade_limite',
                    'nivel' => 'critico',
                    'mensagem' => "Atendido completou {$idade} anos - Desligamento automático pendente",
                    'icone' => '🎂',
                    'acao_sugerida' => 'Processar desligamento automático por idade'
                ];
            } elseif ($idade >= 17) {
                $alertas[] = [
                    'tipo' => 'idade_proxima',
                    'nivel' => 'info',
                    'mensagem' => "Atendido próximo aos 18 anos (idade atual: {$idade})",
                    'icone' => '📅',
                    'acao_sugerida' => 'Preparar encerramento do atendimento'
                ];
            }
        }
        
        return $alertas;
    }
    
    /**
     * Processa desligamento
     */
    public function processarDesligamento($atendidoId, $motivo, $observacao = '', $automatico = false) {
        // Verificar se já está desligado
        if ($this->desligamentoModel->isDesligado($atendidoId)) {
            throw new Exception('Atendido já foi desligado');
        }
        
        // Buscar dados do atendido
        $atendido = $this->acolhimentoModel->findById($atendidoId);
        if (!$atendido) {
            throw new Exception('Atendido não encontrado');
        }
        
        // Determinar tipo de motivo
        $tipoMotivo = 'manual';
        if ($automatico && $motivo === 'idade') {
            $tipoMotivo = 'idade';
        } elseif ($motivo === 'excesso_faltas') {
            $tipoMotivo = 'excesso_faltas';
        }
        
        // Registrar desligamento
        $desligamento = $this->desligamentoModel->registerDesligamento($atendidoId, [
            'atendido_nome' => $atendido['nome_completo'] ?? '',
            'atendido_cpf' => $atendido['cpf'] ?? '',
            'motivo' => $motivo,
            'tipo_motivo' => $tipoMotivo,
            'observacao' => $observacao,
            'automatico' => $automatico
        ]);
        
        // Log da ação
        $this->logAction('desligamento', $atendidoId, "Desligamento: {$motivo}");
        
        return $desligamento;
    }
    
    /**
     * Verifica e processa desligamentos automáticos por idade
     */
    public function processarDesligamentosAutomaticosPorIdade() {
        $atendidos = $this->acolhimentoModel->findAll();
        $desligados = [];
        
        foreach ($atendidos as $atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            if (!$aid) { continue; }
            // Pular se já está desligado
            if ($this->desligamentoModel->isDesligado($aid)) {
                continue;
            }
            
            $idade = $this->calculateAge($atendido['data_nascimento'] ?? '');
            
            if ($idade >= self::IDADE_DESLIGAMENTO_AUTOMATICO) {
                try {
                    $desligamento = $this->processarDesligamento(
                        $aid,
                        'idade',
                        "Desligamento automático por completar {$idade} anos",
                        true
                    );
                    $desligados[] = [
                        'atendido' => $atendido,
                        'desligamento' => $desligamento
                    ];
                } catch (Exception $e) {
                    error_log("Erro ao desligar atendido {$aid}: " . $e->getMessage());
                }
            }
        }
        
        return $desligados;
    }
    
    /**
     * Cancela desligamento (reativa atendido)
     */
    public function cancelarDesligamento($atendidoId) {
        $desligamento = $this->desligamentoModel->getByAtendido($atendidoId);
        
        if (!$desligamento) {
            throw new Exception('Desligamento não encontrado');
        }
        
        $result = $this->desligamentoModel->cancelDesligamento($desligamento['id']);
        
        if ($result) {
            $this->logAction('reativacao', $atendidoId, 'Atendido reativado');
        }
        
        return $result;
    }
    
    /**
     * Calcula idade
     */
    private function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) { return null; }
        $now = new DateTime();
        // Tenta Y-m-d
        $date = DateTime::createFromFormat('Y-m-d', $dataNascimento);
        if ($date instanceof DateTime) { return $now->diff($date)->y; }
        // Tenta d/m/Y
        $date = DateTime::createFromFormat('d/m/Y', $dataNascimento);
        if ($date instanceof DateTime) { return $now->diff($date)->y; }
        // Fallback parse livre
        try {
            $date = new DateTime($dataNascimento);
            return $now->diff($date)->y;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Cria alerta no sistema
     */
    private function createAlert($atendidoId, $tipo, $mensagem) {
        $alertsFile = DATA_PATH . '/alerts.json';
        
        if (!file_exists($alertsFile)) {
            file_put_contents($alertsFile, json_encode([]));
        }
        
        $alerts = json_decode(file_get_contents($alertsFile), true) ?: [];
        
        // Verificar se já existe alerta similar
        $exists = false;
        foreach ($alerts as $alert) {
            if ($alert['atendido_id'] === $atendidoId && $alert['tipo'] === $tipo) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $alerts[] = [
                'id' => uniqid('alert_'),
                'atendido_id' => $atendidoId,
                'tipo' => $tipo,
                'mensagem' => $mensagem,
                'criado_em' => date('Y-m-d H:i:s'),
                'lido' => false
            ];
            
            file_put_contents($alertsFile, json_encode($alerts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    
    /**
     * Log de ações
     */
    private function logAction($action, $atendidoId, $description) {
        $logFile = DATA_PATH . '/attendance_log.json';
        
        if (!file_exists($logFile)) {
            file_put_contents($logFile, json_encode([]));
        }
        
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
        
        $logs[] = [
            'id' => uniqid(),
            'action' => $action,
            'atendido_id' => $atendidoId,
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
     * Obtém histórico de um atendido
     */
    public function getHistorico($atendidoId) {
        $registros = $this->attendanceModel->getByAtendido($atendidoId);
        
        // Ordenar por data decrescente
        usort($registros, function($a, $b) {
            return strcmp($b['data'], $a['data']);
        });
        
        return $registros;
    }
    
    /**
     * Remove registro de falta/presença
     */
    public function removeRecord($recordId) {
        return $this->attendanceModel->removeRecord($recordId);
    }
    
    /**
     * Atualiza justificativa de falta
     */
    public function updateJustification($recordId, $justificativa) {
        return $this->attendanceModel->updateJustification($recordId, $justificativa);
    }
    
    /**
     * Gera relatório geral de frequência
     */
    public function gerarRelatorioGeral($filtros = []) {
        $atendidos = $this->acolhimentoModel->findAll();
        $relatorio = [];
        
        foreach ($atendidos as $atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            if (!$aid) { continue; }
            $stats = $this->getAtendidoStatistics($aid);
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                if ($filtros['status'] === 'desligado' && !$stats['desligado']) continue;
                if ($filtros['status'] === 'ativo' && $stats['desligado']) continue;
            }
            
            if (!empty($filtros['alerta'])) {
                $temAlerta = !empty($stats['alertas']);
                if ($filtros['alerta'] === 'com_alerta' && !$temAlerta) continue;
                if ($filtros['alerta'] === 'sem_alerta' && $temAlerta) continue;
            }
            
            if (!empty($filtros['min_faltas']) && $stats['faltas_nao_justificadas'] < $filtros['min_faltas']) {
                continue;
            }
            
            $relatorio[] = [
                'atendido' => $atendido,
                'stats' => $stats
            ];
        }
        
        return $relatorio;
    }
    
    /**
     * Gera estatísticas gerais do sistema
     */
    public function gerarEstatisticasGerais() {
        $atendidos = $this->acolhimentoModel->findAll();
        $allRecords = $this->attendanceModel->findAll();
        
        $stats = [
            'total_atendidos' => count($atendidos),
            'total_atendidos_ativos' => 0,
            'total_atendidos_desligados' => 0,
            'total_registros' => count($allRecords),
            'total_presencas' => 0,
            'total_faltas' => 0,
            'total_faltas_justificadas' => 0,
            'total_faltas_nao_justificadas' => 0,
            'atendidos_com_alertas' => 0,
            'atendidos_excesso_faltas' => 0,
            'atendidos_idade_limite' => 0,
            'taxa_presenca_geral' => 0,
            'por_atividade' => [],
            'por_mes' => []
        ];
        
        // Contar registros
        foreach ($allRecords as $record) {
            if ($record['tipo'] === 'presenca') {
                $stats['total_presencas']++;
            } else {
                $stats['total_faltas']++;
                if ($record['justificada']) {
                    $stats['total_faltas_justificadas']++;
                } else {
                    $stats['total_faltas_nao_justificadas']++;
                }
            }
            
            // Por atividade
            $atividade = $record['atividade'] ?? 'Não especificado';
            if (!isset($stats['por_atividade'][$atividade])) {
                $stats['por_atividade'][$atividade] = [
                    'presencas' => 0,
                    'faltas' => 0
                ];
            }
            if ($record['tipo'] === 'presenca') {
                $stats['por_atividade'][$atividade]['presencas']++;
            } else {
                $stats['por_atividade'][$atividade]['faltas']++;
            }
            
            // Por mês
            $mes = date('Y-m', strtotime($record['data']));
            if (!isset($stats['por_mes'][$mes])) {
                $stats['por_mes'][$mes] = [
                    'presencas' => 0,
                    'faltas' => 0
                ];
            }
            if ($record['tipo'] === 'presenca') {
                $stats['por_mes'][$mes]['presencas']++;
            } else {
                $stats['por_mes'][$mes]['faltas']++;
            }
        }
        
        // Contar atendidos por status
        foreach ($atendidos as $atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            if (!$aid) { continue; }
            $atendidoStats = $this->getAtendidoStatistics($aid);
            
            if ($atendidoStats['desligado']) {
                $stats['total_atendidos_desligados']++;
            } else {
                $stats['total_atendidos_ativos']++;
            }
            
            if (!empty($atendidoStats['alertas'])) {
                $stats['atendidos_com_alertas']++;
                
                foreach ($atendidoStats['alertas'] as $alerta) {
                    if ($alerta['tipo'] === 'excesso_faltas') {
                        $stats['atendidos_excesso_faltas']++;
                    }
                    if ($alerta['tipo'] === 'idade_limite') {
                        $stats['atendidos_idade_limite']++;
                    }
                }
            }
        }
        
        // Calcular taxa de presença geral
        $totalRegistros = $stats['total_presencas'] + $stats['total_faltas'];
        if ($totalRegistros > 0) {
            $stats['taxa_presenca_geral'] = round(($stats['total_presencas'] / $totalRegistros) * 100, 1);
        }
        
        // Ordenar por mês
        ksort($stats['por_mes']);
        
        return $stats;
    }
    
    /**
     * Gera relatório por período
     */
    public function gerarRelatorioPorPeriodo($dataInicio, $dataFim) {
        $allRecords = $this->attendanceModel->findAll();
        $registros = [];
        
        foreach ($allRecords as $record) {
            $dataRecord = $record['data'];
            if ($dataRecord >= $dataInicio && $dataRecord <= $dataFim) {
                $registros[] = $record;
            }
        }
        
        return $registros;
    }
    
    /**
     * Gera ranking de frequência
     */
    public function gerarRankingFrequencia($limite = 10, $ordem = 'melhor') {
        $atendidos = $this->acolhimentoModel->findAll();
        $ranking = [];
        
        foreach ($atendidos as $atendido) {
            $aid = $atendido['id'] ?? $atendido['idatendido'] ?? null;
            if (!$aid) { continue; }
            $stats = $this->getAtendidoStatistics($aid);
            
            // Apenas atendidos ativos
            if ($stats['desligado']) continue;
            
            $ranking[] = [
                'atendido' => $atendido,
                'taxa_presenca' => $stats['percentual_presenca'],
                'total_presencas' => $stats['total_presencas'],
                'total_faltas' => $stats['total_faltas']
            ];
        }
        
        // Ordenar
        usort($ranking, function($a, $b) use ($ordem) {
            if ($ordem === 'melhor') {
                return $b['taxa_presenca'] <=> $a['taxa_presenca'];
            } else {
                return $a['taxa_presenca'] <=> $b['taxa_presenca'];
            }
        });
        
        return array_slice($ranking, 0, $limite);
    }
}
