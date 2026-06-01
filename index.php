<?php
/**
 * Front Controller e Roteador Central - Criança Feliz
 * Único ponto de entrada para a aplicação.
 */

// Carregar o bootstrap (que inicializa sessões, autoloader, constantes, etc.)
require_once __DIR__ . '/app/bootstrap.php';

// Obter a URI da requisição
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Remover a query string (ex: ?action=index)
$uriParts = explode('?', $requestUri);
$path = $uriParts[0];

// Determinar o caminho relativo caso o sistema esteja rodando em um subdiretório
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = dirname($scriptName);
$baseDir = str_replace('\\', '/', $baseDir);
if ($baseDir !== '/') {
    $baseDir = rtrim($baseDir, '/');
    if (strpos($path, $baseDir) === 0) {
        $path = substr($path, strlen($baseDir));
    }
}

// Limpar a rota para comparação
$route = trim($path, '/');

// Roteamento de páginas públicas (não exigem login)
if (empty($route) || in_array($route, ['', 'index.php', 'index', 'login', 'login.php'])) {
    $authController = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $authController->processLogin();
    } else {
        $authController->showLogin();
    }
    exit;
}

if (in_array($route, ['forgot', 'forgot.php'])) {
    $authController = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $authController->processForgotPassword();
    } else {
        $authController->showForgotPassword();
    }
    exit;
}

if (in_array($route, ['reset_password', 'reset_password.php'])) {
    $authController = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $authController->processResetPassword();
    } else {
        $authController->showResetPassword();
    }
    exit;
}

// Para todas as outras rotas, exige autenticação
if (!isLoggedIn()) {
    redirect('index.php');
}

