<?php

class PasswordHelperTest extends TestCase {
    public function testPasswordPolicyRejectsWeakPasswords() {
        $this->assertFalse(PasswordHelper::isValid('admin123'));
        $this->assertFalse(PasswordHelper::isValid('short'));
        $this->assertFalse(PasswordHelper::isValid(str_repeat(' ', 12)));
    }

    public function testPasswordHashAndVerify() {
        $password = 'SenhaForteParaTeste!2026';
        $hash = PasswordHelper::hash($password);

        $this->assertNotEmpty($hash);
        $this->assertTrue(PasswordHelper::verify($password, $hash));
        $this->assertFalse(PasswordHelper::verify('SenhaErrada!2026', $hash));
        $this->assertTrue(is_bool(PasswordHelper::needsRehash($hash)));
    }

    public function testHashThrowsForInvalidPassword() {
        $this->assertThrows(function () {
            PasswordHelper::hash('admin123');
        }, InvalidArgumentException::class);
    }
}
