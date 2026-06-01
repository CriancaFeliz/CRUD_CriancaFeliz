<?php

class UserIntegrationTest extends IntegrationTestCase {
    public function testUserCrudAndAuthentication() {
        $userModel = new User();
        $email = $this->unique('user') . '@example.test';
        $password = 'SenhaForteTeste!2026';
        $createdId = null;

        try {
            $created = $userModel->createUser([
                'name' => 'Usuario Integracao',
                'email' => $email,
                'password' => $password,
                'role' => 'funcionario',
                'status' => 'Ativo'
            ]);
            $createdId = $created['idusuario'] ?? $created['id'] ?? null;

            $this->assertNotEmpty($createdId);
            $this->assertSame('funcionario', $created['role']);
            $this->assertFalse(array_key_exists('Senha', $created), 'Hash de senha nao deve vazar no retorno');

            $authenticated = $userModel->authenticate($email, $password);
            $this->assertNotEmpty($authenticated);
            $this->assertSame($email, $authenticated['email']);
            $this->assertSame('funcionario', $authenticated['role']);

            $updated = $userModel->updateUser($createdId, [
                'name' => 'Usuario Integracao Atualizado',
                'email' => $email,
                'role' => 'psicologo',
                'status' => 'Ativo'
            ]);

            $this->assertSame('Usuario Integracao Atualizado', $updated['name']);
            $this->assertSame('psicologo', $updated['role']);
            $this->assertSame(null, $userModel->authenticate($email, 'SenhaErrada!2026'));
        } finally {
            if ($createdId) {
                $userModel->delete($createdId);
            }
        }
    }

    public function testDuplicateEmailIsRejected() {
        $userModel = new User();

        $this->assertThrows(function () use ($userModel) {
            $userModel->createUser([
                'name' => 'Admin Duplicado',
                'email' => 'admin@criancafeliz.org',
                'password' => 'SenhaForteTeste!2026',
                'role' => 'admin',
                'status' => 'Ativo'
            ]);
        }, Exception::class);
    }
}
