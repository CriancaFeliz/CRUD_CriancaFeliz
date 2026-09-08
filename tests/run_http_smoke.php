<?php

$root = dirname(__DIR__);
$sessionPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'criancafeliz_php_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}
ini_set('session.save_path', $sessionPath);

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
    private const ADMIN_EMAIL = 'smoke_admin@example.test';
    private const ADMIN_PASSWORD = 'SmokeAdmin!2026';

    private $baseUrl;

    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->ensureAdminFixture();
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
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);

        $pages = [
            '/dashboard.php',
            '/prontuarios.php',
            '/acolhimento_list.php',
            '/socioeconomico_list.php',
            '/faltas.php',
            '/users.php',
            '/logs.php',
            '/reports.php',
            '/profile.php'
        ];

        foreach ($pages as $page) {
            $response = $client->get($page);
            $this->assertSame(200, $response['status'], "Pagina critica deve abrir: {$page}");
            $this->assertTrue(strlen($response['body']) > 100, "Pagina critica retornou corpo pequeno demais: {$page}");
        }

        $reportPage = $client->get('/reports.php?type=frequencia&data_inicio=2026-01-01&data_fim=2026-12-31');
        $this->assertTrue(strpos($reportPage['body'], 'Central de Relatórios') !== false, 'Central de relatórios deve renderizar');
        $this->assertTrue(strpos($reportPage['body'], 'CPF protegido') !== false, 'Relatório deve informar proteção de CPF');

        $csv = $client->get('/reports.php?action=export&type=atendidos');
        $this->assertSame(200, $csv['status'], 'Exportação CSV de relatório deve funcionar para admin');
        $this->assertTrue($this->hasHeader($csv['headers'], 'Content-Type', 'text/csv; charset=UTF-8'), 'Exportação deve retornar CSV');
        $this->assertTrue(strpos($csv['body'], 'CPF protegido') !== false, 'CSV deve conter cabeçalho esperado');
    }

    public function testRolePermissionsProtectSensitiveAreas() {
        $admin = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
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
        $this->assertRedirectsToDashboard($employee->get('/acolhimento_list.php?action=export'), 'Funcionario nao deve exportar dados de acolhimento');
        $this->assertRedirectsToDashboard($employee->get('/socioeconomico_list.php?action=report'), 'Funcionario nao deve abrir relatorio socioeconomico');
        $this->assertRedirectsToDashboard($employee->get('/socioeconomico_list.php?action=export'), 'Funcionario nao deve exportar dados socioeconomicos');
        $this->assertRedirectsToDashboard($employee->get('/reports.php'), 'Funcionario nao deve acessar a central de relatorios');
        $this->assertRedirectsToDashboard(
            $employee->post('/socioeconomico_list.php?delete=1', ['csrf_token' => 'token-invalido']),
            'Funcionario nao deve excluir ficha socioeconomica'
        );
    }

    public function testSecurityHeadersCsrfAndPostLogout() {
        $public = $this->newClient()->get('/');
        $this->assertTrue($this->hasHeader($public['headers'], 'X-Content-Type-Options', 'nosniff'), 'Resposta deve impedir MIME sniffing');
        $this->assertTrue($this->hasHeader($public['headers'], 'X-Frame-Options', 'DENY'), 'Resposta deve impedir carregamento em frame');
        $this->assertTrue($this->hasHeader($public['headers'], 'Referrer-Policy', 'same-origin'), 'Resposta deve limitar o referrer');
        $this->assertTrue($this->hasCookieAttribute($public['headers'], 'HttpOnly'), 'Cookie de sessao deve ser HttpOnly');
        $this->assertTrue($this->hasCookieAttribute($public['headers'], 'SameSite=Lax'), 'Cookie de sessao deve usar SameSite');

        $protectedUser = $this->createUserFixture('funcionario');
        $admin = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $invalidDelete = $admin->post('/users.php?action=delete&id=' . urlencode($protectedUser['id']), [
            'csrf_token' => 'token-invalido'
        ]);
        $this->assertSame(400, $invalidDelete['status'], 'Exclusao com CSRF invalido deve ser rejeitada');
        $this->assertSame(1, (int) $this->fetchValue('SELECT COUNT(*) FROM usuario WHERE idusuario = ?', [$protectedUser['id']]), 'CSRF invalido nao pode excluir usuario');

        $getLogout = $admin->get('/logout.php');
        $this->assertSame(302, $getLogout['status'], 'Logout por GET deve ser rejeitado');
        $this->assertSame(200, $admin->get('/dashboard.php')['status'], 'Logout por GET nao deve encerrar a sessao');

        $dashboard = $admin->get('/dashboard.php');
        $csrfToken = $this->extractCsrfToken($dashboard['body']);
        $logout = $admin->post('/logout.php', ['csrf_token' => $csrfToken]);
        $this->assertSame(302, $logout['status'], 'Logout seguro deve redirecionar');
        $this->assertTrue(strpos((string) $logout['location'], 'index.php') !== false, 'Logout seguro deve voltar ao login');
        $this->assertSame(302, $admin->get('/dashboard.php')['status'], 'Sessao deve ser encerrada depois do logout');
    }

    public function testProfilePhotoUploadAcceptsValidPng() {
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $profile = $client->get('/profile.php');
        $this->assertSame(200, $profile['status'], 'Perfil deve abrir para usuario autenticado');

        $csrfToken = $this->extractCsrfToken($profile['body']);
        $adminId = (int)$this->fetchValue('SELECT idusuario FROM usuario WHERE email = ?', [self::ADMIN_EMAIL]);
        $previousPhoto = $this->fetchValue('SELECT foto_perfil FROM usuario WHERE email = ?', [self::ADMIN_EMAIL]);
        $privatePath = null;

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
            $this->assertNotEmpty($payload['photo'] ?? null, 'Upload de foto deve retornar rota autenticada');

            $privatePath = $this->fetchValue('SELECT foto_perfil FROM usuario WHERE email = ?', [self::ADMIN_EMAIL]);
            $this->assertTrue(
                strpos((string)$privatePath, 'var/private/profiles/') === 0,
                'Foto deve ficar na area privada'
            );
            $this->assertTrue(is_file(BASE_PATH . '/' . $privatePath), 'Arquivo de perfil deve existir no disco');
            $this->assertSame(
                'profile.php?action=photo&id=' . $adminId,
                $payload['photo'],
                'Resposta deve apontar para a rota autenticada'
            );

            $direct = $client->get('/' . $privatePath);
            $this->assertTrue(in_array($direct['status'], [403, 404], true), 'Foto privada nao pode ter URL direta');

            $served = $client->get('/' . $payload['photo']);
            $this->assertSame(200, $served['status'], 'Foto deve abrir para o proprio usuario autenticado');
            $this->assertTrue(
                stripos(implode("\n", $served['headers']), 'Cache-Control: private, no-store') !== false,
                'Foto privada nao deve ficar em cache compartilhado'
            );
        } finally {
            $this->execute('UPDATE usuario SET foto_perfil = ? WHERE email = ?', [$previousPhoto, self::ADMIN_EMAIL]);
            if ($privatePath) {
                $this->cleanupPrivateUpload($privatePath, 'profiles');
            }
        }
    }

    public function testAdminCanUploadProntuarioDocumentViaMultipart() {
        $created = $this->createAcolhimentoFixture();
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $show = $client->get('/prontuarios.php?action=show&cpf=' . urlencode($created['cpf']));
        $this->assertSame(200, $show['status'], 'Prontuario deve abrir antes do upload');

        $csrfToken = $this->extractCsrfToken($show['body']);
        $privatePath = null;

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
            $this->assertTrue(strpos($document['arquivo'], 'var/private/documents/') === 0, 'Documento deve ficar na area privada');

            $privatePath = $document['arquivo'];
            $this->assertTrue(is_file(BASE_PATH . '/' . $privatePath), 'Documento enviado deve existir no disco');

            $direct = $client->get('/' . $privatePath);
            $this->assertTrue(
                in_array($direct['status'], [403, 404], true),
                'Documento privado nao pode ser acessado por URL direta'
            );

            $view = $client->get('/prontuarios.php?action=document&id=' . (int) $document['iddocumento']);
            $this->assertSame(200, $view['status'], 'Documento privado deve ser entregue pela rota autenticada');
            $this->assertTrue(
                stripos(implode("\n", $view['headers']), 'Cache-Control: private, no-store') !== false,
                'Documento privado nao deve ficar em cache compartilhado'
            );
        } finally {
            if ($privatePath) {
                $this->cleanupPrivateUpload($privatePath, 'documents');
            }
        }
    }

    public function testProntuarioAccessibleByIdAndCpf() {
        $created = $this->createAcolhimentoFixture();
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);

        // 1. Acesso com action=show, cpf e id (link gerado pelo modal de aniversariantes)
        $showFull = $client->get('/prontuarios.php?action=show&cpf=' . urlencode($created['cpf']) . '&id=' . (int)$created['id']);
        $this->assertSame(200, $showFull['status'], 'Prontuario deve abrir com action=show, cpf e id');
        $this->assertTrue(strpos($showFull['body'], $created['nome_completo']) !== false, 'Prontuario deve exibir o nome do atendido');

        // 2. Acesso apenas por ID (action=show&id=...)
        $showById = $client->get('/prontuarios.php?action=show&id=' . (int)$created['id']);
        $this->assertSame(200, $showById['status'], 'Prontuario deve abrir apenas com ID');
        $this->assertTrue(strpos($showById['body'], $created['nome_completo']) !== false, 'Prontuario deve exibir o nome do atendido quando buscado por ID');

        // 3. Acesso direto com query id (prontuarios.php?id=...)
        $showDirect = $client->get('/prontuarios.php?id=' . (int)$created['id']);
        $this->assertSame(200, $showDirect['status'], 'Prontuario deve abrir diretamente com prontuarios.php?id=...');
        $this->assertTrue(strpos($showDirect['body'], $created['nome_completo']) !== false, 'Prontuario deve exibir o nome do atendido quando acessado com prontuarios.php?id=...');
    }

    public function testSocioeconomicWizardPersistsAllStepsWithoutBrowserStorage() {
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $formPage = $client->get('/socioeconomico_form.php');
        $this->assertSame(200, $formPage['status'], 'Assistente socioeconomico deve abrir');
        $this->assertSame(5, substr_count($formPage['body'], 'class="form-step'), 'Assistente deve conter cinco etapas no mesmo formulario');
        $this->assertTrue(strpos($formPage['body'], 'socioeconomico-wizard.js') !== false, 'Assistente deve usar o script seguro');

        $script = $client->get('/js/socioeconomico-wizard.js');
        $this->assertSame(200, $script['status'], 'Script do assistente deve carregar');
        $this->assertTrue(strpos($script['body'], 'sessionStorage') === false, 'Script nao deve persistir dados sensiveis no navegador');
        $this->assertTrue(strpos($script['body'], 'localStorage') === false, 'Script nao deve persistir dados sensiveis localmente');
        $this->assertTrue(strpos($script['body'], 'console.log') === false, 'Script nao deve imprimir dados sensiveis no console');

        $csrfToken = $this->extractCsrfToken($formPage['body']);
        $cpf = $this->fakeCpf();
        $suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
        $response = $client->post('/socioeconomico_form.php', [
            'csrf_token' => $csrfToken,
            'nome_entrevistado' => 'Entrevistado HTTP ' . $suffix,
            'nome_menor' => 'Menor HTTP ' . $suffix,
            'rg' => (string) random_int(100000000, 999999999),
            'cpf' => $cpf,
            'data_acolhimento' => '01/06/2026',
            'assistente_social' => 'Assistente HTTP',
            'residencia' => 'Propria',
            'numero_comodos' => 4,
            'quartos' => 2,
            'banheiro' => 1,
            'agua' => 'Rede Publica',
            'esgoto' => 'Rede Publica',
            'energia' => 'Relogio Proprio',
            'cond_residencia' => 'Boa',
            'moradia' => 'Propria',
            'nr_veiculos' => 1,
            'veiculos_motocicleta' => 1,
            'renda_salario' => '1600.00',
            'renda_bolsa' => '200.00',
            'renda_familiar' => '1800.00',
            'renda_per_capita' => '900.00',
            'qtd_pessoas' => 2,
            'bolsa_familia' => 1,
            'trabalho_clt' => 'Sim',
            'trabalho_clt_qual' => 'Auxiliar',
            'convenio_medico' => 'Nao',
            'cadunico' => 'Sim',
            'familia_json' => json_encode([
                ['nome' => 'Familiar HTTP', 'parentesco' => 'Mae', 'dataNasc' => '01/01/1985', 'formacao' => 'Medio', 'renda' => 1600]
            ]),
            'despesas_json' => json_encode([
                ['tipo' => 'Aluguel', 'valor' => 700, 'renda' => 0]
            ])
        ]);

        $this->assertSame(302, $response['status'], 'Cadastro socioeconomico deve redirecionar depois de salvar');
        $row = $this->fetchRow(
            'SELECT a.idatendido, f.* FROM atendido a INNER JOIN ficha_socioeconomico f ON f.id_atendido = a.idatendido WHERE a.cpf = ? LIMIT 1',
            [$cpf]
        );
        $this->assertNotEmpty($row, 'Cadastro enviado pelas cinco etapas deve existir');
        $this->assertSame(2, (int) $row['quartos'], 'Quantidade de quartos deve ser preservada');
        $this->assertSame(1, (int) $row['banheiros'], 'Quantidade de banheiros deve ser preservada');
        $this->assertEquals(1600.0, $row['renda_salario'], 'Renda salarial deve ser preservada');
        $this->assertEquals(200.0, $row['renda_bolsa'], 'Beneficio deve ser preservado');
        $this->assertSame(1, (int) $row['trabalho_clt'], 'Situacao de trabalho deve ser preservada');
        $this->assertSame(0, (int) $row['convenio_medico'], 'Situacao de convenio deve ser preservada');

        $this->execute('DELETE FROM atendido WHERE idatendido = ?', [$row['idatendido']]);
    }

    public function testAcolhimentoWizardKeepsFieldsInOneSafePage() {
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $formPage = $client->get('/acolhimento_form.php');
        $this->assertSame(200, $formPage['status'], 'Assistente de acolhimento deve abrir');
        $this->assertSame(4, substr_count($formPage['body'], 'class="form-section"'), 'Assistente de acolhimento deve conter quatro etapas');
        $this->assertTrue(strpos($formPage['body'], 'acolhimento-wizard.js') !== false, 'Acolhimento deve usar o script seguro');

        $script = $client->get('/js/acolhimento-wizard.js');
        $this->assertSame(200, $script['status'], 'Script de acolhimento deve carregar');
        $this->assertTrue(strpos($script['body'], 'sessionStorage') === false, 'Acolhimento nao deve persistir dados sensiveis no navegador');
        $this->assertTrue(strpos($script['body'], 'localStorage') === false, 'Acolhimento nao deve persistir dados sensiveis localmente');
        $this->assertTrue(strpos($script['body'], 'console.log') === false, 'Acolhimento nao deve imprimir dados sensiveis no console');
    }

    public function testAcolhimentoPhotoUploadIsPrivateAndSurvivesEdit() {
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $formPage = $client->get('/acolhimento_form.php');
        $this->assertSame(200, $formPage['status'], 'Formulario de acolhimento deve abrir antes do upload');

        $csrfToken = $this->extractCsrfToken($formPage['body']);
        $suffix = date('YmdHis') . '_' . bin2hex(random_bytes(3));
        $alphaSuffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
        $cpf = $this->fakeCpf();
        $fields = [
            'csrf_token' => $csrfToken,
            'nome_completo' => 'Crianca Foto HTTP ' . $alphaSuffix,
            'rg' => (string) random_int(10000000, 99999999),
            'cpf' => $cpf,
            'data_nascimento' => '10/05/2015',
            'data_acolhimento' => '01/06/2026',
            'encaminha_por' => 'Teste automatizado',
            'queixa_principal' => 'Teste seguro de foto',
            'endereco' => 'Rua Smoke',
            'numero' => '123',
            'cep' => '07000000',
            'bairro' => 'Centro',
            'cidade' => 'Guarulhos',
            'estado' => 'SP',
            'nome_responsavel' => 'Responsavel Foto ' . $suffix,
            'rg_responsavel' => (string) random_int(10000000, 99999999),
            'cpf_responsavel' => $this->fakeCpf(),
            'grau_parentesco' => 'Mae',
            'contato_1' => '11999990000'
        ];
        $createdId = null;
        $privatePath = null;

        try {
            $response = $client->postMultipart('/acolhimento_form.php', $fields, [
                'foto' => [
                    'filename' => 'foto-smoke.php.png',
                    'content_type' => 'image/png',
                    'content' => $this->tinyPng()
                ]
            ]);

            $this->assertSame(302, $response['status'], 'Cadastro com foto deve redirecionar depois de salvar');
            $this->assertTrue(
                strpos((string)$response['location'], 'acolhimento_list.php') !== false,
                'Cadastro com foto deve voltar para a listagem'
            );

            $row = $this->fetchRow('SELECT idatendido, foto FROM atendido WHERE cpf = ? LIMIT 1', [$cpf]);
            $this->assertNotEmpty($row, 'Cadastro com foto deve existir no banco');
            $createdId = (int)$row['idatendido'];
            $privatePath = (string)$row['foto'];

            $this->assertTrue(
                strpos($privatePath, 'var/private/children/') === 0,
                'Foto da crianca deve ficar na area privada'
            );
            $this->assertTrue(substr($privatePath, -4) === '.png', 'Extensao deve vir do conteudo real da imagem');
            $this->assertTrue(is_file(BASE_PATH . '/' . $privatePath), 'Foto da crianca deve existir no disco');

            $direct = $client->get('/' . $privatePath);
            $this->assertTrue(in_array($direct['status'], [403, 404], true), 'Foto privada nao pode ter URL direta');

            $served = $client->get('/acolhimento_view.php?action=photo&id=' . $createdId);
            $this->assertSame(200, $served['status'], 'Foto deve abrir pela rota autenticada');
            $this->assertTrue(
                stripos(implode("\n", $served['headers']), 'Cache-Control: private, no-store') !== false,
                'Foto da crianca nao deve ficar em cache compartilhado'
            );

            $anonymous = $this->newClient()->get('/acolhimento_view.php?action=photo&id=' . $createdId);
            $this->assertSame(302, $anonymous['status'], 'Foto da crianca deve exigir autenticacao');

            $fields['id'] = $createdId;
            $edit = $client->post('/acolhimento_form.php', $fields);
            $this->assertSame(302, $edit['status'], 'Edicao sem nova foto deve concluir normalmente');
            $this->assertSame(
                $privatePath,
                (string)$this->fetchValue('SELECT foto FROM atendido WHERE idatendido = ?', [$createdId]),
                'Edicao sem novo upload deve preservar a foto existente'
            );
            $this->assertTrue(is_file(BASE_PATH . '/' . $privatePath), 'Edicao sem upload nao pode apagar a foto');
        } finally {
            if ($createdId) {
                $this->execute('DELETE FROM atendido WHERE idatendido = ?', [$createdId]);
            }
            if ($privatePath) {
                $this->cleanupPrivateUpload($privatePath, 'children');
            }
        }
    }

    public function testNameAndEncaminhadoSecurityValidation() {
        $client = $this->loginAs(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $form = $client->get('/acolhimento_form.php');
        $this->assertSame(200, $form['status'], 'Formulario de acolhimento deve abrir');
        $this->assertTrue(strpos($form['body'], 'pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"') !== false, 'Campo nome_completo deve conter pattern seguro');
        $this->assertTrue(strpos($form['body'], 'pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]*$"') !== false, 'Campo encaminha_por deve conter pattern seguro');

        $csrfToken = $this->extractCsrfToken($form['body']);
        $baseData = [
            'csrf_token' => $csrfToken,
            'nome_completo' => 'Crianca Teste Valida',
            'rg' => (string) random_int(10000000, 99999999),
            'cpf' => $this->fakeCpf(),
            'data_nascimento' => '10/05/2015',
            'data_acolhimento' => '01/06/2026',
            'encaminha_por' => 'Conselho Tutelar',
            'queixa_principal' => 'Teste validacao',
            'endereco' => 'Rua Smoke',
            'numero' => '123',
            'cep' => '07000000',
            'bairro' => 'Centro',
            'cidade' => 'Guarulhos',
            'estado' => 'SP',
            'nome_responsavel' => 'Responsavel Teste',
            'rg_responsavel' => (string) random_int(10000000, 99999999),
            'cpf_responsavel' => $this->fakeCpf(),
            'grau_parentesco' => 'Mae',
            'contato_1' => '11999990000'
        ];

        // 1. Tentar enviar numeros em nome_completo
        $invalidNum = $baseData;
        $invalidNum['nome_completo'] = 'Crianca 123 Invalida';
        $resNum = $client->post('/acolhimento_form.php', $invalidNum);
        $this->assertSame(302, $resNum['status'], 'Backend deve rejeitar numeros em nome_completo');
        $this->assertTrue(strpos((string)$resNum['location'], 'acolhimento_form.php') !== false, 'Deve redirecionar para o form em erro');

        // 2. Tentar enviar emoji em nome_completo
        $invalidEmoji = $baseData;
        $invalidEmoji['nome_completo'] = 'Crianca 😊 Invalida';
        $resEmoji = $client->post('/acolhimento_form.php', $invalidEmoji);
        $this->assertSame(302, $resEmoji['status'], 'Backend deve rejeitar emojis em nome_completo');

        // 3. Tentar enviar numeros em encaminha_por
        $invalidEncNum = $baseData;
        $invalidEncNum['encaminha_por'] = 'CRAS 123';
        $resEncNum = $client->post('/acolhimento_form.php', $invalidEncNum);
        $this->assertSame(302, $resEncNum['status'], 'Backend deve rejeitar numeros em encaminha_por');

        // 4. Tentar enviar emoji em encaminha_por
        $invalidEncEmoji = $baseData;
        $invalidEncEmoji['encaminha_por'] = 'Conselho 🚀 Tutelar';
        $resEncEmoji = $client->post('/acolhimento_form.php', $invalidEncEmoji);
        $this->assertSame(302, $resEncEmoji['status'], 'Backend deve rejeitar emojis em encaminha_por');
    }

    private function newClient() {
        return new HttpSmokeClient($this->baseUrl);
    }

    private function ensureAdminFixture() {
        $users = new User();
        $existing = $users->findByEmail(self::ADMIN_EMAIL);

        if (!$existing) {
            $users->createUser([
                'name' => 'Administrador Smoke',
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'role' => 'admin',
                'status' => 'Ativo'
            ]);
            return;
        }

        $users->updateUser($existing['idusuario'], [
            'password' => self::ADMIN_PASSWORD,
            'role' => 'admin',
            'status' => 'Ativo'
        ]);
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

    private function hasHeader(array $headers, $name, $expectedValue) {
        foreach ($headers as $header) {
            if (stripos($header, $name . ':') === 0) {
                return strcasecmp(trim(substr($header, strlen($name) + 1)), $expectedValue) === 0;
            }
        }

        return false;
    }

    private function hasCookieAttribute(array $headers, $attribute) {
        foreach ($headers as $header) {
            if (stripos($header, 'Set-Cookie:') === 0 && stripos($header, $attribute) !== false) {
                return true;
            }
        }

        return false;
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

    private function cleanupPrivateUpload($privatePath, $subdir) {
        $privateRoot = realpath(BASE_PATH . '/var/private/' . $subdir);
        $filePath = realpath(BASE_PATH . '/' . ltrim($privatePath, '/\\'));

        if ($privateRoot && $filePath && is_file($filePath)
            && strpos($filePath, $privateRoot . DIRECTORY_SEPARATOR) === 0) {
            for ($attempt = 0; $attempt < 5 && is_file($filePath); $attempt++) {
                @unlink($filePath);
                if (is_file($filePath)) {
                    usleep(50000);
                }
            }
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
