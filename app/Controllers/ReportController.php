<?php

class ReportController extends BaseController {
    private $reportService;

    public function __construct() {
        parent::__construct();
        $this->reportService = new ReportService();
    }

    public function index() {
        $this->requireAuth();
        $this->requirePermission('view_reports');

        try {
            $type = (string)($_GET['type'] ?? 'atendidos');
            $report = $this->reportService->generate($type, $_GET);

            if (($_GET['print'] ?? '') === '1') {
                $this->reportService->audit($type, $report['filters'], 'impressao_pdf');
            }

            $this->renderWithLayout('main', 'reports/index', [
                'title' => 'Central de Relatórios',
                'pageTitle' => 'Central de Relatórios',
                'types' => $this->reportService->getTypes(),
                'report' => $report,
                'printMode' => (($_GET['print'] ?? '') === '1'),
                'additionalStyles' => ['css/reports.css'],
                'messages' => $this->getFlashMessages()
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->redirectWithError('reports.php', $exception->getMessage(), false);
        } catch (Exception $exception) {
            $this->handleException($exception);
        }
    }

    public function export() {
        $this->requireAuth();
        $this->requirePermission('view_reports');

        try {
            $type = (string)($_GET['type'] ?? 'atendidos');
            $report = $this->reportService->generate($type, $_GET);
            $this->reportService->audit($type, $report['filters'], 'csv');
            $fileName = 'crianca-feliz-' . $type . '-' . date('Y-m-d-His') . '.csv';

            if (!headers_sent()) {
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('X-Content-Type-Options: nosniff');
            }

            echo $this->reportService->exportCsv($report);
            exit;
        } catch (InvalidArgumentException $exception) {
            $this->redirectWithError('reports.php', $exception->getMessage(), false);
        } catch (Exception $exception) {
            $this->handleException($exception);
        }
    }
}
