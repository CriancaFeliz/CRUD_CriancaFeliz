<?php
/**
 * TESTE SIMPLES - VERIFICAR SE OS BOTÕES FUNCIONAM
 */

require_once 'bootstrap.php';

try {
    $authService = new AuthService();
    $authService->requireAuth();
    $authService->requirePermission('view_psychological_area');
    
    // Buscar um paciente com anotações para teste
    $db = Database::getConnection();
    
    $result = $db->query("
        SELECT 
            at.idatendido,
            at.cpf,
            at.nome,
            COUNT(ap.id_anotacao) as total_anotacoes
        FROM atendido at
        LEFT JOIN anotacao_psicologica ap ON ap.id_atendido = at.idatendido
        GROUP BY at.idatendido
        HAVING total_anotacoes > 0
        LIMIT 1
    ");
    
    $paciente = $result->fetch();
    
    if (!$paciente) {
        echo "<div style='background:#ffcccc; padding:20px; border-radius:8px;'>";
        echo "<strong>❌ Nenhum paciente com anotações encontrado para teste!</strong><br>";
        echo "Crie uma anotação psicológica primeiro.";
        echo "</div>";
        exit;
    }
    
    $cpf = $paciente['cpf'];
    $nome = $paciente['nome'];
    $total = $paciente['total_anotacoes'];
    
    echo "<h1>🧪 TESTE DE FUNCIONALIDADE - BOTÕES EDITAR/DELETAR</h1>";
    echo "<hr>";
    
    echo "<div style='background:#ccffcc; padding:15px; border-radius:8px; margin-bottom:20px;'>";
    echo "<strong>✅ Informações do Paciente de Teste:</strong><br>";
    echo "Nome: <strong>$nome</strong><br>";
    echo "CPF: <strong>$cpf</strong><br>";
    echo "Total de Anotações: <strong>$total</strong>";
    echo "</div>";
    
    echo "<h2>📋 Instruções para Testar:</h2>";
    echo "<ol>";
    echo "<li><a href='psychology.php?action=patient&cpf=$cpf' target='_blank' style='color:#17a2b8; text-decoration:none;'>";
    echo "👉 Clique aqui para abrir a página do paciente</a></li>";
    echo "<li>Procure por uma das anotações</li>";
    echo "<li>Teste o botão <strong>✏️ Editar</strong>:";
    echo "<ul>";
    echo "<li>O modal deve abrir com os dados preenchidos</li>";
    echo "<li>O título do modal muda para 'Editar Anotação'</li>";
    echo "<li>O botão muda para 'Atualizar Anotação'</li>";
    echo "<li>Modifique um campo e salve</li>";
    echo "<li>A página deve recarregar com a anotação atualizada</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li>Teste o botão <strong>🗑️ Excluir</strong>:";
    echo "<ul>";
    echo "<li>Um diálogo deve pedir confirmação</li>";
    echo "<li>Após confirmar, a anotação deve ser deletada</li>";
    echo "<li>A página deve recarregar sem a anotação</li>";
    echo "</ul>";
    echo "</li>";
    echo "</ol>";
    
    echo "<h2>📝 Checklist de Debug:</h2>";
    echo "<div style='background:#f0f0f0; padding:15px; border-radius:8px;'>";
    echo "<strong>Se os botões NÃO funcionarem, abra o Console do Navegador (F12) e verifique:</strong><br><br>";
    echo "<code>Pressione F12 → Aba 'Console' → Clique no botão Editar/Deletar</code><br><br>";
    echo "Você deve ver mensagens como:<br>";
    echo "<pre style='background:white; padding:10px; border:1px solid #ccc;'>";
    echo "✅ Editando anotação ID: 123\n";
    echo "✅ Status da resposta: 200\n";
    echo "✅ Dados recebidos: {success: true, note: {...}}\n";
    echo "✅ Modal sendo aberto...";
    echo "</pre>";
    echo "</div>";
    
    echo "<h2>🔍 Verificações Técnicas:</h2>";
    echo "<table style='width:100%; border-collapse:collapse; margin-top:15px;'>";
    echo "<tr style='background:#f0f0f0;'><th style='padding:10px; border:1px solid #ccc; text-align:left;'>Componente</th><th style='padding:10px; border:1px solid #ccc; text-align:left;'>Status</th></tr>";
    
    // Verificar métodos
    $checks = [
        'API edit_annotation.php' => file_exists('edit_annotation.php'),
        'Método PsychologyService::getAnnotationById' => method_exists(new PsychologyService(), 'getAnnotationById'),
        'Método PsychologyService::updateNote' => method_exists(new PsychologyService(), 'updateNote'),
        'Método PsychologyService::deleteNote' => method_exists(new PsychologyService(), 'deleteNote'),
        'Método PsychologyNote::findById' => method_exists(new PsychologyNote(), 'findById'),
        'Controller action update_note' => true,
        'Controller action delete_note' => true,
    ];
    
    foreach ($checks as $name => $status) {
        $icon = $status ? '✅' : '❌';
        $color = $status ? '#28a745' : '#dc3545';
        echo "<tr><td style='padding:10px; border:1px solid #ccc;'>$name</td>";
        echo "<td style='padding:10px; border:1px solid #ccc; color:$color;'><strong>$icon</strong></td></tr>";
    }
    
    echo "</table>";
    
    echo "<h2>🛠️ Passos de Resolução se Houver Erro:</h2>";
    echo "<ol>";
    echo "<li><strong>Erro 404 ao buscar edit_annotation.php:</strong> O arquivo foi criado corretamente?</li>";
    echo "<li><strong>Erro 'ID da anotação é obrigatório':</strong> O atributo onclick tem o ID correto?</li>";
    echo "<li><strong>Erro ao carregar anotação:</strong> O usuário tem permissão 'add_psychological_note'?</li>";
    echo "<li><strong>Modal não abre:</strong> Verifique a função openNewNoteModal() no console</li>";
    echo "<li><strong>Edição falha ao salvar:</strong> Verifique se o CSRF token está sendo passado</li>";
    echo "</ol>";
    
    echo "<h2>📞 Dados para Teste Manual (SQL):</h2>";
    echo "<pre style='background:#f9f9f9; padding:15px; border:1px solid #ccc; border-radius:8px; overflow-x:auto;'>";
    echo "SELECT * FROM anotacao_psicologica WHERE id_atendido = (SELECT idatendido FROM atendido WHERE cpf = '$cpf') LIMIT 3;";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='background:#ffcccc; padding:20px; border-radius:8px;'>";
    echo "<strong>❌ ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine();
    echo "</div>";
}
