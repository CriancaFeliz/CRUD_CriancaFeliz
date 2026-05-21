<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Verificar se usuário está logado
if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller de usuários
$userController = new UserController();

// Obter ação
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Roteamento
try {
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
} catch (Exception $e) {
    // Log do erro
    error_log("Erro em users.php: " . $e->getMessage());
    
    // Redirecionar com erro
    $_SESSION['flash_error'] = $e->getMessage();
    redirect('users.php');
}
