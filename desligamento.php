<?php
/**
 * Sistema de Desligamento
 */

// Iniciar output buffering ANTES de qualquer output
ob_start();

require_once 'bootstrap.php';

// Verificar se é AJAX ANTES de instanciar controller
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Instanciar controller
$controller = new DesligamentoController();

// Obter ação da URL
$action = $_GET['action'] ?? 'index';

// Mapear ações para métodos
$actions = [
    'index' => 'index',
    'novo' => 'novo',
    'salvar' => 'salvar',
    'reativar' => 'reativar',
    'automatico' => 'automatico'
];

// Executar ação
if (isset($actions[$action])) {
    $method = $actions[$action];
    
    // Se a ação precisa de ID
    if ($action === 'novo' && isset($_GET['id'])) {
        $controller->$method($_GET['id']);
    } else {
        $controller->$method();
    }
} else {
    // Ação não encontrada
    if ($isAjaxRequest) {
        // Retornar JSON para AJAX
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Ação não encontrada']);
        exit;
    } else {
        // Redirect para requisições normais
        header('Location: desligamento.php');
        exit;
    }
}
