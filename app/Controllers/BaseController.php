<?php

/**
 * Controller base com funcionalidades comuns
 */
class BaseController {
    protected $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    /**
     * Renderiza uma view
     */
    protected function render($view, $data = []) {
        // Adicionar dados globais
        $data['currentUser'] = $this->authService->getCurrentUser();
        $data['isLoggedIn'] = $this->authService->isLoggedIn();
        $data['old_input'] = $_SESSION['old_input'] ?? [];
        
        view($view, $data);
    }
    
    /**
     * Renderiza uma view com layout
     */
    protected function renderWithLayout($layout, $view, $data = []) {
        // Adicionar dados globais
        $data['currentUser'] = $this->authService->getCurrentUser();
        $data['isLoggedIn'] = $this->authService->isLoggedIn();
        $data['old_input'] = $_SESSION['old_input'] ?? [];
        
        // Limpar old_input após usar (para não aparecer em próximas páginas)
        if (isset($_SESSION['old_input'])) {
            unset($_SESSION['old_input']);
        }
        
        // Capturar conteúdo da view
        ob_start();
        view($view, $data);
        $content = ob_get_clean();
        
        // Renderizar layout com conteúdo
        layout($layout, $content, $data);
    }
    
    /**
     * Redireciona com mensagem de sucesso
     */
    protected function redirectWithSuccess($url, $message) {
        // Limpar qualquer output anterior
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $_SESSION['flash_success'] = $message;
        
        // Redirecionar
        if (!headers_sent()) {
            header('Location: ' . $url, true, 302);
        }
        exit;
    }
    
    /**
     * Redireciona com mensagem de erro
     */
    protected function redirectWithError($url, $message, $preserveInput = true) {
        // Limpar qualquer output anterior
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $_SESSION['flash_error'] = $message;
        
        // Preservar valores dos campos após erro
        if ($preserveInput && !empty($_POST)) {
            $_SESSION['old_input'] = $_POST;
        }
        
        // Redirecionar
        if (!headers_sent()) {
            header('Location: ' . $url, true, 302);
        }
        exit;
    }
    
    /**
     * Retorna JSON
     */
    protected function json($data, $statusCode = 200) {
        // Limpar qualquer output anterior
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Limpar headers anteriores
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Valida se é requisição POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Valida se é requisição GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Obtém dados POST sanitizados
     */
    protected function getPostData() {
        // Sanitizar a maior parte dos campos, mas preservar campos JSON
        $raw = $_POST;
        $sanitized = sanitizeInput($_POST);

        // Preserve raw JSON payloads (enviados por campos hidden) para evitar
        // que as aspas sejam convertidas e o json_decode falhe.
        $jsonKeys = ['familia_json', 'despesas_json', 'despesas', 'familia'];
        foreach ($jsonKeys as $key) {
            if (isset($raw[$key])) {
                $sanitized[$key] = $raw[$key];
            }
        }

        return $sanitized;
    }
    
    /**
     * Obtém dados GET sanitizados
     */
    protected function getGetData() {
        return sanitizeInput($_GET);
    }
    
    /**
     * Obtém parâmetro específico
     */
    protected function getParam($key, $default = null) {
        return $_REQUEST[$key] ?? $default;
    }
    
    /**
     * Verifica se usuário está autenticado
     */
    protected function requireAuth() {
        $this->authService->requireAuth();
    }
    
    /**
     * Verifica permissão específica
     */
    protected function requirePermission($permission) {
        $this->authService->requirePermission($permission);
    }
    
    /**
     * Obtém mensagens flash
     */
    protected function getFlashMessages() {
        $messages = [];
        
        if (isset($_SESSION['flash_success'])) {
            $messages['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        
        if (isset($_SESSION['flash_error'])) {
            $messages['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }
        
        if (isset($_SESSION['flash_info'])) {
            $messages['info'] = $_SESSION['flash_info'];
            unset($_SESSION['flash_info']);
        }
        
        return $messages;
    }
    
    /**
     * Obter valor antigo de campo (após erro)
     */
    protected function old($key, $default = '') {
        if (isset($_SESSION['old_input'][$key])) {
            return $_SESSION['old_input'][$key];
        }
        return $default;
    }
    
    /**
     * Limpar valores antigos
     */
    protected function clearOldInput() {
        if (isset($_SESSION['old_input'])) {
            unset($_SESSION['old_input']);
        }
    }
    
    /**
     * Valida CSRF token
     */
    protected function validateCSRF() {
        // Aceitar tanto csrf_token quanto _csrf_token
        $token = $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? null;
        
        if (!$token || !isset($_SESSION['csrf_token'])) {
            throw new Exception('Token CSRF inválido');
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('Token CSRF inválido');
        }
    }
    
    /**
     * Gera CSRF token
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Trata exceções
     */
    protected function handleException(Exception $e) {
        error_log("Erro no controller: " . $e->getMessage());
        
        if ($this->isAjaxRequest()) {
            $this->json(['error' => $e->getMessage()], 500);
        } else {
            $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? 'index.php', $e->getMessage());
        }
    }
    
    /**
     * Verifica se é requisição AJAX
     */
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Upload de arquivo
     */
    protected function uploadFile($fileKey, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $file = $_FILES[$fileKey];
        
        // Verificar tamanho
        if ($file['size'] > $maxSize) {
            throw new Exception('Arquivo muito grande. Máximo: ' . ($maxSize / 1024 / 1024) . 'MB');
        }
        
        // Verificar tipo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Tipo de arquivo não permitido. Permitidos: ' . implode(', ', $allowedTypes));
        }
        
        // Gerar nome único
        $fileName = uniqid() . '.' . $extension;
        $uploadDir = BASE_PATH . '/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return 'uploads/' . $fileName;
        }
        
        throw new Exception('Erro ao fazer upload do arquivo');
    }
}
