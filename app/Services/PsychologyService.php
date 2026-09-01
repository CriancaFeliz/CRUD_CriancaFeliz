<?php

class PsychologyService
{
    private $noteModel;
    private $acolhimentoModel;

    public function __construct()
    {
        $this->noteModel = new PsychologyNote();
        $this->acolhimentoModel = new Acolhimento();
    }

    /* ================= MAPAS ================= */

    private function mapTipoToDb($tipo)
    {
        $map = [
            'consulta' => 'Consulta',
            'avaliacao' => 'Avaliação',
            'evolucao' => 'Evolução',
            'observacao' => 'Observação'
        ];

        $key = strtolower(
            str_replace(
                ['á','à','ã','â','é','ê','í','ó','ô','õ','ú','ç'],
                ['a','a','a','a','e','e','i','o','o','o','u','c'],
                $tipo
            )
        );

        return $map[$key] ?? 'Consulta';
    }

    private function mapTipoToInternal($tipo)
    {
        $map = [
            'Consulta' => 'consulta',
            'Avaliação' => 'avaliacao',
            'Evolução' => 'evolucao',
            'Observação' => 'observacao'
        ];

        return $map[$tipo] ?? strtolower($tipo);
    }

    /* ================= PACIENTES ================= */

    public function getAllPatients()
    {
        $fichas = $this->acolhimentoModel->getAll();
        $patients = [];

        foreach ($fichas as $ficha) {

            $cpf = $ficha['cpf'] ?? null;
            if (!$cpf) continue;

            $nome = $ficha['nome'] ?? $ficha['nome_completo'] ?? 'Não informado';

            $data_nasc = $ficha['data_nascimento'] ?? null;

            $patients[] = [
                'cpf' => $cpf,
                'nome_completo' => $nome,
                'idade' => $data_nasc ? $this->calculateAgeSafe($data_nasc) : null,
                'responsavel' => $ficha['nome_responsavel'] ?? 'Não informado',
                'data_acolhimento' => $ficha['data_cadastro'] ?? null,
                'last_note' => null
            ];
        }

        usort($patients, fn($a,$b)=>strcmp($a['nome_completo'],$b['nome_completo']));
        return $patients;
    }

    public function getPatient($cpf)
    {
        $at = $this->acolhimentoModel->findByCpf($cpf);
        if (!$at) return null;

        return [
            'cpf' => $at['cpf'],
            'nome_completo' => $at['nome'] ?? $at['nome_completo'],
            'data_nascimento' => $at['data_nascimento'],
            'idade' => $this->calculateAgeSafe($at['data_nascimento']),
            'responsavel' => $at['nome_responsavel'] ?? 'Não informado',
            'contato' => $at['contato_1'] ?? null,
            'endereco' => $this->formatAddress($at),
            'data_acolhimento' => $at['data_cadastro'] ?? null,
            '_raw' => $at
        ];
    }

    /* ================= ANOTAÇÕES ================= */

    public function getPatientNotes($cpf)
    {
        $rows = $this->noteModel->findByCpf($cpf);

        foreach ($rows as &$row) {
            $row['id'] = $row['id_anotacao'];
            $row['note_type'] = $this->mapTipoToInternal($row['tipo']);
            $row['title'] = $row['titulo'];
            $row['content'] = $row['conteudo'];
            $row['psychologist_id'] = $row['id_psicologo'];
            $row['created_at'] = $row['data_anotacao'];
        }

        return $rows;
    }

