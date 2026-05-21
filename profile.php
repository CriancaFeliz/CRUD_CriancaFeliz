<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller de perfil
$profileController = new ProfileController();

// Verificar ação
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
