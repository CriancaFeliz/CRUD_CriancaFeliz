<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller de acolhimento
$acolhimentoController = new AcolhimentoController();

// Verificar se é POST (criar) ou GET (exibir formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acolhimentoController->store();
} else {
    $acolhimentoController->create();
}
