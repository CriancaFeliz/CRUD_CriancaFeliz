<?php
/**
 * Debug: Verificar se o formulário de edição está carregando com ID
 */

require_once 'bootstrap.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<h2>❌ Nenhum ID fornecido</h2>";
    echo "<p>Para debugar a edição, acesse: <code>debug_edit_socio.php?id=8</code></p>";
    exit;
}

try {
    $service = new SocioeconomicoService();
    $ficha = $service->getFicha($id);
    
    echo "<h2>✓ Ficha encontrada para edição</h2>";
    echo "<p><strong>ID Atendido:</strong> {$ficha['idatendido']}</p>";
    echo "<p><strong>Nome:</strong> {$ficha['nome']}</p>";
    echo "<p><strong>CPF:</strong> {$ficha['cpf']}</p>";
    
    echo "<hr>";
    echo "<h3>Dados da Ficha (completo):</h3>";
    echo "<pre>";
    echo json_encode($ficha, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>Teste: Abrir formulário de edição</h3>";
    echo "<p>Link de edição: <a href='socioeconomico_form.php?id={$id}' target='_blank' style='color: blue; text-decoration: underline;'>Abrir formulário</a></p>";
    
    echo "<hr>";
    echo "<h3>Instruções de Debug</h3>";
    echo "<ol>";
    echo "<li>Abra o formulário clicando no link acima</li>";
    echo "<li>Abra o DevTools (F12) → Console</li>";
    echo "<li>Verifique se a mensagem '✓ ID de edição encontrado' aparece</li>";
    echo "<li>Se o ID estiver sendo encontrado, o problema está no Controller/Model</li>";
    echo "<li>Se o ID NÃO aparecer, o problema é no carregamento do formulário</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro ao buscar ficha</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
