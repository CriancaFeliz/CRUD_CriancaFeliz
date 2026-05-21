<?php

/**
 * Model para gerenciar Oficinas - MySQL
 */
class OficinaDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Oficina', 'id_oficina');
    }
    
    /**
     * Lista oficinas ativas
     */
    public function getAtivas() {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM Oficina WHERE ativo = 1 ORDER BY nome";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista oficinas por dia da semana
     */
    public function getByDiaSemana($diaSemana) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM Oficina WHERE dia_semana = ? AND ativo = 1 ORDER BY horario_inicio";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$diaSemana]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ativar/desativar oficina
     */
    public function toggleAtivo($id) {
        $pdo = Database::getConnection();
        $sql = "UPDATE Oficina SET ativo = NOT ativo WHERE id_oficina = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Criar nova oficina
     */
    public function createOficina($data) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO Oficina (nome, descricao, dia_semana, horario_inicio, horario_fim) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['dia_semana'] ?? null,
            $data['horario_inicio'] ?? null,
            $data['horario_fim'] ?? null
        ]);
        return $pdo->lastInsertId();
    }
    
    /**
     * Atualizar oficina
     */
    public function updateOficina($id, $data) {
        $pdo = Database::getConnection();
        $sql = "UPDATE Oficina SET 
                nome = ?, 
                descricao = ?, 
                dia_semana = ?, 
                horario_inicio = ?, 
                horario_fim = ?
                WHERE id_oficina = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['dia_semana'] ?? null,
            $data['horario_inicio'] ?? null,
            $data['horario_fim'] ?? null,
            $id
        ]);
    }
}
