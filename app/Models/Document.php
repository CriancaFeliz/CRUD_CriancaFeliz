<?php

/**
 * Model para documentos anexados ao prontuario do atendido.
 */
class Document extends BaseModel {
    public function __construct() {
        parent::__construct('documento', 'iddocumento');
    }

    public function findByAtendido($atendidoId) {
        $stmt = $this->query(
            "SELECT * FROM documento WHERE IDatendido = ? ORDER BY data_upload DESC, iddocumento DESC",
            [$atendidoId]
        );

        return $stmt->fetchAll();
    }

    public function createForAtendido($atendidoId, $tipo, $arquivo) {
        return $this->create([
            'tipo' => $tipo,
            'arquivo' => $arquivo,
            'data_upload' => date('Y-m-d H:i:s'),
            'IDatendido' => $atendidoId
        ]);
    }
}
