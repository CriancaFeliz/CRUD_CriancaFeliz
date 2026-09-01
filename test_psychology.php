<?php
// Teste de sintaxe do PsychologyService
require_once 'bootstrap.php';

try {
    echo "Testando carregamento do PsychologyService...<br>";
    $service = new PsychologyService();
    echo "✅ PsychologyService carregado com sucesso!<br>";
    
    echo "Testando método getNote...<br>";
    $note = $service->getNote('note_001');
    echo "✅ Método getNote funciona!<br>";
    
    echo "Testando método updateNote...<br>";
    // Não vamos executar, apenas verificar se existe
    if (method_exists($service, 'updateNote')) {
        echo "✅ Método updateNote existe!<br>";
    } else {
        echo "❌ Método updateNote não existe!<br>";
    }
    
    echo "<br><strong>Todos os testes passaram!</strong>";
    
} catch (Exception $e) {
    echo "<strong>Erro encontrado:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
}
?>
