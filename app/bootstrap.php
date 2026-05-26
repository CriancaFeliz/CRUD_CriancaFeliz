<?php
/**
 * Bootstrap do Sistema Criança Feliz
 * Inicialização da estrutura MVC
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes do sistema
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('DATA_PATH', BASE_PATH . '/data');
define('CSS_PATH', BASE_PATH . '/css');
define('JS_PATH', BASE_PATH . '/js');
define('IMG_PATH', BASE_PATH . '/img');

// Configurações de segurança (apenas para requisições não-AJAX)
// Verificar se é AJAX antes de enviar headers que podem interferir
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjaxRequest && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}

// Configurações de erro
$appDebug = getenv('APP_DEBUG');
$isDebug = $appDebug === false ? true : filter_var($appDebug, FILTER_VALIDATE_BOOLEAN);
error_reporting(E_ALL);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');

// Autoloader simples para as classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/Config/' . $class . '.php',
        BASE_PATH . '/app/Controllers/' . $class . '.php',
        BASE_PATH . '/app/Models/' . $class . '.php',
        BASE_PATH . '/app/Services/' . $class . '.php',
        BASE_PATH . '/app/Helpers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Preparar variáveis de log para MySQL triggers
if (isLoggedIn()) {
    LogHelper::prepareLogVariables();
}

// Função para sanitizar dados de entrada
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para validar senha
function validatePassword($password) {
    return strlen($password) >= 6;
}

// Função para converter data dd/mm/yyyy para yyyy-mm-dd (para inserção no banco)
function formatDateToDb($date) {
    if (empty($date)) return null;
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
    }
    return $date;
}

// Função para converter data yyyy-mm-dd para dd/mm/yyyy (para exibição)
function formatDateToBr($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;
    return date('d/m/Y', $timestamp);
}

// Função para calcular idade de forma dinâmica
function calculateAge($dataNascimento) {
    if (empty($dataNascimento)) return 0;
    
    // Garantir que a data esteja no formato YYYY-MM-DD para DateTime
    $date = formatDateToDb($dataNascimento);
    
    try {
        $birthDate = new DateTime($date);
        $today = new DateTime();
        return $birthDate->diff($today)->y;
    } catch (Exception $e) {
        return 0;
    }
}

// Função para obter faixa etária com base na idade
function getFaixaEtaria($idade) {
    if ($idade <= 11) return 'Criança (0-11)';
    if ($idade <= 17) return 'Adolescente (12-17)';
    return 'Adulto (18+)';
}

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para redirecionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Função para incluir view
function view($viewName, $data = []) {
    extract($data);
    $viewPath = APP_PATH . '/Views/' . $viewName . '.php';
    
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        throw new Exception("View não encontrada: $viewName");
    }
}

// Função para incluir layout
function layout($layoutName, $content, $data = []) {
    extract($data);
    $layoutPath = APP_PATH . '/Views/layouts/' . $layoutName . '.php';
    
    if (file_exists($layoutPath)) {
        include $layoutPath;
    } else {
        throw new Exception("Layout não encontrado: $layoutName");
    }
}

/**
 * Recuperar valor antigo do campo após erro
 * Útil para manter valores preenchidos quando formulário retorna com erro
 * 
 * Uso: value="<?php echo old('nome'); ?>"
 */
function old($key, $default = '') {
    if (isset($_SESSION['old_input'][$key])) {
        $value = $_SESSION['old_input'][$key];
        // Se for array, retornar JSON para campos múltiplos
        return is_array($value) ? htmlspecialchars(json_encode($value)) : htmlspecialchars($value);
    }
    return htmlspecialchars($default);
}

// Criar diretório de dados se não existir
if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0777, true);
}
