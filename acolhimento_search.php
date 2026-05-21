<?php
// Endpoint de busca em tempo real para fichas de acolhimento
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$controller = new AcolhimentoController();
$controller->search();
