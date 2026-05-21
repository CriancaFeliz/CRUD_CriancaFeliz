<?php
// Função de segurança para datas
function safeDate($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '-';
    $ts = strtotime(str_replace('/', '-', $date));
    return ($ts && $ts > 0) ? date('d/m/Y', $ts) : '-';
}

// Garantir variáveis obrigatórias
$patient = $patient ?? [];
$notes = $notes ?? [];

// Campos seguros
$nome = htmlspecialchars((string)($patient['nome_completo'] ?? $patient['nome'] ?? 'Não informado'));
$cpf = htmlspecialchars((string)($patient['cpf'] ?? 'Não informado'));
$idade = isset($patient['idade']) ? (int)$patient['idade'] : 0;
$responsavel = htmlspecialchars((string)($patient['responsavel'] ?? 'Não informado'));
$contato = htmlspecialchars((string)($patient['contato'] ?? 'Não informado'));
$dataAcolh = safeDate($patient['data_acolhimento'] ?? null);
$queixa = htmlspecialchars((string)($patient['queixa_principal'] ?? ''));

// Avatar inicial
$avatar = strtoupper(mb_substr($nome, 0, 1, 'UTF-8'));
?>

<div class="actions" style="display:flex; gap:10px; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <a href="psychology.php?action=patients" class="btn secondary" style="background:#6c757d; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
        ← Voltar aos Pacientes
    </a>

    <button onclick="openNewNoteModal()" class="btn" style="background:#17a2b8; color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:8px;">
        📝 Nova Anotação
    </button>
</div>

<!-- Cabeçalho do Paciente -->
<div class="patient-header" style="background: linear-gradient(135deg, #17a2b8, #20c997); border-radius: 16px; padding: 24px; margin-bottom: 24px; color: white;">
    <div style="display: flex; align-items: center; gap: 20px;">

        <div class="patient-avatar" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 32px; border: 3px solid rgba(255,255,255,0.3);">
            <?= $avatar ?>
        </div>

        <div style="flex: 1;">
            <h1 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700;">
                <?= $nome ?>
            </h1>

            <div style="font-weight: 600; font-size: 16px;">
                <?= $idade ?> anos
            </div>

            <div style="font-weight: 600; font-size: 16px;">
                <?= $responsavel ?>
            </div>

            <div style="font-weight: 600; font-size: 16px;">
                <?= $contato ?>
            </div>

            <div style="font-weight: 600; font-size: 16px;">
                <?= $dataAcolh ?>
            </div>
        </div>
    </div>

    <?php if (!empty($patient['queixa_principal'])): ?>
        <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 16px; margin-top: 20px;">
            <div style="font-weight: 600; margin-bottom: 8px; opacity: 0.9;">🎯 Queixa Principal</div>
            <div style="line-height: 1.5; opacity: 0.9;">
                <?= $queixa ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Anotações -->
