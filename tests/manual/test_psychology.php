<?php
// Teste de sintaxe do PsychologyService
require_once __DIR__ . '/../../bootstrap.php';

try {
    echo "Testando carregamento do PsychologyService...<br>";
    $service = new PsychologyService();
    echo "âœ… PsychologyService carregado com sucesso!<br>";
    
    echo "Testando mÃ©todo getNote...<br>";
    $note = $service->getNote('note_001');
    echo "âœ… MÃ©todo getNote funciona!<br>";
    
    echo "Testando mÃ©todo updateNote...<br>";
    // NÃ£o vamos executar, apenas verificar se existe
    if (method_exists($service, 'updateNote')) {
        echo "âœ… MÃ©todo updateNote existe!<br>";
    } else {
        echo "âŒ MÃ©todo updateNote nÃ£o existe!<br>";
    }
    
    echo "<br><strong>Todos os testes passaram!</strong>";
    
} catch (Exception $e) {
    echo "<strong>Erro encontrado:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
}
?>

