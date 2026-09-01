<?php
/**
 * Sistema de Controle de Faltas
 */

require_once 'bootstrap.php';

// Instanciar controller
$controller = new FaltasController();

// Obter ação da URL
$action = $_GET['action'] ?? 'index';

// Mapear ações para métodos
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

// Executar ação
if (isset($actions[$action])) {
    $method = $actions[$action];
    
    // Se a ação precisa de ID
    if ($action === 'historico' && isset($_GET['id'])) {
        $controller->$method($_GET['id']);
    } else {
        $controller->$method();
    }
} else {
    // Ação não encontrada
    header('Location: faltas.php');
    exit;
}
