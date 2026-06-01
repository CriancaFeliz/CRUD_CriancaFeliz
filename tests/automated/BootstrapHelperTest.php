<?php

class BootstrapHelperTest extends TestCase {
    public function testEmailValidation() {
        $this->assertTrue(validateEmail('admin@criancafeliz.org') !== false);
        $this->assertFalse(validateEmail('email-invalido'));
    }

    public function testInputSanitization() {
        $this->assertSame('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', sanitizeInput("<script>alert('x')</script>"));
        $this->assertSame(['nome' => 'Maria'], sanitizeInput(['nome' => ' Maria ']));
    }

    public function testDateFormatting() {
        $this->assertSame('2026-06-01', formatDateToDb('01/06/2026'));
        $this->assertSame('01/06/2026', formatDateToBr('2026-06-01'));
        $this->assertSame('', formatDateToBr(''));
    }
}
