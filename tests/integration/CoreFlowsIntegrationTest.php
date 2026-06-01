<?php

class CoreFlowsIntegrationTest extends IntegrationTestCase {
    public function testAcolhimentoCanBeCreatedAndFoundByCpf() {
        $cpf = $this->fakeCpf();
        $created = $this->createAcolhimentoFixture(['cpf' => $cpf]);
        $found = (new Acolhimento())->findByCpf($cpf);

        $this->assertNotEmpty($created['id']);
        $this->assertNotEmpty($found);
        $this->assertSame($created['id'], $found['id']);
        $this->assertSame($cpf, $found['cpf']);
    }

    public function testSocioeconomicoCreatesFamilyExpensesAndAuditLog() {
        $_SESSION['user_id'] = 1;
        $this->pdo()->exec('SET @usuario_id = 1');
        $this->pdo()->exec("SET @ip_usuario = '127.0.0.1'");

        $created = $this->createSocioeconomicoFixture();
        $atendidoId = $created['idatendido'];
        $fichaId = $created['idficha'];

        $this->assertNotEmpty($atendidoId);
        $this->assertNotEmpty($fichaId);
        $this->assertEquals(1800.0, $created['renda_familiar']);
        $this->assertGreaterThanOrEqual(1, count($created['familia']));
        $this->assertGreaterThanOrEqual(1, count($created['despesas']));
        $this->assertGreaterThanOrEqual(1, (int) $this->fetchValue(
            'SELECT COUNT(*) FROM log WHERE tabela_afetada = ? AND acao = ? AND id_registro = ?',
            ['ficha_socioeconomico', 'INSERT', $fichaId]
        ));
    }

    public function testDailyFrequencyAlertAndDesligamentoLifecycle() {
        $_SESSION['user_id'] = 1;
        $created = $this->createAcolhimentoFixture();
        $atendidoId = $created['id'];

        $frequencia = new FrequenciaDia();
        $this->assertTrue($frequencia->registrarFalta($atendidoId, '2026-05-01'));
        $this->assertTrue($frequencia->registrarFalta($atendidoId, '2026-05-02'));
        $this->assertTrue($frequencia->registrarFalta($atendidoId, '2026-05-03'));

        $stats = $frequencia->getEstatisticas($atendidoId);
        $this->assertSame(3, (int) $stats['faltas']);

        $alertas = $frequencia->getAtendidosComAlertas();
        $alertIds = array_map(function ($row) {
            return (int) $row['idatendido'];
        }, $alertas);
        $this->assertTrue(in_array((int) $atendidoId, $alertIds, true), 'Atendido com tres faltas deve aparecer nos alertas');

        $desligamento = new Desligamento();
        $desligados = $desligamento->desligarPorExcessoFaltas();
        $desligadosIds = array_map(function ($row) {
            return (int) $row['idatendido'];
        }, $desligados);

        $this->assertTrue(in_array((int) $atendidoId, $desligadosIds, true), 'Atendido deve ser desligado automaticamente');
        $this->assertTrue($desligamento->isDesligado($atendidoId));

        $this->assertTrue($desligamento->cancelarDesligamento($atendidoId));
        $this->assertFalse($desligamento->isDesligado($atendidoId));
        $this->assertSame('Ativo', $this->fetchValue('SELECT status FROM atendido WHERE idatendido = ?', [$atendidoId]));
    }

    public function testDocumentCanBeAttachedToProntuarioRecord() {
        $created = $this->createAcolhimentoFixture();
        $documentModel = new Document();

        $document = $documentModel->createForAtendido($created['id'], 'rg', 'uploads/documents/teste-integracao.pdf');
        $documents = $documentModel->findByAtendido($created['id']);

        $this->assertNotEmpty($document['iddocumento']);
        $this->assertGreaterThanOrEqual(1, count($documents));
        $this->assertSame('uploads/documents/teste-integracao.pdf', $documents[0]['arquivo']);
    }

    public function testPsychologyNoteLifecycleUsesPatientCpf() {
        $_SESSION['user_id'] = 1;
        $created = $this->createAcolhimentoFixture();
        $service = new PsychologyService();

        $patient = $service->getPatient($created['cpf']);
        $this->assertNotEmpty($patient);
        $this->assertSame($created['cpf'], $patient['cpf']);

        $createdNote = $service->saveNote([
            'patient_cpf' => $created['cpf'],
            'note_type' => 'consulta',
            'title' => 'Nota de integracao',
            'content' => 'Conteudo criado pelo teste de integracao',
            'mood_assessment' => 4
        ]);

        $this->assertTrue($createdNote['success']);
        $noteId = $createdNote['id'];
        $note = $service->getAnnotationById($noteId);
        $this->assertSame('Nota de integracao', $note['title']);

        $updated = $service->updateNote($noteId, [
            'title' => 'Nota atualizada',
            'content' => 'Conteudo atualizado',
            'note_type' => 'evolucao',
            'mood_assessment' => 5
        ]);
        $this->assertTrue($updated['success']);

        $updatedNote = $service->getAnnotationById($noteId);
        $this->assertSame('Nota atualizada', $updatedNote['title']);
        $this->assertSame('evolucao', $updatedNote['note_type']);

        $deleted = $service->deleteNote($noteId);
        $this->assertTrue($deleted['success']);
        $this->assertSame(null, $service->getAnnotationById($noteId));
    }
}
