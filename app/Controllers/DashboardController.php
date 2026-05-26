<?php

/**
 * Controller para o dashboard
 */
class DashboardController extends BaseController {
    private $acolhimentoService;
    private $socioeconomicoService;
    
    public function __construct() {
        parent::__construct();
        $this->acolhimentoService = new AcolhimentoService();
        $this->socioeconomicoService = new SocioeconomicoService();
    }
    
    /**
     * Exibe o dashboard principal
     */
    public function index() {
        $this->requireAuth();
        
        try {
            // Obter estatísticas
            $statsAcolhimento = $this->normalizeFichaStats($this->acolhimentoService->getStatistics());
            $statsSocioeconomico = $this->normalizeFichaStats($this->socioeconomicoService->getStatistics());
            
            // Obter alertas
            $alertas = $this->getAlertas();
            
            // Obter anotações do calendário
            $anotacoes = $this->getAnotacoesCalendario();
            
            $data = [
                'title' => 'Dashboard - Associação Criança Feliz',
                'userName' => $_SESSION['user_name'] ?? 'Usuário',
                'userEmail' => $_SESSION['user_email'] ?? '',
                'userRole' => $_SESSION['user_role'] ?? 'user',
                'statsAcolhimento' => $statsAcolhimento,
                'statsSocioeconomico' => $statsSocioeconomico,
                'alertas' => $alertas,
                'anotacoes' => $anotacoes,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'dashboard/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * API para obter estatísticas
     */
    public function getStats() {
        $this->requireAuth();
        
        try {
            $statsAcolhimento = $this->normalizeFichaStats($this->acolhimentoService->getStatistics());
            $statsSocioeconomico = $this->normalizeFichaStats($this->socioeconomicoService->getStatistics());
            
            $stats = [
                'acolhimento' => $statsAcolhimento,
                'socioeconomico' => $statsSocioeconomico,
                'totais' => [
                    'fichas_ativas' => $statsAcolhimento['ativas'] + $statsSocioeconomico['ativas'],
                    'fichas_inativas' => $statsAcolhimento['inativas'] + $statsSocioeconomico['inativas'],
                    'total_geral' => $statsAcolhimento['total'] + $statsSocioeconomico['total']
                ]
            ];
            
            $this->json($stats);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva anotação do calendário
     */
    public function saveCalendarNote() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $date = $this->getParam('date', '');
            $note = $this->getParam('note', '');
            $type = $this->getParam('type', 'anotacao'); // 'anotacao' ou 'aviso'
            
            if (empty($date)) {
                throw new Exception('Data é obrigatória');
            }
            
            if (empty($note)) {
                throw new Exception('Anotação é obrigatória');
            }
            
            // Validar formato da data
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new Exception('Formato de data inválido');
            }
            
            $id = $this->saveNote($date, $note, $type);
            
            $this->json(['success' => 'Anotação salva com sucesso', 'id' => $id]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Remove anotação do calendário
     */
    public function deleteCalendarNote() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $id = $this->getParam('id', '');
            
            if (empty($id)) {
                throw new Exception('ID é obrigatório');
            }
            
            $this->deleteNote($id);
            
            $this->json(['success' => 'Anotação removida com sucesso']);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Obtém anotações do calendário
     */
    public function getCalendarNotes() {
        $this->requireAuth();
        
        try {
            $month = $this->getParam('month', date('Y-m'));
            $notes = $this->getAnotacoesPorMes($month);
            
            $this->json($notes);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtém alertas do sistema
     */
    private function getAlertas() {
        $alertas = [];
        
        try {
            // Alertas de fichas incompletas
            $acolhimentos = $this->acolhimentoService->listFichas(1, 100);
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 100);
            
            $fichasIncompletas = 0;
            $fichasVencidas = 0;
            
            // Verificar fichas de acolhimento
            foreach ($acolhimentos['data'] as $ficha) {
                if (empty($ficha['cpf']) || empty($ficha['nome_completo'])) {
                    $fichasIncompletas++;
                }
                
                // Verificar se ficha tem mais de 6 meses
                if (!empty($ficha['data_acolhimento'])) {
                    $dataAcolhimento = DateTime::createFromFormat('d/m/Y', $ficha['data_acolhimento']);
                    if ($dataAcolhimento && $dataAcolhimento->diff(new DateTime())->days > 180) {
                        $fichasVencidas++;
                    }
                }
            }
            
            // Alertas de faltas e desligamentos
            try {
                $frequenciaModel = new FrequenciaDia();
                $desligamentoModel = new Desligamento();
                
                // Buscar atendidos com alertas de faltas
                $atendidosComAlertas = $frequenciaModel->getAtendidosComAlertas();
                
                $excessoFaltas = 0;
                foreach ($atendidosComAlertas as $atendido) {
                    if ($atendido['total_faltas'] >= 3) {
                        $excessoFaltas++;
                    }
                }
                
                // Buscar atendidos com idade limite (>= 18 anos)
                $acolhimentoModel = new Acolhimento();
                $todosAtendidos = $acolhimentoModel->findAll();
                $idadeLimite = 0;
                
                foreach ($todosAtendidos as $atendido) {
                    $id = $atendido['idatendido'] ?? $atendido['id'];
                    if (($atendido['status'] ?? 'Ativo') === 'Ativo' && !$desligamentoModel->isDesligado($id)) {
                        $idade = calculateAge($atendido['data_nascimento'] ?? '');
                        if ($idade >= 18) {
                            $idadeLimite++;
                        }
                    }
                }
                
                if ($excessoFaltas > 0) {
                    $alertas[] = [
                        'tipo' => 'warning',
                        'titulo' => 'Excesso de Faltas',
                        'mensagem' => "$excessoFaltas atendido(s) com excesso de faltas não justificadas",
                        'icone' => '⚠️',
                        'link' => 'desligamento.php'
                    ];
                }
                
                if ($idadeLimite > 0) {
                    $alertas[] = [
                        'tipo' => 'error',
                        'titulo' => 'Desligamento Pendente',
                        'mensagem' => "$idadeLimite atendido(s) completou(aram) 18 anos - Desligamento automático pendente",
                        'icone' => '🎂',
                        'link' => 'desligamento.php'
                    ];
                }
            } catch (Exception $e) {
                error_log("Erro ao buscar alertas de faltas: " . $e->getMessage());
            }
            
            if ($fichasIncompletas > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Fichas Incompletas',
                    'mensagem' => "$fichasIncompletas ficha(s) com dados incompletos",
                    'icone' => '⚠️'
                ];
            }
            
            if ($fichasVencidas > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'titulo' => 'Fichas para Revisão',
                    'mensagem' => "$fichasVencidas ficha(s) com mais de 6 meses",
                    'icone' => '📅'
                ];
            }
            
            // Alertas de sistema
            if (empty($alertas)) {
                $alertas[] = [
                    'tipo' => 'success',
                    'titulo' => 'Sistema Funcionando',
                    'mensagem' => 'Todas as funcionalidades operacionais',
                    'icone' => '✅'
                ];
            }
            
        } catch (Exception $e) {
            $alertas[] = [
                'tipo' => 'error',
                'titulo' => 'Erro no Sistema',
                'mensagem' => 'Erro ao carregar alertas: ' . $e->getMessage(),
                'icone' => '❌'
            ];
        }
        
        return $alertas;
    }

    /**
     * Normaliza estatisticas vindas de models JSON e MySQL.
     */
    private function normalizeFichaStats($stats) {
        $stats = is_array($stats) ? $stats : [];
        $stats['total'] = intval($stats['total'] ?? 0);

        if (!isset($stats['ativas']) || !isset($stats['inativas'])) {
            $ativas = 0;
            $inativas = 0;

            foreach (($stats['porStatus'] ?? []) as $row) {
                $status = strtolower($row['status'] ?? '');
                $totalStatus = intval($row['total'] ?? 0);

                if ($status === 'ativo' || $status === 'active') {
                    $ativas += $totalStatus;
                } else {
                    $inativas += $totalStatus;
                }
            }

            $stats['ativas'] = $ativas;
            $stats['inativas'] = $inativas ?: max(0, $stats['total'] - $ativas);
        }

        return $stats;
    }
    
    /**
     * Obtém anotações do calendário
     */
    private function getAnotacoesCalendario() {
        try {
            $pdo = Database::getConnection();
            $currentMonth = date('Y-m');
            
            $stmt = $pdo->prepare("
                SELECT id_notificacao as id, mensagem as note, tipo as type, DATE(data_envio) as date
                FROM agenda
                WHERE data_envio LIKE ?
                ORDER BY data_envio ASC
            ");
            $stmt->execute([$currentMonth . '%']);
            $allNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $anotacoes = [];
            $avisos = [];
            
            foreach ($allNotes as $item) {
                $noteData = [
                    'id' => $item['id'],
                    'date' => $item['date'],
                    'note' => $item['note'],
                    'type' => $item['type'] ?? 'anotacao',
                    'formatted_date' => date('d/m/Y', strtotime($item['date']))
                ];
                
                if ($item['type'] === 'aviso') {
                    $avisos[] = $noteData;
                } else {
                    $anotacoes[] = $noteData;
                }
            }
            
            return ['anotacoes' => $anotacoes, 'avisos' => $avisos];
        } catch (Exception $e) {
            error_log("Erro ao buscar anotações do calendário: " . $e->getMessage());
            return ['anotacoes' => [], 'avisos' => []];
        }
    }
    
    /**
     * Obtém anotações por mês
     */
    private function getAnotacoesPorMes($month) {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT id_notificacao as id, mensagem as note, tipo as type, DATE(data_envio) as date
                FROM agenda
                WHERE data_envio LIKE ?
                ORDER BY data_envio ASC
            ");
            $stmt->execute([$month . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar anotações por mês: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Salva anotação
     */
    private function saveNote($date, $note, $type = 'anotacao') {
        $pdo = Database::getConnection();
        $dateTimeStr = $date . ' 00:00:00';
        
        $stmt = $pdo->prepare("
            INSERT INTO agenda (mensagem, tipo, lida, data_envio)
            VALUES (?, ?, 0, ?)
        ");
        $stmt->execute([trim($note), $type, $dateTimeStr]);
        return $pdo->lastInsertId();
    }
    
    /**
     * Remove anotação
     */
    private function deleteNote($id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            DELETE FROM agenda
            WHERE id_notificacao = ?
        ");
        $stmt->execute([$id]);
    }
}
