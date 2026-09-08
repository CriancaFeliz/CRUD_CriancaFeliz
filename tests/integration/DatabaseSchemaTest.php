<?php

class DatabaseSchemaTest extends IntegrationTestCase {
    public function setUp() {
        // Estes testes validam o estado imediatamente após o setup limpo.
    }

    public function testDatabaseConnectionWorks() {
        $this->assertSame('1', (string) $this->fetchValue('SELECT 1'));
        $this->assertNotEmpty($this->fetchValue('SELECT DATABASE()'));
    }

    public function testCriticalTablesAndViewExist() {
        $expected = [
            'usuario',
            'atendido',
            'responsavel',
            'ficha_socioeconomico',
            'familia',
            'despesas',
            'frequencia_dia',
            'frequencia_oficina',
            'oficina',
            'desligamento',
            'documento',
            'anotacao_psicologica',
            'password_reset_tokens',
            'auth_rate_limits',
            'log',
            'atendidos_com_alerta'
        ];

        foreach ($expected as $table) {
            $this->assertTrue($this->logicalTableExists($table), "Tabela ou view esperada nao encontrada: {$table}");
        }
    }

    public function testFreshSetupDoesNotExposeDefaultCredentialsOrPersonalData() {
        $this->assertSame(0, (int) $this->fetchValue('SELECT COUNT(*) FROM usuario'));
        $this->assertSame(0, (int) $this->fetchValue('SELECT COUNT(*) FROM atendido'));
        $this->assertSame(0, (int) $this->fetchValue('SELECT COUNT(*) FROM responsavel'));
        $this->assertGreaterThanOrEqual(1, (int) $this->fetchValue('SELECT COUNT(*) FROM oficina WHERE ativo = 1'));
    }
}
