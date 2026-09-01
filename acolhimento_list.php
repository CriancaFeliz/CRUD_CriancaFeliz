<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller de acolhimento
$acolhimentoController = new AcolhimentoController();

// Verificar se é uma ação de exclusão
if (isset($_GET['delete'])) {
    $_POST['csrf_token'] = $_SESSION['csrf_token'] ?? '';
    $acolhimentoController->delete($_GET['delete']);
} else {
    // Exibir lista com layout padrão (menu lateral etc.)
    $acolhimentoController->index();
}
