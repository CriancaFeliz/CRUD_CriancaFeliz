<?php
/**
 * Debug: Verificar se o formulÃ¡rio de ediÃ§Ã£o estÃ¡ carregando com ID
 */

require_once __DIR__ . '/../../bootstrap.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<h2>âŒ Nenhum ID fornecido</h2>";
    echo "<p>Para debugar a ediÃ§Ã£o, acesse: <code>debug_edit_socio.php?id=8</code></p>";
    exit;
}

try {
    $service = new SocioeconomicoService();
    $ficha = $service->getFicha($id);
    
    echo "<h2>âœ“ Ficha encontrada para ediÃ§Ã£o</h2>";
    echo "<p><strong>ID Atendido:</strong> {$ficha['idatendido']}</p>";
    echo "<p><strong>Nome:</strong> {$ficha['nome']}</p>";
    echo "<p><strong>CPF:</strong> {$ficha['cpf']}</p>";
    
    echo "<hr>";
    echo "<h3>Dados da Ficha (completo):</h3>";
    echo "<pre>";
    echo json_encode($ficha, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>Teste: Abrir formulÃ¡rio de ediÃ§Ã£o</h3>";
    echo "<p>Link de ediÃ§Ã£o: <a href='socioeconomico_form.php?id={$id}' target='_blank' style='color: blue; text-decoration: underline;'>Abrir formulÃ¡rio</a></p>";
    
    echo "<hr>";
    echo "<h3>InstruÃ§Ãµes de Debug</h3>";
    echo "<ol>";
    echo "<li>Abra o formulÃ¡rio clicando no link acima</li>";
    echo "<li>Abra o DevTools (F12) â†’ Console</li>";
    echo "<li>Verifique se a mensagem 'âœ“ ID de ediÃ§Ã£o encontrado' aparece</li>";
    echo "<li>Se o ID estiver sendo encontrado, o problema estÃ¡ no Controller/Model</li>";
    echo "<li>Se o ID NÃƒO aparecer, o problema Ã© no carregamento do formulÃ¡rio</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>âŒ Erro ao buscar ficha</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>


