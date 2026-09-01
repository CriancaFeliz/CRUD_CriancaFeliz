<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller de autenticação
$authController = new AuthController();

// Processar logout
$authController->logout();
