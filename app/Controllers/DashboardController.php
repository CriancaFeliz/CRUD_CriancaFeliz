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
            
            // Obter lista detalhada de aniversariantes do mês
            $aniversariantesDetalhes = $this->getAniversariantesDoMes();
            
            // Obter anotações do calendário
            $anotacoes = $this->getAnotacoesCalendario();
            
            $data = [
                'title' => 'Dashboard - Associação Criança Feliz',
                'userName' => $_SESSION['user_name'] ?? 'Usuário',
                'userEmail' => $_SESSION['user_email'] ?? '',
                'userRole' => $_SESSION['user_role'] ?? 'user',
                'userId' => $_SESSION['user_id'] ?? 0,
                'statsAcolhimento' => $statsAcolhimento,
                'statsSocioeconomico' => $statsSocioeconomico,
                'alertas' => $alertas,
                'aniversariantesDetalhes' => $aniversariantesDetalhes,
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
            $this->validateCSRF();
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
            $this->validateCSRF();
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
     * Obtém alertas do sistema com identificador único
     */
    private function getAlertas() {
        $alertas = [];
        $userRole = $_SESSION['user_role'] ?? 'funcionario';
        
        try {
            $pdo = Database::getConnection();
            
            if ($userRole !== 'psicologo') {
            // 1. Faltas Não Justificadas (Críticas >= 3 e em Risco = 2)
            try {
                $frequenciaModel = new FrequenciaDia();
                $atendidosComAlertas = $frequenciaModel->getAtendidosComAlertas();
                
                $excessoFaltasCritico = 0;
                $excessoFaltasRisco = 0;
                
                foreach ($atendidosComAlertas as $atendido) {
                    $total = (int)($atendido['total_faltas'] ?? 0);
                    if ($total >= 3) {
                        $excessoFaltasCritico++;
                    } elseif ($total === 2) {
                        $excessoFaltasRisco++;
                    }
                }
                
                if ($excessoFaltasCritico > 0) {
                    $alertas[] = [
                        'id' => 'faltas_criticas',
                        'tipo' => 'error',
                        'titulo' => 'Faltas Críticas',
                        'mensagem' => "{$excessoFaltasCritico} atendido(s) com 3+ faltas não justificadas (passíveis de desligamento)",
                        'icone' => 'fa-exclamation-circle',
                        'link' => 'faltas.php?action=alertas'
                    ];
                }
                
                if ($excessoFaltasRisco > 0) {
                    $alertas[] = [
                        'id' => 'faltas_risco',
                        'tipo' => 'warning',
                        'titulo' => 'Risco de Desligamento',
                        'mensagem' => "{$excessoFaltasRisco} atendido(s) em risco de desligamento (2 faltas não justificadas)",
                        'icone' => 'fa-exclamation-triangle',
                        'link' => 'faltas.php?action=alertas'
                    ];
                }
            } catch (Exception $e) {
                reportException($e, 'DashboardController::alertasFaltas');
            }
            
            // 2. Maioridade (>= 18 anos) com desligamento pendente
            try {
                $stmt = $pdo->query("
                    SELECT COUNT(*) as total
                    FROM atendido a
                    WHERE a.status = 'Ativo'
                      AND a.data_nascimento IS NOT NULL
                      AND a.data_nascimento != '0000-00-00'
                      AND TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) >= 18
                      AND NOT EXISTS (
                          SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                      )
                ");
                $idadeLimite = (int)($stmt->fetchColumn() ?: 0);
                if ($idadeLimite > 0) {
                    $alertas[] = [
                        'id' => 'maioridade',
                        'tipo' => 'error',
                        'titulo' => 'Desligamento Pendente',
                        'mensagem' => "{$idadeLimite} atendido(s) completaram 18 anos — Desligamento pendente",
                        'icone' => 'fa-user-clock',
                        'link' => 'desligamento.php'
                    ];
                }
            } catch (Exception $e) {
                reportException($e, 'DashboardController::alertasMaioridade');
            }
            
            // 3. Atendidos ativos sem Ficha Socioeconômica vinculada
            try {
                $stmt = $pdo->query("
                    SELECT COUNT(*) as total
                    FROM atendido a
                    WHERE a.status = 'Ativo'
                      AND NOT EXISTS (
                          SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                      )
                      AND NOT EXISTS (
                          SELECT 1 FROM ficha_socioeconomico fs WHERE fs.id_atendido = a.idatendido
                      )
                ");
                $semSocio = (int)($stmt->fetchColumn() ?: 0);
                if ($semSocio > 0) {
                    $alertas[] = [
                        'id' => 'sem_socioeconomico',
                        'tipo' => 'warning',
                        'titulo' => 'Estudo Socioeconômico Pendente',
                        'mensagem' => "{$semSocio} atendido(s) sem Ficha Socioeconômica cadastrada",
                        'icone' => 'fa-file-invoice',
                        'link' => 'socioeconomico_list.php'
                    ];
                }
            } catch (Exception $e) {
                reportException($e, 'DashboardController::alertasSemSocio');
            }
            
            // 4. Fichas de Acolhimento com dados essenciais incompletos
            try {
                $stmt = $pdo->query("
                    SELECT COUNT(*) as total
                    FROM atendido a
                    WHERE a.status = 'Ativo'
                      AND NOT EXISTS (
                          SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                      )
                      AND (
                          a.id_responsavel IS NULL 
                          OR a.telefone IS NULL 
                          OR TRIM(a.telefone) = ''
                          OR a.endereco IS NULL 
                          OR TRIM(a.endereco) = ''
                      )
                ");
                $fichasIncompletas = (int)($stmt->fetchColumn() ?: 0);
                if ($fichasIncompletas > 0) {
                    $alertas[] = [
                        'id' => 'dados_incompletos',
                        'tipo' => 'warning',
                        'titulo' => 'Dados Incompletos',
                        'mensagem' => "{$fichasIncompletas} ficha(s) com dados essenciais incompletos",
                        'icone' => 'fa-id-card',
                        'link' => 'acolhimento_list.php'
                    ];
                }
            } catch (Exception $e) {
                reportException($e, 'DashboardController::alertasIncompletas');
            }
            
            // 5. Fichas Socioeconômicas com mais de 6 meses sem reavaliação
            try {
                $stmt = $pdo->query("
                    SELECT COUNT(*) as total
                    FROM ficha_socioeconomico fs
                    JOIN atendido a ON a.idatendido = fs.id_atendido
                    WHERE a.status = 'Ativo'
                      AND NOT EXISTS (
                          SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                      )
                      AND (
                          (fs.data_atualizacao IS NOT NULL AND fs.data_atualizacao < DATE_SUB(NOW(), INTERVAL 6 MONTH))
                          OR (fs.data_atualizacao IS NULL AND fs.data_criacao < DATE_SUB(NOW(), INTERVAL 6 MONTH))
                      )
                ");
                $fichasVencidas = (int)($stmt->fetchColumn() ?: 0);
                if ($fichasVencidas > 0) {
                    $alertas[] = [
                        'id' => 'revisao_socioeconomica',
                        'tipo' => 'info',
                        'titulo' => 'Revisão Socioeconômica',
                        'mensagem' => "{$fichasVencidas} ficha(s) socioeconômica(s) com mais de 6 meses para revisão",
                        'icone' => 'fa-sync-alt',
                        'link' => 'socioeconomico_list.php'
                    ];
                }
            } catch (Exception $e) {
                reportException($e, 'DashboardController::alertasVencidas');
            }
            } // fim if !== 'psicologo'
            
            if ($userRole === 'psicologo') {
            // 6. Atendimentos Psicológicos agendados para hoje
            try {
                $stmt = $pdo->query("
                    SELECT COUNT(*) as total
                    FROM anotacao_psicologica ap
                    JOIN atendido a ON a.idatendido = ap.id_atendido
                    WHERE a.status = 'Ativo'
                      AND ap.proxima_sessao = CURDATE()
                      AND NOT EXISTS (
                          SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                      )
                ");
                $sessoesHoje = (int)($stmt->fetchColumn() ?: 0);
                if ($sessoesHoje > 0) {
                    $alertas[] = [
                        'id' => 'sessoes_psicologicas',
                        'tipo' => 'info',
                        'titulo' => 'Atendimento Psicológico',
                        'mensagem' => "{$sessoesHoje} sessão(ões) psicológica(s) agendada(s) para hoje",
                        'icone' => 'fa-brain',
                        'link' => 'psychology.php'
                    ];
                }
            } catch (Exception $e) {
                // Tabela opcional ou não utilizada
            }
            } // fim if === 'psicologo'
            
            // 7. Aniversariantes do Mês (Com Ação de Abrir Modal)
            try {
                $mesAtual = (int)date('n');
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total
                    FROM atendido a
                    WHERE a.status = 'Ativo'
                      AND a.data_nascimento IS NOT NULL
                      AND a.data_nascimento != '0000-00-00'
                      AND MONTH(a.data_nascimento) = ?
                      AND NOT EXISTS (
                          SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                      )
                ");
                $stmt->execute([$mesAtual]);
                $aniversariantesMes = (int)($stmt->fetchColumn() ?: 0);
                if ($aniversariantesMes > 0) {
                    $mesesPt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                    $nomeMes = $mesesPt[$mesAtual] ?? 'este mês';
                    $alertas[] = [
                        'id' => 'aniversariantes_mes',
                        'tipo' => 'info',
                        'titulo' => 'Aniversariantes',
                        'mensagem' => "{$aniversariantesMes} atendido(s) fazem aniversário em {$nomeMes}",
                        'icone' => 'fa-birthday-cake',
                        'action' => 'open_birthday_modal',
                        'link' => ''
                    ];
                }
            } catch (Exception $e) {
                reportException($e, 'DashboardController::alertasAniversariantes');
            }
            
            // Alertas de sistema se tudo estiver limpo
            if (empty($alertas)) {
                $alertas[] = [
                    'id' => 'sistema_ok',
                    'tipo' => 'success',
                    'titulo' => 'Sistema Funcionando',
                    'mensagem' => 'Nenhum alerta prioritário pendente. Todas as rotinas em dia.',
                    'icone' => 'fa-check-circle'
                ];
            }
            
        } catch (Exception $e) {
            reportException($e, 'DashboardController::getAlertas');
            $alertas[] = [
                'id' => 'sistema_erro',
                'tipo' => 'error',
                'titulo' => 'Erro no Sistema',
                'mensagem' => 'Não foi possível carregar os alertas neste momento.',
                'icone' => 'fa-times-circle'
            ];
        }
        
        return $alertas;
    }

    /**
     * Obtém lista detalhada de aniversariantes do mês atual para exibição no modal
     */
    private function getAniversariantesDoMes() {
        try {
            $pdo = Database::getConnection();
            $mesAtual = (int)date('n');
            $stmt = $pdo->prepare("
                SELECT 
                    a.idatendido as id,
                    a.nome,
                    a.cpf,
                    a.data_nascimento,
                    a.foto,
                    a.telefone,
                    DAY(a.data_nascimento) as dia_aniversario
                FROM atendido a
                WHERE a.status = 'Ativo'
                  AND a.data_nascimento IS NOT NULL
                  AND a.data_nascimento != '0000-00-00'
                  AND MONTH(a.data_nascimento) = ?
                  AND NOT EXISTS (
                      SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                  )
                ORDER BY DAY(a.data_nascimento) ASC, a.nome ASC
            ");
            $stmt->execute([$mesAtual]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $aniversariantes = [];
            $hojeDia = (int)date('j');
            
            foreach ($rows as $row) {
                $nasc = $row['data_nascimento'];
                $dia = (int)($row['dia_aniversario'] ?? date('d', strtotime($nasc)));
                $anoNasc = (int)date('Y', strtotime($nasc));
                $idadeCompletando = (int)date('Y') - $anoNasc;
                
                $isHoje = ($dia === $hojeDia);
                $jaPassou = ($dia < $hojeDia);
                
                $cpfTrimmed = trim((string)($row['cpf'] ?? ''));
                $linkProntuario = ($cpfTrimmed !== '') 
                    ? 'prontuarios.php?action=show&cpf=' . urlencode($cpfTrimmed) . '&id=' . (int)$row['id']
                    : 'prontuarios.php?action=show&id=' . (int)$row['id'];

                $aniversariantes[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'cpf' => $cpfTrimmed,
                    'data_nascimento' => date('d/m/Y', strtotime($nasc)),
                    'dia' => $dia,
                    'dia_formatado' => sprintf('%02d', $dia),
                    'idade_completando' => $idadeCompletando,
                    'foto' => !empty($row['foto']) ? 'acolhimento_view.php?action=photo&id=' . (int)$row['id'] : null,
                    'telefone' => $row['telefone'] ?? null,
                    'is_hoje' => $isHoje,
                    'ja_passou' => $jaPassou,
                    'link_prontuario' => $linkProntuario
                ];
            }
            
            return $aniversariantes;
        } catch (Exception $e) {
            reportException($e, 'DashboardController::getAniversariantesDoMes');
            return [];
        }
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
            reportException($e, 'DashboardController::anotacoes');
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
            reportException($e, 'DashboardController::anotacoesMes');
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
