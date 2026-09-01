<?php

/**
 * Model para Frequência por Dia - MySQL
 */
class FrequenciaDiaDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Frequencia_Dia', 'id_frequencia_dia');
    }
    
    /**
     * Registrar presença no dia
     */
    public function registrarPresenca($idAtendido, $data, $observacao = null) {
        $pdo = Database::getConnection();
        $userId = $_SESSION['user_id'] ?? null;
        
        $sql = "INSERT INTO Frequencia_Dia (id_atendido, data, status, observacao, registrado_por)
                VALUES (?, ?, 'P', ?, ?)
                ON DUPLICATE KEY UPDATE 
                    status = 'P',
                    justificativa = NULL,
                    observacao = VALUES(observacao),
                    registrado_por = VALUES(registrado_por),
                    updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$idAtendido, $data, $observacao, $userId]);
    }
    
    /**
     * Registrar falta no dia
     */
    public function registrarFalta($idAtendido, $data, $justificativa = null, $observacao = null) {
        $pdo = Database::getConnection();
        $userId = $_SESSION['user_id'] ?? null;
        $status = !empty($justificativa) ? 'J' : 'F';
        
        $sql = "INSERT INTO Frequencia_Dia (id_atendido, data, status, justificativa, observacao, registrado_por)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    justificativa = VALUES(justificativa),
                    observacao = VALUES(observacao),
                    registrado_por = VALUES(registrado_por),
                    updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$idAtendido, $data, $status, $justificativa, $observacao, $userId]);
    }
    
    /**
     * Buscar frequências por atendido
     */
    public function getByAtendido($idAtendido, $dataInicio = null, $dataFim = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT fd.*, u.nome as registrado_por_nome
                FROM Frequencia_Dia fd
                LEFT JOIN Usuario u ON fd.registrado_por = u.idusuario
                WHERE fd.id_atendido = ?";
        
        $params = [$idAtendido];
        
        if ($dataInicio) {
            $sql .= " AND fd.data >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND fd.data <= ?";
            $params[] = $dataFim;
        }
        
        $sql .= " ORDER BY fd.data DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar frequências por data
     */
    public function getByData($data) {
        $pdo = Database::getConnection();
        $sql = "SELECT fd.*, a.nome as atendido_nome, a.cpf
                FROM Frequencia_Dia fd
                INNER JOIN Atendido a ON fd.id_atendido = a.idatendido
                WHERE fd.data = ?
                ORDER BY a.nome";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estatísticas de frequência
     */
    public function getEstatisticas($idAtendido, $dataInicio = null, $dataFim = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT 
                    COUNT(CASE WHEN status = 'P' THEN 1 END) as presencas,
                    COUNT(CASE WHEN status = 'F' THEN 1 END) as faltas,
                    COUNT(CASE WHEN status = 'J' THEN 1 END) as justificadas,
                    COUNT(*) as total
                FROM Frequencia_Dia
                WHERE id_atendido = ?";
        
        $params = [$idAtendido];
        
        if ($dataInicio) {
            $sql .= " AND data >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND data <= ?";
            $params[] = $dataFim;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $result['percentual_presenca'] = $result['total'] > 0 
            ? round(($result['presencas'] / $result['total']) * 100, 2) 
            : 0;
        
        return $result;
    }
    
    /**
     * Conta faltas não justificadas
     */
    public function contarFaltasNaoJustificadas($idAtendido, $dataInicio = null, $dataFim = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) as total
                FROM Frequencia_Dia
                WHERE id_atendido = ? AND status = 'F'";
        
        $params = [$idAtendido];
        
        if ($dataInicio) {
            $sql .= " AND data >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND data <= ?";
            $params[] = $dataFim;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
    
    /**
     * Lista atendidos com alertas de falta
     */
    public function getAtendidosComAlertas() {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM Atendidos_Com_Alerta ORDER BY total_faltas DESC, ultima_falta DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Remover registro de frequência
     */
    public function remover($id) {
        $pdo = Database::getConnection();
        $sql = "DELETE FROM Frequencia_Dia WHERE id_frequencia_dia = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Atualizar justificativa
     */
    public function atualizarJustificativa($id, $justificativa) {
        $pdo = Database::getConnection();
        $status = !empty($justificativa) ? 'J' : 'F';
        $sql = "UPDATE Frequencia_Dia 
                SET justificativa = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id_frequencia_dia = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$justificativa, $status, $id]);
    }
}