<div class="psychology-notes" style="background:#fff; border-radius:12px; padding:24px; margin-bottom:24px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 20px 0; color:#495057; display:flex; align-items:center; gap:8px; justify-content:space-between;">
        <span>🧠 Anotações Psicológicas</span>
        <span style="font-size:14px; font-weight:400; color:#6c757d;">
            <?= count($notes) ?> anotação(ões)
        </span>
    </h3>

    <?php if (empty($notes)): ?>
        <div class="empty-notes" style="text-align:center; padding:40px; color:#6c757d;">
            <div style="font-size:48px; margin-bottom:16px;">📝</div>
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Nenhuma anotação ainda</div>
            <div style="margin-bottom:20px;">Comece criando a primeira anotação psicológica para este paciente</div>

            <button onclick="openNewNoteModal()" class="btn" style="background:#17a2b8; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
                📝 Criar Primeira Anotação
            </button>
        </div>
    <?php else: ?>

        <div class="notes-timeline">
            <?php foreach ($notes as $index => $note): 

                $type = htmlspecialchars((string)($note['note_type'] ?? 'observacao'));
                $title = htmlspecialchars((string)($note['title'] ?? ''));
                $content = nl2br(htmlspecialchars((string)($note['content'] ?? '')));
                $psychologist = htmlspecialchars((string)($note['psychologist_name'] ?? 'Psicólogo'));

                $created = !empty($note['created_at']) ? date('d/m/Y H:i', strtotime($note['created_at'])) : '-';

                $behavior = nl2br(htmlspecialchars((string)($note['behavior_notes'] ?? '')));
                $recommend = nl2br(htmlspecialchars((string)($note['recommendations'] ?? '')));
                $nextSession = !empty($note['next_session']) ? date('d/m/Y H:i', strtotime($note['next_session'])) : null;

                $mood = (int)($note['mood_assessment'] ?? 0);

                $icons = [
                    'consulta' => '💬',
                    'avaliacao' => '📋',
                    'evolucao' => '📈',
                    'observacao' => '👁️'
                ];

                $icon = $icons[$type] ?? '📝';

            ?>
                <div class="note-item"
                    style="position:relative; padding-left:40px; margin-bottom:24px; <?= $index === count($notes) - 1 ? '' : 'border-left:2px solid #e9ecef;' ?>">

                    <div style="position:absolute; left:-8px; top:8px; width:16px; height:16px; border-radius:50%; background:#17a2b8; border:3px solid #fff; box-shadow:0 0 0 2px #e9ecef;"></div>

                    <div class="note-card psychology-note-card"
                        style="border-radius:12px; padding:20px; border-left:4px solid #17a2b8;">

                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                            <div>
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                    <span style="font-size:20px;"><?= $icon ?></span>
                                    <span class="note-type-text" style="font-weight:600; color:#17a2b8; text-transform:capitalize;">
                                        <?= $type ?>
                                    </span>
                                </div>

                                <?php if ($title): ?>
                                    <div class="note-title" style="font-weight:600; color:#212529; margin-bottom:8px;">
                                        <?= $title ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="note-meta"
                                style="text-align:right; font-size:12px; color:#6c757d;">
                                <div><?= $created ?></div>
                                <div style="margin-top:2px;">Por: <?= $psychologist ?></div>
                            </div>
                        </div>

                        <div class="note-content" style="color:#495057; line-height:1.6; margin-bottom:16px;">
                            <?= $content ?>
                        </div>

                        <?php if ($mood > 0): ?>
                            <div class="mood-assessment-section" style="background:#fff; border-radius:8px; padding:12px; margin-bottom:12px;">
                                <div class="mood-label" style="font-size:12px; color:#6c757d; margin-bottom:4px;">😊 Avaliação de Humor</div>

                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="display:flex; gap:3px;">
                                        <?php
                                            $colors = [1=>'#dc3545',2=>'#fd7e14',3=>'#ffc107',4=>'#20c997',5=>'#28a745'];
                                            for ($i=1; $i<=5; $i++):
                                        ?>
                                            <div style="width:12px; height:12px; border-radius:50%; background:<?= $i <= $mood ? $colors[$mood] : '#e9ecef' ?>;"></div>
                                        <?php endfor; ?>
                                    </div>

                                    <span style="color:<?= $colors[$mood] ?? '#6c757d' ?>">
                                        <?= ['','Muito Triste','Triste','Neutro','Alegre','Muito Alegre'][$mood] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($behavior)): ?>
                            <div class="behavior-section" style="background:#fff; border-radius:8px; padding:12px; margin-bottom:12px;">
                                <div class="behavior-label" style="font-size:12px; color:#6c757d; margin-bottom:4px;">
                                    👀 Observações Comportamentais
                                </div>
                                <div><?= $behavior ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recommend)): ?>
                            <div class="recommendations-section" style="background:#fff; border-radius:8px; padding:12px; margin-bottom:12px;">
                                <div class="recommendations-label" style="font-size:12px; color:#6c757d; margin-bottom:4px;">
                                    💡 Recomendações
                                </div>
                                <div><?= $recommend ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($nextSession): ?>
                            <div class="next-session-section" style="background:#e3f2fd; border-radius:8px; padding:12px; border-left:4px solid #2196f3;">
                                <div class="next-session-label" style="font-size:12px; color:#1976d2; margin-bottom:4px;">
                                    📅 Próxima Sessão
                                </div>
                                <div style="color:#1976d2; font-weight:500;">
                                    <?= $nextSession ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
                            <button onclick="editNote('<?= $note['id'] ?>')" class="btn-sm"
                                style="background:#f0a36b; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px;">
                                ✏️ Editar
                            </button>

                            <button onclick="deleteNote('<?= $note['id'] ?>')" class="btn-sm"
                                style="background:#dc3545; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px;">
                                🗑️ Excluir
                            </button>
                        </div>


                    </div>
                </div>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>


<!-- Seção de Nova Anotação (para âncora) -->
<div id="new-note"></div>

