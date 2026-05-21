<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller socioeconômico
$socioeconomicoController = new SocioeconomicoController();

// Obter ID da URL
$id = $_GET['id'] ?? '';

if (empty($id)) {
    redirect('socioeconomico_list.php');
}

// Exibir ficha
$socioeconomicoController->show($id);
