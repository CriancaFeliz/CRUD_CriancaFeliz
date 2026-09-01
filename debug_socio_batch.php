<?php
/**
 * Debug em lote de fichas socioeconômicas
 * Uso: http://localhost/CriancaFeliz/debug_socio_batch.php?limit=5
 * Retorna últimas N fichas (Atendido) e verifica campos chave.
 */
require_once 'bootstrap.php';

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
if ($limit < 1) $limit = 5;

try {
    $db = Database::getConnection();
    // Pegar últimos atendidos com ficha (ordenar por data_cadastro)
    $sql = "SELECT a.idatendido FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            ORDER BY a.data_cadastro DESC
            LIMIT ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$limit]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $model = new SocioeconomicoDB();

    echo "<h1>Debug em lote - Últimas {$limit} fichas</h1>";
    echo "<p>Abrir individual: <code>debug_socio_ficha.php?id=&lt;id_atendido&gt;</code></p>";

    if (empty($rows)) {
        echo "<p>Nenhuma ficha encontrada (verifique se há registros em Ficha_Socioeconomico).</p>";
        exit;
    }

    echo "<table border=1 cellpadding=6 cellspacing=0 style=\"border-collapse:collapse;\">";
    echo "<tr><th>id_atendido</th><th>nome</th><th>Campos ausentes</th><th>Resumo (renda / percapita / nr_comodos)</th></tr>";

    foreach ($rows as $r) {
        $id = $r['idatendido'];
        $mapped = $model->getFicha($id);

        $missing = [];
        $checks = [
            'nome_menor','assistente_social','numero_comodos','renda_familiar','renda_per_capita','cadunico','tipo_moradia','situacao_moradia','despesa_agua','despesa_energia','esgoto'
        ];
        foreach ($checks as $f) {
            $val = $mapped[$f] ?? null;
            // considerar ausente: null, empty string, zero for money fields
            $isMoney = in_array($f, ['despesa_agua','despesa_energia','renda_familiar','renda_per_capita']);
            if ($val === null || $val === '' || ($isMoney && floatval($val) == 0)) {
                $missing[] = $f;
            }
        }

        $name = $mapped['nome_completo'] ?? $mapped['nome_entrevistado'] ?? '';
        $summary = sprintf('R$ %s / R$ %s / %s',
            number_format(floatval($mapped['renda_familiar'] ?? 0), 2, ',', '.'),
            number_format(floatval($mapped['renda_per_capita'] ?? 0), 2, ',', '.'),
            htmlspecialchars($mapped['numero_comodos'] ?? $mapped['nr_comodos'] ?? 'N/I')
        );

        echo "<tr>";
        echo "<td><a href=\"debug_socio_ficha.php?id={$id}\">{$id}</a></td>";
        echo "<td>" . htmlspecialchars($name) . "</td>";
        echo "<td>" . (!empty($missing) ? htmlspecialchars(implode(', ', $missing)) : '<strong>OK</strong>') . "</td>";
        echo "<td>" . $summary . "</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (Exception $e) {
    echo "<h2>Erro</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

?>