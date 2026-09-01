<?php
/**
 * Debug de ficha socioeconômica
 * Uso: http://localhost/CriancaFeliz/debug_socio_ficha.php?id=123
 */
require_once 'bootstrap.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    echo "<h2>Informe ?id=&lt;id_atendido&gt; na URL</h2>";
    exit;
}

try {
    $db = Database::getConnection();
    // Dados Atendido
    $stmt = $db->prepare("SELECT * FROM Atendido WHERE idatendido = ?");
    $stmt->execute([$id]);
    $atendido = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dados Ficha_Socioeconomico
    $stmt = $db->prepare("SELECT * FROM Ficha_Socioeconomico WHERE id_atendido = ?");
    $stmt->execute([$id]);
    $fichaRaw = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obter idficha (se existir)
    $idficha = $fichaRaw['idficha'] ?? $fichaRaw['id_ficha'] ?? null;

    // Familia
    $familia = [];
    if ($idficha) {
        $stmt = $db->prepare("SELECT * FROM Familia WHERE id_ficha = ?");
        $stmt->execute([$idficha]);
        $familia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Despesas
    $despesas = [];
    if ($idficha) {
        $stmt = $db->prepare("SELECT * FROM Despesas WHERE id_ficha = ?");
        $stmt->execute([$idficha]);
        $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Colunas de Ficha_Socioeconomico
    $cols = [];
    $stmt = $db->query("SHOW COLUMNS FROM Ficha_Socioeconomico");
    $colsInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colsInfo as $c) $cols[] = $c['Field'];

    // Usar o model para obter ficha mapeada
    $model = new SocioeconomicoDB();
    $mapped = $model->getFicha($id);

    echo "<h1>Debug Ficha socioeconômica - id_atendido={$id}</h1>";
    echo "<p><a href=\"socioeconomico_list.php\">Voltar</a></p>";

    echo "<h2>Atendido (raw)</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($atendido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

    echo "<h2>Ficha_Socioeconomico (raw)</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($fichaRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

    echo "<h2>Colunas em Ficha_Socioeconomico</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($cols, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

    echo "<h2>Familia (raw)</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($familia, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

    echo "<h2>Despesas (raw)</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($despesas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

    echo "<h2>Ficha (model -> getFicha) mapeada</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($mapped, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

    // Verificações rápidas de campos solicitados
    $checkFields = [
        'nome_menor','assistente_social','numero_comodos','renda_familiar','renda_per_capita','cadunico','tipo_moradia','situacao_moradia','despesa_agua','despesa_energia','esgoto'
    ];
    echo "<h2>Verificação rápida de campos</h2>";
    echo "<table border=1 cellpadding=6 cellspacing=0 style=\"border-collapse:collapse;\">";
    echo "<tr><th>Campo</th><th>Valor (raw ficha)</th><th>Valor (mapped)</th><th>Comentário</th></tr>";
    foreach ($checkFields as $f) {
        $rawVal = $fichaRaw[$f] ?? ($atendido[$f] ?? null);
        $mappedVal = $mapped[$f] ?? null;
        $comment = '';
        if ($mappedVal === null || $mappedVal === '' || (is_numeric($mappedVal) && $mappedVal == 0 && in_array($f, ['despesa_agua','despesa_energia','renda_familiar','renda_per_capita']))) {
            $comment = 'Parece ausente/no DB -> "Não informado" na view';
        }
        echo "<tr><td><code>{$f}</code></td><td>" . htmlspecialchars(var_export($rawVal, true)) . "</td><td>" . htmlspecialchars(var_export($mappedVal, true)) . "</td><td>" . htmlspecialchars($comment) . "</td></tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<h2>Erro</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

?>