    public function saveNote($data)
    {
        try {
            $cpf = $data['patient_cpf'] ?? null;

            $at = $this->acolhimentoModel->findByCpf($cpf);
            if (!$at) {
                return ['success'=>false,'message'=>'Paciente não encontrado'];
            }

            $id_atendido = $at['idatendido'];

            if (empty(trim($data['content'] ?? ''))) {
                return ['success'=>false,'message'=>'Conteúdo é obrigatório'];
            }

            $tipo = $data['note_type'] ?? 'consulta';

            $next = $data['next_session'] ?? null;
            if (!empty($next)) {
                if (str_contains($next, 'T')) {
                    $next = explode('T', $next)[0];
                }
                $ts = strtotime($next);
                $next = $ts ? date('Y-m-d', $ts) : null;
            }

            // Tratar humor - converter valores vazios para NULL
            $humor = $data['mood_assessment'] ?? null;
            $humor = empty($humor) || $humor === '' ? null : (int)$humor;

            $noteData = [
                'id_atendido' => $id_atendido,
                'id_psicologo' => $_SESSION['user_id'],
                'tipo' => $this->mapTipoToDb($tipo),
                'titulo' => $data['title'] ?? 'Sem título',
                'conteudo' => $data['content'],
                'data_anotacao' => date('Y-m-d H:i:s'),
                'humor' => $humor,
                'observacoes_comportamentais' => !empty($data['behavior_notes']) ? $data['behavior_notes'] : null,
                'recomendacoes' => !empty($data['recommendations']) ? $data['recommendations'] : null,
                'proxima_sessao' => $next
            ];

            $created = $this->noteModel->create($noteData);

            return [
                'success'=>true,
                'message'=>'Anotação salva com sucesso',
                'id'=>$created['id_anotacao'] ?? $created
            ];

        } catch (Exception $e) {
            error_log("saveNote error: ".$e->getMessage());
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    /* ================= UTIL ================= */

    private function formatAddress($a)
    {
        $parts = [];

        if (!empty($a['endereco'])) $parts[] = $a['endereco'];
        if (!empty($a['numero'])) $parts[] = "nº ".$a['numero'];
        if (!empty($a['bairro'])) $parts[] = $a['bairro'];
        if (!empty($a['cidade'])) $parts[] = $a['cidade'];

        return implode(', ', $parts);
    }

    private function calculateAgeSafe($date)
    {
        if (!$date || $date=="0000-00-00") return null;

        try {
            $d = new DateTime($date);
            return (new DateTime())->diff($d)->y;
        } catch (Exception $e) {
            return null;
        }
    }

    /* ================= DASHBOARD - ESTATÍSTICAS ================= */

    public function getStatistics()
    {
        $db = Database::getConnection();

        $stats = [];

        /* ---- TOTAL DE PACIENTES ---- */
        $stats['total_patients'] = $db->query("
            SELECT COUNT(*) AS total
            FROM atendido
        ")->fetch()['total'];

        /* ---- TOTAL DE ANOTAÇÕES ---- */
        $stats['total_notes'] = $db->query("
            SELECT COUNT(*) AS total
            FROM anotacao_psicologica
        ")->fetch()['total'];

        /* ---- ANOTAÇÕES DO MÊS ---- */
        $stats['notes_this_month'] = $db->query("
            SELECT COUNT(*) AS total
            FROM anotacao_psicologica
            WHERE MONTH(data_anotacao) = MONTH(CURRENT_DATE())
              AND YEAR(data_anotacao) = YEAR(CURRENT_DATE())
        ")->fetch()['total'];

        /* ---- ANOTAÇÕES POR TIPO ---- */
        $stats['by_note_type'] = [
            'consulta' => $this->countByType('Consulta'),
            'avaliacao' => $this->countByType('Avaliação'),
            'evolucao' => $this->countByType('Evolução'),
            'observacao' => $this->countByType('Observação')
        ];

        /* ---- FAIXA ETÁRIA ---- */
        $stats['by_age_group'] = $db->query("
            SELECT
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 6 AND 11 THEN 1 ELSE 0 END) AS crianca,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 12 AND 14 THEN 1 ELSE 0 END) AS preadolescente,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 15 AND 18 THEN 1 ELSE 0 END) AS adolescente
            FROM atendido
        ")->fetch();

        return $stats;
    }

    private function countByType($tipoDb)
    {
        $db = Database::getConnection();

        return $db->query("
            SELECT COUNT(*) AS total
            FROM anotacao_psicologica
            WHERE tipo = '$tipoDb'
        ")->fetch()['total'];
    }

    /* ================= DASHBOARD - ÚLTIMAS ANOTAÇÕES ================= */

    public function getRecentNotes()
    {
        $db = Database::getConnection();

        $rows = $db->query("
            SELECT a.*, at.cpf, at.nome AS paciente_nome
            FROM anotacao_psicologica a
            LEFT JOIN atendido at ON at.idatendido = a.id_atendido
            ORDER BY a.data_anotacao DESC
            LIMIT 5
        ")->fetchAll();

        foreach ($rows as &$row) {
            $row['id'] = $row['id_anotacao'];
            $row['note_type'] = $this->mapTipoToInternal($row['tipo']);
            $row['title'] = $row['titulo'];
            $row['content'] = $row['conteudo'];
            $row['patient_cpf'] = $row['cpf'];
            $row['created_at'] = $row['data_anotacao'];
        }

        return $rows;
    }

    public function updateNote($id, $data)
    {
        // Tratar valores vazios de humor
        $humor = $data['mood_assessment'] ?? null;
        $humor = empty($humor) || $humor === '' ? null : (int)$humor;

        $update = [
            'titulo' => $data['title'] ?? 'Sem título',
            'conteudo' => $data['content'] ?? '',
            'tipo' => $this->mapTipoToDb($data['note_type'] ?? 'consulta'),
            'humor' => $humor,
            'observacoes_comportamentais' => !empty($data['behavior_notes']) ? $data['behavior_notes'] : null,
            'recomendacoes' => !empty($data['recommendations']) ? $data['recommendations'] : null,
            'proxima_sessao' => !empty($data['next_session']) ? $data['next_session'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $this->noteModel->updateNote($id, $update);

        return [
            'success' => $ok,
            'message' => $ok ? 'Anotação atualizada com sucesso!' : 'Erro ao atualizar'
        ];
    }

    public function deleteNote($id)
    {
        $ok = $this->noteModel->deleteNote($id);

        return [
            'success' => $ok,
            'message' => $ok ? 'Anotação excluída com sucesso!' : 'Erro ao excluir'
        ];
    }

    public function getAnnotationById($id)
    {
        $note = $this->noteModel->findById($id);
        
        if (!$note) {
            return null;
        }

        // Mapear campos do banco para o padrão interno
        $note['id'] = $note['id_anotacao'] ?? $note['id'] ?? $id;
        $note['note_type'] = $this->mapTipoToInternal($note['tipo'] ?? 'Consulta');
        $note['title'] = $note['titulo'] ?? '';
        $note['content'] = $note['conteudo'] ?? '';
        $note['psychologist_id'] = $note['id_psicologo'] ?? null;
        $note['mood_assessment'] = $note['humor'] ?? null;
        $note['behavior_notes'] = $note['observacoes_comportamentais'] ?? null;
        $note['recommendations'] = $note['recomendacoes'] ?? null;
        $note['next_session'] = $note['proxima_sessao'] ?? null;
        $note['created_at'] = $note['data_anotacao'] ?? null;

        return $note;
    }

}