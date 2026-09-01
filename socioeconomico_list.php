<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller socioeconômico
$socioeconomicoController = new SocioeconomicoController();

// Verificar se é uma ação de exclusão
if (isset($_GET['delete'])) {
    // Simular POST para o controller aceitar
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['csrf_token'] = $_SESSION['csrf_token'] ?? '';
    $socioeconomicoController->delete($_GET['delete']);
} else {
    // Exibir lista
    $socioeconomicoController->index();
}
