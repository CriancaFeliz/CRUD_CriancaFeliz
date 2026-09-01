<?php

/**
 * Controller para fichas de acolhimento
 */
class AcolhimentoController extends BaseController {
    private $acolhimentoService;
    
    public function __construct() {
        parent::__construct();
        $this->acolhimentoService = new AcolhimentoService();
    }
    
    /**
     * Lista fichas de acolhimento
     */
    public function index() {
        $this->requireAuth();
        
        try {
            $page = intval($this->getParam('page', 1));
            $perPage = 10;
            
            $result = $this->acolhimentoService->listFichas($page, $perPage);
            
            // Adicionar dados calculados
            foreach ($result['data'] as &$ficha) {
                $ficha['idade'] = $this->calculateAge($ficha['data_nascimento'] ?? '');
                $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
            }
            
            $data = [
                'title' => 'Fichas de Acolhimento',
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
                'messages' => $this->getFlashMessages(),
                'csrf_token' => $this->generateCSRF()
            ];
            
            $this->renderWithLayout('main', 'acolhimento/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    /**
     * Exibe formulário de criação/edição
     */
    public function create() {
        $this->requireAuth();
        $this->requirePermission('create_records');
        
        // Verificar se é edição
        $editId = $_GET['id'] ?? null;
        $ficha = null;
        
        if ($editId) {
            try {
                $ficha = $this->acolhimentoService->getFicha($editId);
            } catch (Exception $e) {
                $this->redirectWithError('acolhimento_list.php', 'Ficha não encontrada');
                return;
            }
        }
        
        $data = [
            'title' => $editId ? 'Editar Ficha de Acolhimento' : 'Cadastrar Ficha de Acolhimento',
            'pageTitle' => $editId ? 'Editar Ficha de Acolhimento' : 'Nova Ficha de Acolhimento',
            'csrf_token' => $this->generateCSRF(),
            'messages' => $this->getFlashMessages(),
            'ficha' => $ficha,
            'editId' => $editId
        ];
        
        $this->renderWithLayout('main', 'acolhimento/create', $data);
    }
    
    /**
     * Processa criação/edição da ficha
     */
    public function store() {
        $this->requireAuth();
        $this->requirePermission('create_records');
        
        if (!$this->isPost()) {
            redirect('acolhimento_form.php');
        }
        
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            
            // Verificar se é edição
            $id = $data['id'] ?? null;
            
            error_log('=== ACOLHIMENTO STORE ===');
            error_log('ID recebido: ' . ($id ?? 'NENHUM'));
            error_log('Dados POST: ' . json_encode($data));
            
            // Upload de foto se fornecida
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = $this->uploadFile('foto', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
            }
            
            if (!empty($id)) {
                // EDIÇÃO
                error_log('EDITANDO ficha ID: ' . $id);
                $ficha = $this->acolhimentoService->updateFicha($id, $data);
                $this->redirectWithSuccess('acolhimento_list.php', 'Ficha de acolhimento atualizada com sucesso!');
                return; // IMPORTANTE: Para execução aqui
            } else {
                // CRIAÇÃO
                error_log('CRIANDO nova ficha');
                $ficha = $this->acolhimentoService->createFicha($data);
                $this->redirectWithSuccess('acolhimento_list.php', 'Ficha de acolhimento cadastrada com sucesso!');
                return;
            }
            
        } catch (Exception $e) {
            error_log('ERRO no store: ' . $e->getMessage());
            // Se estava editando, manter o id na URL para voltar ao modo edição
            $backId = $_POST['id'] ?? null;
            $target = 'acolhimento_form.php' . ($backId ? ('?id=' . urlencode($backId)) : '');
            $this->redirectWithError($target, $e->getMessage());
        }
    }
    
    /**
     * Exibe ficha específica
     */
    public function show($id) {
        $this->requireAuth();
        
        try {
            $ficha = $this->acolhimentoService->getFicha($id);
            
            $data = [
                'title' => 'Visualizar Ficha de Acolhimento',
                'ficha' => $ficha
            ];
            
            $this->renderWithLayout('main', 'acolhimento/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit($id) {
        $this->requireAuth();
        $this->requirePermission('edit_records');
        
        try {
            $ficha = $this->acolhimentoService->getFicha($id);
            
            $data = [
                'title' => 'Editar Ficha de Acolhimento',
                'ficha' => $ficha,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'acolhimento/edit', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Processa atualização da ficha
     */
    public function update($id) {
        $this->requireAuth();
        $this->requirePermission('edit_records');
        
        if (!$this->isPost()) {
            redirect("acolhimento_view.php?id=$id");
        }
        
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            
            // Upload de nova foto se fornecida
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = $this->uploadFile('foto', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
            }
            
            $ficha = $this->acolhimentoService->updateFicha($id, $data);
            
            $this->redirectWithSuccess('acolhimento_list.php', 'Ficha de acolhimento atualizada com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError("acolhimento_view.php?id=$id", $e->getMessage());
        }
    }
    
    /**
     * Exclui ficha
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requirePermission('delete_records');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $result = $this->acolhimentoService->deleteFicha($id);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => 'Ficha excluída com sucesso']);
            } else {
                $this->redirectWithSuccess('acolhimento_list.php', 'Ficha excluída com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('acolhimento_list.php', $e->getMessage());
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
            
            $results = $this->acolhimentoService->searchFichas($query, $filters);
            
            // Formatar resultados para JSON
            $formattedResults = [];
            foreach ($results as $ficha) {
                $formattedResults[] = [
                    'id' => $ficha['id'],
                    'nome_completo' => $ficha['nome_completo'] ?? '',
                    'cpf' => $this->formatCPF($ficha['cpf'] ?? ''),
                    'rg' => $this->formatRG($ficha['rg'] ?? ''),
                    'idade' => $ficha['idade'],
                    'categoria' => $ficha['categoria'],
                    'responsavel' => $ficha['nome_responsavel'] ?? '',
                    'contato' => $this->formatPhone($ficha['contato_1'] ?? ''),
                    'status' => $ficha['status'] ?? 'Ativo',
                    'data_acolhimento' => $ficha['data_acolhimento'] ?? ''
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
            $csv = $this->acolhimentoService->exportToCSV($filters);
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="fichas_acolhimento_' . date('Y-m-d') . '.csv"');
            
            echo "\xEF\xBB\xBF"; // BOM para UTF-8
            echo $csv;
            exit;
            
        } catch (Exception $e) {
            $this->redirectWithError('acolhimento_list.php', $e->getMessage());
        }
    }
    
    /**
     * Obtém estatísticas
     */
    public function stats() {
        $this->requireAuth();
        
        try {
            $stats = $this->acolhimentoService->getStatistics();
            $this->json($stats);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
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
     * Categoriza por idade
     */
    private function categorizeByAge($age) {
        if ($age === null) return 'Indefinido';
        if ($age < 12) return 'Criança';
        if ($age < 18) return 'Adolescente';
        return 'Adulto';
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
    
    /**
     * Formata telefone
     */
    private function formatPhone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
        } elseif (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6, 4);
        }
        return $phone;
    }
}
