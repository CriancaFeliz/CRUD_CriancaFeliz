<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller do dashboard
$dashboardController = new DashboardController();

// Verificar ação
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
