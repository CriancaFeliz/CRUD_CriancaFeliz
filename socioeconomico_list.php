<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller socioeconômico
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

// Verificar se é uma ação de exclusão
if (isset($_GET['delete'])) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['flash_error'] = 'Exclusão deve ser enviada por formulário seguro.';
        redirect('socioeconomico_list.php');
    }

    $socioeconomicoController->delete($_GET['delete']);
} else {
    // Exibir lista
    $socioeconomicoController->index();
}
