<?php

/**
 * Controller para controle de faltas e desligamentos
 */
class AttendanceController extends BaseController {
    private $attendanceService;
    
    public function __construct() {
        parent::__construct();
        $this->attendanceService = new AttendanceService();
    }
    
    /**
     * Lista atendidos com controle de faltas
     */
    public function index() {
        $this->requireAuth();
        $this->requirePermission('view_attendance');
        
        try {
            $page = intval($this->getParam('page', 1));
            $search = $this->getParam('search', '');
            
            $result = $this->attendanceService->listAtendidosComFaltas($page, 50);

            // Remover desligados do controle de faltas
            $result['data'] = array_values(array_filter($result['data'] ?? [], function($a) {
                return empty($a['desligado']);
            }));

            // Filtrar por busca se fornecida
            if (!empty($search)) {
                $result['data'] = array_filter($result['data'], function($atendido) use ($search) {
                    $searchLower = strtolower($search);
                    return stripos($atendido['nome_completo'] ?? '', $searchLower) !== false ||
                           stripos($atendido['cpf'] ?? '', $searchLower) !== false;
                });
                $result['data'] = array_values($result['data']);
            }
            
            $data = [
                'title' => 'Controle de Faltas',
                'pageTitle' => 'Controle de Faltas',
                'atendidos' => $result['data'] ?? [],
                'pagination' => [
                    'current_page' => $result['current_page'] ?? 1,
                    'last_page' => $result['last_page'] ?? 1,
                    'total' => $result['total'] ?? 0
                ],
                'current_page' => $result['current_page'] ?? 1,
                'last_page' => $result['last_page'] ?? 1,
                'search' => $search,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'attendance/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe detalhes de um atendido
     */
    public function show($id) {
        $this->requireAuth();
        
        try {
            $stats = $this->attendanceService->getAtendidoStatistics($id);
            $historico = $this->attendanceService->getHistorico($id);
            
            $data = [
                'title' => 'Detalhes - ' . ($stats['atendido']['nome'] ?? 'Atendido'),
                'pageTitle' => 'Controle de Faltas - Detalhes',
                'stats' => $stats,
                'historico' => $historico,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'attendance/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Registra presença
     */
    public function registerPresence() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $atendidoId = $this->getParam('atendido_id', '');
            $data = [
                'data' => $this->getParam('data', date('Y-m-d')),
                'atividade' => $this->getParam('atividade', 'Atendimento'),
                'observacao' => $this->getParam('observacao', '')
            ];
            
            if (empty($atendidoId)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            $result = $this->attendanceService->registerPresence($atendidoId, $data);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Presença registrada com sucesso', 'data' => $result]);
            } else {
                $this->redirectWithSuccess("attendance.php?action=show&id={$atendidoId}", 'Presença registrada com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Registra falta
     */
    public function registerAbsence() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $atendidoId = $this->getParam('atendido_id', '');
            $data = [
                'data' => $this->getParam('data', date('Y-m-d')),
                'atividade' => $this->getParam('atividade', 'Atendimento'),
                'justificativa' => $this->getParam('justificativa', ''),
                'observacao' => $this->getParam('observacao', '')
            ];
            
            if (empty($atendidoId)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            $result = $this->attendanceService->registerAbsence($atendidoId, $data);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Falta registrada com sucesso', 'data' => $result]);
            } else {
                $this->redirectWithSuccess("attendance.php?action=show&id={$atendidoId}", 'Falta registrada com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Atualiza justificativa de falta
     */
    public function updateJustification() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $recordId = $this->getParam('record_id', '');
            $justificativa = $this->getParam('justificativa', '');
            
            if (empty($recordId)) {
                throw new Exception('ID do registro é obrigatório');
            }
            
            $result = $this->attendanceService->updateJustification($recordId, $justificativa);
            
            if (!$result) {
                throw new Exception('Registro não encontrado ou não é uma falta');
            }
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Justificativa atualizada com sucesso']);
            } else {
                $this->redirectWithSuccess($_SERVER['HTTP_REFERER'] ?? 'attendance.php', 'Justificativa atualizada com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? 'attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Remove registro
     */
    public function removeRecord() {
        $this->requireAuth();
        $this->requirePermission('delete_records');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $recordId = $this->getParam('record_id', '');
            
            if (empty($recordId)) {
                throw new Exception('ID do registro é obrigatório');
            }
            
            $result = $this->attendanceService->removeRecord($recordId);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Registro removido com sucesso']);
            } else {
                $this->redirectWithSuccess($_SERVER['HTTP_REFERER'] ?? 'attendance.php', 'Registro removido com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? 'attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Exibe formulário de desligamento
     */
    public function showDesligamento($id) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        // Deny access to psychologists
        if ($this->authService->hasPermission('view_psychological_area')) {
            throw new Exception('Acesso negado: Psicólogos não têm permissão para acessar esta funcionalidade');
        }
        
        try {
            $stats = $this->attendanceService->getAtendidoStatistics($id);
            
            if ($stats['desligado']) {
                throw new Exception('Atendido já foi desligado');
            }
            
            $data = [
                'title' => 'Desligar Atendido',
                'pageTitle' => 'Desligamento de Atendido',
                'stats' => $stats,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'attendance/desligamento', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Processa desligamento
     */
    public function processarDesligamento() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $atendidoId = $this->getParam('atendido_id', '');
            $motivo = $this->getParam('motivo', '');
            $observacao = $this->getParam('observacao', '');
            
            if (empty($atendidoId)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            if (empty($motivo)) {
                throw new Exception('Motivo do desligamento é obrigatório');
            }
            
            $result = $this->attendanceService->processarDesligamento($atendidoId, $motivo, $observacao, false);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Atendido desligado com sucesso', 'data' => $result]);
            } else {
                $this->redirectWithSuccess('attendance.php', 'Atendido desligado com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Cancela desligamento (reativa atendido)
     */
    public function cancelarDesligamento() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $atendidoId = $this->getParam('atendido_id', '');
            
            if (empty($atendidoId)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            $result = $this->attendanceService->cancelarDesligamento($atendidoId);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Atendido reativado com sucesso']);
            } else {
                $this->redirectWithSuccess("attendance.php?action=show&id={$atendidoId}", 'Atendido reativado com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Processa desligamentos automáticos por idade
     */
    public function processarDesligamentosAutomaticos() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $desligados = $this->attendanceService->processarDesligamentosAutomaticosPorIdade();
            
            $message = count($desligados) > 0 
                ? count($desligados) . ' atendido(s) desligado(s) automaticamente por idade'
                : 'Nenhum atendido para desligar automaticamente';
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => $message, 'desligados' => $desligados]);
            } else {
                $this->redirectWithSuccess('attendance.php', $message);
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('attendance.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Lista atendidos com alertas
     */
    public function alertas() {
        $this->requireAuth();
        $this->requirePermission('view_attendance_alerts');
        
        try {
            $atendidos = $this->attendanceService->getAtendidosComAlertas();
            
            $data = [
                'title' => 'Alertas de Atendimento',
                'pageTitle' => 'Alertas de Atendimento',
                'atendidos' => $atendidos,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'attendance/alertas', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe relatórios de frequência
     */
    public function relatorios() {
        $this->requireAuth();
        $this->requirePermission('view_attendance_reports');
        
        try {
            // Obter filtros
            $filtros = [
                'status' => $this->getParam('status', ''),
                'alerta' => $this->getParam('alerta', ''),
                'min_faltas' => $this->getParam('min_faltas', '')
            ];
            
            // Gerar estatísticas gerais
            $estatisticasGerais = $this->attendanceService->gerarEstatisticasGerais();
            
            // Gerar ranking
            $rankingMelhores = $this->attendanceService->gerarRankingFrequencia(10, 'melhor');
            $rankingPiores = $this->attendanceService->gerarRankingFrequencia(10, 'pior');
            
            // Gerar relatório geral com filtros
            $relatorioGeral = $this->attendanceService->gerarRelatorioGeral($filtros);
            
            $data = [
                'title' => 'Relatórios de Frequência - Associação Criança Feliz',
                'pageTitle' => 'Relatórios de Frequência',
                'estatisticas' => $estatisticasGerais,
                'rankingMelhores' => $rankingMelhores,
                'rankingPiores' => $rankingPiores,
                'relatorioGeral' => $relatorioGeral,
                'filtros' => $filtros,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'attendance/relatorios', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Tela de lançamento em lote de presença/falta
     */
    public function batch() {
        $this->requireAuth();
        $this->requirePermission('manage_attendance_batch');
        
        try {
            $date = $this->getParam('data', date('Y-m-d'));
            $grupo = $this->getParam('grupo', 'Todos'); // Todos | Crianca | Adolescente
            
            // Buscar todos os atendidos ativos
            $acolhimentoModel = App::getAcolhimentoModel();
            $atendidos = method_exists($acolhimentoModel, 'findAll') ? $acolhimentoModel->findAll() : [];
            
            // Filtrar por grupo
            $filtrados = [];
            $desligamentoModel = new Desligamento();
            foreach ($atendidos as $a) {
                // Determinar ID do atendido
                $aid = $a['id'] ?? $a['idatendido'] ?? null;
                if (!$aid) continue;
                // Ocultar desligados (fonte oficial)
                if ($desligamentoModel->isDesligado($aid)) continue;
                // Também respeitar campo de status se existir
                if (($a['status'] ?? 'Ativo') !== 'Ativo') continue;
                $idade = $this->calcAgeFromYmd($a['data_nascimento'] ?? null, $date);
                if ($grupo === 'Crianca' && !($idade !== null && $idade < 12)) continue;
                if ($grupo === 'Adolescente' && !($idade !== null && $idade >= 12 && $idade < 18)) continue;
                $filtrados[] = $a;
            }
            
            $dataView = [
                'title' => 'Lançamento em Lote - Frequência',
                'pageTitle' => 'Marcar Presença/Falta em Lote',
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages(),
                'data' => $date,
                'grupo' => $grupo,
                'atendidos' => $filtrados
            ];
            
            $this->renderWithLayout('main', 'attendance/batch', $dataView);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Aplica lançamento em lote
     */
    public function applyBatch() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $date = $this->getParam('data', date('Y-m-d'));
            $grupo = $this->getParam('grupo', 'Todos'); // Todos | Crianca | Adolescente
            $acao = $this->getParam('acao', 'Presenca'); // Presenca | Falta
            
            // IDs selecionados pela UI (checkboxes)
            $alvo = $_POST['ids'] ?? [];
            if (is_string($alvo)) { $alvo = [$alvo]; }
            $alvo = array_values(array_filter($alvo));
            
            if (empty($alvo)) {
                throw new Exception('Nenhum atendido encontrado para o filtro selecionado');
            }
            
            $total = 0;
            foreach ($alvo as $atendidoId) {
                if ($acao === 'Presenca') {
                    $this->attendanceService->registerPresence($atendidoId, [
                        'data' => $date,
                        'atividade' => 'Atendimento'
                    ]);
                } else {
                    $this->attendanceService->registerAbsence($atendidoId, [
                        'data' => $date,
                        'atividade' => 'Atendimento',
                        'justificativa' => ''
                    ]);
                }
                $total++;
            }
            
            // Registrar auditoria em lote se existir tabela (opcional)
            if (class_exists('Database')) {
                try {
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("INSERT INTO frequencia_lote (data, grupo, acao, total_afetados, criado_por) VALUES (?, ?, ?, ?, ?)");
                    $userId = isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
                    if ($userId !== null) {
                        try {
                            $chk = $pdo->prepare("SELECT 1 FROM usuario WHERE idusuario = ?");
                            $chk->execute([$userId]);
                            if (!$chk->fetchColumn()) { $userId = null; }
                        } catch (Exception $e) { $userId = null; }
                    }
                    $stmt->execute([$date, $grupo, $acao, $total, $userId]);
                } catch (Exception $e) {
                    // Ignorar se tabela não existir
                    error_log('Auditoria lote não registrada: ' . $e->getMessage());
                }
            }
            
            $this->redirectWithSuccess('attendance.php?action=batch&data=' . urlencode($date) . '&grupo=' . urlencode($grupo),
                "Lançamento em lote aplicado: {$acao} para {$total} atendido(s).");
            
        } catch (Exception $e) {
            $this->redirectWithError('attendance.php?action=batch', $e->getMessage());
        }
    }

    /**
     * Utilitário local: calcula idade a partir de data Y-m-d em referência a outra data
     */
    private function calcAgeFromYmd($ymd, $refYmd) {
        if (empty($ymd)) return null;
        try {
            $birth = new DateTime($ymd);
            $ref = new DateTime($refYmd);
            return $birth->diff($ref)->y;
        } catch (Exception $e) {
            return null;
        }
    }
}