// Roteamento das páginas protegidas
try {
    switch ($route) {
        case 'logout':
        case 'logout.php':
            $authController = new AuthController();
            $authController->logout();
            break;

        case 'dashboard':
        case 'dashboard.php':
            $dashboardController = new DashboardController();
            $action = $_GET['action'] ?? 'index';
            switch ($action) {
                case 'getCalendarNotes':
                    $dashboardController->getCalendarNotes();
                    break;
                case 'saveCalendarNote':
                    $dashboardController->saveCalendarNote();
                    break;
                case 'deleteCalendarNote':
                    $dashboardController->deleteCalendarNote();
                    break;
                default:
                    $dashboardController->index();
                    break;
            }
            break;

        case 'prontuarios':
        case 'prontuarios.php':
            $prontuarioController = new ProntuarioController();
            $prontuarioController->index();
            break;

        case 'attendance':
        case 'attendance.php':
            $action = $_GET['action'] ?? 'index';
            $id = $_GET['id'] ?? null;
            switch ($action) {
                case 'show':
                    if (!$id) {
                        throw new Exception('ID do atendido é obrigatório');
                    }
                    redirect('faltas.php?action=historico&id=' . urlencode($id));
                    break;
                case 'desligamento':
                    if (!$id) {
                        throw new Exception('ID do atendido é obrigatório');
                    }
                    redirect('desligamento.php?action=novo&id=' . urlencode($id));
                    break;
                case 'alertas':
                    redirect('faltas.php?action=alertas');
                    break;
                case 'index':
                case 'batch':
                default:
                    redirect('faltas.php');
                    break;
            }
            break;

        case 'faltas':
        case 'faltas.php':
            $controller = new FaltasController();
            $action = $_GET['action'] ?? 'index';
            $actions = [
                'index' => 'index',
                'oficina' => 'oficina',
                'historico' => 'historico',
                'alertas' => 'alertas',
                'salvarDia' => 'salvarDia',
                'salvarOficina' => 'salvarOficina',
                'gerenciarOficinas' => 'gerenciarOficinas',
                'salvarOficinaConfig' => 'salvarOficinaConfig',
                'toggleOficina' => 'toggleOficina'
            ];
            if (isset($actions[$action])) {
                $method = $actions[$action];
                if ($action === 'historico' && isset($_GET['id'])) {
                    $controller->$method($_GET['id']);
                } else {
                    $controller->$method();
                }
            } else {
                header('Location: faltas.php');
                exit;
            }
            break;

        case 'desligamento':
        case 'desligamento.php':
            // Iniciar output buffering para esta rota específica
            ob_start();
            $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $controller = new DesligamentoController();
            $action = $_GET['action'] ?? 'index';
            $actions = [
                'index' => 'index',
                'novo' => 'novo',
                'salvar' => 'salvar',
                'reativar' => 'reativar',
                'automatico' => 'automatico'
            ];
            if (isset($actions[$action])) {
                $method = $actions[$action];
                if ($action === 'novo' && isset($_GET['id'])) {
                    $controller->$method($_GET['id']);
                } else {
                    $controller->$method();
                }
            } else {
                if ($isAjaxRequest) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Ação não encontrada']);
                    exit;
                } else {
                    header('Location: desligamento.php');
                    exit;
                }
            }
            break;

        case 'psychology':
        case 'psychology.php':
            $psychologyController = new PsychologyController();
            $action = $_GET['action'] ?? 'index';
            $cpf = $_GET['cpf'] ?? null;
            $id = $_GET['id'] ?? null;
            switch ($action) {
                case 'index':
                    $psychologyController->index();
                    break;
                case 'patients':
                    $psychologyController->patients();
                    break;
                case 'patient':
                    if (!$cpf) {
                        throw new Exception('CPF do paciente é obrigatório');
                    }
                    $psychologyController->patient($cpf);
                    break;
                case 'save_note':
                    $psychologyController->saveNote();
                    break;
                case 'get_note':
                    $psychologyController->getNote();
                    break;
                case 'update_note':
                    $psychologyController->updateNote();
                    break;
                case 'save_assessment':
                    $psychologyController->saveAssessment();
                    break;
                case 'delete_note':
                    if (!$id) {
                        throw new Exception('ID da anotação é obrigatório');
                    }
                    $psychologyController->deleteNote($id);
                    break;
                case 'search':
                    $psychologyController->search();
                    break;
                case 'report':
                    $psychologyController->report();
                    break;
                default:
                    $psychologyController->index();
                    break;
            }
            break;

        case 'users':
        case 'users.php':
            $userController = new UserController();
            $action = $_GET['action'] ?? 'index';
            $id = $_GET['id'] ?? null;
            switch ($action) {
                case 'index':
                    $userController->index();
                    break;
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $userController->store();
                    } else {
                        $userController->create();
                    }
                    break;
                case 'edit':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $userController->update($id);
                    } else {
                        $userController->edit($id);
                    }
                    break;
                case 'delete':
                    $userController->delete($id);
                    break;
                case 'toggle_status':
                    $userController->toggleStatus($id);
                    break;
                default:
                    $userController->index();
                    break;
            }
            break;

        case 'logs':
        case 'logs.php':
            // Configurar variáveis de sessão MySQL para logs
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                try {
                    $pdo = Database::getConnection();
                    $pdo->exec("SET @usuario_id = " . intval($userId));
                    $stmt = $pdo->prepare("SET @ip_usuario = ?");
                    $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);
                } catch (Exception $e) {
                    // Silenciosamente ignorar
                }
            }
            $controller = new LogController();
            $action = $_GET['action'] ?? 'index';
            switch ($action) {
                case 'by_table':
                    $controller->byTable();
                    break;
                case 'by_action':
                    $controller->byAction();
                    break;
                case 'by_user':
                    $controller->byUser();
                    break;
                case 'historico':
                    $controller->historicoRegistro();
                    break;
                case 'search':
                    $controller->search();
                    break;
                case 'show':
                    $controller->show();
                    break;
                case 'export':
                    $controller->export();
                    break;
                case 'delete_old':
                    $controller->deleteOld();
                    break;
                case 'api_logs':
                    $controller->apiGetLogs();
                    break;
                case 'api_search':
                    $controller->apiSearch();
                    break;
                case 'api_stats':
                    $controller->apiStats();
                    break;
                default:
                    $controller->index();
            }
            break;

        case 'profile':
        case 'profile.php':
            $profileController = new ProfileController();
            $action = $_GET['action'] ?? 'index';
            switch ($action) {
                case 'updatePhoto':
                    $profileController->updatePhoto();
                    break;
                case 'updatePassword':
                    $profileController->updatePassword();
                    break;
                default:
                    $profileController->index();
                    break;
            }
            break;

        case 'acolhimento/form':
        case 'acolhimento_form':
        case 'acolhimento_form.php':
            $acolhimentoController = new AcolhimentoController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $acolhimentoController->store();
            } else {
                $acolhimentoController->create();
            }
            break;

        case 'acolhimento':
        case 'acolhimento/list':
        case 'acolhimento_list':
        case 'acolhimento_list.php':
            $acolhimentoController = new AcolhimentoController();
            $action = $_GET['action'] ?? 'index';
            if ($action === 'export') {
                $acolhimentoController->export();
                exit;
            }
            if ($action === 'stats') {
                $acolhimentoController->stats();
                exit;
            }
            if (isset($_GET['delete'])) {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $_SESSION['flash_error'] = 'Exclusão deve ser enviada por formulário seguro.';
                    redirect('acolhimento_list.php');
                }
                $acolhimentoController->delete($_GET['delete']);
            } else {
                $acolhimentoController->index();
            }
            break;

        case 'acolhimento/search':
        case 'acolhimento_search':
        case 'acolhimento_search.php':
            $controller = new AcolhimentoController();
            $controller->search();
            break;

        case 'acolhimento/view':
        case 'acolhimento_view':
        case 'acolhimento_view.php':
            $acolhimentoController = new AcolhimentoController();
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                redirect('acolhimento_list.php');
            }
            $acolhimentoController->show($id);
            break;

        case 'socioeconomico/form':
        case 'socioeconomico_form':
        case 'socioeconomico_form.php':
            $socioeconomicoController = new SocioeconomicoController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $socioeconomicoController->store();
            } else {
                $socioeconomicoController->create();
            }
            break;

        case 'socioeconomico':
        case 'socioeconomico/list':
        case 'socioeconomico_list':
        case 'socioeconomico_list.php':
            $socioeconomicoController = new SocioeconomicoController();
            $action = $_GET['action'] ?? 'index';
            if ($action === 'export') {
                $socioeconomicoController->export();
                exit;
            }
            if ($action === 'stats') {
                $socioeconomicoController->stats();
                exit;
            }
            if ($action === 'report') {
                $socioeconomicoController->report();
                exit;
            }
            if (isset($_GET['delete'])) {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $_SESSION['flash_error'] = 'Exclusão deve ser enviada por formulário seguro.';
                    redirect('socioeconomico_list.php');
                }
                $socioeconomicoController->delete($_GET['delete']);
            } else {
                $socioeconomicoController->index();
            }
            break;

        case 'socioeconomico/view':
        case 'socioeconomico_view':
        case 'socioeconomico_view.php':
            $socioeconomicoController = new SocioeconomicoController();
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                redirect('socioeconomico_list.php');
            }
            $socioeconomicoController->show($id);
            break;

        default:
            // Rota não encontrada. Redirecionar para home/dashboard se logado, se não, login.
            if (isLoggedIn()) {
                redirect('dashboard.php');
            } else {
                redirect('index.php');
            }
            break;
    }
} catch (Exception $e) {
    // Tratamento genérico de exceções nas rotas
    error_log("Erro de Rota [{$route}]: " . $e->getMessage());
    
    // Resposta baseada no tipo de requisição (AJAX vs Normal)
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        $_SESSION['flash_error'] = 'Ocorreu um erro ao processar sua requisição: ' . $e->getMessage();
        
        // Evitar loop infinito se falhar na própria página de erro / dashboard
        if ($route !== 'dashboard' && $route !== 'dashboard.php') {
            redirect('dashboard.php');
        } else {
            echo "<h1>Erro Crítico</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
