<?php

/**
 * Model para Desligamento - MySQL
 */
class DesligamentoDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Desligamento', 'id_desligamento');
    }
    
    /**
     * Registrar desligamento
     */
    public function registrarDesligamento($idAtendido, $data) {
        $pdo = Database::getConnection();
        $userId = $_SESSION['user_id'] ?? null;
        
        $sql = "INSERT INTO Desligamento 
                (id_atendido, motivo, tipo_motivo, data_desligamento, observacao, automatico, pode_retornar, desligado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idAtendido,
            $data['motivo'],
            $data['tipo_motivo'] ?? 'outros',
            $data['data_desligamento'] ?? date('Y-m-d'),
            $data['observacao'] ?? null,
            $data['automatico'] ?? false,
            $data['pode_retornar'] ?? true,
            $userId
        ]);
        
        // Atualizar status do atendido
        $this->atualizarStatusAtendido($idAtendido, 'Desligado');
        
        return $pdo->lastInsertId();
    }
    
    /**
     * Verificar se atendido está desligado
     */
    public function isDesligado($idAtendido) {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM Desligamento WHERE id_atendido = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAtendido]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Buscar desligamento por atendido
     */
    public function getByAtendido($idAtendido) {
        $pdo = Database::getConnection();
        $sql = "SELECT d.*, a.nome as atendido_nome, u.nome as desligado_por_nome
                FROM Desligamento d
                INNER JOIN Atendido a ON d.id_atendido = a.idatendido
                LEFT JOIN Usuario u ON d.desligado_por = u.idusuario
                WHERE d.id_atendido = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAtendido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar desligamentos com filtros
     */
    public function listar($filtros = []) {
        $pdo = Database::getConnection();
        $sql = "SELECT d.*, a.nome as atendido_nome, a.cpf, u.nome as desligado_por_nome
                FROM Desligamento d
                INNER JOIN Atendido a ON d.id_atendido = a.idatendido
                LEFT JOIN Usuario u ON d.desligado_por = u.idusuario
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['tipo_motivo'])) {
            $sql .= " AND d.tipo_motivo = ?";
            $params[] = $filtros['tipo_motivo'];
        }
        
        if (!empty($filtros['automatico'])) {
            $sql .= " AND d.automatico = ?";
            $params[] = $filtros['automatico'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND d.data_desligamento >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND d.data_desligamento <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        $sql .= " ORDER BY d.data_desligamento DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cancelar desligamento (reativar)
     */
    public function cancelarDesligamento($idAtendido) {
        $pdo = Database::getConnection();
        
        // Remover desligamento
        $sql = "DELETE FROM Desligamento WHERE id_atendido = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAtendido]);
        
        // Reativar atendido
        $this->atualizarStatusAtendido($idAtendido, 'Ativo');
        
        return true;
    }
    
    /**
     * Desligar automaticamente por excesso de faltas
     */
    public function desligarPorExcessoFaltas() {
        $pdo = Database::getConnection();
        
        // Buscar atendidos com 3 ou mais faltas
        $sql = "SELECT 
                    a.idatendido,
                    a.nome,
                    COUNT(CASE WHEN fd.status = 'F' THEN 1 END) as total_faltas
                FROM Atendido a
                LEFT JOIN Frequencia_Dia fd ON a.idatendido = fd.id_atendido
                WHERE a.status = 'Ativo'
                    AND NOT EXISTS (SELECT 1 FROM Desligamento d WHERE d.id_atendido = a.idatendido)
                GROUP BY a.idatendido, a.nome
                HAVING COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 3";
        
        $stmt = $pdo->query($sql);
        $atendidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $desligados = [];
        foreach ($atendidos as $atendido) {
            $this->registrarDesligamento($atendido['idatendido'], [
                'motivo' => 'Desligamento automático por excesso de faltas (' . $atendido['total_faltas'] . ' faltas)',
                'tipo_motivo' => 'excesso_faltas',
                'automatico' => true,
                'pode_retornar' => true
            ]);
            $desligados[] = $atendido;
        }
        
        return $desligados;
    }
    
    /**
     * Estatísticas de desligamentos
     */
    public function getEstatisticas() {
        $pdo = Database::getConnection();
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN tipo_motivo = 'idade' THEN 1 END) as por_idade,
                    COUNT(CASE WHEN tipo_motivo = 'excesso_faltas' THEN 1 END) as por_faltas,
                    COUNT(CASE WHEN tipo_motivo = 'pedido_familia' THEN 1 END) as por_pedido,
                    COUNT(CASE WHEN tipo_motivo = 'transferencia' THEN 1 END) as por_transferencia,
                    COUNT(CASE WHEN tipo_motivo = 'outros' THEN 1 END) as outros,
                    COUNT(CASE WHEN automatico = 1 THEN 1 END) as automaticos
                FROM Desligamento";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualizar status do atendido
     */
    private function atualizarStatusAtendido($idAtendido, $status) {
        $pdo = Database::getConnection();
        $sql = "UPDATE Atendido SET status = ? WHERE idatendido = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$status, $idAtendido]);
    }
}
