<?php
require_once 'bootstrap.php';

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->processResetPassword();
} else {
    $authController->showResetPassword();
}
