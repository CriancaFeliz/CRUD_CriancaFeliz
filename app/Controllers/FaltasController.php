<?php

/**
 * Controller para Sistema de Faltas Moderno
 */
class FaltasController extends BaseController {
    
    private $frequenciaDiaDB;
    private $frequenciaOficinaDB;
    private $oficinaDB;
    private $desligamentoDB;
    
    public function __construct() {
        parent::__construct();
        $this->frequenciaDiaDB = new FrequenciaDiaDB();
        $this->frequenciaOficinaDB = new FrequenciaOficinaDB();
        $this->oficinaDB = new OficinaDB();
        $this->desligamentoDB = new DesligamentoDB();
    }
    
    /**
     * Tela principal - Lançamento por DIA
     */
    public function index() {
        $this->requireAuth();
        
        try {
            $data = $this->getParam('data', date('Y-m-d'));
            $search = $this->getParam('search', '');
            $faixaEtaria = $this->getParam('faixa_etaria', '');
            
            // Buscar todos os atendidos ativos
            $acolhimentoModel = new AcolhimentoDB();
            $atendidos = $acolhimentoModel->findAll();
            
            // Filtrar desligados
            $atendidosAtivos = [];
            foreach ($atendidos as $atendido) {
                $id = $atendido['idatendido'] ?? $atendido['id'];
                if (!$this->desligamentoDB->isDesligado($id)) {
                    $atendidosAtivos[] = $atendido;
                }
            }
            
            // Buscar frequências do dia
            $frequencias = $this->frequenciaDiaDB->getByData($data);
            $frequenciasMap = [];
            foreach ($frequencias as $freq) {
                $frequenciasMap[$freq['id_atendido']] = $freq;
            }
            
            // Combinar dados e calcular idade
            foreach ($atendidosAtivos as &$atendido) {
                $id = $atendido['idatendido'] ?? $atendido['id'];
                $atendido['frequencia'] = $frequenciasMap[$id] ?? null;
                
                // Calcular idade
                if (!empty($atendido['data_nascimento'])) {
                    $nascimento = new DateTime($atendido['data_nascimento']);
                    $hoje = new DateTime();
                    $atendido['idade'] = $nascimento->diff($hoje)->y;
                } else {
                    $atendido['idade'] = 0;
                }
            }
            
            // Filtrar por faixa etária
            if (!empty($faixaEtaria)) {
                $atendidosAtivos = array_filter($atendidosAtivos, function($a) use ($faixaEtaria) {
                    $idade = $a['idade'];
                    switch ($faixaEtaria) {
                        case '0-13':
                            return $idade >= 0 && $idade <= 13;
                        case '13-18':
                            return $idade >= 13 && $idade <= 18;
                        default:
                            return true;
                    }
                });
            }
            
            // Filtrar por busca
            if (!empty($search)) {
                $atendidosAtivos = array_filter($atendidosAtivos, function($a) use ($search) {
                    return stripos($a['nome'], $search) !== false || 
                           stripos($a['cpf'] ?? '', $search) !== false;
                });
            }
            
            $viewData = [
                'title' => 'Controle de Faltas - Por Dia',
                'pageTitle' => 'Controle de Faltas - Por Dia',
                'data' => $data,
                'atendidos' => array_values($atendidosAtivos),
                'search' => $search,
                'faixa_etaria' => $faixaEtaria,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'faltas/dia', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Tela - Lançamento por OFICINA
     */
    public function oficina() {
        $this->requireAuth();
        
        try {
            $idOficina = $this->getParam('oficina', '');
            $data = $this->getParam('data', date('Y-m-d'));
            
            // Buscar oficinas ativas
            $oficinas = $this->oficinaDB->getAtivas();
            
            // Se tem oficina selecionada
            $atendidos = [];
            $oficinaAtual = null;
            if (!empty($idOficina)) {
                $oficinaAtual = $this->oficinaDB->findById($idOficina);
                
                // Buscar atendidos ativos
                $acolhimentoModel = new AcolhimentoDB();
                $todosAtendidos = $acolhimentoModel->findAll();
                
                foreach ($todosAtendidos as $atendido) {
                    $id = $atendido['idatendido'] ?? $atendido['id'];
                    if (!$this->desligamentoDB->isDesligado($id)) {
                        $atendidos[] = $atendido;
                    }
                }
                
                // Buscar frequências da oficina no dia
                $frequencias = $this->frequenciaOficinaDB->getByOficinaData($idOficina, $data);
                $frequenciasMap = [];
                foreach ($frequencias as $freq) {
                    $frequenciasMap[$freq['id_atendido']] = $freq;
                }
                
                // Combinar dados
                foreach ($atendidos as &$atendido) {
                    $id = $atendido['idatendido'] ?? $atendido['id'];
                    $atendido['frequencia'] = $frequenciasMap[$id] ?? null;
                }
            }
            
            $viewData = [
                'title' => 'Controle de Faltas - Por Oficina',
                'pageTitle' => 'Controle de Faltas - Por Oficina',
                'oficinas' => $oficinas,
                'oficinaAtual' => $oficinaAtual,
                'idOficina' => $idOficina,
                'data' => $data,
                'atendidos' => $atendidos,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'faltas/oficina', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Salvar frequência do DIA (AJAX)
     */
    public function salvarDia() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $idAtendido = $this->getParam('id_atendido', '');
            $data = $this->getParam('data', date('Y-m-d'));
            $status = $this->getParam('status', 'P'); // P, F, J
            $justificativa = $this->getParam('justificativa', '');
            
            if (empty($idAtendido)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            if ($status === 'P') {
                $this->frequenciaDiaDB->registrarPresenca($idAtendido, $data);
                $mensagem = 'Presença registrada com sucesso';
            } else {
                $this->frequenciaDiaDB->registrarFalta($idAtendido, $data, $justificativa);
                $mensagem = ($status === 'J') ? 'Falta justificada registrada' : 'Falta registrada';
            }
            
            $this->json(['success' => true, 'message' => $mensagem]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Salvar frequência da OFICINA (AJAX)
     */
    public function salvarOficina() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $idAtendido = $this->getParam('id_atendido', '');
            $idOficina = $this->getParam('id_oficina', '');
            $data = $this->getParam('data', date('Y-m-d'));
            $status = $this->getParam('status', 'P');
            $justificativa = $this->getParam('justificativa', '');
            
            if (empty($idAtendido) || empty($idOficina)) {
                throw new Exception('ID do atendido e oficina são obrigatórios');
            }
            
            if ($status === 'P') {
                $this->frequenciaOficinaDB->registrarPresenca($idAtendido, $idOficina, $data);
                $mensagem = 'Presença registrada com sucesso';
            } else {
                $this->frequenciaOficinaDB->registrarFalta($idAtendido, $idOficina, $data, $justificativa);
                $mensagem = ($status === 'J') ? 'Falta justificada registrada' : 'Falta registrada';
            }
            
            $this->json(['success' => true, 'message' => $mensagem]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Ver histórico de um atendido
     */
    public function historico($id) {
        $this->requireAuth();
        
        try {
            $acolhimentoModel = new AcolhimentoDB();
            $atendido = $acolhimentoModel->findById($id);
            
            if (!$atendido) {
                throw new Exception('Atendido não encontrado');
            }
            
            // Buscar frequências
            $frequenciasDia = $this->frequenciaDiaDB->getByAtendido($id);
            $frequenciasOficina = $this->frequenciaOficinaDB->getByAtendido($id);
            
            // Estatísticas
            $estatsDia = $this->frequenciaDiaDB->getEstatisticas($id);
            $estatsOficina = $this->frequenciaOficinaDB->getEstatisticas($id);
            
            $viewData = [
                'title' => 'Histórico - ' . $atendido['nome'],
                'pageTitle' => 'Histórico de Frequência',
                'atendido' => $atendido,
                'frequenciasDia' => $frequenciasDia,
                'frequenciasOficina' => $frequenciasOficina,
                'estatsDia' => $estatsDia,
                'estatsOficina' => $estatsOficina,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'faltas/historico', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Tela de Alertas
     */
    public function alertas() {
        $this->requireAuth();
        
        try {
            $atendidosComAlertas = $this->frequenciaDiaDB->getAtendidosComAlertas();
            
            // Filtrar apenas atendidos não desligados
            $atendidosAtivos = [];
            foreach ($atendidosComAlertas as $atendido) {
                $id = $atendido['id_atendido'] ?? $atendido['idatendido'] ?? $atendido['id'];
                if (!$this->desligamentoDB->isDesligado($id)) {
                    $atendidosAtivos[] = $atendido;
                }
            }
            
            $viewData = [
                'title' => 'Alertas de Faltas',
                'pageTitle' => 'Alertas de Faltas',
                'atendidos' => $atendidosAtivos,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'faltas/alertas', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Gerenciar Oficinas
     */
    public function gerenciarOficinas() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        try {
            $oficinas = $this->oficinaDB->findAll();
            
            $viewData = [
                'title' => 'Gerenciar Oficinas',
                'pageTitle' => 'Gerenciar Oficinas',
                'oficinas' => $oficinas,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'faltas/gerenciar_oficinas', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Salvar oficina (criar/editar)
     */
    public function salvarOficinaConfig() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->redirectWithError('faltas.php?action=gerenciarOficinas', 'Método não permitido');
        }
        
        try {
            $this->validateCSRF();
            
            $id = $this->getParam('id_oficina', '');
            $data = [
                'nome' => $this->getParam('nome', ''),
                'descricao' => $this->getParam('descricao', ''),
                'dia_semana' => $this->getParam('dia_semana', ''),
                'horario_inicio' => $this->getParam('horario_inicio', ''),
                'horario_fim' => $this->getParam('horario_fim', '')
            ];
            
            if (empty($data['nome'])) {
                throw new Exception('Nome da oficina é obrigatório');
            }
            
            if (empty($id)) {
                $this->oficinaDB->createOficina($data);
                $mensagem = 'Oficina criada com sucesso!';
            } else {
                $this->oficinaDB->updateOficina($id, $data);
                $mensagem = 'Oficina atualizada com sucesso!';
            }
            
            $this->redirectWithSuccess('faltas.php?action=gerenciarOficinas', $mensagem);
            
        } catch (Exception $e) {
            $this->redirectWithError('faltas.php?action=gerenciarOficinas', $e->getMessage());
        }
    }
    
    /**
     * Ativar/Desativar oficina (AJAX)
     */
    public function toggleOficina() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $id = $this->getParam('id_oficina', '');
            
            if (empty($id)) {
                throw new Exception('ID da oficina é obrigatório');
            }
            
            $oficina = $this->oficinaDB->findById($id);
            if (!$oficina) {
                throw new Exception('Oficina não encontrada');
            }
            
            $this->oficinaDB->toggleAtivo($id);
            
            $novoStatus = !$oficina['ativo'];
            $mensagem = $novoStatus ? 'Oficina ativada com sucesso!' : 'Oficina desativada com sucesso!';
            
            $this->json([
                'success' => true, 
                'message' => $mensagem,
                'novo_status' => $novoStatus
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
