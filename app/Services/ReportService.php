<?php

class ReportService {
    private const MAX_ROWS = 10000;

    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getTypes() {
        return [
            'atendidos' => 'Atendidos ativos',
            'frequencia' => 'Frequência e faltas',
            'desligamentos' => 'Desligamentos',
            'socioeconomico' => 'Socioeconômico sintético',
            'usuarios' => 'Relatório de Funcionários',
            'psicologia' => 'Relatório Psicológico'
        ];
    }

    public function generate($type, array $filters = []) {
        if (!array_key_exists($type, $this->getTypes())) {
            throw new InvalidArgumentException('Tipo de relatório inválido.');
        }

        $filters = $this->normalizeFilters($type, $filters);

        switch ($type) {
            case 'frequencia':
                return $this->attendanceReport($filters);
            case 'desligamentos':
                return $this->dismissalsReport($filters);
            case 'socioeconomico':
                return $this->socioeconomicReport($filters);
            case 'usuarios':
                return $this->employeeReport($filters);
            case 'psicologia':
                return $this->psychologyReport($filters);
            case 'atendidos':
            default:
                return $this->activePeopleReport($filters);
        }
    }

    public function exportCsv(array $report) {
        return "\xEF\xBB\xBF" . ReportExportHelper::csv($report['headers'], $report['rows']);
    }

