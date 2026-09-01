<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller de acolhimento
$acolhimentoController = new AcolhimentoController();

// Obter ID da URL
$id = $_GET['id'] ?? '';

if (empty($id)) {
    redirect('acolhimento_list.php');
}

// Exibir ficha
$acolhimentoController->show($id);
