<?php

class SecurityIntegrationTest extends IntegrationTestCase {
    public function testRateLimiterBlocksAndCanClearAnIdentifier() {
        $identifier = $this->unique('rate-limit') . '@example.test';
        $limiter = new RateLimitService();

        $this->assertTrue($limiter->isAllowed('integration_login', $identifier, 2, 3600));
        $limiter->hit('integration_login', $identifier, 2, 3600, 900);
        $this->assertTrue($limiter->isAllowed('integration_login', $identifier, 2, 3600));
        $limiter->hit('integration_login', $identifier, 2, 3600, 900);
        $this->assertFalse($limiter->isAllowed('integration_login', $identifier, 2, 3600));

        $limiter->clear('integration_login', $identifier);
        $this->assertTrue($limiter->isAllowed('integration_login', $identifier, 2, 3600));
    }

    public function testAuditLogSurvivesUserDeletion() {
        $email = $this->unique('audit-user') . '@example.test';
        $user = (new User())->createUser([
            'name' => 'Usuário de Auditoria',
            'email' => $email,
            'password' => 'AuditTest!2026',
            'role' => 'funcionario',
            'status' => 'Ativo'
        ]);
        $userId = $user['idusuario'] ?? $user['id'];

        $stmt = $this->pdo()->prepare(
            'INSERT INTO log (data_alteracao, registro_alt, acao, tabela_afetada, id_usuario)
             VALUES (NOW(), ?, ?, ?, ?)'
        );
        $stmt->execute(['Teste de preservação', 'TEST', 'usuario', $userId]);
        $logId = $this->pdo()->lastInsertId();

        (new User())->delete($userId);

        $this->assertSame(null, $this->fetchValue('SELECT id_usuario FROM log WHERE id_log = ?', [$logId]));
        $this->assertSame(1, (int) $this->fetchValue('SELECT COUNT(*) FROM log WHERE id_log = ?', [$logId]));
    }
}
