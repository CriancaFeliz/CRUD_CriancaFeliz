<?php
/**
 * Sistema de Logs - Criança Feliz
 * Página principal de gerenciamento de logs
 * Apenas administradores têm acesso
 */

require_once 'bootstrap.php';

// Verificar se usuário está logado
if (!isLoggedIn()) {
    redirect('index.php');
}

// Passar ID do usuário para os triggers MySQL
$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    // Conectar ao banco e definir variável de sessão MySQL
    try {
        $pdo = Database::getConnection();
        $pdo->exec("SET @usuario_id = " . intval($userId));
        $pdo->exec("SET @ip_usuario = '" . $_SERVER['REMOTE_ADDR'] . "'");
    } catch (Exception $e) {
        // Silenciosamente ignorar se não conseguir
    }
}

// Instanciar controller (ele fará a verificação de admin)
try {
    $controller = new LogController();
} catch (Exception $e) {
    die($e->getMessage());
}

// Obter ação
$action = $_GET['action'] ?? 'index';

// Rotear para método apropriado
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
