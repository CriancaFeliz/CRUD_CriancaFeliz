<?php

$root = dirname(__DIR__);

require_once $root . '/app/bootstrap.php';
require_once __DIR__ . '/automated/TestCase.php';

class HttpSmokeClient {
    private $baseUrl;
    private $cookies = [];

    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get($path) {
        return $this->request('GET', $path);
    }

    public function post($path, array $data) {
        return $this->request('POST', $path, http_build_query($data), [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
    }

    public function postMultipart($path, array $fields, array $files) {
        $boundary = '----CriancaFelizSmoke' . bin2hex(random_bytes(8));
        $lineBreak = "\r\n";
        $body = '';

        foreach ($fields as $name => $value) {
            $body .= '--' . $boundary . $lineBreak;
            $body .= 'Content-Disposition: form-data; name="' . $this->escapeHeaderValue($name) . '"' . $lineBreak . $lineBreak;
            $body .= (string) $value . $lineBreak;
        }

        foreach ($files as $name => $file) {
            $filename = $file['filename'] ?? 'upload.bin';
            $contentType = $file['content_type'] ?? 'application/octet-stream';
            $content = $file['content'] ?? '';

            $body .= '--' . $boundary . $lineBreak;
            $body .= 'Content-Disposition: form-data; name="' . $this->escapeHeaderValue($name) . '"; filename="' . $this->escapeHeaderValue($filename) . '"' . $lineBreak;
            $body .= 'Content-Type: ' . $contentType . $lineBreak . $lineBreak;
            $body .= $content . $lineBreak;
        }

        $body .= '--' . $boundary . '--' . $lineBreak;

        return $this->request('POST', $path, $body, [
            'Content-Type: multipart/form-data; boundary=' . $boundary
        ]);
    }

    private function request($method, $path, $body = null, array $extraHeaders = []) {
        $headers = [
            'Accept: text/html,application/json',
            'User-Agent: CriancaFelizSmokeTest/1.0'
        ];

        if (!empty($this->cookies)) {
            $pairs = [];
            foreach ($this->cookies as $name => $value) {
                $pairs[] = $name . '=' . $value;
            }
            $headers[] = 'Cookie: ' . implode('; ', $pairs);
        }

        foreach ($extraHeaders as $header) {
            $headers[] = $header;
        }

        if ($body !== null) {
            $headers[] = 'Content-Length: ' . strlen($body);
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body ?? '',
                'ignore_errors' => true,
                'follow_location' => 0,
                'max_redirects' => 0,
                'timeout' => 10
            ]
        ]);

        $responseBody = @file_get_contents($this->baseUrl . $path, false, $context);
        $responseHeaders = $http_response_header ?? [];
        $status = $this->parseStatus($responseHeaders);
        $this->captureCookies($responseHeaders);

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $responseBody === false ? '' : $responseBody,
            'location' => $this->headerValue($responseHeaders, 'Location')
        ];
    }

    private function parseStatus(array $headers) {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    private function captureCookies(array $headers) {
        foreach ($headers as $header) {
            if (stripos($header, 'Set-Cookie:') !== 0) {
                continue;
            }

            $cookie = trim(substr($header, strlen('Set-Cookie:')));
            $firstPart = explode(';', $cookie, 2)[0];
            [$name, $value] = array_pad(explode('=', $firstPart, 2), 2, '');
            if ($name !== '') {
                $this->cookies[$name] = $value;
            }
        }
    }

    private function headerValue(array $headers, $name) {
        foreach ($headers as $header) {
            if (stripos($header, $name . ':') === 0) {
                return trim(substr($header, strlen($name) + 1));
            }
        }

        return null;
    }

    private function escapeHeaderValue($value) {
        return str_replace(['\\', '"', "\r", "\n"], ['\\\\', '\\"', '', ''], (string) $value);
    }
}