    public function audit($type, array $filters, $format) {
        $safeFilters = [
            'data_inicio' => $filters['data_inicio'] ?? '',
            'data_fim' => $filters['data_fim'] ?? '',
            'origem' => $filters['origem'] ?? 'todos',
            'busca_aplicada' => !empty($filters['q'])
        ];

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO log
                    (data_alteracao, registro_alt, acao, tabela_afetada, id_usuario, ip_usuario, dados_completos)
                 VALUES (NOW(), ?, ?, 'relatorios', ?, ?, ?)"
            );
            $stmt->execute([
                'Relatório ' . ($this->getTypes()[$type] ?? $type),
                strtoupper((string) $format),
                $_SESSION['user_id'] ?? null,
                getClientIp(),
                json_encode($safeFilters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
        } catch (Throwable $exception) {
            reportException($exception, 'reports:audit');
        }
    }

    private function normalizeFilters($type, array $filters) {
        $today = date('Y-m-d');
        $start = trim((string)($filters['data_inicio'] ?? ''));
        $end = trim((string)($filters['data_fim'] ?? ''));

        if ($type === 'frequencia') {
            $start = $start ?: date('Y-m-01');
            $end = $end ?: $today;
        } elseif ($type === 'desligamentos') {
            $start = $start ?: date('Y-01-01');
            $end = $end ?: $today;
        }

        if ($start !== '' && !$this->validDate($start)) {
            throw new InvalidArgumentException('Data inicial inválida.');
        }
        if ($end !== '' && !$this->validDate($end)) {
            throw new InvalidArgumentException('Data final inválida.');
        }
        if ($start !== '' && $end !== '' && $start > $end) {
            throw new InvalidArgumentException('A data inicial deve ser anterior à data final.');
        }

        $origin = (string)($filters['origem'] ?? 'todos');
        if (!in_array($origin, ['todos', 'dia', 'oficina'], true)) {
            $origin = 'todos';
        }

        return [
            'data_inicio' => $start,
            'data_fim' => $end,
            'origem' => $origin,
            'q' => mb_substr(trim((string)($filters['q'] ?? '')), 0, 100, 'UTF-8')
        ];
    }

    private function activePeopleReport(array $filters) {
        $sql = "SELECT a.nome, a.cpf, a.data_nascimento, a.data_acolhimento,
                       a.data_cadastro, a.bairro, r.nome AS responsavel
                FROM atendido a
                LEFT JOIN responsavel r ON r.idresponsavel = a.id_responsavel
                WHERE LOWER(COALESCE(a.status, 'ativo')) IN ('ativo', 'active')
                  AND NOT EXISTS (
                      SELECT 1 FROM desligamento d WHERE d.id_atendido = a.idatendido
                  )";
        $params = [];
        $this->appendDateFilters($sql, $params, 'COALESCE(a.data_acolhimento, a.data_cadastro)', $filters);
        $this->appendSearchFilter($sql, $params, $filters['q'], 'a.nome', 'a.cpf');
        $sql .= ' ORDER BY a.nome ASC LIMIT ' . (self::MAX_ROWS + 1);

        $rawRows = $this->fetchAll($sql, $params);
        $truncated = count($rawRows) > self::MAX_ROWS;
        $rawRows = array_slice($rawRows, 0, self::MAX_ROWS);
        $categories = ['Crianças' => 0, 'Adolescentes' => 0, 'Adultos' => 0];
        $rows = [];

        foreach ($rawRows as $row) {
            $age = calculateAge($row['data_nascimento'] ?? '');
            $category = $age < 12 ? 'Criança' : ($age < 18 ? 'Adolescente' : 'Adulto');
            $categories[$category . 's']++;
            $rows[] = [
                'nome' => $row['nome'] ?? '',
                'cpf' => $this->maskCpf($row['cpf'] ?? ''),
                'idade' => $age,
                'faixa_etaria' => $category,
                'data_acolhimento' => $this->formatDate($row['data_acolhimento'] ?? $row['data_cadastro'] ?? ''),
                'bairro' => $row['bairro'] ?: 'Não informado',
                'responsavel' => $row['responsavel'] ?: 'Não informado'
            ];
        }

        return $this->reportEnvelope(
            'atendidos',
            'Atendidos ativos',
            'Pessoas atualmente vinculadas ao programa.',
            [
                'nome' => 'Nome',
                'cpf' => 'CPF protegido',
                'idade' => 'Idade',
                'faixa_etaria' => 'Faixa etária',
                'data_acolhimento' => 'Acolhimento',
                'bairro' => 'Bairro',
                'responsavel' => 'Responsável'
            ],
            $rows,
            [
                ['label' => 'Atendidos ativos', 'value' => count($rows), 'tone' => 'green'],
                ['label' => 'Crianças', 'value' => $categories['Crianças'], 'tone' => 'blue'],
                ['label' => 'Adolescentes', 'value' => $categories['Adolescentes'], 'tone' => 'orange'],
                ['label' => 'Adultos', 'value' => $categories['Adultos'], 'tone' => 'purple']
            ],
            $filters,
            $truncated
        );
    }

    private function attendanceReport(array $filters) {
        $queries = [];
        $params = [];

        if ($filters['origem'] !== 'oficina') {
            $sql = "SELECT fd.data, a.nome, a.cpf, 'Frequência diária' AS atividade,
                           fd.status, fd.justificativa, u.nome AS registrado_por
                    FROM frequencia_dia fd
                    INNER JOIN atendido a ON a.idatendido = fd.id_atendido
                    LEFT JOIN usuario u ON u.idusuario = fd.registrado_por
                    WHERE 1=1";
            $localParams = [];
            $this->appendDateFilters($sql, $localParams, 'fd.data', $filters);
            $this->appendSearchFilter($sql, $localParams, $filters['q'], 'a.nome', 'a.cpf');
            $queries[] = $sql;
            $params = array_merge($params, $localParams);
        }

        if ($filters['origem'] !== 'dia') {
            $sql = "SELECT fo.data, a.nome, a.cpf, CONCAT('Oficina: ', o.nome) AS atividade,
                           fo.status, fo.justificativa, u.nome AS registrado_por
                    FROM frequencia_oficina fo
                    INNER JOIN atendido a ON a.idatendido = fo.id_atendido
                    INNER JOIN oficina o ON o.id_oficina = fo.id_oficina
                    LEFT JOIN usuario u ON u.idusuario = fo.registrado_por
                    WHERE 1=1";
            $localParams = [];
            $this->appendDateFilters($sql, $localParams, 'fo.data', $filters);
            $this->appendSearchFilter($sql, $localParams, $filters['q'], 'a.nome', 'a.cpf');
            $queries[] = $sql;
            $params = array_merge($params, $localParams);
        }

        $sql = 'SELECT * FROM (' . implode(' UNION ALL ', $queries) . ') relatorio '
             . 'ORDER BY data DESC, nome ASC LIMIT ' . (self::MAX_ROWS + 1);
        $rawRows = $this->fetchAll($sql, $params);
        $truncated = count($rawRows) > self::MAX_ROWS;
        $rawRows = array_slice($rawRows, 0, self::MAX_ROWS);
        $totals = ['P' => 0, 'F' => 0, 'J' => 0];
        $statusLabels = ['P' => 'Presente', 'F' => 'Falta', 'J' => 'Justificada'];
        $rows = [];

        foreach ($rawRows as $row) {
            $status = array_key_exists(($row['status'] ?? ''), $statusLabels) ? $row['status'] : 'F';
            $totals[$status]++;
            $rows[] = [
                'data' => $this->formatDate($row['data'] ?? ''),
                'nome' => $row['nome'] ?? '',
                'cpf' => $this->maskCpf($row['cpf'] ?? ''),
                'atividade' => $row['atividade'] ?? '',
                'status' => $statusLabels[$status],
                'justificativa' => $row['justificativa'] ?: '—',
                'registrado_por' => $row['registrado_por'] ?: 'Sistema'
            ];
        }

        $total = array_sum($totals);
        $presenceRate = $total > 0 ? round(($totals['P'] / $total) * 100, 1) . '%' : '0%';

        return $this->reportEnvelope(
            'frequencia',
            'Frequência e faltas',
            'Registros diários e de oficinas no período informado.',
            [
                'data' => 'Data',
                'nome' => 'Atendido',
                'cpf' => 'CPF protegido',
                'atividade' => 'Origem',
                'status' => 'Situação',
                'justificativa' => 'Justificativa',
                'registrado_por' => 'Registrado por'
            ],
            $rows,
            [
                ['label' => 'Registros', 'value' => $total, 'tone' => 'blue'],
                ['label' => 'Presenças', 'value' => $totals['P'], 'tone' => 'green'],
                ['label' => 'Faltas', 'value' => $totals['F'], 'tone' => 'red'],
                ['label' => 'Presença', 'value' => $presenceRate, 'tone' => 'purple']
            ],
            $filters,
            $truncated
        );
    }

    private function dismissalsReport(array $filters) {
        $sql = "SELECT d.data_desligamento, a.nome, a.cpf, d.tipo_motivo, d.motivo,
                       d.automatico, d.pode_retornar, u.nome AS desligado_por
                FROM desligamento d
                INNER JOIN atendido a ON a.idatendido = d.id_atendido
                LEFT JOIN usuario u ON u.idusuario = d.desligado_por
                WHERE 1=1";
        $params = [];
        $this->appendDateFilters($sql, $params, 'd.data_desligamento', $filters);
        $this->appendSearchFilter($sql, $params, $filters['q'], 'a.nome', 'a.cpf');
        $sql .= ' ORDER BY d.data_desligamento DESC, a.nome ASC LIMIT ' . (self::MAX_ROWS + 1);

        $rawRows = $this->fetchAll($sql, $params);
        $truncated = count($rawRows) > self::MAX_ROWS;
        $rawRows = array_slice($rawRows, 0, self::MAX_ROWS);
        $typeLabels = [
            'idade' => 'Idade',
            'excesso_faltas' => 'Excesso de faltas',
            'pedido_familia' => 'Pedido da família',
            'transferencia' => 'Transferência',
            'outros' => 'Outros'
        ];
        $automatic = 0;
        $canReturn = 0;
        $rows = [];

        foreach ($rawRows as $row) {
            $automatic += !empty($row['automatico']) ? 1 : 0;
            $canReturn += !empty($row['pode_retornar']) ? 1 : 0;
            $rows[] = [
                'data' => $this->formatDate($row['data_desligamento'] ?? ''),
                'nome' => $row['nome'] ?? '',
                'cpf' => $this->maskCpf($row['cpf'] ?? ''),
                'tipo' => $typeLabels[$row['tipo_motivo'] ?? ''] ?? 'Outros',
                'motivo' => $row['motivo'] ?? '',
                'automatico' => !empty($row['automatico']) ? 'Sim' : 'Não',
                'pode_retornar' => !empty($row['pode_retornar']) ? 'Sim' : 'Não',
                'responsavel' => $row['desligado_por'] ?: 'Sistema'
            ];
        }

        return $this->reportEnvelope(
            'desligamentos',
            'Desligamentos',
            'Histórico de desligamentos realizados no período.',
            [
                'data' => 'Data',
                'nome' => 'Atendido',
                'cpf' => 'CPF protegido',
                'tipo' => 'Tipo',
                'motivo' => 'Motivo',
                'automatico' => 'Automático',
                'pode_retornar' => 'Pode retornar',
                'responsavel' => 'Registrado por'
            ],
            $rows,
            [
                ['label' => 'Desligamentos', 'value' => count($rows), 'tone' => 'red'],
                ['label' => 'Automáticos', 'value' => $automatic, 'tone' => 'orange'],
                ['label' => 'Manuais', 'value' => count($rows) - $automatic, 'tone' => 'blue'],
                ['label' => 'Podem retornar', 'value' => $canReturn, 'tone' => 'green']
            ],
            $filters,
            $truncated
        );
    }

    private function socioeconomicReport(array $filters) {
        $sql = "SELECT a.nome, a.cpf, a.bairro, a.data_cadastro, f.renda_familiar,
                       f.renda_per_capita, f.qtd_pessoas, f.cond_residencia, f.moradia,
                       f.bolsa_familia, f.auxilio_brasil, f.bpc, f.auxilio_emergencial,
                       f.seguro_desemprego, f.aposentadoria,
                       (SELECT COUNT(*) FROM familia fam WHERE fam.id_ficha = f.idficha) AS membros_cadastrados
                FROM ficha_socioeconomico f
                INNER JOIN atendido a ON a.idatendido = f.id_atendido
                WHERE 1=1";
        $params = [];
        $this->appendDateFilters($sql, $params, 'a.data_cadastro', $filters);
        $this->appendSearchFilter($sql, $params, $filters['q'], 'a.nome', 'a.cpf');
        $sql .= ' ORDER BY a.nome ASC LIMIT ' . (self::MAX_ROWS + 1);

        $rawRows = $this->fetchAll($sql, $params);
        $truncated = count($rawRows) > self::MAX_ROWS;
        $rawRows = array_slice($rawRows, 0, self::MAX_ROWS);
        $incomeReference = socialIncomeReference();
        $withBenefits = 0;
        $incomeTotal = 0.0;
        $perCapitaTotal = 0.0;
        $rows = [];

        foreach ($rawRows as $row) {
            $benefits = $this->benefitLabels($row);
            $withBenefits += empty($benefits) ? 0 : 1;
            $income = (float)($row['renda_familiar'] ?? 0);
            $perCapita = (float)($row['renda_per_capita'] ?? 0);
            if ($perCapita <= 0 && (int)($row['qtd_pessoas'] ?? 0) > 0) {
                $perCapita = $income / max(1, (int)$row['qtd_pessoas']);
            }
            $incomeTotal += $income;
            $perCapitaTotal += $perCapita;
            $rows[] = [
                'nome' => $row['nome'] ?? '',
                'cpf' => $this->maskCpf($row['cpf'] ?? ''),
                'bairro' => $row['bairro'] ?: 'Não informado',
                'pessoas' => (int)($row['qtd_pessoas'] ?? 0),
                'membros_cadastrados' => (int)($row['membros_cadastrados'] ?? 0),
                'renda_familiar' => $this->formatMoney($income),
                'renda_per_capita' => $this->formatMoney($perCapita),
                'faixa_renda' => $this->incomeBand($perCapita, $incomeReference),
                'moradia' => $row['moradia'] ?: ($row['cond_residencia'] ?: 'Não informada'),
                'beneficios' => empty($benefits) ? 'Nenhum informado' : implode(', ', $benefits)
            ];
        }

        $count = count($rows);
        return $this->reportEnvelope(
            'socioeconomico',
            'Socioeconômico sintético',
            'Visão resumida de renda, composição familiar, moradia e benefícios.',
            [
                'nome' => 'Atendido',
                'cpf' => 'CPF protegido',
                'bairro' => 'Bairro',
                'pessoas' => 'Pessoas no lar',
                'membros_cadastrados' => 'Membros detalhados',
                'renda_familiar' => 'Renda familiar',
                'renda_per_capita' => 'Renda per capita',
                'faixa_renda' => 'Faixa de renda',
                'moradia' => 'Moradia',
                'beneficios' => 'Benefícios'
            ],
            $rows,
            [
                ['label' => 'Famílias', 'value' => $count, 'tone' => 'blue'],
                ['label' => 'Com benefícios', 'value' => $withBenefits, 'tone' => 'green'],
                ['label' => 'Renda média familiar', 'value' => $this->formatMoney($count ? $incomeTotal / $count : 0), 'tone' => 'orange'],
                ['label' => 'Média per capita', 'value' => $this->formatMoney($count ? $perCapitaTotal / $count : 0), 'tone' => 'purple']
            ],
            $filters,
            $truncated,
            'Faixas calculadas com salário mínimo de referência de ' . $this->formatMoney($incomeReference) . '.'
        );
    }

    private function reportEnvelope($type, $title, $description, array $headers, array $rows, array $summary, array $filters, $truncated, $note = '') {
        return [
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'headers' => $headers,
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'generated_at' => date('d/m/Y H:i'),
            'truncated' => (bool)$truncated,
            'note' => $note
        ];
    }

    private function appendDateFilters(&$sql, array &$params, $column, array $filters) {
        if ($filters['data_inicio'] !== '') {
            $sql .= " AND {$column} >= ?";
            $params[] = $filters['data_inicio'];
        }
        if ($filters['data_fim'] !== '') {
            $sql .= " AND {$column} <= ?";
            $params[] = $filters['data_fim'];
        }
    }

    private function appendSearchFilter(&$sql, array &$params, $query, $nameColumn, $cpfColumn) {
        if ($query === '') {
            return;
        }

        $normalizedCpf = preg_replace('/\D+/', '', $query);
        $sql .= " AND ({$nameColumn} LIKE ? OR REPLACE(REPLACE({$cpfColumn}, '.', ''), '-', '') LIKE ?)";
        $params[] = '%' . $query . '%';
        $params[] = '%' . ($normalizedCpf ?: $query) . '%';
    }

    private function fetchAll($sql, array $params) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function validDate($date) {
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function formatDate($date) {
        if (!$date || !$this->validDate(substr((string)$date, 0, 10))) {
            return 'Não informada';
        }
        return DateTime::createFromFormat('!Y-m-d', substr((string)$date, 0, 10))->format('d/m/Y');
    }

    private function maskCpf($cpf) {
        $digits = preg_replace('/\D+/', '', (string)$cpf);
        if (strlen($digits) !== 11) {
            return 'Não informado';
        }
        return '***.***.***-' . substr($digits, -2);
    }

    private function formatMoney($value) {
        return 'R$ ' . number_format((float)$value, 2, ',', '.');
    }

    private function incomeBand($perCapita, $reference) {
        if ($perCapita <= 0) {
            return 'Sem renda informada';
        }
        if ($reference <= 0) {
            return 'Referência não configurada';
        }
        if ($perCapita <= $reference * 0.5) {
            return 'Até 1/2 salário mínimo';
        }
        if ($perCapita <= $reference) {
            return 'De 1/2 a 1 salário mínimo';
        }
        if ($perCapita <= $reference * 2) {
            return 'De 1 a 2 salários mínimos';
        }
        return 'Acima de 2 salários mínimos';
    }

    private function benefitLabels(array $row) {
        $map = [
            'bolsa_familia' => 'Bolsa Família',
            'auxilio_brasil' => 'Auxílio Brasil',
            'bpc' => 'BPC',
            'auxilio_emergencial' => 'Auxílio emergencial',
            'seguro_desemprego' => 'Seguro-desemprego',
            'aposentadoria' => 'Aposentadoria'
        ];
        $labels = [];
        foreach ($map as $field => $label) {
            if (!empty($row[$field])) {
                $labels[] = $label;
            }
        }
        return $labels;
    }

    private function employeeReport(array $filters) {
        $sql = "SELECT nome, email, nivel, status, created_at FROM usuario WHERE 1=1";
        $params = [];
        
        $this->appendSearchFilter($sql, $params, $filters['q'], 'nome', 'email');
        $sql .= " ORDER BY nome ASC LIMIT " . (self::MAX_ROWS + 1);

        $rawRows = $this->fetchAll($sql, $params);
        $truncated = count($rawRows) > self::MAX_ROWS;
        $rawRows = array_slice($rawRows, 0, self::MAX_ROWS);

        $rows = [];
        $totals = ['ativos' => 0, 'inativos' => 0];

        foreach ($rawRows as $row) {
            $status = strtolower($row['status'] ?? 'ativo');
            if ($status === 'ativo' || $status === 'active') {
                $statusDisplay = 'Ativo';
                $totals['ativos']++;
            } else {
                $statusDisplay = 'Inativo';
                $totals['inativos']++;
            }

            $rows[] = [
                'nome' => $row['nome'] ?? '',
                'email' => $row['email'] ?? '',
                'nivel' => $row['nivel'] ?? '',
                'status' => $statusDisplay,
                'data_cadastro' => $this->formatDate($row['created_at'] ?? '')
            ];
        }

        return $this->reportEnvelope(
            'usuarios',
            'Relatório de Funcionários',
            'Listagem da equipe e controle de acessos.',
            [
                'nome' => 'Nome',
                'email' => 'E-mail',
                'nivel' => 'Nível de Acesso',
                'status' => 'Status',
                'data_cadastro' => 'Data de Cadastro'
            ],
            $rows,
            [
                ['label' => 'Total', 'value' => count($rows), 'tone' => 'blue'],
                ['label' => 'Ativos', 'value' => $totals['ativos'], 'tone' => 'green'],
                ['label' => 'Inativos', 'value' => $totals['inativos'], 'tone' => 'red']
            ],
            $filters,
            $truncated
        );
    }

    private function psychologyReport(array $filters) {
        $sql = "SELECT a.data_anotacao, at.nome, at.cpf, a.tipo_anotacao, u.nome AS psicologo
                FROM anotacao_psicologica a
                JOIN atendido at ON a.id_atendido = at.idatendido
                LEFT JOIN usuario u ON a.id_psicologo = u.idusuario
                WHERE 1=1";
        $params = [];
        $this->appendDateFilters($sql, $params, 'a.data_anotacao', $filters);
        $this->appendSearchFilter($sql, $params, $filters['q'], 'at.nome', 'at.cpf');
        
        $sql .= " ORDER BY a.data_anotacao DESC, at.nome ASC LIMIT " . (self::MAX_ROWS + 1);

        $rawRows = $this->fetchAll($sql, $params);
        $truncated = count($rawRows) > self::MAX_ROWS;
        $rawRows = array_slice($rawRows, 0, self::MAX_ROWS);

        $rows = [];
        $totals = ['Consulta' => 0, 'Avaliação' => 0, 'Evolução' => 0, 'Observação' => 0];

        foreach ($rawRows as $row) {
            $tipo = $row['tipo_anotacao'] ?? 'Consulta';
            if (!isset($totals[$tipo])) {
                $totals[$tipo] = 0;
            }
            $totals[$tipo]++;

            $rows[] = [
                'data' => $this->formatDate($row['data_anotacao'] ?? ''),
                'paciente' => $row['nome'] ?? '',
                'cpf' => $this->maskCpf($row['cpf'] ?? ''),
                'tipo' => $tipo,
                'psicologo' => $row['psicologo'] ?? 'Não informado'
            ];
        }

        return $this->reportEnvelope(
            'psicologia',
            'Relatório Psicológico',
            'Histórico de consultas, avaliações e evoluções psicológicas.',
            [
                'data' => 'Data',
                'paciente' => 'Paciente',
                'cpf' => 'CPF protegido',
                'tipo' => 'Tipo',
                'psicologo' => 'Psicólogo'
            ],
            $rows,
            [
                ['label' => 'Total', 'value' => count($rows), 'tone' => 'blue'],
                ['label' => 'Consultas', 'value' => $totals['Consulta'] ?? 0, 'tone' => 'green'],
                ['label' => 'Avaliações', 'value' => $totals['Avaliação'] ?? 0, 'tone' => 'orange'],
                ['label' => 'Evoluções', 'value' => $totals['Evolução'] ?? 0, 'tone' => 'purple']
            ],
            $filters,
            $truncated,
            'Estes dados são protegidos e devem ser manuseados apenas por profissionais autorizados.'
        );
    }
}
