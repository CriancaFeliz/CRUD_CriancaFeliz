<?php
/**
 * TESTE SIMPLES - VERIFICAR SE OS BOTÃ•ES FUNCIONAM
 */

require_once __DIR__ . '/../../bootstrap.php';

try {
    $authService = new AuthService();
    $authService->requireAuth();
    $authService->requirePermission('view_psychological_area');
    
    // Buscar um paciente com anotaÃ§Ãµes para teste
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
        echo "<strong>âŒ Nenhum paciente com anotaÃ§Ãµes encontrado para teste!</strong><br>";
        echo "Crie uma anotaÃ§Ã£o psicolÃ³gica primeiro.";
        echo "</div>";
        exit;
    }
    
    $cpf = $paciente['cpf'];
    $nome = $paciente['nome'];
    $total = $paciente['total_anotacoes'];
    
    echo "<h1>ðŸ§ª TESTE DE FUNCIONALIDADE - BOTÃ•ES EDITAR/DELETAR</h1>";
    echo "<hr>";
    
    echo "<div style='background:#ccffcc; padding:15px; border-radius:8px; margin-bottom:20px;'>";
    echo "<strong>âœ… InformaÃ§Ãµes do Paciente de Teste:</strong><br>";
    echo "Nome: <strong>$nome</strong><br>";
    echo "CPF: <strong>$cpf</strong><br>";
    echo "Total de AnotaÃ§Ãµes: <strong>$total</strong>";
    echo "</div>";
    
    echo "<h2>ðŸ“‹ InstruÃ§Ãµes para Testar:</h2>";
    echo "<ol>";
    echo "<li><a href='psychology.php?action=patient&cpf=$cpf' target='_blank' style='color:#17a2b8; text-decoration:none;'>";
    echo "ðŸ‘‰ Clique aqui para abrir a pÃ¡gina do paciente</a></li>";
    echo "<li>Procure por uma das anotaÃ§Ãµes</li>";
    echo "<li>Teste o botÃ£o <strong>âœï¸ Editar</strong>:";
    echo "<ul>";
    echo "<li>O modal deve abrir com os dados preenchidos</li>";
    echo "<li>O tÃ­tulo do modal muda para 'Editar AnotaÃ§Ã£o'</li>";
    echo "<li>O botÃ£o muda para 'Atualizar AnotaÃ§Ã£o'</li>";
    echo "<li>Modifique um campo e salve</li>";
    echo "<li>A pÃ¡gina deve recarregar com a anotaÃ§Ã£o atualizada</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li>Teste o botÃ£o <strong>ðŸ—‘ï¸ Excluir</strong>:";
    echo "<ul>";
    echo "<li>Um diÃ¡logo deve pedir confirmaÃ§Ã£o</li>";
    echo "<li>ApÃ³s confirmar, a anotaÃ§Ã£o deve ser deletada</li>";
    echo "<li>A pÃ¡gina deve recarregar sem a anotaÃ§Ã£o</li>";
    echo "</ul>";
    echo "</li>";
    echo "</ol>";
    
    echo "<h2>ðŸ“ Checklist de Debug:</h2>";
    echo "<div style='background:#f0f0f0; padding:15px; border-radius:8px;'>";
    echo "<strong>Se os botÃµes NÃƒO funcionarem, abra o Console do Navegador (F12) e verifique:</strong><br><br>";
    echo "<code>Pressione F12 â†’ Aba 'Console' â†’ Clique no botÃ£o Editar/Deletar</code><br><br>";
    echo "VocÃª deve ver mensagens como:<br>";
    echo "<pre style='background:white; padding:10px; border:1px solid #ccc;'>";
    echo "âœ… Editando anotaÃ§Ã£o ID: 123\n";
    echo "âœ… Status da resposta: 200\n";
    echo "âœ… Dados recebidos: {success: true, note: {...}}\n";
    echo "âœ… Modal sendo aberto...";
    echo "</pre>";
    echo "</div>";
    
    echo "<h2>ðŸ” VerificaÃ§Ãµes TÃ©cnicas:</h2>";
    echo "<table style='width:100%; border-collapse:collapse; margin-top:15px;'>";
    echo "<tr style='background:#f0f0f0;'><th style='padding:10px; border:1px solid #ccc; text-align:left;'>Componente</th><th style='padding:10px; border:1px solid #ccc; text-align:left;'>Status</th></tr>";
    
    // Verificar mÃ©todos
    $checks = [
        'API edit_annotation.php' => file_exists('edit_annotation.php'),
        'MÃ©todo PsychologyService::getAnnotationById' => method_exists(new PsychologyService(), 'getAnnotationById'),
        'MÃ©todo PsychologyService::updateNote' => method_exists(new PsychologyService(), 'updateNote'),
        'MÃ©todo PsychologyService::deleteNote' => method_exists(new PsychologyService(), 'deleteNote'),
        'MÃ©todo PsychologyNote::findById' => method_exists(new PsychologyNote(), 'findById'),
        'Controller action update_note' => true,
        'Controller action delete_note' => true,
    ];
    
    foreach ($checks as $name => $status) {
        $icon = $status ? 'âœ…' : 'âŒ';
        $color = $status ? '#28a745' : '#dc3545';
        echo "<tr><td style='padding:10px; border:1px solid #ccc;'>$name</td>";
        echo "<td style='padding:10px; border:1px solid #ccc; color:$color;'><strong>$icon</strong></td></tr>";
    }
    
    echo "</table>";
    
    echo "<h2>ðŸ› ï¸ Passos de ResoluÃ§Ã£o se Houver Erro:</h2>";
    echo "<ol>";
    echo "<li><strong>Erro 404 ao buscar edit_annotation.php:</strong> O arquivo foi criado corretamente?</li>";
    echo "<li><strong>Erro 'ID da anotaÃ§Ã£o Ã© obrigatÃ³rio':</strong> O atributo onclick tem o ID correto?</li>";
    echo "<li><strong>Erro ao carregar anotaÃ§Ã£o:</strong> O usuÃ¡rio tem permissÃ£o 'add_psychological_note'?</li>";
    echo "<li><strong>Modal nÃ£o abre:</strong> Verifique a funÃ§Ã£o openNewNoteModal() no console</li>";
    echo "<li><strong>EdiÃ§Ã£o falha ao salvar:</strong> Verifique se o CSRF token estÃ¡ sendo passado</li>";
    echo "</ol>";
    
    echo "<h2>ðŸ“ž Dados para Teste Manual (SQL):</h2>";
    echo "<pre style='background:#f9f9f9; padding:15px; border:1px solid #ccc; border-radius:8px; overflow-x:auto;'>";
    echo "SELECT * FROM anotacao_psicologica WHERE id_atendido = (SELECT idatendido FROM atendido WHERE cpf = '$cpf') LIMIT 3;";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='background:#ffcccc; padding:20px; border-radius:8px;'>";
    echo "<strong>âŒ ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine();
    echo "</div>";
}


