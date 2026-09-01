<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller de psicologia
$psychologyController = new PsychologyController();

// Obter ação e parâmetros
$action = $_GET['action'] ?? 'index';
$cpf = $_GET['cpf'] ?? null;
$id = $_GET['id'] ?? null;

// Roteamento
try {
    switch ($action) {
        case 'index':
            $psychologyController->index();
            break;

        case 'patients':
            $psychologyController->patients();
            break;

        case 'patient':
            if (!$cpf) {
                throw new Exception('CPF do paciente é obrigatório');
            }
            $psychologyController->patient($cpf);
            break;

        case 'save_note':
            $psychologyController->saveNote();
            break;

        case 'get_note':
            $psychologyController->getNote();
            break;

        case 'update_note':
            $psychologyController->updateNote();
            break;

        case 'save_assessment':
            $psychologyController->saveAssessment();
            break;

        case 'delete_note':
            if (!$id) {
                throw new Exception('ID da anotação é obrigatório');
            }
            $psychologyController->deleteNote($id);
            break;

        case 'search':
            $psychologyController->search();
            break;

        case 'report':
            $psychologyController->report();
            break;

        default:
            $psychologyController->index();
            break;
    }
} catch (Exception $e) {
    // Log do erro
    error_log("Erro em psychology.php: " . $e->getMessage());

    // Resposta baseada no tipo de requisição
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        $_SESSION['flash_error'] = $e->getMessage();
        redirect('psychology.php');
    }
}