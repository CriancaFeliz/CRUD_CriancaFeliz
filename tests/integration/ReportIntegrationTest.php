<?php

class ReportIntegrationTest extends IntegrationTestCase {
    private $createdAttendedIds = [];

    public function tearDown() {
        foreach (array_reverse($this->createdAttendedIds) as $id) {
            $stmt = $this->pdo()->prepare('DELETE FROM atendido WHERE idatendido = ?');
            $stmt->execute([$id]);
        }
        $this->createdAttendedIds = [];
    }

    public function testActiveAndSocioeconomicReportsProtectCpf() {
        $acolhimento = $this->createAcolhimentoFixture();
        $this->createdAttendedIds[] = (int)$acolhimento['id'];

        $reports = new ReportService();
        $active = $reports->generate('atendidos', ['q' => $acolhimento['nome_completo']]);

        $this->assertSame('atendidos', $active['type']);
        $this->assertSame(1, count($active['rows']));
        $this->assertTrue(strpos($active['rows'][0]['cpf'], '***.***.***-') === 0);
        $this->assertFalse(strpos($reports->exportCsv($active), $acolhimento['cpf']) !== false);

        $socio = $this->createSocioeconomicoFixture();
        $this->createdAttendedIds[] = (int)$socio['id'];
        $economic = $reports->generate('socioeconomico', ['q' => $socio['nome_completo']]);

        $this->assertSame(1, count($economic['rows']));
        $this->assertSame('R$ 1.800,00', $economic['rows'][0]['renda_familiar']);
        $this->assertArrayHasKey('faixa_renda', $economic['rows'][0]);
    }

    public function testAttendanceAndDismissalReportsUsePeriodFilters() {
        $ficha = $this->createAcolhimentoFixture();
        $id = (int)$ficha['id'];
        $this->createdAttendedIds[] = $id;

        (new FrequenciaDia())->registrarPresenca($id, '2026-08-10');
        (new FrequenciaDia())->registrarFalta($id, '2026-08-11', null);

        $reports = new ReportService();
        $attendance = $reports->generate('frequencia', [
            'q' => $ficha['nome_completo'],
            'data_inicio' => '2026-08-01',
            'data_fim' => '2026-08-31',
            'origem' => 'dia'
        ]);

        $this->assertSame(2, count($attendance['rows']));
        $this->assertSame('Frequência diária', $attendance['rows'][0]['atividade']);
        $this->assertSame('Falta', $attendance['rows'][0]['status']);

        (new Desligamento())->registrarDesligamento($id, [
            'motivo' => 'Teste de relatório',
            'tipo_motivo' => 'pedido_familia',
            'data_desligamento' => '2026-08-20',
            'automatico' => false,
            'pode_retornar' => true
        ]);

        $dismissals = $reports->generate('desligamentos', [
            'q' => $ficha['nome_completo'],
            'data_inicio' => '2026-08-01',
            'data_fim' => '2026-08-31'
        ]);

        $this->assertSame(1, count($dismissals['rows']));
        $this->assertSame('Pedido da família', $dismissals['rows'][0]['tipo']);
        $this->assertSame('Sim', $dismissals['rows'][0]['pode_retornar']);
    }

    public function testReportValidationAndAuditTrail() {
        $reports = new ReportService();

        $this->assertThrows(function () use ($reports) {
            $reports->generate('desconhecido');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($reports) {
            $reports->generate('frequencia', [
                'data_inicio' => '2026-12-31',
                'data_fim' => '2026-01-01'
            ]);
        }, InvalidArgumentException::class);

        $before = (int)$this->fetchValue("SELECT COUNT(*) FROM log WHERE tabela_afetada = 'relatorios' AND acao = 'CSV'");
        $reports->audit('atendidos', ['q' => 'cpf sensivel'], 'csv');
        $after = (int)$this->fetchValue("SELECT COUNT(*) FROM log WHERE tabela_afetada = 'relatorios' AND acao = 'CSV'");

        $this->assertSame($before + 1, $after);
        $latest = $this->fetchRow("SELECT dados_completos FROM log WHERE tabela_afetada = 'relatorios' AND acao = 'CSV' ORDER BY id_log DESC LIMIT 1");
        $this->assertFalse(strpos($latest['dados_completos'] ?? '', 'cpf sensivel') !== false);
    }
}
