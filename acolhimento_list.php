<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller de acolhimento
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

// Verificar se é uma ação de exclusão
if (isset($_GET['delete'])) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['flash_error'] = 'Exclusão deve ser enviada por formulário seguro.';
        redirect('acolhimento_list.php');
    }

    $acolhimentoController->delete($_GET['delete']);
} else {
    // Exibir lista com layout padrão (menu lateral etc.)
    $acolhimentoController->index();
}
