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
            $cpfNormalizado = preg_replace('/\D+/', '', $cpf);
            
            // Buscar ficha de acolhimento
            $acolhimentos = $this->acolhimentoService->listFichas(1, 1000);
            foreach ($acolhimentos['data'] as $ficha) {
                if (preg_replace('/\D+/', '', $ficha['cpf'] ?? '') === $cpfNormalizado) {
                    $acolhimento = $ficha;
                    break;
                }
            }
            
            // Buscar ficha socioeconômica
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 1000);
            foreach ($socioeconomicos['data'] as $ficha) {
                if (preg_replace('/\D+/', '', $ficha['cpf'] ?? '') === $cpfNormalizado) {
                    $socioeconomico = $ficha;
                    break;
                }
            }
            
            if (!$acolhimento && !$socioeconomico) {
                throw new Exception('Prontuário não encontrado');
            }

            $atendidoId = $acolhimento['id'] ?? $socioeconomico['id'] ?? null;
            $documents = [];
            if ($atendidoId) {
                try {
                    $documentModel = new Document();
                    $documents = $documentModel->findByAtendido($atendidoId);
                } catch (Exception $e) {
                    error_log("Erro ao buscar documentos do prontuário: " . $e->getMessage());
                }
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
                'documents' => $documents,
                'atendidoId' => $atendidoId,
                'cpf' => $cpf,
                'csrf_token' => $this->generateCSRF()
            ];
            
            $this->renderWithLayout('main', 'prontuarios/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function buscar() {
        $this->requireAuth();

        $query = trim($_GET['q'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');

        if (strlen($query) < 2) {
            $this->json([]);
        }

        $resultados = [];
        if ($categoria === '' || $categoria === 'acolhimento') {
            foreach ($this->acolhimentoService->searchFichas($query) as $ficha) {
                $ficha['categoria'] = 'acolhimento';
                $ficha['nome'] = $ficha['nome_completo'] ?? $ficha['nome'] ?? '';
                $resultados[] = $ficha;
            }
        }

        if ($categoria === '' || $categoria === 'socioeconomico') {
            foreach ($this->socioeconomicoService->searchFichas($query) as $ficha) {
                $ficha['categoria'] = 'socioeconomico';
                $ficha['nome'] = $ficha['nome_completo'] ?? $ficha['nome_entrevistado'] ?? $ficha['nome'] ?? '';
                $resultados[] = $ficha;
            }
        }

        $this->json(array_values($resultados));
    }

    public function uploadDocument() {
        $this->requireAuth();
        $this->requirePermission('edit_records');

        if (!$this->isPost()) {
            $this->redirectWithError('prontuarios.php', 'Método não permitido');
        }

        try {
            $this->validateCSRF();

            $atendidoId = intval($_POST['id_atendido'] ?? 0);
            $cpf = trim($_POST['cpf'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'outros');

            if ($atendidoId <= 0) {
                throw new Exception('Atendido inválido para anexar documento');
            }

            $allowedTypes = [
                'identidade',
                'comprovante_residencia',
                'escola',
                'saude',
                'autorizacao',
                'outros'
            ];
            if (!in_array($tipo, $allowedTypes, true)) {
                $tipo = 'outros';
            }

            if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Nenhum documento foi enviado');
            }

            $file = $_FILES['documento'];
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new Exception('Arquivo muito grande. Tamanho máximo: 10MB');
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            if (!in_array($extension, $allowedExtensions, true)) {
                throw new Exception('Tipo de documento não permitido. Use PDF, JPG, PNG, DOC ou DOCX');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
            if ($finfo) {
                finfo_close($finfo);
            }

            $allowedMimeTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip'
            ];
            if (!$mimeType || !in_array($mimeType, $allowedMimeTypes, true)) {
                throw new Exception('Conteúdo do arquivo não corresponde a um documento permitido');
            }

            $uploadDir = BASE_PATH . '/uploads/documents';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $htaccess = $uploadDir . '/.htaccess';
            if (!file_exists($htaccess)) {
                @file_put_contents($htaccess, "Options -Indexes\nRequire all denied\n");
            }

            $fileName = $atendidoId . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $targetPath = $uploadDir . '/' . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Erro ao salvar documento');
            }

            $documentModel = new Document();
            $documentModel->createForAtendido($atendidoId, $tipo, 'uploads/documents/' . $fileName);

            $redirect = 'prontuarios.php';
            if ($cpf !== '') {
                $redirect .= '?action=show&cpf=' . urlencode($cpf);
            }

            $this->redirectWithSuccess($redirect, 'Documento anexado com sucesso!');
        } catch (Exception $e) {
            $redirect = 'prontuarios.php';
            if (!empty($_POST['cpf'])) {
                $redirect .= '?action=show&cpf=' . urlencode($_POST['cpf']);
            }
            $this->redirectWithError($redirect, $e->getMessage(), false);
        }
    }

    public function viewDocument($id) {
        $this->requireAuth();

        try {
            $documentModel = new Document();
            $document = $documentModel->findById($id);
            if (!$document) {
                throw new Exception('Documento não encontrado');
            }

            $relativePath = $document['arquivo'] ?? '';
            $baseDir = realpath(BASE_PATH . '/uploads/documents');
            $filePath = realpath(BASE_PATH . '/' . $relativePath);

            if (!$baseDir || !$filePath || strpos($filePath, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($filePath)) {
                throw new Exception('Arquivo não encontrado');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $filePath) : 'application/octet-stream';
            if ($finfo) {
                finfo_close($finfo);
            }

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
            header('X-Content-Type-Options: nosniff');
            readfile($filePath);
            exit;
        } catch (Exception $e) {
            $this->redirectWithError('prontuarios.php', $e->getMessage(), false);
        }
    }

}
