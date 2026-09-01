<?php

/**
 * Controller para gerenciar Logs do Sistema
 * Acesso exclusivo para Administradores
 */
class LogController extends BaseController {
    
    private $logModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->logModel = new LogDB();
        $this->userModel = new User();
        
        // Verificar se está autenticado
        $this->requireAuth();
        
        // Verificar se é administrador (usar sessão diretamente)
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole !== 'admin') {
            throw new Exception('❌ Acesso negado. Apenas administradores podem acessar o sistema de logs.');
        }
    }
    
    /**
     * Dashboard principal de logs
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        
        // Obter logs com paginação
        $logs = $this->logModel->getAllLogs($page, $perPage);
        
        // Obter estatísticas
        $stats = $this->logModel->getStatistics();
        
        // Contar logs por ação
        $acoes = ['INSERT' => 0, 'UPDATE' => 0, 'DELETE' => 0];
        foreach ($stats['por_acao'] as $item) {
            if (isset($acoes[$item['acao']])) {
                $acoes[$item['acao']] = $item['total'];
            }
        }
        
        $data = [
            'logs' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ],
            'stats' => $stats,
            'acoes' => $acoes,
            'usuarios' => $this->userModel->all()
        ];
        
        return view('logs/index', $data);
    }
    
    /**
     * Filtrar logs por tabela
     */
    public function byTable() {
        $table = $_GET['table'] ?? null;
        $page = $_GET['page'] ?? 1;
        
        if (!$table) {
            redirect('logs.php');
        }
        
        $logs = $this->logModel->getLogsByTable($table, $page, 50);
        
        $data = [
            'logs' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ],
            'filtro_tabela' => $table,
            'stats' => $this->logModel->getStatistics()
        ];
        
        return view('logs/by_table', $data);
    }
    
    /**
     * Filtrar logs por ação
     */
    public function byAction() {
        $action = $_GET['action'] ?? null;
        $page = $_GET['page'] ?? 1;
        
        if (!in_array($action, ['INSERT', 'UPDATE', 'DELETE'])) {
            redirect('logs.php');
        }
        
        $logs = $this->logModel->getLogsByAction($action, $page, 50);
        
        $data = [
            'logs' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ],
            'filtro_acao' => $action,
            'stats' => $this->logModel->getStatistics()
        ];
        
        return view('logs/by_action', $data);
    }
    
    /**
     * Filtrar logs por usuário
     */
    public function byUser() {
        $userId = $_GET['user_id'] ?? null;
        $page = $_GET['page'] ?? 1;
        
        if (!$userId) {
            redirect('logs.php');
        }
        
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $this->setFlash('error', 'Usuário não encontrado');
            redirect('logs.php');
        }
        
        $logs = $this->logModel->getLogsByUser($userId, $page, 50);
        
        $data = [
            'logs' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ],
            'filtro_usuario' => $user,
            'stats' => $this->logModel->getStatistics()
        ];
        
        return view('logs/by_user', $data);
    }
    
    /**
     * Histórico de um registro específico
     */
    public function historicoRegistro() {
        $registroId = $_GET['id'] ?? null;
        $page = $_GET['page'] ?? 1;
        
        if (!$registroId) {
            redirect('logs.php');
        }
        
        $logs = $this->logModel->getLogsByRegistroId($registroId, $page, 50);
        
        $data = [
            'logs' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ],
            'registro_id' => $registroId
        ];
        
        return view('logs/historico_registro', $data);
    }
    
    /**
     * Busca avançada de logs
     */
    public function search() {
        $page = $_GET['page'] ?? 1;
        
        $filters = [
            'tabela' => $_GET['tabela'] ?? null,
            'acao' => $_GET['acao'] ?? null,
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'busca' => $_GET['busca'] ?? null
        ];
        
        $logs = $this->logModel->searchAdvanced($filters, $page, 50);
        
        $data = [
            'logs' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ],
            'filters' => $filters,
            'usuarios' => $this->userModel->all(),
            'stats' => $this->logModel->getStatistics()
        ];
        
        return view('logs/search', $data);
    }
    
    /**
     * Visualizar detalhes de um log
     */
    public function show() {
        $logId = $_GET['id'] ?? null;
        
        if (!$logId) {
            redirect('logs.php');
        }
        
        $log = $this->logModel->findById($logId);
        
        if (!$log) {
            $this->setFlash('error', 'Log não encontrado');
            redirect('logs.php');
        }
        
        // Obter informações do usuário
        $usuario = null;
        if ($log['id_usuario']) {
            $usuario = $this->userModel->findById($log['id_usuario']);
        }
        
        $data = [
            'log' => $log,
            'usuario' => $usuario
        ];
        
        return view('logs/show', $data);
    }
    
    /**
     * Exportar logs em CSV
     */
    public function export() {
        $filters = [
            'tabela' => $_GET['tabela'] ?? null,
            'acao' => $_GET['acao'] ?? null,
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'busca' => $_GET['busca'] ?? null
        ];
        
        $csv = $this->logModel->exportToCSV($filters);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="logs_' . date('Y-m-d_H-i-s') . '.csv"');
        
        echo "\xEF\xBB\xBF"; // BOM para UTF-8
        echo $csv;
        exit;
    }
    
    /**
     * Limpar logs antigos
     */
    public function deleteOld() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('logs.php');
        }
        
        $days = (int)($_POST['days'] ?? 90);
        
        if ($days < 30) {
            $this->setFlash('error', 'Mínimo de 30 dias para limpeza de logs');
            redirect('logs.php');
        }
        
        $deleted = $this->logModel->deleteOldLogs($days);
        
        if ($deleted) {
            $this->setFlash('success', "Logs com mais de $days dias foram removidos");
        } else {
            $this->setFlash('error', 'Erro ao remover logs antigos');
        }
        
        redirect('logs.php');
    }
    
    /**
     * API: Obter logs em JSON
     */
    public function apiGetLogs() {
        header('Content-Type: application/json');
        
        $page = $_GET['page'] ?? 1;
        $perPage = (int)($_GET['per_page'] ?? 50);
        
        $logs = $this->logModel->getAllLogs($page, $perPage);
        
        echo json_encode([
            'success' => true,
            'data' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ]
        ]);
        exit;
    }
    
    /**
     * API: Busca avançada em JSON
     */
    public function apiSearch() {
        header('Content-Type: application/json');
        
        $page = $_GET['page'] ?? 1;
        $perPage = (int)($_GET['per_page'] ?? 50);
        
        $filters = [
            'tabela' => $_GET['tabela'] ?? null,
            'acao' => $_GET['acao'] ?? null,
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'busca' => $_GET['busca'] ?? null
        ];
        
        $logs = $this->logModel->searchAdvanced($filters, $page, $perPage);
        
        echo json_encode([
            'success' => true,
            'data' => $logs['data'],
            'pagination' => [
                'current_page' => $logs['current_page'],
                'last_page' => $logs['last_page'],
                'total' => $logs['total'],
                'per_page' => $logs['per_page']
            ]
        ]);
        exit;
    }
    
    /**
     * API: Obter estatísticas
     */
    public function apiStats() {
        header('Content-Type: application/json');
        
        $stats = $this->logModel->getStatistics();
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        exit;
    }
}
