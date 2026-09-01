<?php

class PsychologyController extends BaseController
{
    private $psychologyService;

    public function __construct()
    {
        parent::__construct();
        $this->psychologyService = new PsychologyService();
    }

    /* ============================================================
       DASHBOARD
    ============================================================ */
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');

        $data = [
            'title' => 'Área Psicológica',
            'pageTitle' => 'Área Psicológica - Dashboard',
            'stats' => $this->psychologyService->getStatistics(),
            'recentNotes' => $this->psychologyService->getRecentNotes(),
            'messages' => $this->getFlashMessages()
        ];

        $this->renderWithLayout('main', 'psychology/index', $data);
    }

    /* ============================================================
       LISTA DE PACIENTES
    ============================================================ */
    public function patients()
    {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');

        try {
            $data = [
                'title' => 'Pacientes',
                'pageTitle' => 'Acompanhamento Psicológico',
                'patients' => $this->psychologyService->getAllPatients(),
                'messages' => $this->getFlashMessages()
            ];
            $this->renderWithLayout('main', 'psychology/patients', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /* ============================================================
       PRONTUÁRIO DO PACIENTE
    ============================================================ */
    public function patient($cpf)
    {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');

        try {
            $patient = $this->psychologyService->getPatient($cpf);
            if (!$patient) throw new Exception('Paciente não encontrado');

            $data = [
                'title' => 'Prontuário Psicológico',
                'pageTitle' => 'Prontuário Psicológico - ' . $patient['nome_completo'],
                'patient' => $patient,
                'notes' => $this->psychologyService->getPatientNotes($cpf),
                'assessments' => $this->psychologyService->getPatientNotes($cpf),
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];

            $this->renderWithLayout('main', 'psychology/patient', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /* ============================================================
       SALVAR ANOTAÇÃO
    ============================================================ */
    public function saveNote()
    {
        $this->requireAuth();
        $this->requirePermission('add_psychological_note');

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }

            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception('Token CSRF inválido');
            }

            $post = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

            $result = $this->psychologyService->saveNote([
                'patient_cpf' => $post['patient_cpf'] ?? null,
                'note_type' => $post['note_type'] ?? null,
                'title' => $post['title'] ?? '',
                'content' => $post['content'] ?? '',
                'mood_assessment' => $post['mood_assessment'] ?? null,
                'next_session' => $post['next_session'] ?? null,
                'behavior_notes' => $post['behavior_notes'] ?? null,
                'recommendations' => $post['recommendations'] ?? null
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }

            if ($result['success']) {
                $_SESSION['flash_success'] = 'Anotação salva com sucesso';
                header('Location: psychology.php?action=patient&cpf=' . $post['patient_cpf']);
            } else {
                $_SESSION['flash_error'] = $result['message'];
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'psychology.php'));
            }
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'psychology.php'));
            exit;
        }
    }

    /* ============================================================
       BUSCAR ANOTAÇÃO POR ID (AJAX)
    ============================================================ */
    public function getNote()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID da anotação é obrigatório']);
            return;
        }

        $note = $this->psychologyService->getAnnotationById($id); 
        if ($note) {
            echo json_encode(['success' => true, 'note' => $note]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Anotação não encontrada']);
        }
    }

    /* ============================================================
       ATUALIZAR ANOTAÇÃO
    ============================================================ */
    public function updateNote()
    {
        $this->requireAuth();
        $this->requirePermission('add_psychological_note');

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }

            // Pode receber via POST form ou JSON
            if ($isAjax && $_SERVER['CONTENT_TYPE'] === 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                $data = $_POST;
            }

            $id = $data['id'] ?? $data['note_id'] ?? null;
            if (!$id) {
                throw new Exception('ID da anotação é obrigatório');
            }

            $result = $this->psychologyService->updateNote($id, [
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'note_type' => $data['note_type'] ?? 'consulta',
                'mood_assessment' => $data['mood_assessment'] ?? null,
                'behavior_notes' => $data['behavior_notes'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'next_session' => $data['next_session'] ?? null
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }

            if ($result['success']) {
                $_SESSION['flash_success'] = 'Anotação atualizada com sucesso';
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'psychology.php'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'psychology.php'));
            exit;
        }
    }

    /* ============================================================
       DELETAR ANOTAÇÃO
    ============================================================ */
    public function deleteNote($id = null)
    {
        $this->requireAuth();
        $this->requirePermission('add_psychological_note');

        $id = $id ?? $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID da anotação é obrigatório'], 400);
            return;
        }

        try {
            $result = $this->psychologyService->deleteNote($id);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /* ============================================================
       MÉTODOS DEPENDENTES DO SERVICE (placeholder)
    ============================================================ */
    public function saveAssessment()         { $this->json(['error' => 'Método não implementado'], 400); }
    public function search()                 { $this->json(['error' => 'Método não implementado'], 400); }
    public function report()                 { $this->json(['error' => 'Método não implementado'], 400); }
}