class HttpSmokeTest extends TestCase {
    private $baseUrl;

    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->prepareAuditContext();
    }

    public function testPublicLoginPageLoadsAndProtectedRouteRedirects() {
        $client = $this->newClient();
        $login = $client->get('/');
        $this->assertSame(200, $login['status'], 'Login deve carregar com HTTP 200');
        $this->assertTrue(strpos($login['body'], 'csrf_token') !== false, 'Login deve conter token CSRF');

        $freshClient = $this->newClient();
        $protected = $freshClient->get('/dashboard.php');
        $this->assertSame(302, $protected['status'], 'Rota protegida sem sessao deve redirecionar');
        $this->assertTrue(strpos((string) $protected['location'], 'index.php') !== false, 'Redirecionamento esperado para login');
    }

    public function testAdminCanLoginAndOpenCriticalPages() {
        $client = $this->loginAs('admin@criancafeliz.org', 'AlterarEstaSenha!2026');

        $pages = [
            '/dashboard.php',
            '/prontuarios.php',
            '/acolhimento_list.php',
            '/socioeconomico_list.php',
            '/faltas.php',
            '/users.php',
            '/logs.php',
            '/profile.php'
        ];

        foreach ($pages as $page) {
            $response = $client->get($page);
            $this->assertSame(200, $response['status'], "Pagina critica deve abrir: {$page}");
            $this->assertTrue(strlen($response['body']) > 100, "Pagina critica retornou corpo pequeno demais: {$page}");
        }
    }

    public function testRolePermissionsProtectSensitiveAreas() {
        $admin = $this->loginAs('admin@criancafeliz.org', 'AlterarEstaSenha!2026');
        $this->assertSame(200, $admin->get('/users.php')['status'], 'Admin deve acessar usuarios');
        $this->assertRedirectsToDashboard($admin->get('/psychology.php'), 'Admin nao deve acessar area psicologica');

        $psychologistUser = $this->createUserFixture('psicologo');
        $psychologist = $this->loginAs($psychologistUser['email'], $psychologistUser['password']);
        $psychology = $psychologist->get('/psychology.php');
        $this->assertSame(200, $psychology['status'], 'Psicologo deve acessar area psicologica');
        $this->assertTrue(strlen($psychology['body']) > 100, 'Area psicologica deve retornar conteudo');
        $this->assertRedirectsToDashboard($psychologist->get('/users.php'), 'Psicologo nao deve gerenciar usuarios');
        $this->assertRedirectsToDashboard($psychologist->get('/acolhimento_form.php'), 'Psicologo nao deve cadastrar acolhimento');

        $employeeUser = $this->createUserFixture('funcionario');
        $employee = $this->loginAs($employeeUser['email'], $employeeUser['password']);
        $this->assertSame(200, $employee->get('/dashboard.php')['status'], 'Funcionario deve acessar dashboard');
        $this->assertRedirectsToDashboard($employee->get('/psychology.php'), 'Funcionario nao deve acessar area psicologica');
        $this->assertRedirectsToDashboard($employee->get('/users.php'), 'Funcionario nao deve gerenciar usuarios');
        $this->assertRedirectsToDashboard($employee->get('/acolhimento_form.php'), 'Funcionario nao deve cadastrar acolhimento');
        $this->assertRedirectsToDashboard(
            $employee->post('/socioeconomico_list.php?delete=1', ['csrf_token' => 'token-invalido']),
            'Funcionario nao deve excluir ficha socioeconomica'
        );
    }

    public function testProfilePhotoUploadAcceptsValidPng() {
        $client = $this->loginAs('admin@criancafeliz.org', 'AlterarEstaSenha!2026');
        $profile = $client->get('/profile.php');
        $this->assertSame(200, $profile['status'], 'Perfil deve abrir para usuario autenticado');

        $csrfToken = $this->extractCsrfToken($profile['body']);
        $previousPhoto = $this->fetchValue('SELECT foto_perfil FROM Usuario WHERE email = ?', ['admin@criancafeliz.org']);
        $publicPath = null;
        $hadHtaccess = file_exists(BASE_PATH . '/uploads/profiles/.htaccess');

        try {
            $response = $client->postMultipart('/profile.php?action=updatePhoto', [
                'csrf_token' => $csrfToken
            ], [
                'photo' => [
                    'filename' => 'perfil-smoke.png',
                    'content_type' => 'image/png',
                    'content' => $this->tinyPng()
                ]
            ]);

            $this->assertSame(200, $response['status'], 'Upload de foto deve retornar HTTP 200');
            $payload = json_decode($response['body'], true);
            $this->assertTrue(is_array($payload), 'Upload de foto deve retornar JSON');
            $this->assertTrue((bool) ($payload['success'] ?? false), 'Upload de foto deve retornar sucesso');
            $this->assertNotEmpty($payload['photo'] ?? null, 'Upload de foto deve retornar caminho publico');

            $publicPath = $payload['photo'];
            $this->assertTrue(is_file(BASE_PATH . '/' . $publicPath), 'Arquivo de perfil deve existir no disco');
            $this->assertSame(
                $publicPath,
                $this->fetchValue('SELECT foto_perfil FROM Usuario WHERE email = ?', ['admin@criancafeliz.org']),
                'Banco deve apontar para a foto enviada'
            );
        } finally {
            $this->execute('UPDATE Usuario SET foto_perfil = ? WHERE email = ?', [$previousPhoto, 'admin@criancafeliz.org']);
            if ($publicPath) {
                $this->cleanupPublicUpload($publicPath);
            }
            $this->cleanupUploadSubdir('profiles', $hadHtaccess);
        }
    }

    public function testAdminCanUploadProntuarioDocumentViaMultipart() {
        $created = $this->createAcolhimentoFixture();
        $client = $this->loginAs('admin@criancafeliz.org', 'AlterarEstaSenha!2026');
        $show = $client->get('/prontuarios.php?action=show&cpf=' . urlencode($created['cpf']));
        $this->assertSame(200, $show['status'], 'Prontuario deve abrir antes do upload');

        $csrfToken = $this->extractCsrfToken($show['body']);
        $publicPath = null;
        $hadHtaccess = file_exists(BASE_PATH . '/uploads/documents/.htaccess');

        try {
            $response = $client->postMultipart('/prontuarios.php?action=upload_document', [
                'csrf_token' => $csrfToken,
                'id_atendido' => $created['id'],
                'cpf' => $created['cpf'],
                'tipo' => 'identidade'
            ], [
                'documento' => [
                    'filename' => 'documento-smoke.png',
                    'content_type' => 'image/png',
                    'content' => $this->tinyPng()
                ]
            ]);

            $this->assertSame(302, $response['status'], 'Upload de documento deve redirecionar apos salvar');
            $this->assertTrue(
                strpos((string) $response['location'], 'prontuarios.php?action=show') !== false,
                'Upload de documento deve voltar para o prontuario'
            );

            $document = $this->fetchRow(
                'SELECT * FROM documento WHERE IDatendido = ? ORDER BY iddocumento DESC LIMIT 1',
                [$created['id']]
            );
            $this->assertNotEmpty($document, 'Documento deve ser gravado no banco');
            $this->assertSame('identidade', $document['tipo'], 'Tipo do documento deve ser preservado');
            $this->assertTrue(strpos($document['arquivo'], 'uploads/documents/') === 0, 'Documento deve ficar no diretorio esperado');

            $publicPath = $document['arquivo'];
            $this->assertTrue(is_file(BASE_PATH . '/' . $publicPath), 'Documento enviado deve existir no disco');
        } finally {
            if ($publicPath) {
                $this->cleanupPublicUpload($publicPath);
            }
            $this->cleanupUploadSubdir('documents', $hadHtaccess);
        }
    }

    private function newClient() {
        return new HttpSmokeClient($this->baseUrl);
    }

    private function loginAs($email, $password) {
        $client = $this->newClient();
        $login = $client->get('/');
        $this->assertSame(200, $login['status'], 'Tela de login deve abrir antes de autenticar');
        $csrfToken = $this->extractCsrfToken($login['body']);

        $post = $client->post('/index.php', [
            'csrf_token' => $csrfToken,
            'email' => $email,
            'password' => $password
        ]);

        $this->assertSame(302, $post['status'], 'Login deve redirecionar');
        $this->assertTrue(strpos((string) $post['location'], 'dashboard.php') !== false, 'Login deve ir para dashboard');

        return $client;
    }

    private function extractCsrfToken($html) {
        $patterns = [
            '/name=["\'](?:csrf_token|_csrf_token)["\'][^>]*value=["\']([^"\']+)["\']/i',
            '/value=["\']([^"\']+)["\'][^>]*name=["\'](?:csrf_token|_csrf_token)["\']/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                return $matches[1];
            }
        }

        $this->assertTrue(false, 'Token CSRF nao encontrado no HTML');
    }

    private function assertRedirectsToDashboard(array $response, $message) {
        $this->assertSame(302, $response['status'], $message);
        $this->assertTrue(strpos((string) $response['location'], 'dashboard.php') !== false, $message . ': destino inesperado');
    }

    private function createUserFixture($role) {
        $email = 'smoke_' . $role . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '@example.test';
        $password = 'SmokeTest!2026';
        $user = (new User())->createUser([
            'name' => 'Smoke ' . ucfirst($role),
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'status' => 'Ativo'
        ]);

        return [
            'id' => $user['id'] ?? $user['idusuario'],
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];
    }

    private function createAcolhimentoFixture() {
        $suffix = date('YmdHis') . '_' . bin2hex(random_bytes(3));

        return (new Acolhimento())->createFicha([
            'nome_completo' => 'Smoke Prontuario ' . $suffix,
            'cpf' => $this->fakeCpf(),
            'rg' => (string) random_int(10000000, 99999999),
            'data_nascimento' => '10/05/2015',
            'data_acolhimento' => '01/06/2026',
            'endereco' => 'Rua Smoke',
            'numero' => '123',
            'complemento' => 'Casa',
            'bairro' => 'Centro',
            'cidade' => 'Guarulhos',
            'estado' => 'SP',
            'cep' => '07000000',
            'contato_1' => '11999990000',
            'email' => 'smoke_' . $suffix . '@example.test',
            'nome_responsavel' => 'Responsavel Smoke ' . $suffix,
            'cpf_responsavel' => $this->fakeCpf(),
            'rg_responsavel' => (string) random_int(10000000, 99999999),
            'grau_parentesco' => 'Mae',
            'encaminha_por' => 'Teste automatizado',
            'queixa_principal' => 'Fluxo HTTP automatizado',
            'escola' => 'Escola Smoke',
            'periodo' => 'Tarde'
        ]);
    }

    private function fakeCpf() {
        return str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
    }

    private function tinyPng() {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');
    }

    private function fetchValue($sql, array $params = []) {
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function fetchRow($sql, array $params = []) {
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function execute($sql, array $params = []) {
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    private function prepareAuditContext() {
        try {
            Database::getConnection()->exec('SET @usuario_id = 1');
            Database::getConnection()->exec("SET @ip_usuario = '127.0.0.1'");
        } catch (Throwable $exception) {
            // O smoke HTTP tambem roda em ambientes sem banco direto; nesses casos as paginas publicas ainda podem ser testadas.
        }
    }

    private function cleanupPublicUpload($publicPath) {
        $uploadsRoot = realpath(BASE_PATH . '/uploads');
        $filePath = realpath(BASE_PATH . '/' . ltrim($publicPath, '/\\'));

        if (!$uploadsRoot || !$filePath || !is_file($filePath)) {
            return;
        }

        if (strpos($filePath, $uploadsRoot . DIRECTORY_SEPARATOR) === 0) {
            @unlink($filePath);
        }
    }

    private function cleanupUploadSubdir($subdir, $hadHtaccess) {
        $dir = BASE_PATH . '/uploads/' . $subdir;
        if (!is_dir($dir)) {
            return;
        }

        $htaccess = $dir . '/.htaccess';
        if (!$hadHtaccess && is_file($htaccess)) {
            @unlink($htaccess);
        }

        $entries = array_values(array_diff(scandir($dir) ?: [], ['.', '..']));
        if (empty($entries)) {
            @rmdir($dir);
        }

        $uploadsDir = BASE_PATH . '/uploads';
        if (is_dir($uploadsDir)) {
            $rootEntries = array_values(array_diff(scandir($uploadsDir) ?: [], ['.', '..']));
            if (empty($rootEntries)) {
                @rmdir($uploadsDir);
            }
        }
    }
}

$baseUrl = getenv('APP_BASE_URL') ?: 'http://localhost';
$test = new HttpSmokeTest($baseUrl);
$failures = [];
$totalTests = 0;
$totalAssertions = 0;

foreach (get_class_methods($test) as $method) {
    if (strpos($method, 'test') !== 0) {
        continue;
    }

    $totalTests++;
    try {
        $before = $test->assertionCount();
        $test->$method();
        $totalAssertions += ($test->assertionCount() - $before);
        echo '.';
    } catch (Throwable $exception) {
        echo 'F';
        $failures[] = 'HttpSmokeTest::' . $method . ' - ' . $exception->getMessage();
    }
}

echo PHP_EOL;

if (!empty($failures)) {
    echo "Falhas:" . PHP_EOL;
    foreach ($failures as $failure) {
        echo "- " . $failure . PHP_EOL;
    }
    exit(1);
}

echo "OK: {$totalTests} smoke tests HTTP, {$totalAssertions} assercoes." . PHP_EOL;
