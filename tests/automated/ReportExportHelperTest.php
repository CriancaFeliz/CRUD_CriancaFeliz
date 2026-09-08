<?php

class ReportExportHelperTest extends TestCase {
    public function testCsvEscapesValuesForSpreadsheetUse() {
        $csv = ReportExportHelper::csv([
            'nome' => 'Nome',
            'observacao' => 'Observacao'
        ], [
            [
                'nome' => 'Maria "Teste"',
                'observacao' => "linha 1\nlinha 2"
            ]
        ]);

        $this->assertTrue(strpos($csv, '"Nome","Observacao"') === 0);
        $this->assertTrue(strpos($csv, '"Maria ""Teste"""') !== false);
        $this->assertTrue(strpos($csv, "\"linha 1\nlinha 2\"") !== false);
    }

    public function testCsvNeutralizesSpreadsheetFormulas() {
        $csv = ReportExportHelper::csv(['valor' => 'Valor'], [
            ['valor' => '=HYPERLINK("https://example.test")']
        ]);

        $this->assertTrue(strpos($csv, '"\'=HYPERLINK(""https://example.test"")"') !== false);
    }
}
