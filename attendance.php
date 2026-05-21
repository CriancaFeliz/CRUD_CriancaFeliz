<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Verificar se usuário está logado
if (!isLoggedIn()) {
    redirect('index.php');
}

// Instanciar controller de attendance
$attendanceController = new AttendanceController();

// Obter ação
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Roteamento
try {
    switch ($action) {
        case 'index':
            $attendanceController->index();
            break;
            
        case 'batch':
            $attendanceController->batch();
            break;
            
        case 'apply_batch':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            $attendanceController->applyBatch();
            break;
            
        case 'show':
            if (!$id) {
                throw new Exception('ID do atendido é obrigatório');
            }
            $attendanceController->show($id);
            break;
            
        case 'register_presence':
            $attendanceController->registerPresence();
            break;
            
        case 'register_absence':
            $attendanceController->registerAbsence();
            break;
            
        case 'update_justification':
            $attendanceController->updateJustification();
            break;
            
        case 'remove_record':
            $attendanceController->removeRecord();
            break;
            
        case 'desligamento':
            if (!$id) {
                throw new Exception('ID do atendido é obrigatório');
            }
            $attendanceController->showDesligamento($id);
            break;
            
        case 'processar_desligamento':
            $attendanceController->processarDesligamento();
            break;
            
        case 'cancelar_desligamento':
            $attendanceController->cancelarDesligamento();
            break;
            
        case 'processar_desligamentos_automaticos':
            $attendanceController->processarDesligamentosAutomaticos();
            break;
            
        case 'alertas':
            $attendanceController->alertas();
            break;
            
        case 'relatorios':
            $attendanceController->relatorios();
            break;
            
        default:
            $attendanceController->index();
            break;
    }
} catch (Exception $e) {
    // Log do erro
    error_log("Erro em attendance.php: " . $e->getMessage());
    
    // Redirecionar com erro
    $_SESSION['flash_error'] = $e->getMessage();
    redirect('attendance.php');
}
