<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller socioeconômico
$socioeconomicoController = new SocioeconomicoController();

// Verificar se é POST (criar) ou GET (exibir formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $socioeconomicoController->store();
} else {
    $socioeconomicoController->create();
}
