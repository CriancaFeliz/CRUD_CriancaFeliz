<?php

class PsychologyNote extends BaseModelDB
{
    public function __construct()
    {
        parent::__construct('anotacao_psicologica', 'id_anotacao');
    }

    /**
     * Busca todas as notas de um CPF
     */
    public function findByCpf($cpf)
    {
        $sql = "SELECT a.*, u.nome AS psicologo_nome
                FROM anotacao_psicologica a
                JOIN usuario u ON a.id_psicologo = u.idusuario
                JOIN atendido at ON a.id_atendido = at.idatendido
                WHERE at.cpf = ?
                ORDER BY a.data_anotacao DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cpf]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma anotação por ID
     */
    public function findById($id)
    {
        $sql = "SELECT a.*, u.nome AS psicologo_nome
                FROM anotacao_psicologica a
                LEFT JOIN usuario u ON a.id_psicologo = u.idusuario
                WHERE a.id_anotacao = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateNote($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE anotacao_psicologica SET " . implode(', ', $fields) . " WHERE id_anotacao = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteNote($id)
    {
        $sql = "DELETE FROM anotacao_psicologica WHERE id_anotacao = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}