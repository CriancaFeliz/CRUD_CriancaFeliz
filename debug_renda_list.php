<?php
/**
 * Debug: Verifica a renda_familiar nas fichas listadas
 */

require_once 'bootstrap.php';

try {
    $db = new Database();
    
    echo "<h2>Debug: Renda Familiar na Lista</h2>";
    echo "<hr>";
    
    // 1. Verificar total de registros em Atendido
    echo "<h3>Total de Atendidos:</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM Atendido");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de registros em Atendido: <strong>" . $result['total'] . "</strong></p>";
    
    // 2. Verificar total de Fichas Socioeconômicas
    echo "<h3>Total de Fichas Socioeconômicas:</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM Ficha_Socioeconomico");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de registros em Ficha_Socioeconomico: <strong>" . $result['total'] . "</strong></p>";
    
    echo "<hr>";
    echo "<h3>Listagem de Fichas com Renda:</h3>";
    
    // Query direta para ver os dados da Ficha_Socioeconomico
    $stmt = $db->query("
        SELECT 
            a.idatendido,
            a.nome,
            a.cpf,
            a.data_acolhimento,
            a.data_cadastro,
            f.idficha,
            f.renda_familiar,
            f.qtd_pessoas,
            f.nr_comodos
        FROM Atendido a
        LEFT JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
        ORDER BY a.data_cadastro DESC
        LIMIT 20
    ");
    
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    echo "Total de fichas (com ou sem Ficha_Socioeconomico): " . count($fichas) . "\n\n";
    
    foreach ($fichas as $ficha) {
        $status = !empty($ficha['idficha']) ? "✓ COM FICHA" : "✗ SEM FICHA";
        echo "[{$status}] ID: {$ficha['idatendido']} | Nome: {$ficha['nome']} | Renda: {$ficha['renda_familiar']} | Qtd Pessoas: {$ficha['qtd_pessoas']} | Nr Cômodos: {$ficha['nr_comodos']}\n";
    }
    
    echo "</pre>";
    echo "<hr>";
    
    // Mostrar também o JSON completo
    echo "<h3>JSON Completo:</h3>";
    echo "<pre>";
    echo json_encode($fichas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>Atendidos SEM Ficha Socioeconômica:</h3>";
    
    $stmt = $db->query("
        SELECT 
            a.idatendido,
            a.nome,
            a.cpf,
            a.data_cadastro
        FROM Atendido a
        LEFT JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
        WHERE f.idficha IS NULL
        ORDER BY a.data_cadastro DESC
    ");
    
    $semFicha = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($semFicha)) {
        echo "<p>Total de atendidos sem ficha: <strong>" . count($semFicha) . "</strong></p>";
        echo "<pre>";
        foreach ($semFicha as $atendido) {
            echo "ID: {$atendido['idatendido']} | Nome: {$atendido['nome']} | CPF: {$atendido['cpf']}\n";
        }
        echo "</pre>";
    } else {
        echo "<p>Todos os atendidos têm ficha socioeconômica!</p>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    echo "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
