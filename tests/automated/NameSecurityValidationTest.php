<?php
require_once __DIR__ . '/../../app/bootstrap.php';

class NameSecurityValidationTest {
    private $service;

    public function __construct() {
        $this->service = new AcolhimentoService();
    }

    public function run() {
        echo "Iniciando testes de seguranca para Nome Completo e Encaminhado por...\n";
        $passed = 0;
        $failed = 0;

        $baseData = [
            'nome_completo' => 'Maria Eduarda da Silva',
            'rg' => '123456789',
            'cpf' => '12345678901',
            'data_nascimento' => '10/05/2015',
            'data_acolhimento' => '01/06/2026',
            'encaminha_por' => 'Conselho Tutelar',
            'queixa_principal' => 'Queixa de teste',
            'endereco' => 'Rua Principal',
            'numero' => '100',
            'cep' => '07000000',
            'bairro' => 'Centro',
            'cidade' => 'Guarulhos',
            'estado' => 'SP',
            'contato_1' => '11999990000',
            'nome_responsavel' => 'Ana Clara da Silva',
            'cpf_responsavel' => '12345678901',
            'grau_parentesco' => 'Mae'
        ];

        // 1. Rejeitar numeros em nome_completo
        $this->assertValidationFails(
            array_merge($baseData, ['nome_completo' => 'Maria 123 Silva']),
            'não pode conter números',
            'Rejeitar numeros em nome_completo',
            $passed, $failed
        );

        // 2. Rejeitar emojis em nome_completo
        $this->assertValidationFails(
            array_merge($baseData, ['nome_completo' => 'Maria 😊 Silva']),
            'apenas letras e espaços',
            'Rejeitar emojis em nome_completo',
            $passed, $failed
        );

        // 3. Rejeitar caracteres especiais / scripts em nome_completo
        $this->assertValidationFails(
            array_merge($baseData, ['nome_completo' => 'Maria <script>alert()</script>']),
            'apenas letras e espaços',
            'Rejeitar scripts / tags em nome_completo',
            $passed, $failed
        );

        // 4. Rejeitar simbolos como @ e # em nome_completo
        $this->assertValidationFails(
            array_merge($baseData, ['nome_completo' => 'Maria @ Silva # Especial']),
            'apenas letras e espaços',
            'Rejeitar simbolos @ e # em nome_completo',
            $passed, $failed
        );

        // 5. Rejeitar numeros em encaminha_por
        $this->assertValidationFails(
            array_merge($baseData, ['encaminha_por' => 'CRAS 2']),
            'não pode conter números',
            'Rejeitar numeros em encaminha_por',
            $passed, $failed
        );

        // 6. Rejeitar emojis em encaminha_por
        $this->assertValidationFails(
            array_merge($baseData, ['encaminha_por' => 'Conselho 🚀']),
            'apenas letras e espaços',
            'Rejeitar emojis em encaminha_por',
            $passed, $failed
        );

        // 7. Rejeitar caracteres especiais em encaminha_por
        $this->assertValidationFails(
            array_merge($baseData, ['encaminha_por' => 'Vara da Infancia & Juventude']),
            'apenas letras e espaços',
            'Rejeitar caracteres especiais em encaminha_por',
            $passed, $failed
        );

        // 8. Aceitar acentos e nomes em portugues
        $this->assertValidationPasses(
            array_merge($baseData, [
                'nome_completo' => 'João Álvaro da Conceição Açúcar',
                'encaminha_por' => 'Vara da Infância e da Família'
            ]),
            'Aceitar letras com acentos em portugues para nome e encaminhado',
            $passed, $failed
        );

        // 9. Aceitar encaminha_por vazio / null
        $this->assertValidationPasses(
            array_merge($baseData, ['encaminha_por' => '']),
            'Aceitar encaminha_por vazio (opcional)',
            $passed, $failed
        );

        echo "\nResumo: {$passed} passaram, {$failed} falharam.\n";
        return ($failed === 0);
    }

    private function assertValidationFails(array $data, string $expectedMessagePart, string $testName, int &$passed, int &$failed) {
        try {
            // Invocar validateFichaData usando Reflection
            $ref = new ReflectionClass($this->service);
            $method = $ref->getMethod('validateFichaData');
            $method->setAccessible(true);
            $method->invoke($this->service, $data);

            echo "[FAIL] $testName: Esperava excecao contendo '$expectedMessagePart', mas nao lancou nada.\n";
            $failed++;
        } catch (Exception $e) {
            if (stripos($e->getMessage(), $expectedMessagePart) !== false) {
                echo "[PASS] $testName: Bloqueado com sucesso ('{$e->getMessage()}').\n";
                $passed++;
            } else {
                echo "[FAIL] $testName: Mensagem inesperada '{$e->getMessage()}'. Esperava '$expectedMessagePart'.\n";
                $failed++;
            }
        }
    }

    private function assertValidationPasses(array $data, string $testName, int &$passed, int &$failed) {
        try {
            $ref = new ReflectionClass($this->service);
            $method = $ref->getMethod('validateFichaData');
            $method->setAccessible(true);
            $method->invoke($this->service, $data);

            echo "[PASS] $testName: Validacao passou corretamente.\n";
            $passed++;
        } catch (Exception $e) {
            echo "[FAIL] $testName: Nao deveria falhar, mas lancou excecao: '{$e->getMessage()}'.\n";
            $failed++;
        }
    }
}

$test = new NameSecurityValidationTest();
$success = $test->run();
exit($success ? 0 : 1);
