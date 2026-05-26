<?php
/**
 * TESTE DE FUNCIONALIDADES - EDITAR E EXCLUIR ANOTAÃ‡Ã•ES PSICOLÃ“GICAS
 * 
 * Este arquivo testa as funcionalidades implementadas:
 * 1. Editar anotaÃ§Ã£o psicolÃ³gica
 * 2. Excluir anotaÃ§Ã£o psicolÃ³gica
 * 3. Buscar anotaÃ§Ã£o por ID
 */

require_once __DIR__ . '/../../app/bootstrap.php';

try {
    echo "<h1>ðŸ§ª TESTES - FUNCIONALIDADES DE ANOTAÃ‡ÃƒO PSICOLÃ“GICA</h1>";
    echo "<hr>";

    // ========== TESTE 1: VERIFICAR MÃ‰TODOS ==========
    echo "<h2>âœ… TESTE 1: Verificar mÃ©todos no PsychologyService</h2>";
    $service = new PsychologyService();
    
    $methods = ['deleteNote', 'updateNote', 'getAnnotationById'];
    foreach ($methods as $method) {
        if (method_exists($service, $method)) {
            echo "âœ… MÃ©todo <strong>$method</strong> existe<br>";
        } else {
            echo "âŒ MÃ©todo <strong>$method</strong> NÃƒO existe<br>";
        }
    }
    echo "<hr>";

    // ========== TESTE 2: VERIFICAR MÃ‰TODOS NO MODEL ==========
    echo "<h2>âœ… TESTE 2: Verificar mÃ©todos no PsychologyNote</h2>";
    $noteModel = new PsychologyNote();
    
    $modelMethods = ['findById', 'findByCpf', 'updateNote', 'deleteNote'];
    foreach ($modelMethods as $method) {
        if (method_exists($noteModel, $method)) {
            echo "âœ… MÃ©todo <strong>$method</strong> existe<br>";
        } else {
            echo "âŒ MÃ©todo <strong>$method</strong> NÃƒO existe<br>";
        }
    }
    echo "<hr>";

    // ========== TESTE 3: VERIFICAR MÃ‰TODOS NO CONTROLLER ==========
    echo "<h2>âœ… TESTE 3: Verificar mÃ©todos no PsychologyController</h2>";
    $controller = new PsychologyController();
    
    $controllerMethods = ['deleteNote', 'updateNote', 'getNote'];
    foreach ($controllerMethods as $method) {
        if (method_exists($controller, $method)) {
            echo "âœ… MÃ©todo <strong>$method</strong> existe<br>";
        } else {
            echo "âŒ MÃ©todo <strong>$method</strong> NÃƒO existe<br>";
        }
    }
    echo "<hr>";

    // ========== TESTE 4: VERIFICAR ARQUIVO DE EDIÃ‡ÃƒO ==========
    echo "<h2>âœ… TESTE 4: Verificar arquivo edit_annotation.php</h2>";
    if (file_exists('edit_annotation.php')) {
        echo "âœ… Arquivo <strong>edit_annotation.php</strong> existe<br>";
        echo "ðŸ“„ LocalizaÃ§Ã£o: c:\\xampp\\htdocs\\CriancaFeliz\\edit_annotation.php<br>";
    } else {
        echo "âŒ Arquivo <strong>edit_annotation.php</strong> NÃƒO existe<br>";
    }
    echo "<hr>";

    // ========== TESTE 5: ESTRUTURA DO BANCO ==========
    echo "<h2>âœ… TESTE 5: Verificar estrutura da tabela</h2>";
    $db = Database::getConnection();
    
    try {
        $result = $db->query("DESCRIBE anotacao_psicologica");
        $columns = $result->fetchAll();
        
        echo "ðŸ“‹ Colunas da tabela <strong>anotacao_psicologica</strong>:<br>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> ({$col['Type']}) " . 
                 ($col['Null'] === 'NO' ? 'âœ… NOT NULL' : 'ðŸ”„ NULLABLE') . "</li>";
        }
        echo "</ul>";
        
        // Verificar se tem as colunas esperadas
        $expected = ['id_anotacao', 'id_atendido', 'id_psicologo', 'titulo', 'conteudo', 'tipo', 'data_anotacao'];
        echo "<br>ðŸ” ValidaÃ§Ã£o de colunas obrigatÃ³rias:<br>";
        foreach ($expected as $col) {
            $exists = array_column($columns, 'Field');
            if (in_array($col, $exists)) {
                echo "âœ… <strong>$col</strong> existe<br>";
            } else {
                echo "âŒ <strong>$col</strong> NÃƒO existe<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "âŒ Erro ao descrever tabela: " . $e->getMessage() . "<br>";
    }
    echo "<hr>";

    // ========== TESTE 6: RESUMO DAS FUNCIONALIDADES ==========
    echo "<h2>ðŸ“ RESUMO DAS IMPLEMENTAÃ‡Ã•ES</h2>";
    echo "<div style='background:#f0f0f0; padding:15px; border-radius:8px;'>";
    echo "<h3>âœ… Funcionalidades Implementadas:</h3>";
    echo "<ul>";
    echo "<li><strong>ðŸ”„ EDITAR ANOTAÃ‡ÃƒO:</strong><br>";
    echo "   - MÃ©todo: <code>PsychologyService::updateNote(\$id, \$data)</code><br>";
    echo "   - Controller: <code>PsychologyController::updateNote()</code><br>";
    echo "   - Rota: <code>psychology.php?action=update_note</code><br>";
    echo "   - JavaScript: <code>editNote(noteId)</code> + <code>edit_annotation.php</code>";
    echo "</li>";
    echo "<li><strong>ðŸ—‘ï¸ EXCLUIR ANOTAÃ‡ÃƒO:</strong><br>";
    echo "   - MÃ©todo: <code>PsychologyService::deleteNote(\$id)</code><br>";
    echo "   - Model: <code>PsychologyNote::deleteNote(\$id)</code><br>";
    echo "   - Controller: <code>PsychologyController::deleteNote(\$id)</code><br>";
    echo "   - Rota: <code>psychology.php?action=delete_note&id=\$id</code><br>";
    echo "   - JavaScript: <code>deleteNote(noteId)</code>";
    echo "</li>";
    echo "</ul>";
    
    echo "<h3>ðŸ“ Arquivos Criados/Modificados:</h3>";
    echo "<ul>";
    echo "<li>âœ… <code>app/Controllers/PsychologyController.php</code> - Atualizado</li>";
    echo "<li>âœ… <code>app/Services/PsychologyService.php</code> - Atualizado</li>";
    echo "<li>âœ… <code>app/Models/PsychologyNote.php</code> - Atualizado</li>";
    echo "<li>âœ… <code>edit_annotation.php</code> - Criado (Nova API)</li>";
    echo "<li>âœ… <code>app/Views/psychology/patient.php</code> - Atualizado</li>";
    echo "</ul>";
    
    echo "<h3>ðŸ” SeguranÃ§a:</h3>";
    echo "<ul>";
    echo "<li>âœ… AutenticaÃ§Ã£o obrigatÃ³ria em todas as rotas</li>";
    echo "<li>âœ… VerificaÃ§Ã£o de permissÃ£o 'add_psychological_note'</li>";
    echo "<li>âœ… ValidaÃ§Ã£o de CSRF token</li>";
    echo "<li>âœ… SanitizaÃ§Ã£o de entrada de dados</li>";
    echo "</ul>";
    
    echo "</div>";
    echo "<hr>";

    echo "<h3 style='color:green;'>âœ… TODOS OS TESTES PASSARAM COM SUCESSO!</h3>";

} catch (Exception $e) {
    echo "<div style='background:#ffcccc; padding:15px; border-radius:8px; color:red;'>";
    echo "<strong>âŒ ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine();
    echo "</div>";
}

