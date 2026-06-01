<?php

abstract class IntegrationTestCase extends TestCase {
    protected function pdo() {
        return Database::getConnection();
    }

    protected function unique($prefix = 'test') {
        return $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
    }

    protected function fakeCpf() {
        return str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
    }

    protected function fetchValue($sql, array $params = []) {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    protected function fetchRow($sql, array $params = []) {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function logicalTableExists($tableName) {
        $rows = $this->pdo()->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
        $expected = strtolower($tableName);

        foreach ($rows as $row) {
            if (strtolower($row[0]) === $expected) {
                return true;
            }
        }

        return false;
    }

    protected function createAcolhimentoFixture(array $overrides = []) {
        $suffix = $this->unique('acolhimento');
        $cpf = $overrides['cpf'] ?? $this->fakeCpf();
        $responsavelCpf = $overrides['cpf_responsavel'] ?? $this->fakeCpf();

        $data = array_merge([
            'nome_completo' => 'Teste Acolhimento ' . $suffix,
            'cpf' => $cpf,
            'rg' => random_int(10000000, 99999999),
            'data_nascimento' => '10/05/2015',
            'data_acolhimento' => '01/06/2026',
            'endereco' => 'Rua de Teste',
            'numero' => '123',
            'complemento' => 'Casa',
            'bairro' => 'Centro',
            'cidade' => 'Guarulhos',
            'estado' => 'SP',
            'cep' => '07000000',
            'contato_1' => '11999990000',
            'email' => $suffix . '@example.test',
            'nome_responsavel' => 'Responsavel ' . $suffix,
            'cpf_responsavel' => $responsavelCpf,
            'rg_responsavel' => random_int(10000000, 99999999),
            'grau_parentesco' => 'Mae',
            'encaminha_por' => 'Teste automatizado',
            'queixa_principal' => 'Fluxo de teste automatizado',
            'escola' => 'Escola Teste',
            'periodo' => 'Tarde'
        ], $overrides);

        return (new Acolhimento())->createFicha($data);
    }

    protected function createSocioeconomicoFixture(array $overrides = []) {
        $suffix = $this->unique('socio');
        $cpf = $overrides['cpf'] ?? $this->fakeCpf();

        $data = array_merge([
            'nome_entrevistado' => 'Teste Socio ' . $suffix,
            'nome_completo' => 'Teste Socio ' . $suffix,
            'nome_menor' => 'Menor ' . $suffix,
            'cpf' => $cpf,
            'rg' => random_int(10000000, 99999999),
            'data_nascimento' => '20/08/2014',
            'data_acolhimento' => '01/06/2026',
            'endereco' => 'Rua Socio',
            'numero' => '456',
            'bairro' => 'Bairro Teste',
            'cidade' => 'Guarulhos',
            'cep' => '07000001',
            'renda_familiar' => '1800,00',
            'pessoas_casa' => 3,
            'situacao_moradia' => 'Propria',
            'tipo_moradia' => 'Casa',
            'numero_comodos' => 4,
            'construcao' => 'Alvenaria',
            'residencia' => 'Urbana',
            'assistente_social' => 'Assistente Teste',
            'cadunico' => 'Sim',
            'agua' => '1',
            'esgoto' => '1',
            'energia' => '1',
            'familia_json' => json_encode([
                [
                    'nome' => 'Familiar ' . $suffix,
                    'parentesco' => 'Mae',
                    'dataNasc' => '01/01/1985',
                    'formacao' => 'Ensino medio',
                    'renda' => '1800'
                ]
            ]),
            'despesas_json' => json_encode([
                [
                    'tipo' => 'Aluguel',
                    'valor' => '700',
                    'renda' => '0'
                ]
            ])
        ], $overrides);

        return (new Socioeconomico())->createFicha($data);
    }
}
