<?php

/**
 * Model para Frequência por Oficina - MySQL
 */
class FrequenciaOficinaDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Frequencia_Oficina', 'id_frequencia');
    }
    
    /**
     * Registrar presença em oficina
     */
    public function registrarPresenca($idAtendido, $idOficina, $data) {
        $pdo = Database::getConnection();
        $userId = $_SESSION['user_id'] ?? null;
        
        $sql = "INSERT INTO Frequencia_Oficina (id_atendido, id_oficina, data, status, registrado_por)
                VALUES (?, ?, ?, 'P', ?)
                ON DUPLICATE KEY UPDATE 
                    status = 'P',
                    justificativa = NULL,
                    observacao = NULL,
                    registrado_por = VALUES(registrado_por),
                    updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$idAtendido, $idOficina, $data, $userId]);
    }
    
    /**
     * Registrar falta em oficina
     */
    public function registrarFalta($idAtendido, $idOficina, $data, $justificativa = null) {
        $pdo = Database::getConnection();
        $userId = $_SESSION['user_id'] ?? null;
        $status = !empty($justificativa) ? 'J' : 'F';
        
        $sql = "INSERT INTO Frequencia_Oficina (id_atendido, id_oficina, data, status, justificativa, registrado_por)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    justificativa = VALUES(justificativa),
                    registrado_por = VALUES(registrado_por),
                    updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$idAtendido, $idOficina, $data, $status, $justificativa, $userId]);
    }
    
    /**
     * Buscar frequências por atendido
     */
    public function getByAtendido($idAtendido, $dataInicio = null, $dataFim = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT fo.*, o.nome as oficina_nome, u.nome as registrado_por_nome
                FROM Frequencia_Oficina fo
                INNER JOIN Oficina o ON fo.id_oficina = o.id_oficina
                LEFT JOIN Usuario u ON fo.registrado_por = u.idusuario
                WHERE fo.id_atendido = ?";
        
        $params = [$idAtendido];
        
        if ($dataInicio) {
            $sql .= " AND fo.data >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND fo.data <= ?";
            $params[] = $dataFim;
        }
        
        $sql .= " ORDER BY fo.data DESC, o.nome";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar frequências por oficina e data
     */
    public function getByOficinaData($idOficina, $data) {
        $pdo = Database::getConnection();
        $sql = "SELECT fo.*, a.nome as atendido_nome, a.cpf
                FROM Frequencia_Oficina fo
                INNER JOIN Atendido a ON fo.id_atendido = a.idatendido
                WHERE fo.id_oficina = ? AND fo.data = ?
                ORDER BY a.nome";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idOficina, $data]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estatísticas de frequência por oficina
     */
    public function getEstatisticas($idAtendido, $dataInicio = null, $dataFim = null) {
        $pdo = Database::getConnection();
        $sql = "SELECT 
                    COUNT(CASE WHEN status = 'P' THEN 1 END) as presencas,
                    COUNT(CASE WHEN status = 'F' THEN 1 END) as faltas,
                    COUNT(CASE WHEN status = 'J' THEN 1 END) as justificadas,
                    COUNT(*) as total
                FROM Frequencia_Oficina
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
     * Remover registro de frequência
     */
    public function remover($id) {
        $pdo = Database::getConnection();
        $sql = "DELETE FROM Frequencia_Oficina WHERE id_frequencia = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Atualizar justificativa
     */
    public function atualizarJustificativa($id, $justificativa) {
        $pdo = Database::getConnection();
        $status = !empty($justificativa) ? 'J' : 'F';
        $sql = "UPDATE Frequencia_Oficina 
                SET justificativa = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id_frequencia = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$justificativa, $status, $id]);
    }
}