<!-- Modal para Nova Anotação -->
<div id="noteModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; padding:20px; box-sizing:border-box; overflow-y:auto;">
    <div class="modal-content" style="background:#fff; border-radius:12px; max-width:800px; margin:0 auto; padding:0; position:relative; max-height:90vh; overflow-y:auto;">
        <div class="modal-header" style="padding:24px; border-bottom:1px solid #e9ecef; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; color:#17a2b8; display:flex; align-items:center; gap:8px;">
                📝 Nova Anotação Psicológica
            </h3>
            <button onclick="closeNoteModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6c757d;">×</button>
        </div>
        
        <form id="noteForm" style="padding:24px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="patient_cpf" value="<?php echo htmlspecialchars($patient['cpf']); ?>">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                        Tipo de Anotação *
                    </label>
                    <select name="note_type" required style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins;">
                        <option value="">Selecione o tipo</option>
                        <option value="consulta">💬 Consulta</option>
                        <option value="avaliacao">📋 Avaliação</option>
                        <option value="evolucao">📈 Evolução</option>
                        <option value="observacao">👁️ Observação</option>
                    </select>
                </div>
                
                <div>
                    <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                        Título (opcional)
                    </label>
                    <input type="text" name="title" style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins;" placeholder="Título da anotação">
                </div>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Conteúdo da Anotação *
                </label>
                <textarea name="content" required rows="6" style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins; resize:vertical;" placeholder="Descreva a sessão, observações, evolução do paciente..."></textarea>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                        Avaliação de Humor
                    </label>
                    <select name="mood_assessment" style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins;">
                        <option value="">Não avaliado</option>
                        <option value="1">😢 Muito Triste</option>
                        <option value="2">😔 Triste</option>
                        <option value="3">😐 Neutro</option>
                        <option value="4">😊 Alegre</option>
                        <option value="5">😄 Muito Alegre</option>
                    </select>
                </div>
                
                <div>
                    <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                        Próxima Sessão
                    </label>
                    <input type="datetime-local" name="next_session" style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins;">
                </div>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Observações Comportamentais
                </label>
                <textarea name="behavior_notes" rows="3" style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins; resize:vertical;" placeholder="Comportamentos observados, interações, reações..."></textarea>
            </div>
            
            <div style="margin-bottom:24px;">
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Recomendações
                </label>
                <textarea name="recommendations" rows="3" style="padding:12px; border:2px solid #17a2b8; border-radius:8px; width:100%; font-family:Poppins; resize:vertical;" placeholder="Recomendações para o paciente, família ou equipe..."></textarea>
            </div>
            
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeNoteModal()" class="btn secondary" style="background:#6c757d; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit" class="btn" style="background:#17a2b8; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
                    💾 Salvar Anotação
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Verificar se deve abrir o modal automaticamente
if (window.location.hash === '#new-note') {
    // Rolar suavemente até a seção
    document.getElementById('new-note').scrollIntoView({ behavior: 'smooth' });
    // Abrir o modal após um pequeno atraso para melhor experiência
    setTimeout(openNewNoteModal, 500);
}

