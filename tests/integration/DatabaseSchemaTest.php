<?php

class DatabaseSchemaTest extends IntegrationTestCase {
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
            'log',
            'atendidos_com_alerta'
        ];

        foreach ($expected as $table) {
            $this->assertTrue($this->logicalTableExists($table), "Tabela ou view esperada nao encontrada: {$table}");
        }
    }

    public function testInitialAdminAndSampleDataExist() {
        $admin = $this->fetchRow('SELECT * FROM usuario WHERE email = ? LIMIT 1', ['admin@criancafeliz.org']);

        $this->assertNotEmpty($admin, 'Usuario admin inicial nao encontrado');
        $this->assertSame('admin', $admin['nivel']);
        $this->assertTrue(PasswordHelper::verify('AlterarEstaSenha!2026', $admin['Senha']));
        $this->assertGreaterThanOrEqual(3, (int) $this->fetchValue('SELECT COUNT(*) FROM atendido'));
        $this->assertGreaterThanOrEqual(1, (int) $this->fetchValue('SELECT COUNT(*) FROM oficina WHERE ativo = 1'));
    }
}
