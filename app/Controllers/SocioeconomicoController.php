<?php

/**
 * Controller para fichas socioeconômicas
 */
class SocioeconomicoController extends BaseController {
    private $socioeconomicoService;
    
    public function __construct() {
        parent::__construct();
        $this->socioeconomicoService = new SocioeconomicoService();
    }
    
    /**
     * Lista fichas socioeconômicas
     */
    public function index() {
        $this->requireAuth();
        
        try {
            $page = intval($this->getParam('page', 1));
            $perPage = 10;
            
            $result = $this->socioeconomicoService->listFichas($page, $perPage);
            
            // Adicionar dados calculados
            foreach ($result['data'] as &$ficha) {
                $ficha['idade'] = $this->calculateAge($ficha['data_nascimento'] ?? '');
                $ficha['renda_familiar'] = $this->calculateRendaFamiliar($ficha);
                $ficha['situacao_economica'] = $this->categorizeSituacao(
                    $ficha['renda_familiar'], 
                    intval($ficha['numero_membros'] ?? 1)
                );
            }
            
            $data = [
                'title' => 'Fichas Socioeconômicas',
                'fichas' => $result['data'] ?? [],
                'pagination' => [
                    'current_page' => $result['current_page'] ?? 1,
                    'last_page' => $result['last_page'] ?? 1,
                    'total' => $result['total'] ?? 0,
                    'per_page' => $result['per_page'] ?? 10
                ],
                'current_page' => $result['current_page'] ?? 1,
                'last_page' => $result['last_page'] ?? 1,
                'per_page' => $result['per_page'] ?? 10,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'socioeconomico/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe formulário de criação (multi-step)
     */
    public function create() {
        $this->requireAuth();
        $this->requirePermission('create_records');
        
        // Verificar se é edição
        $id = $this->getParam('id');
        $ficha = null;
        
        if ($id) {
            try {
                $ficha = $this->socioeconomicoService->getFicha($id);
            } catch (Exception $e) {
                $this->redirectWithError('socioeconomico_list.php', 'Ficha não encontrada');
                return;
            }
        }
        
        $data = [
            'title' => $ficha ? 'Editar Ficha Socioeconômica' : 'Cadastrar Ficha Socioeconômica',
            'csrf_token' => $this->generateCSRF(),
            'messages' => $this->getFlashMessages(),
            'ficha' => $ficha
        ];
        
        $this->renderWithLayout('main', 'socioeconomico/create_multistep', $data);
    }
    
    /**
     * Processa criação ou atualização da ficha
     */
    public function store() {
        $this->requireAuth();
        $this->requirePermission('create_records');
        
        if (!$this->isPost()) {
            redirect('socioeconomico_form.php');
        }
        
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            
            // Debug: Log dos dados recebidos
            error_log('=== SOCIOECONOMICO STORE ===');
            error_log('ID recebido: ' . ($data['id'] ?? 'NENHUM'));
            
            // Log detalhado dos dados recebidos
            error_log('=== DADOS RECEBIDOS NO CONTROLLER ===');
            error_log('Keys presentes: ' . implode(', ', array_keys($data)));
            error_log('despesas_json presente: ' . (isset($data['despesas_json']) && !empty($data['despesas_json']) ? 'SIM (' . strlen($data['despesas_json']) . ' chars)' : 'NÃO'));
            error_log('familia_json presente: ' . (isset($data['familia_json']) && !empty($data['familia_json']) ? 'SIM (' . strlen($data['familia_json']) . ' chars)' : 'NÃO'));
            
            // Decodificar JSON de despesas e família ANTES de enviar ao service
            if (!empty($data['despesas_json'])) {
                error_log('Decodificando despesas_json no controller...');
                error_log('Conteúdo raw: ' . substr($data['despesas_json'], 0, 300));
                $despesasDecoded = json_decode($data['despesas_json'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($despesasDecoded)) {
                    $data['despesas'] = $despesasDecoded;
                    error_log('✅ Despesas decodificadas no controller: ' . count($despesasDecoded) . ' itens');
                } else {
                    error_log('❌ ERRO ao decodificar despesas_json no controller: ' . json_last_error_msg());
                    error_log('   JSON Error Code: ' . json_last_error());
                    $data['despesas'] = [];
                }
            } else {
                error_log('⚠️ despesas_json está vazio ou não existe');
            }
            
            if (!empty($data['familia_json'])) {
                error_log('Decodificando familia_json no controller...');
                error_log('Conteúdo raw: ' . substr($data['familia_json'], 0, 300));
                $familiaDecoded = json_decode($data['familia_json'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($familiaDecoded)) {
                    $data['familia'] = $familiaDecoded;
                    error_log('✅ Família decodificada no controller: ' . count($familiaDecoded) . ' membros');
                } else {
                    error_log('❌ ERRO ao decodificar familia_json no controller: ' . json_last_error_msg());
                    error_log('   JSON Error Code: ' . json_last_error());
                    $data['familia'] = [];
                }
            } else {
                error_log('⚠️ familia_json está vazio ou não existe');
            }
            
            // Garantir que arrays estão presentes mesmo se vazios
            if (!isset($data['despesas'])) {
                $data['despesas'] = [];
            }
            if (!isset($data['familia'])) {
                $data['familia'] = [];
            }
            
            error_log('Dados finais preparados - despesas: ' . count($data['despesas']) . ', familia: ' . count($data['familia']));
            error_log('==========================================');
            
            error_log('Dados preparados: ' . print_r(array_keys($data), true));
            
            // Verificar se é edição ou criação
            if (!empty($data['id'])) {
                // Edição
                $id = $data['id'];
                unset($data['id']); // Remover ID dos dados
                error_log('EDITANDO ficha ID: ' . $id);
                $ficha = $this->socioeconomicoService->updateFicha($id, $data);
                $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha socioeconômica atualizada com sucesso!');
                return; // IMPORTANTE: Parar execução aqui
            } else {
                // Criação
                error_log('CRIANDO nova ficha');
                $ficha = $this->socioeconomicoService->createFicha($data);
                $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha socioeconômica cadastrada com sucesso!');
                return; // IMPORTANTE: Parar execução aqui
            }
            
        } catch (Exception $e) {
            error_log('ERRO: ' . $e->getMessage());
            $this->redirectWithError('socioeconomico_form.php', $e->getMessage());
        }
    }
    
    /**
     * Exibe ficha específica
     */
    public function show($id) {
        $this->requireAuth();
        
        try {
            $ficha = $this->socioeconomicoService->getFicha($id);
            
            $data = [
                'title' => 'Visualizar Ficha Socioeconômica',
                'ficha' => $ficha
            ];
            
            $this->renderWithLayout('main', 'socioeconomico/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit($id) {
        $this->requireAuth();
        
        try {
            $ficha = $this->socioeconomicoService->getFicha($id);
            
            $data = [
                'title' => 'Editar Ficha Socioeconômica',
                'ficha' => $ficha,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'socioeconomico/edit', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Processa atualização da ficha
     */
    public function update($id) {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            redirect("socioeconomico_view.php?id=$id");
        }
        
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            
            $ficha = $this->socioeconomicoService->updateFicha($id, $data);
            
            $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha socioeconômica atualizada com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError("socioeconomico_view.php?id=$id", $e->getMessage());
        }
    }
    
    /**
     * Exclui ficha
     */
    public function delete($id) {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $result = $this->socioeconomicoService->deleteFicha($id);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => 'Ficha excluída com sucesso']);
            } else {
                $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha excluída com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('socioeconomico_list.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Busca fichas
     */
    public function search() {
        $this->requireAuth();
        
        try {
            $query = $this->getParam('q', '');
            $filters = $this->getGetData();
            
            if (empty($query)) {
                $this->json([]);
                return;
            }
            
            $results = $this->socioeconomicoService->searchFichas($query, $filters);
            
            // Formatar resultados para JSON
            $formattedResults = [];
            foreach ($results as $ficha) {
                $formattedResults[] = [
                    'id' => $ficha['id'],
                    'nome_completo' => $ficha['nome_completo'] ?? '',
                    'cpf' => $this->formatCPF($ficha['cpf'] ?? ''),
                    'rg' => $this->formatRG($ficha['rg'] ?? ''),
                    'idade' => $ficha['idade'],
                    'renda_familiar' => number_format($ficha['renda_familiar'], 2, ',', '.'),
                    'situacao_economica' => $ficha['situacao_economica'],
                    'numero_membros' => $ficha['numero_membros'] ?? 1,
                    'status' => $ficha['status'] ?? 'Ativo'
                ];
            }
            
            $this->json($formattedResults);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Exporta fichas para CSV
     */
    public function export() {
        $this->requireAuth();
        
        try {
            $filters = $this->getGetData();
            $csv = $this->socioeconomicoService->exportToCSV($filters);
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="fichas_socioeconomico_' . date('Y-m-d') . '.csv"');
            
            echo "\xEF\xBB\xBF"; // BOM para UTF-8
            echo $csv;
            exit;
            
        } catch (Exception $e) {
            $this->redirectWithError('socioeconomico_list.php', $e->getMessage());
        }
    }
    
    /**
     * Obtém estatísticas
     */
    public function stats() {
        $this->requireAuth();
        
        try {
            $stats = $this->socioeconomicoService->getStatistics();
            $this->json($stats);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Gera relatório socioeconômico
     */
    public function report() {
        $this->requireAuth();
        
        try {
            $filters = $this->getGetData();
            $report = $this->socioeconomicoService->generateReport($filters);
            
            if ($this->isAjaxRequest()) {
                $this->json($report);
            } else {
                $data = [
                    'title' => 'Relatório Socioeconômico',
                    'report' => $report,
                    'filters' => $filters
                ];
                
                $this->renderWithLayout('main', 'socioeconomico/report', $data);
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 500);
            } else {
                $this->handleException($e);
            }
        }
    }
    
    /**
     * Calcula idade
     */
    private function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) {
            return null;
        }
        
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
     * Calcula renda familiar
     */
    private function calculateRendaFamiliar($data) {
        $renda = 0;
        
        // Tentar calcular a partir do JSON da família
        if (!empty($data['familia_json'])) {
            $familia = json_decode($data['familia_json'], true);
            if (is_array($familia)) {
                foreach ($familia as $membro) {
                    if (!empty($membro['renda'])) {
                        $renda += floatval(str_replace(['.', ','], ['', '.'], $membro['renda']));
                    }
                }
            }
        }
        
        // Fallback: tentar calcular a partir de campos individuais
        if ($renda == 0) {
            for ($i = 1; $i <= 10; $i++) {
                $rendaMembro = $data["renda_membro_$i"] ?? 0;
                $renda += floatval(str_replace(['.', ','], ['', '.'], $rendaMembro));
            }
        }
        
        return $renda;
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
     * Formata CPF
     */
    private function formatCPF($cpf) {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }
    
    /**
     * Formata RG
     */
    private function formatRG($rg) {
        $rg = preg_replace('/\D/', '', $rg);
        if (strlen($rg) === 9) {
            return substr($rg, 0, 2) . '.' . substr($rg, 2, 3) . '.' . substr($rg, 5, 3) . '-' . substr($rg, 8, 1);
        }
        return $rg;
    }
}