function openNewNoteModal() {
    document.getElementById('noteModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeNoteModal() {
    document.getElementById('noteModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('noteForm').reset();
    
    // Resetar título e botão para modo de criação
    document.querySelector('.modal-header h3').innerHTML = '📝 Nova Anotação Psicológica';
    document.querySelector('button[type="submit"]').innerHTML = '💾 Salvar Anotação';
    
    // Remover campo hidden de ID se existir
    const hiddenId = document.querySelector('input[name="note_id"]');
    if (hiddenId) {
        hiddenId.remove();
    }
}

function editNote(noteId) {
    console.log('Editando anotação ID:', noteId);
    
    if (!noteId) {
        console.error('ID da anotação não foi fornecido!');
        alert('Erro: ID da anotação inválido');
        return;
    }
    
    // Buscar dados da anotação
    fetch(`edit_annotation.php?id=${noteId}`)
        .then(res => {
            console.log('Status da resposta:', res.status);
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            
            if (data.success) {
                const note = data.note;
                
                // Preencher o formulário com os dados existentes
                document.querySelector('select[name="note_type"]').value = note.note_type || '';
                document.querySelector('input[name="title"]').value = note.title || '';
                document.querySelector('textarea[name="content"]').value = note.content || '';
                document.querySelector('select[name="mood_assessment"]').value = note.mood_assessment || '';
                document.querySelector('input[name="next_session"]').value = note.next_session ? note.next_session.replace(' ', 'T') : '';
                document.querySelector('textarea[name="behavior_notes"]').value = note.behavior_notes || '';
                document.querySelector('textarea[name="recommendations"]').value = note.recommendations || '';
                
                // Adicionar campo hidden com o ID da anotação
                let hiddenId = document.querySelector('input[name="note_id"]');
                if (!hiddenId) {
                    hiddenId = document.createElement('input');
                    hiddenId.type = 'hidden';
                    hiddenId.name = 'note_id';
                    document.getElementById('noteForm').appendChild(hiddenId);
                }
                hiddenId.value = noteId;
                
                // Alterar título do modal
                document.querySelector('.modal-header h3').innerHTML = '✏️ Editar Anotação Psicológica';
                
                // Alterar texto do botão
                document.querySelector('button[type="submit"]').innerHTML = '💾 Atualizar Anotação';
                
                console.log('Modal sendo aberto...');
                // Abrir modal
                openNewNoteModal();
            } else {
                console.error('Erro do servidor:', data.error);
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro completo:', error);
            alert('Erro ao carregar anotação: ' + error.message);
        });
}

function deleteNote(noteId) {
    console.log('Deletando anotação ID:', noteId);
    
    if (!noteId) {
        console.error('ID da anotação não foi fornecido!');
        alert('Erro: ID da anotação inválido');
        return;
    }
    
    if (!confirm('Tem certeza que deseja excluir esta anotação?\n\nEsta ação não pode ser desfeita.')) {
        return;
    }
    
    // Obter CSRF token do formulário
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    console.log('CSRF Token obtido:', csrfToken ? 'Sim' : 'Não');
    
    fetch(`psychology.php?action=delete_note&id=${noteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            csrf_token: csrfToken
        })
    })
    .then(response => {
        console.log('Status da resposta:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Dados recebidos:', data);
        if (data.success) {
            alert('Anotação excluída com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.error || data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro completo:', error);
        alert('Erro ao excluir anotação: ' + error.message);
    });
}

// Submissão do formulário
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const noteId = formData.get('note_id');
    
    // Determinar se é edição ou nova anotação
    const action = noteId ? 'update_note' : 'save_note';
    const url = `psychology.php?action=${action}`;
    
    fetch(url, {
        method: 'POST',
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || (noteId ? 'Anotação atualizada com sucesso!' : 'Anotação salva com sucesso!'));
            location.reload();
        } else {
            alert('Erro: ' + (data.error || data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar anotação: ' + error.message);
    });
});

// Fechar modal ao clicar fora
document.getElementById('noteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNoteModal();
    }
});

// SOLUÇÃO RADICAL - JavaScript que monitora constantemente
function forceDarkModeColors() {
    try {
        const isDarkMode = document.body.classList.contains('dark-mode') || 
                          document.documentElement.getAttribute('data-theme') === 'dark';
        
        if (isDarkMode) {
            const noteCards = document.querySelectorAll('.psychology-note-card');
            console.log('Cards encontrados:', noteCards.length);
            
            noteCards.forEach((card, index) => {
                console.log('Processando card', index);
                
                // Forçar cores do card principal
                card.style.setProperty('background', '#2a3441', 'important');
                card.style.setProperty('background-color', '#2a3441', 'important');
                card.style.setProperty('color', '#e9ecef', 'important');
                
                // Forçar cores específicas para seções
                const moodSections = card.querySelectorAll('.mood-assessment-section');
                moodSections.forEach(section => {
                    section.style.setProperty('background', '#3c4651', 'important');
                    section.style.setProperty('background-color', '#3c4651', 'important');
                    section.style.setProperty('color', '#e9ecef', 'important');
                    
                    // Corrigir cores das bolinhas de humor
                    const moodCircles = section.querySelectorAll('div[style*="border-radius:50%"]');
                    const moodColors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
                    
                    moodCircles.forEach((circle, index) => {
                        const currentBg = circle.style.background || window.getComputedStyle(circle).backgroundColor;
                        
                        // Se a bolinha tem cor (não é #e9ecef), é uma bolinha preenchida
                        if (!currentBg.includes('#e9ecef') && !currentBg.includes('233, 236, 239')) {
                            // Encontrar qual cor deveria ter baseado no valor
                            let targetColor = '#6c757d'; // cor padrão
                            
                            // Verificar se tem alguma das cores originais
                            moodColors.forEach((color, colorIndex) => {
                                if (currentBg.includes(color.replace('#', '')) || 
                                    currentBg.includes(color)) {
                                    targetColor = color;
                                }
                            });
                            
                            // Aplicar a cor correta
                            circle.style.setProperty('background', targetColor, 'important');
                            circle.style.setProperty('background-color', targetColor, 'important');
                        } else {
                            // Bolinhas vazias ficam cinza escuro no modo escuro
                            circle.style.setProperty('background', '#495057', 'important');
                            circle.style.setProperty('background-color', '#495057', 'important');
                        }
                    });
                });
                
                const behaviorSections = card.querySelectorAll('.behavior-section');
                behaviorSections.forEach(section => {
                    section.style.setProperty('background', '#3c4651', 'important');
                    section.style.setProperty('background-color', '#3c4651', 'important');
                    section.style.setProperty('color', '#e9ecef', 'important');
                });
                
                const recommendationSections = card.querySelectorAll('.recommendations-section');
                recommendationSections.forEach(section => {
                    section.style.setProperty('background', '#3c4651', 'important');
                    section.style.setProperty('background-color', '#3c4651', 'important');
                    section.style.setProperty('color', '#e9ecef', 'important');
                });
                
                const nextSessionSections = card.querySelectorAll('.next-session-section');
                nextSessionSections.forEach(section => {
                    section.style.setProperty('background', '#1e3a5f', 'important');
                    section.style.setProperty('background-color', '#1e3a5f', 'important');
                    section.style.setProperty('color', '#64b5f6', 'important');
                });
                
                // Corrigir labels específicos
                const labels = card.querySelectorAll('.mood-label, .behavior-label, .recommendations-label');
                labels.forEach(label => {
                    label.style.setProperty('color', '#adb5bd', 'important');
                });
                
                const nextSessionLabels = card.querySelectorAll('.next-session-label');
                nextSessionLabels.forEach(label => {
                    label.style.setProperty('color', '#64b5f6', 'important');
                });
                
                // Monitorar mudanças no hover
                const observer = new MutationObserver(() => {
                    if (isDarkMode) {
                        card.style.setProperty('background', '#2a3441', 'important');
                        card.style.setProperty('background-color', '#2a3441', 'important');
                    }
                });
                
                observer.observe(card, { 
                    attributes: true, 
                    attributeFilter: ['style', 'class'] 
                });
            });
        }
    } catch (error) {
        console.error('Erro ao forçar cores:', error);
    }
}

// Executar a cada 100ms para garantir
setInterval(forceDarkModeColors, 100);

// Executar quando a página carrega
document.addEventListener('DOMContentLoaded', forceDarkModeColors);

// Executar novamente após um pequeno delay (para garantir)
setTimeout(forceDarkModeColors, 100);
</script>

<style>
/* Classe específica para cards de psicologia */
.psychology-note-card {
    background: #f8f9fa;
    transition: background 0.2s ease;
}

/* Modo escuro - FORÇAR com especificidade máxima */
html [data-theme="dark"] .psychology-note-card,
html [data-theme="dark"] .psychology-note-card:hover,
html [data-theme="dark"] .psychology-note-card:focus,
html [data-theme="dark"] .psychology-note-card:active,
html body.dark-mode .psychology-note-card,
html body.dark-mode .psychology-note-card:hover,
html body.dark-mode .psychology-note-card:focus,
html body.dark-mode .psychology-note-card:active {
    background: #2a3441 !important;
    background-color: #2a3441 !important;
    color: #e9ecef !important;
}

body.dark-mode .note-card {
    background: #2a3441 !important;
    color: #e9ecef !important;
}

body.dark-mode .note-card:hover {
    background: #343a46 !important;
    transition: background 0.2s ease;
}

/* Elementos específicos do card */
body.dark-mode .note-type-text {
    color: #64b5f6 !important;
}

body.dark-mode .note-title {
    color: #f8f9fa !important;
}

body.dark-mode .note-meta {
    color: #adb5bd !important;
}

body.dark-mode .note-content {
    color: #e9ecef !important;
}

/* Seções internas */
body.dark-mode .note-card div[style*="background:#fff"] {
    background: #3c4651 !important;
    color: #e9ecef !important;
}

body.dark-mode .note-card div[style*="color:#495057"] {
    color: #e9ecef !important;
}

body.dark-mode .note-card div[style*="color:#6c757d"] {
    color: #adb5bd !important;
}

body.dark-mode .note-card div[style*="color:#212529"] {
    color: #f8f9fa !important;
}

body.dark-mode .note-card div[style*="color:#1976d2"] {
    color: #64b5f6 !important;
}

/* Avaliação de humor - modo escuro */
body.dark-mode .note-card div[style*="background:#e3f2fd"] {
    background: #1e3a5f !important;
    color: #64b5f6 !important;
}

/* Seções específicas */
body.dark-mode .note-card > div > div[style*="background:#fff"] {
    background: #3c4651 !important;
}

body.dark-mode .note-card span[style*="color:#"] {
    color: #adb5bd !important;
}

/* Responsivo */
@media (max-width: 768px) {
    .patient-header {
        padding: 16px !important;
    }
    
    .patient-header > div {
        flex-direction: column !important;
        text-align: center !important;
        gap: 16px !important;
    }
    
    .modal-content {
        margin: 10px !important;
        max-width: none !important;
    }
    
    .modal form > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}

/* Efeitos hover */
.btn-sm:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

/* Modo claro - hover effect */
:not([data-theme="dark"]) .psychology-note-card:hover,
body:not(.dark-mode) .psychology-note-card:hover {
    background: #f1f3f4 !important;
    transition: background 0.2s ease;
}

/* Modo escuro - DESABILITAR COMPLETAMENTE hover */
[data-theme="dark"] .psychology-note-card,
[data-theme="dark"] .psychology-note-card:hover,
[data-theme="dark"] .psychology-note-card:focus,
[data-theme="dark"] .psychology-note-card:active,
body.dark-mode .psychology-note-card,
body.dark-mode .psychology-note-card:hover,
body.dark-mode .psychology-note-card:focus,
body.dark-mode .psychology-note-card:active {
    background: #2a3441 !important;
    transition: none !important;
    pointer-events: auto !important;
}

/* Garantir que TODOS os elementos mantenham as cores no modo escuro */
[data-theme="dark"] .psychology-note-card,
[data-theme="dark"] .psychology-note-card:hover,
body.dark-mode .psychology-note-card,
body.dark-mode .psychology-note-card:hover {
    background: #2a3441 !important;
    color: #e9ecef !important;
}

[data-theme="dark"] .psychology-note-card *,
[data-theme="dark"] .psychology-note-card:hover *,
body.dark-mode .psychology-note-card *,
body.dark-mode .psychology-note-card:hover * {
    color: inherit !important;
}

[data-theme="dark"] .psychology-note-card .note-type-text,
[data-theme="dark"] .psychology-note-card:hover .note-type-text,
body.dark-mode .psychology-note-card .note-type-text,
body.dark-mode .psychology-note-card:hover .note-type-text {
    color: #64b5f6 !important;
}

[data-theme="dark"] .psychology-note-card .note-title,
[data-theme="dark"] .psychology-note-card:hover .note-title,
body.dark-mode .psychology-note-card .note-title,
body.dark-mode .psychology-note-card:hover .note-title {
    color: #f8f9fa !important;
}

[data-theme="dark"] .psychology-note-card .note-meta,
[data-theme="dark"] .psychology-note-card:hover .note-meta,
body.dark-mode .psychology-note-card .note-meta,
body.dark-mode .psychology-note-card:hover .note-meta {
    color: #adb5bd !important;
}

[data-theme="dark"] .psychology-note-card .note-content,
[data-theme="dark"] .psychology-note-card:hover .note-content,
body.dark-mode .psychology-note-card .note-content,
body.dark-mode .psychology-note-card:hover .note-content {
    color: #e9ecef !important;
}

/* Forçar cores específicas para elementos com estilos inline */
[data-theme="dark"] .psychology-note-card div[style],
[data-theme="dark"] .psychology-note-card:hover div[style],
body.dark-mode .psychology-note-card div[style],
body.dark-mode .psychology-note-card:hover div[style] {
    background: #2a3441 !important;
    color: #e9ecef !important;
}

[data-theme="dark"] .psychology-note-card div[style*="background:#fff"],
[data-theme="dark"] .psychology-note-card:hover div[style*="background:#fff"],
body.dark-mode .psychology-note-card div[style*="background:#fff"],
body.dark-mode .psychology-note-card:hover div[style*="background:#fff"] {
    background: #3c4651 !important;
    color: #e9ecef !important;
}

[data-theme="dark"] .psychology-note-card div[style*="background:#e3f2fd"],
[data-theme="dark"] .psychology-note-card:hover div[style*="background:#e3f2fd"],
body.dark-mode .psychology-note-card div[style*="background:#e3f2fd"],
body.dark-mode .psychology-note-card:hover div[style*="background:#e3f2fd"] {
    background: #1e3a5f !important;
    color: #64b5f6 !important;
}

/* Sobrescrever TODOS os estilos inline de cor */
[data-theme="dark"] .psychology-note-card [style*="color:"],
[data-theme="dark"] .psychology-note-card:hover [style*="color:"],
body.dark-mode .psychology-note-card [style*="color:"],
body.dark-mode .psychology-note-card:hover [style*="color:"] {
    color: #e9ecef !important;
}

[data-theme="dark"] .psychology-note-card [style*="background:"],
[data-theme="dark"] .psychology-note-card:hover [style*="background:"],
body.dark-mode .psychology-note-card [style*="background:"],
body.dark-mode .psychology-note-card:hover [style*="background:"] {
    background: inherit !important;
}

/* Seções específicas com classes */
[data-theme="dark"] .mood-assessment-section,
[data-theme="dark"] .behavior-section,
[data-theme="dark"] .recommendations-section,
body.dark-mode .mood-assessment-section,
body.dark-mode .behavior-section,
body.dark-mode .recommendations-section {
    background: #3c4651 !important;
}

[data-theme="dark"] .next-session-section,
body.dark-mode .next-session-section {
    background: #1e3a5f !important;
}

[data-theme="dark"] .mood-label,
[data-theme="dark"] .behavior-label,
[data-theme="dark"] .recommendations-label,
body.dark-mode .mood-label,
body.dark-mode .behavior-label,
body.dark-mode .recommendations-label {
    color: #adb5bd !important;
}

[data-theme="dark"] .next-session-label,
body.dark-mode .next-session-label {
    color: #64b5f6 !important;
}

/* SOLUÇÃO RADICAL - SOBRESCREVER TUDO NO MODO ESCURO */
html[data-theme="dark"] .psychology-note-card,
html[data-theme="dark"] .psychology-note-card *,
html[data-theme="dark"] .psychology-note-card:hover,
html[data-theme="dark"] .psychology-note-card:hover *,
html body.dark-mode .psychology-note-card,
html body.dark-mode .psychology-note-card *,
html body.dark-mode .psychology-note-card:hover,
html body.dark-mode .psychology-note-card:hover * {
    background: inherit !important;
    color: inherit !important;
}

html[data-theme="dark"] .psychology-note-card,
html[data-theme="dark"] .psychology-note-card:hover,
html body.dark-mode .psychology-note-card,
html body.dark-mode .psychology-note-card:hover {
    background: #2a3441 !important;
    color: #e9ecef !important;
}

/* Forçar cores específicas com !important absoluto */
html[data-theme="dark"] .psychology-note-card div[style*="background"],
html[data-theme="dark"] .psychology-note-card:hover div[style*="background"],
html body.dark-mode .psychology-note-card div[style*="background"],
html body.dark-mode .psychology-note-card:hover div[style*="background"] {
    background: #3c4651 !important;
}

html[data-theme="dark"] .psychology-note-card div[style*="background:#e3f2fd"],
html[data-theme="dark"] .psychology-note-card:hover div[style*="background:#e3f2fd"],
html body.dark-mode .psychology-note-card div[style*="background:#e3f2fd"],
html body.dark-mode .psychology-note-card:hover div[style*="background:#e3f2fd"] {
    background: #1e3a5f !important;
}

/* ÚLTIMA TENTATIVA - CSS com especificidade MÁXIMA */
html body.dark-mode div.psychology-note-card.note-card,
html body.dark-mode div.psychology-note-card.note-card:hover,
html body.dark-mode div.psychology-note-card.note-card:focus {
    background: #2a3441 !important;
    background-color: #2a3441 !important;
    color: #e9ecef !important;
    transition: none !important;
}

/* Sobrescrever QUALQUER estilo inline */
html body.dark-mode .psychology-note-card[style] {
    background: #2a3441 !important;
    background-color: #2a3441 !important;
}

html body.dark-mode .psychology-note-card[style]:hover {
    background: #2a3441 !important;
    background-color: #2a3441 !important;
}
</style>
