<?php
// Endpoint de busca em tempo real para fichas de acolhimento
require_once 'bootstrap.php';

$controller = new AcolhimentoController();
$controller->search();
