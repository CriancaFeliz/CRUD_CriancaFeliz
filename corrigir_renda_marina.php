<?php
/**
 * Corrigir renda de Marina Carla
 */

require_once 'bootstrap.php';

if (isset($_GET['confirm']) && $_GET['confirm'] == '1') {
    try {
        $db = new Database();
        
        // Atualizar a renda para 3500 (2000 + 1500)
        $db->query("
            UPDATE Ficha_Socioeconomico
            SET renda_familiar = 3500
            WHERE idficha = 4
        ");
        
        echo "<h2>✓ Renda de Marina Carla corrigida!</h2>";
        echo "<p>Renda atualizada para: <strong>R$ 3.500,00</strong></p>";
        echo "<p><a href='socioeconomico_list.php'>Voltar para a lista</a></p>";
    } catch (Exception $e) {
        echo "<h2>✗ Erro ao atualizar</h2>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
} else {
    echo "<h2>Confirmar Correção de Renda</h2>";
    echo "<p><strong>Marina Carla</strong> (ID 8)</p>";
    echo "<p>Renda atual (ERRADA): <strong style='color: red;'>R$ 360.000,00</strong></p>";
    echo "<p>Renda corrigida: <strong style='color: green;'>R$ 3.500,00</strong></p>";
    echo "<p>(Pai: R$ 2.000,00 + Ela: R$ 1.500,00)</p>";
    echo "<br>";
    echo "<a href='?confirm=1' style='background: green; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>Confirmar Correção</a>";
    echo " | ";
    echo "<a href='socioeconomico_list.php' style='background: gray; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>Cancelar</a>";
}
?>
