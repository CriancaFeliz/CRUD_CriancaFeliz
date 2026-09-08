<?php

/**
 * Model para gerenciar oficinas - MySQL
 */
class Oficina extends BaseModel {
    
    public function __construct() {
        parent::__construct('oficina', 'id_oficina');
    }
    
    /**
     * Lista oficinas ativas
     */
    public function getAtivas() {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM oficina WHERE ativo = 1 ORDER BY nome";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista oficinas por dia da semana
     */
    public function getByDiaSemana($diaSemana) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM oficina WHERE dia_semana = ? AND ativo = 1 ORDER BY horario_inicio";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$diaSemana]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    
    /**
     * Ativar/desativar oficina
     */
    public function toggleAtivo($id) {
        $pdo = Database::getConnection();
        
        $sqlSelect = "SELECT * FROM oficina WHERE id_oficina = ?";
        $stmtSelect = $pdo->prepare($sqlSelect);
        $stmtSelect->execute([$id]);
        $oficina = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        
        $sql = "UPDATE oficina SET ativo = NOT ativo WHERE id_oficina = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result && $oficina) {
            $novoStatus = $oficina['ativo'] ? 0 : 1;
            $msg = $novoStatus ? 'ativada' : 'desativada';
            
            require_once APP_PATH . '/Models/Log.php';
            $log = new Log();
            $log->logAction('UPDATE', 'oficina', "Oficina '{$oficina['nome']}' $msg", json_encode(['ativo' => $oficina['ativo']]), json_encode(['ativo' => $novoStatus]), $id);
        }
        
        return $result;
    }
    
    /**
     * Criar nova oficina
     */
    public function createOficina($data) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO oficina (nome, descricao, dia_semana, horario_inicio, horario_fim)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['dia_semana'] ?? null,
            $data['horario_inicio'] ?? null,
            $data['horario_fim'] ?? null
        ]);
        
        $newId = $pdo->lastInsertId();
        if ($newId) {
            require_once APP_PATH . '/Models/Log.php';
            $log = new Log();
            $log->logAction('INSERT', 'oficina', "Oficina criada: {$data['nome']}", null, json_encode($data, JSON_UNESCAPED_UNICODE), $newId);
        }
        
        return $newId;
    }
    
    /**
     * Atualizar oficina
     */
    public function updateOficina($id, $data) {
        $pdo = Database::getConnection();
        $sqlSelect = "SELECT * FROM oficina WHERE id_oficina = ?";
        $stmtSelect = $pdo->prepare($sqlSelect);
        $stmtSelect->execute([$id]);
        $oficinaAntiga = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        
        $sql = "UPDATE oficina SET
                nome = ?, 
                descricao = ?, 
                dia_semana = ?, 
                horario_inicio = ?, 
                horario_fim = ?
                WHERE id_oficina = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['dia_semana'] ?? null,
            $data['horario_inicio'] ?? null,
            $data['horario_fim'] ?? null,
            $id
        ]);
        
        if ($result && $oficinaAntiga) {
            require_once APP_PATH . '/Models/Log.php';
            $log = new Log();
            $log->logAction('UPDATE', 'oficina', "Oficina atualizada: {$data['nome']}", json_encode($oficinaAntiga, JSON_UNESCAPED_UNICODE), json_encode($data, JSON_UNESCAPED_UNICODE), $id);
        }
        
        return $result;
    }
}
