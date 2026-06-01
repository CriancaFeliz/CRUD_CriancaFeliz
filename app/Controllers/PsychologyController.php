<?php

class PsychologyController extends BaseController
{
    private $psychologyService;

    public function __construct()
    {
        parent::__construct();
        $this->psychologyService = null;
    }

    private function service()
    {
        if ($this->psychologyService === null) {
            $this->psychologyService = new PsychologyService();
        }

        return $this->psychologyService;
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
            'stats' => $this->service()->getStatistics(),
            'recentNotes' => $this->service()->getRecentNotes(),
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
                'patients' => $this->service()->getAllPatients(),
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
            $patient = $this->service()->getPatient($cpf);
            if (!$patient) throw new Exception('Paciente não encontrado');

            $data = [
                'title' => 'Prontuário Psicológico',
                'pageTitle' => 'Prontuário Psicológico - ' . $patient['nome_completo'],
                'patient' => $patient,
                'notes' => $this->service()->getPatientNotes($cpf),
                'assessments' => $this->service()->getPatientNotes($cpf),
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

            $this->validateCsrfTokenFromData($_POST);

            $post = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

            $result = $this->service()->saveNote([
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
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID da anotação é obrigatório']);
            return;
        }

        $note = $this->service()->getAnnotationById($id); 
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
        $this->requirePermission('edit_psychological_notes');

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }

            // Pode receber via POST form ou JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if ($isAjax && stripos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                $data = $_POST;
            }

            if (!is_array($data)) {
                throw new Exception('Dados inválidos');
            }

            $this->validateCsrfTokenFromData($data);

            $id = $data['id'] ?? $data['note_id'] ?? null;
            if (!$id) {
                throw new Exception('ID da anotação é obrigatório');
            }

            $result = $this->service()->updateNote($id, [
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
        $this->requirePermission('delete_psychological_note');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método não permitido'], 405);
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = $_POST;
        }

        if (!is_array($data)) {
            $data = [];
        }

        $id = $id ?? $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID da anotação é obrigatório'], 400);
            return;
        }

        try {
            $this->validateCsrfTokenFromData($data);
            $result = $this->service()->deleteNote($id);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /* ============================================================
       AVALIAÇÃO, BUSCA E RELATÓRIO
    ============================================================ */
    public function saveAssessment()
    {
        $this->requireAuth();
        $this->requirePermission('add_psychological_note');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método não permitido'], 405);
        }

        try {
            $this->validateCsrfTokenFromData($_POST);

            $result = $this->service()->saveAssessment([
                'patient_cpf' => $_POST['patient_cpf'] ?? null,
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'mood_assessment' => $_POST['mood_assessment'] ?? null,
                'next_session' => $_POST['next_session'] ?? null,
                'behavior_notes' => $_POST['behavior_notes'] ?? null,
                'recommendations' => $_POST['recommendations'] ?? null
            ]);

            $this->json($result, !empty($result['success']) ? 200 : 400);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function search()
    {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');

        $query = $_GET['q'] ?? '';
        $this->json([
            'success' => true,
            'patients' => $this->service()->searchPatients($query)
        ]);
    }

    public function report()
    {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');

        $filters = $this->getGetData();
        $format = $filters['format'] ?? 'html';
        unset($filters['format']);

        try {
            if ($format === 'csv') {
                $csv = $this->service()->exportReportToCSV($filters);
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="relatorio_psicologia_' . date('Y-m-d') . '.csv"');
                echo "\xEF\xBB\xBF";
                echo $csv;
                exit;
            }

            $data = [
                'title' => 'Relatório Psicológico',
                'pageTitle' => 'Relatório Psicológico',
                'filters' => $filters,
                'rows' => $this->service()->getReportRows($filters),
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];

            $this->renderWithLayout('main', 'psychology/report', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function validateCsrfTokenFromData($data)
    {
        $token = is_array($data) ? ($data['csrf_token'] ?? null) : null;

        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('Token CSRF inválido');
        }
    }
}
