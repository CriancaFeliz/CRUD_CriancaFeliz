<?php
/**
 * Debug: Verifica e corrige a renda de Marina Carla
 */

require_once 'bootstrap.php';

try {
    $db = new Database();
    
    echo "<h2>Debug: Renda de Marina Carla (ID 8)</h2>";
    echo "<hr>";
    
    // 1. Ver os dados brutos da ficha
    echo "<h3>Dados Brutos da Ficha (idficha 4):</h3>";
    $stmt = $db->query("
        SELECT *
        FROM Ficha_Socioeconomico
        WHERE idficha = 4
    ");
    
    $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    echo json_encode($ficha, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</pre>";
    
    // 2. Ver se há JSON com despesas ou renda
    echo "<hr>";
    echo "<h3>Campos com possíveis rendas:</h3>";
    
    $rendaFields = ['renda_familiar', 'renda_per_capita', 'despesas_json', 'familia_json', 'entrevistado_renda', 'renda_membro_1', 'renda_membro_2', 'renda_membro_3', 'renda_membro_4', 'renda_membro_5'];
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    
    foreach ($rendaFields as $field) {
        if (isset($ficha[$field])) {
            $valor = $ficha[$field];
            echo "<tr><td>{$field}</td><td>";
            if (is_string($valor) && strlen($valor) > 100) {
                echo "<pre>" . json_encode(json_decode($valor, true), JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo htmlspecialchars($valor);
            }
            echo "</td></tr>";
        }
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Correção Manual (se necessário):</h3>";
    echo "<p>Se o valor de 360000.00 estiver incorreto, você pode:</p>";
    echo "<ol>";
    echo "<li>Editar a ficha através do formulário e reenviar</li>";
    echo "<li>Ou clicar no botão abaixo para resettá-la para 0:</li>";
    echo "</ol>";
    
    if ($_GET['reset'] == 'marina') {
        $stmt = $db->query("
            UPDATE Ficha_Socioeconomico
            SET renda_familiar = 0
            WHERE idficha = 4
        ");
        echo "<p style='color: green; font-weight: bold;'>✓ Renda de Marina Carla resetada para 0!</p>";
    } else {
        echo "<p><a href='?reset=marina' style='background: red; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Resetar Renda para 0</a></p>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    echo "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
