<?php

/**
 * Controller para prontuários
 */
class ProntuarioController extends BaseController {
    private $acolhimentoService;
    private $socioeconomicoService;
    
    public function __construct() {
        parent::__construct();
        $this->acolhimentoService = new AcolhimentoService();
        $this->socioeconomicoService = new SocioeconomicoService();
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
            
            $attendanceStats = null;
            if ($acolhimento) {
                try {
                    $frequenciaModel = new FrequenciaDia();
                    $desligamentoModel = new Desligamento();
                    
                    $dbStats = $frequenciaModel->getEstatisticas($acolhimento['id']);
                    $desligamento = $desligamentoModel->getByAtendido($acolhimento['id']);
                    
                    // Alertas dinâmicos baseados nas faltas não justificadas
                    $alertas = [];
                    $faltasNaoJustificadas = $dbStats['faltas'] ?? 0;
                    if ($faltasNaoJustificadas >= 3) {
                        $alertas[] = [
                            'tipo' => 'excesso_faltas',
                            'mensagem' => "Atendido com {$faltasNaoJustificadas} faltas não justificadas"
                        ];
                    }
                    
                    // Idade limite alerta
                    $idade = $acolhimento['idade'] ?? calculateAge($acolhimento['data_nascimento']);
                    if ($idade >= 18) {
                        $alertas[] = [
                            'tipo' => 'idade_limite',
                            'mensagem' => "Atendido completou {$idade} anos - Desligamento automático pendente"
                        ];
                    }
                    
                    $attendanceStats = [
                        'desligado' => !empty($desligamento),
                        'desligamento' => $desligamento,
                        'alertas' => $alertas,
                        'total_presencas' => $dbStats['presencas'] ?? 0,
                        'faltas_justificadas' => $dbStats['justificadas'] ?? 0,
                        'faltas_nao_justificadas' => $dbStats['faltas'] ?? 0,
                        'percentual_presenca' => $dbStats['percentual_presenca'] ?? 100
                    ];
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
