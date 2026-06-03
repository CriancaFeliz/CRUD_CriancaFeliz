<?php

class LogHelperTest extends TestCase {
    public function testMasksSensitiveValuesInLogPayloads() {
        $maskedJson = LogHelper::maskSensitiveValue('{"nome":"Maria","senha":"abc123","nested":{"token":"tok_456"}}');

        $this->assertFalse(strpos($maskedJson, 'abc123') !== false);
        $this->assertFalse(strpos($maskedJson, 'tok_456') !== false);
        $this->assertTrue(strpos($maskedJson, '[valor sensivel mascarado]') !== false);

        $maskedText = LogHelper::maskSensitiveValue('senha=MinhaSenha!2026; email=teste@example.com');
        $this->assertFalse(strpos($maskedText, 'MinhaSenha!2026') !== false);
        $this->assertTrue(strpos($maskedText, 'teste@example.com') !== false);
    }

    public function testMasksLogRowByChangedField() {
        $masked = LogHelper::maskLogRow([
            'campo_alterado' => 'Senha',
            'registro_alt' => 'Alteracao de senha do usuario',
            'valor_anterior' => '$argon2id$valor-antigo',
            'valor_atual' => '$argon2id$valor-novo'
        ]);

        $this->assertSame('[valor sensivel mascarado]', $masked['valor_anterior']);
        $this->assertSame('[valor sensivel mascarado]', $masked['valor_atual']);
    }

    public function testNeutralizesSpreadsheetFormulas() {
        $this->assertSame("'=HYPERLINK(\"http://example.com\")", LogHelper::neutralizeSpreadsheetFormula('=HYPERLINK("http://example.com")'));
        $this->assertSame('texto comum', LogHelper::neutralizeSpreadsheetFormula('texto comum'));
    }
}
