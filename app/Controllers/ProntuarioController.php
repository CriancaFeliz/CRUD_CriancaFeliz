<?php

/**
 * Controller para prontuários
 */
class ProntuarioController extends BaseController {
    private $acolhimentoService;
    private $socioeconomicoService;
    private $attendanceService;
    
    public function __construct() {
        parent::__construct();
        $this->acolhimentoService = new AcolhimentoService();
        $this->socioeconomicoService = new SocioeconomicoService();
        $this->attendanceService = new AttendanceService();
    }
    
    /**
     * Lista prontuários
     */
    public function index() {
        $this->requireAuth();
        
        try {
            // Buscar fichas de acolhimento e socioeconômicas
            $acolhimentos = $this->acolhimentoService->listFichas(1, 100);
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 100);
            
            $data = [
                'title' => 'Prontuários - Associação Criança Feliz',
                'pageTitle' => 'Prontuários',
                'acolhimentos' => $acolhimentos['data'] ?? [],
                'socioeconomicos' => $socioeconomicos['data'] ?? [],
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'prontuarios/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Visualiza prontuário específico
     */
    public function show($cpf) {
        $this->requireAuth();
        
        try {
            // Buscar fichas pelo CPF
            $acolhimento = null;
            $socioeconomico = null;
            
            // Buscar ficha de acolhimento
            $acolhimentos = $this->acolhimentoService->listFichas(1, 1000);
            foreach ($acolhimentos['data'] as $ficha) {
                if ($ficha['cpf'] === $cpf) {
                    $acolhimento = $ficha;
                    break;
                }
            }
            
            // Buscar ficha socioeconômica
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 1000);
            foreach ($socioeconomicos['data'] as $ficha) {
                if ($ficha['cpf'] === $cpf) {
                    $socioeconomico = $ficha;
                    break;
                }
            }
            
            if (!$acolhimento && !$socioeconomico) {
                throw new Exception('Prontuário não encontrado');
            }
            
            // Buscar estatísticas de faltas se tiver acolhimento
            $attendanceStats = null;
            if ($acolhimento) {
                try {
                    $attendanceStats = $this->attendanceService->getAtendidoStatistics($acolhimento['id']);
                } catch (Exception $e) {
                    error_log("Erro ao buscar estatísticas de faltas: " . $e->getMessage());
                }
            }
            
            $data = [
                'title' => 'Prontuário - ' . ($acolhimento['nome_completo'] ?? $socioeconomico['nome_completo'] ?? 'Não informado'),
                'pageTitle' => 'Prontuário',
                'acolhimento' => $acolhimento,
                'socioeconomico' => $socioeconomico,
                'attendanceStats' => $attendanceStats,
                'cpf' => $cpf,
                'csrf_token' => $this->generateCSRF()
            ];
            
            $this->renderWithLayout('main', 'prontuarios/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function buscar(){
    $query = $_GET['q'] ?? '';

    $model = new ProntuarioModel();
    $resultados = $model->buscarPorNomeOuCpf($query);

    $this->renderWithLayout('main', 'prontuarios/index', [
        'resultados' => $resultados,
        'query' => $query
    ]);
}

}
