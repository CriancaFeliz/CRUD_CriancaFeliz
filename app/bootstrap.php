<?php
/**
 * Bootstrap do Sistema Criança Feliz
 * Inicialização da estrutura MVC
 */

// Definir constantes do sistema
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('DATA_PATH', BASE_PATH . '/data');
define('CSS_PATH', BASE_PATH . '/css');
define('JS_PATH', BASE_PATH . '/js');
define('IMG_PATH', BASE_PATH . '/img');

/**
 * Carrega variáveis de um arquivo .env local quando o servidor não fornece
 * variáveis de ambiente diretamente. Valores já definidos pelo servidor têm
 * prioridade e o arquivo nunca deve ser versionado.
 */
function loadEnvironmentFile($path) {
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (strpos($line, 'export ') === 0) {
            $line = trim(substr($line, 7));
        }

        $separator = strpos($line, '=');
        if ($separator === false) {
            continue;
        }

        $name = trim(substr($line, 0, $separator));
        $value = trim(substr($line, $separator + 1));
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) {
            continue;
        }

        if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
        if (!isset($_ENV[$name])) {
            $_ENV[$name] = $value;
        }
        if (!isset($_SERVER[$name])) {
            $_SERVER[$name] = $value;
        }
    }
}

// Carrega .env do diretório raiz acima de public_html ou dentro do próprio public_html
loadEnvironmentFile(dirname(BASE_PATH) . '/.env');
loadEnvironmentFile(BASE_PATH . '/.env');

// Configurar fuso horário padrão da aplicação (America/Sao_Paulo)
$appTimezone = $_ENV['APP_TIMEZONE'] ?? $_SERVER['APP_TIMEZONE'] ?? getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo';
date_default_timezone_set($appTimezone);

/**
 * Detecta HTTPS sem confiar em cabeçalhos de proxy enviados diretamente pelo
 * cliente. Hospedagens atrás de proxy devem definir SESSION_COOKIE_SECURE=true.
 */
function requestUsesHttps() {
    return !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
}

/**
 * Retorna apenas o endereço visto diretamente pelo servidor. Cabeçalhos de
 * proxy enviados pelo cliente não são usados como identidade de segurança.
 */
function getClientIp() {
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

// A sessão precisa ser configurada antes de session_start().
if (session_status() === PHP_SESSION_NONE) {
    $secureCookieConfig = getenv('SESSION_COOKIE_SECURE');
    $secureCookie = $secureCookieConfig === false
        ? requestUsesHttps()
        : filter_var($secureCookieConfig, FILTER_VALIDATE_BOOLEAN);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    session_name('criancafeliz_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Configurações de segurança HTTP.
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 0');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    if (requestUsesHttps()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Configurações de erro
$appDebug = getenv('APP_DEBUG');
$isDebug = $appDebug === false ? false : filter_var($appDebug, FILTER_VALIDATE_BOOLEAN);
define('APP_DEBUG_MODE', $isDebug);
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

// Expirar sessões inativas e impedir cache de páginas autenticadas.
if (isLoggedIn()) {
    $sessionTimeout = (int) (getenv('SESSION_TIMEOUT_SECONDS') ?: 3600);
    $sessionTimeout = max(300, min($sessionTimeout, 43200));

    if (!(new AuthService())->checkSessionTimeout($sessionTimeout)) {
        if ($isAjaxRequest) {
            if (!headers_sent()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['error' => 'Sessão expirada. Faça login novamente.']);
            exit;
        }

        redirect('index.php');
    }

    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    // Preparar variáveis de log para MySQL triggers.
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

/**
 * Escapa um valor no momento em que ele é exibido em HTML.
 * Dados vindos do banco ou do usuário nunca devem ser concatenados em HTML
 * sem passar por esta função.
 */
function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Função para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para validar senha
function validatePassword($password) {
    return PasswordHelper::isValid($password);
}

function passwordValidationMessage() {
    return PasswordHelper::policyDescription();
}

function appDebugEnabled() {
    return defined('APP_DEBUG_MODE') && APP_DEBUG_MODE === true;
}

function debugLog($message, array $context = []) {
    if (!appDebugEnabled()) {
        return;
    }

    if (!empty($context)) {
        $encoded = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $message .= ' ' . ($encoded ?: '');
    }

    error_log($message);
}

function debugFileLog($fileName, array $entry) {
    if (!appDebugEnabled()) {
        return;
    }

    $safeName = basename($fileName);
    $path = DATA_PATH . '/' . $safeName;
    @file_put_contents($path, json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
}

/**
 * Registra uma exceção com identificador rastreável sem expor detalhes ao
 * navegador. A mensagem completa só é gravada quando o debug está habilitado.
 */
function reportException(Throwable $exception, $context = 'application') {
    $errorId = bin2hex(random_bytes(6));
    error_log(sprintf(
        'Erro %s [%s]: %s (código %s)',
        $errorId,
        $context,
        get_class($exception),
        (string) $exception->getCode()
    ));

    debugLog('Detalhes do erro ' . $errorId, [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);

    return $errorId;
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

/**
 * Valor de referência usado apenas nas faixas socioeconômicas.
 * O padrão corresponde ao salário mínimo nacional de 2026 (Decreto 12.797/2025)
 * e deve ser atualizado pela configuração quando houver reajuste.
 */
function socialIncomeReference() {
    $configured = getenv('SOCIAL_INCOME_REFERENCE');
    $value = $configured === false ? 1621.00 : (float)$configured;
    return $value > 0 ? $value : 1621.00;
}

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Aceita somente destinos relativos ou URLs absolutas do próprio host.
 * URLs absolutas válidas são reduzidas a caminho local antes do redirecionamento.
 */
function safeLocalRedirectTarget($candidate, $fallback = 'index.php') {
    $candidate = trim((string)$candidate);
    if ($candidate === '' || preg_match('/[\x00-\x1F\x7F\\\\]/', $candidate)) {
        return $fallback;
    }

    $parts = parse_url($candidate);
    if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
        return $fallback;
    }

    if (isset($parts['scheme']) || isset($parts['host'])) {
        if (!isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return $fallback;
        }

        $currentHost = parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''));
        if (!$currentHost || empty($currentHost['host'])
            || strcasecmp((string)$parts['host'], (string)$currentHost['host']) !== 0) {
            return $fallback;
        }

        if (isset($parts['port'], $currentHost['port'])
            && (int)$parts['port'] !== (int)$currentHost['port']) {
            return $fallback;
        }

        $target = (string)($parts['path'] ?? '/');
        if (isset($parts['query'])) {
            $target .= '?' . $parts['query'];
        }
        return $target;
    }

    if (strpos($candidate, '//') === 0) {
        return $fallback;
    }

    return $candidate;
}

// Função para redirecionar
function redirect($url) {
    header('Location: ' . safeLocalRedirectTarget($url));
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
        return is_array($value)
            ? e(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            : e($value);
    }
    return e($default);
}

// Criar diretório de dados se não existir
if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0777, true);
}
