<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller de autenticação
$authController = new AuthController();

// Verificar se é POST (processar login) ou GET (exibir formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->processLogin();
} else {
    $authController->showLogin();
}
