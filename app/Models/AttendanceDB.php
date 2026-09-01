<?php

/**
 * Model para controle de faltas e presenças - MySQL
 */
class AttendanceDB extends BaseModelDB {
    public function __construct() {
        parent::__construct('frequencia', 'id');
    }

    public function registerPresence($atendidoId, $data) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO frequencia (id_atendido, data, status, justificativa, criado_por)
                VALUES (?, ?, 'P', NULL, ?)
                ON DUPLICATE KEY UPDATE status='P', justificativa=VALUES(justificativa), criado_por=VALUES(criado_por), updated_at=CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);
        $userId = isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        if ($userId !== null) {
            try {
                $chk = $pdo->prepare("SELECT 1 FROM usuario WHERE idusuario = ?");
                $chk->execute([$userId]);
                if (!$chk->fetchColumn()) { $userId = null; }
            } catch (Exception $e) { $userId = null; }
        }
        $stmt->execute([
            $atendidoId,
            $data['data'] ?? date('Y-m-d'),
            $userId
        ]);
        return [
            'id_atendido' => $atendidoId,
            'data' => $data['data'] ?? date('Y-m-d'),
            'status' => 'P'
        ];
    }

    public function registerAbsence($atendidoId, $data) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO frequencia (id_atendido, data, status, justificativa, criado_por)
                VALUES (?, ?, 'F', ?, ?)
                ON DUPLICATE KEY UPDATE status='F', justificativa=VALUES(justificativa), criado_por=VALUES(criado_por), updated_at=CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);
        $userId = isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        if ($userId !== null) {
            try {
                $chk = $pdo->prepare("SELECT 1 FROM usuario WHERE idusuario = ?");
                $chk->execute([$userId]);
                if (!$chk->fetchColumn()) { $userId = null; }
            } catch (Exception $e) { $userId = null; }
        }
        $stmt->execute([
            $atendidoId,
            $data['data'] ?? date('Y-m-d'),
            $data['justificativa'] ?? null,
            $userId
        ]);
        return [
            'id_atendido' => $atendidoId,
            'data' => $data['data'] ?? date('Y-m-d'),
            'status' => 'F',
            'justificativa' => $data['justificativa'] ?? null
        ];
    }

    public function getByAtendido($atendidoId) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM frequencia WHERE id_atendido = ? ORDER BY data DESC");
        $stmt->execute([$atendidoId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Normalizar para o formato esperado pelas views (compatível com JSON antigo)
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => $r['id'],
                'atendido_id' => $r['id_atendido'],
                'tipo' => ($r['status'] === 'P' ? 'presenca' : 'falta'),
                'data' => $r['data'],
                'atividade' => 'Atendimento',
                'justificada' => isset($r['justificativa']) && $r['justificativa'] !== null && $r['justificativa'] !== '',
                'justificativa' => $r['justificativa'] ?? '',
                'observacao' => '',
                'registrado_por' => $r['criado_por'] ?? null,
                'registrado_em' => $r['created_at'] ?? null
            ];
        }
        return $out;
    }

    public function countPresences($atendidoId, $startDate = null, $endDate = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM frequencia WHERE id_atendido = ? AND status = 'P'";
        $params = [$atendidoId];
        if ($startDate) { $sql .= " AND data >= ?"; $params[] = $startDate; }
        if ($endDate) { $sql .= " AND data <= ?"; $params[] = $endDate; }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function countAbsences($atendidoId, $justificadas = null, $startDate = null, $endDate = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM frequencia WHERE id_atendido = ? AND status = 'F'";
        $params = [$atendidoId];
        if ($justificadas === true) {
            $sql .= " AND justificativa IS NOT NULL AND justificativa <> ''";
        } elseif ($justificadas === false) {
            $sql .= " AND (justificativa IS NULL OR justificativa = '')";
        }
        if ($startDate) { $sql .= " AND data >= ?"; $params[] = $startDate; }
        if ($endDate) { $sql .= " AND data <= ?"; $params[] = $endDate; }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getLastActivity($atendidoId) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM frequencia WHERE id_atendido = ? ORDER BY data DESC LIMIT 1");
        $stmt->execute([$atendidoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return [
            'data' => $row['data'],
            'tipo' => ($row['status'] === 'P' ? 'presenca' : 'falta')
        ];
    }

    public function getStatistics($atendidoId, $startDate = null, $endDate = null) {
        $totalPresencas = $this->countPresences($atendidoId, $startDate, $endDate);
        $faltasJustificadas = $this->countAbsences($atendidoId, true, $startDate, $endDate);
        $faltasNaoJustificadas = $this->countAbsences($atendidoId, false, $startDate, $endDate);
        $totalFaltas = $faltasJustificadas + $faltasNaoJustificadas;
        $totalRegistros = $totalPresencas + $totalFaltas;
        $percentualPresenca = $totalRegistros > 0 ? round(($totalPresencas / $totalRegistros) * 100, 2) : 0;
        return [
            'total_presencas' => $totalPresencas,
            'faltas_justificadas' => $faltasJustificadas,
            'faltas_nao_justificadas' => $faltasNaoJustificadas,
            'total_faltas' => $totalFaltas,
            'total_registros' => $totalRegistros,
            'percentual_presenca' => $percentualPresenca,
            'ultima_atividade' => $this->getLastActivity($atendidoId)
        ];
    }

    public function removeRecord($recordId) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM frequencia WHERE id = ?");
        return $stmt->execute([$recordId]);
    }

    public function updateJustification($recordId, $justificativa) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE frequencia SET justificativa = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'F'");
        return $stmt->execute([$justificativa, $recordId]);
    }

    public function findAll() {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM frequencia");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => $r['id'],
                'atendido_id' => $r['id_atendido'],
                'tipo' => ($r['status'] === 'P' ? 'presenca' : 'falta'),
                'data' => $r['data'],
                'atividade' => 'Atendimento',
                'justificada' => isset($r['justificativa']) && $r['justificativa'] !== null && $r['justificativa'] !== '',
                'justificativa' => $r['justificativa'] ?? '',
                'observacao' => '',
                'registrado_por' => $r['criado_por'] ?? null,
                'registrado_em' => $r['created_at'] ?? null
            ];
        }
        return $out;
    }
}
