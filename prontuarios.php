<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller de prontuários
$prontuarioController = new ProntuarioController();

// Exibir página de prontuários
$prontuarioController->index();
