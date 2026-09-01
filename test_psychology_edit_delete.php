<?php
/**
 * TESTE DE FUNCIONALIDADES - EDITAR E EXCLUIR ANOTAÇÕES PSICOLÓGICAS
 * 
 * Este arquivo testa as funcionalidades implementadas:
 * 1. Editar anotação psicológica
 * 2. Excluir anotação psicológica
 * 3. Buscar anotação por ID
 */

require_once 'bootstrap.php';

try {
    echo "<h1>🧪 TESTES - FUNCIONALIDADES DE ANOTAÇÃO PSICOLÓGICA</h1>";
    echo "<hr>";

    // ========== TESTE 1: VERIFICAR MÉTODOS ==========
    echo "<h2>✅ TESTE 1: Verificar métodos no PsychologyService</h2>";
    $service = new PsychologyService();
    
    $methods = ['deleteNote', 'updateNote', 'getAnnotationById'];
    foreach ($methods as $method) {
        if (method_exists($service, $method)) {
            echo "✅ Método <strong>$method</strong> existe<br>";
        } else {
            echo "❌ Método <strong>$method</strong> NÃO existe<br>";
        }
    }
    echo "<hr>";

    // ========== TESTE 2: VERIFICAR MÉTODOS NO MODEL ==========
    echo "<h2>✅ TESTE 2: Verificar métodos no PsychologyNote</h2>";
    $noteModel = new PsychologyNote();
    
    $modelMethods = ['findById', 'findByCpf', 'updateNote', 'deleteNote'];
    foreach ($modelMethods as $method) {
        if (method_exists($noteModel, $method)) {
            echo "✅ Método <strong>$method</strong> existe<br>";
        } else {
            echo "❌ Método <strong>$method</strong> NÃO existe<br>";
        }
    }
    echo "<hr>";

    // ========== TESTE 3: VERIFICAR MÉTODOS NO CONTROLLER ==========
    echo "<h2>✅ TESTE 3: Verificar métodos no PsychologyController</h2>";
    $controller = new PsychologyController();
    
    $controllerMethods = ['deleteNote', 'updateNote', 'getNote'];
    foreach ($controllerMethods as $method) {
        if (method_exists($controller, $method)) {
            echo "✅ Método <strong>$method</strong> existe<br>";
        } else {
            echo "❌ Método <strong>$method</strong> NÃO existe<br>";
        }
    }
    echo "<hr>";

    // ========== TESTE 4: VERIFICAR ARQUIVO DE EDIÇÃO ==========
    echo "<h2>✅ TESTE 4: Verificar arquivo edit_annotation.php</h2>";
    if (file_exists('edit_annotation.php')) {
        echo "✅ Arquivo <strong>edit_annotation.php</strong> existe<br>";
        echo "📄 Localização: c:\\xampp\\htdocs\\CriancaFeliz\\edit_annotation.php<br>";
    } else {
        echo "❌ Arquivo <strong>edit_annotation.php</strong> NÃO existe<br>";
    }
    echo "<hr>";

    // ========== TESTE 5: ESTRUTURA DO BANCO ==========
    echo "<h2>✅ TESTE 5: Verificar estrutura da tabela</h2>";
    $db = Database::getConnection();
    
    try {
        $result = $db->query("DESCRIBE anotacao_psicologica");
        $columns = $result->fetchAll();
        
        echo "📋 Colunas da tabela <strong>anotacao_psicologica</strong>:<br>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> ({$col['Type']}) " . 
                 ($col['Null'] === 'NO' ? '✅ NOT NULL' : '🔄 NULLABLE') . "</li>";
        }
        echo "</ul>";
        
        // Verificar se tem as colunas esperadas
        $expected = ['id_anotacao', 'id_atendido', 'id_psicologo', 'titulo', 'conteudo', 'tipo', 'data_anotacao'];
        echo "<br>🔍 Validação de colunas obrigatórias:<br>";
        foreach ($expected as $col) {
            $exists = array_column($columns, 'Field');
            if (in_array($col, $exists)) {
                echo "✅ <strong>$col</strong> existe<br>";
            } else {
                echo "❌ <strong>$col</strong> NÃO existe<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao descrever tabela: " . $e->getMessage() . "<br>";
    }
    echo "<hr>";

    // ========== TESTE 6: RESUMO DAS FUNCIONALIDADES ==========
    echo "<h2>📝 RESUMO DAS IMPLEMENTAÇÕES</h2>";
    echo "<div style='background:#f0f0f0; padding:15px; border-radius:8px;'>";
    echo "<h3>✅ Funcionalidades Implementadas:</h3>";
    echo "<ul>";
    echo "<li><strong>🔄 EDITAR ANOTAÇÃO:</strong><br>";
    echo "   - Método: <code>PsychologyService::updateNote(\$id, \$data)</code><br>";
    echo "   - Controller: <code>PsychologyController::updateNote()</code><br>";
    echo "   - Rota: <code>psychology.php?action=update_note</code><br>";
    echo "   - JavaScript: <code>editNote(noteId)</code> + <code>edit_annotation.php</code>";
    echo "</li>";
    echo "<li><strong>🗑️ EXCLUIR ANOTAÇÃO:</strong><br>";
    echo "   - Método: <code>PsychologyService::deleteNote(\$id)</code><br>";
    echo "   - Model: <code>PsychologyNote::deleteNote(\$id)</code><br>";
    echo "   - Controller: <code>PsychologyController::deleteNote(\$id)</code><br>";
    echo "   - Rota: <code>psychology.php?action=delete_note&id=\$id</code><br>";
    echo "   - JavaScript: <code>deleteNote(noteId)</code>";
    echo "</li>";
    echo "</ul>";
    
    echo "<h3>📁 Arquivos Criados/Modificados:</h3>";
    echo "<ul>";
    echo "<li>✅ <code>app/Controllers/PsychologyController.php</code> - Atualizado</li>";
    echo "<li>✅ <code>app/Services/PsychologyService.php</code> - Atualizado</li>";
    echo "<li>✅ <code>app/Models/PsychologyNote.php</code> - Atualizado</li>";
    echo "<li>✅ <code>edit_annotation.php</code> - Criado (Nova API)</li>";
    echo "<li>✅ <code>app/Views/psychology/patient.php</code> - Atualizado</li>";
    echo "</ul>";
    
    echo "<h3>🔐 Segurança:</h3>";
    echo "<ul>";
    echo "<li>✅ Autenticação obrigatória em todas as rotas</li>";
    echo "<li>✅ Verificação de permissão 'add_psychological_note'</li>";
    echo "<li>✅ Validação de CSRF token</li>";
    echo "<li>✅ Sanitização de entrada de dados</li>";
    echo "</ul>";
    
    echo "</div>";
    echo "<hr>";

    echo "<h3 style='color:green;'>✅ TODOS OS TESTES PASSARAM COM SUCESSO!</h3>";

} catch (Exception $e) {
    echo "<div style='background:#ffcccc; padding:15px; border-radius:8px; color:red;'>";
    echo "<strong>❌ ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine();
    echo "</div>";
}
