<?php
// Função de segurança para datas
function safeDate($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '-';
    $ts = strtotime(str_replace('/', '-', $date));
    return ($ts && $ts > 0) ? date('d/m/Y', $ts) : '-';
}

$patient = $patient ?? [];
$notes = $notes ?? [];

$nome = (string)($patient['nome_completo'] ?? $patient['nome'] ?? 'Não informado');
$cpf = (string)($patient['cpf'] ?? 'Não informado');
$cpfLimpo = preg_replace('/\D/', '', $cpf);
$cpfFormatado = (strlen($cpfLimpo) === 11) ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfLimpo) : $cpf;

$idade = isset($patient['idade']) ? (int)$patient['idade'] : 0;
$responsavel = (string)($patient['responsavel'] ?? 'Não informado');
$contato = (string)($patient['contato'] ?? 'Não informado');
$dataAcolh = safeDate($patient['data_acolhimento'] ?? null);
$queixa = (string)($patient['queixa_principal'] ?? '');

$avatar = strtoupper(mb_substr($nome, 0, 1, 'UTF-8'));
?>

<div class="actions flex-between flex-wrap gap-2 mb-4">
    <a href="psychology.php?action=patients" class="btn secondary">
        <i class="fas fa-arrow-left"></i> Voltar aos Pacientes
    </a>

    <button onclick="openNewNoteModal()" class="btn success">
        <i class="fas fa-plus"></i> Nova Anotação
    </button>
</div>

<!-- Cabeçalho do Paciente -->
<div class="patient-header card-glass p-4 mb-4" style="border-left: 4px solid var(--primary-blue);">
    <div style="display: flex; align-items: flex-start; gap: 24px;">
        
        <?php if (!empty($patient['foto'])): ?>
            <img src="acolhimento_view.php?action=photo&id=<?= $patient['id'] ?>" alt="<?= e($nome) ?>" style="width:80px; height:80px; border-radius:50%; object-fit:cover; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <?php else: ?>
            <div class="patient-avatar" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #20c997); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 32px; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <?= e($avatar) ?>
            </div>
        <?php endif; ?>

        <div style="flex: 1;">
            <h1 style="margin: 0 0 16px 0; font-size: 28px; font-weight: 700; color: var(--text-primary);">
                <?= e($nome) ?>
            </h1>

            <div style="display: flex; flex-wrap: wrap; gap: 20px; font-weight: 600; font-size: 14px; color: var(--text-muted);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-id-card" style="opacity: 0.7;"></i> <?= e($cpfFormatado) ?>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-birthday-cake" style="opacity: 0.7;"></i> <?= $idade ?> anos
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-user-friends" style="opacity: 0.7;"></i> 
                    <span style="<?= $responsavel === 'Não informado' ? 'font-style:italic; opacity:0.6;' : '' ?>"><?= e($responsavel) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-phone" style="opacity: 0.7;"></i> <?= e($contato) ?>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-calendar-alt" style="opacity: 0.7;"></i> Acolhimento: <?= e($dataAcolh) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($queixa)): ?>
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-top: 20px;">
            <div style="font-weight: 600; margin-bottom: 8px; color: var(--text-primary);"><i class="fas fa-bullseye text-muted" style="margin-right: 6px;"></i> Queixa Principal</div>
            <div style="line-height: 1.5; color: var(--text-muted);">
                <?= nl2br(e($queixa)) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Anotações -->
<div class="psychology-notes card-glass p-4 mb-4">
    <h3 style="margin:0 0 24px 0; color:var(--text-primary); display:flex; align-items:center; gap:8px; justify-content:space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
        <span><i class="fas fa-brain text-muted"></i> Histórico de Anotações</span>
        <span style="font-size:14px; font-weight:600; color:var(--text-muted); background: var(--bg-secondary); padding: 4px 12px; border-radius: 20px;">
            <?= count($notes) ?> registros
        </span>
    </h3>

    <?php if (empty($notes)): ?>
        <div class="empty-notes text-center p-5 text-muted">
            <div style="font-size:48px; margin-bottom:16px; opacity:0.5;"><i class="fas fa-folder-open"></i></div>
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Nenhuma anotação registrada</div>
            <div style="margin-bottom:20px; font-size:14px;">Inicie o acompanhamento criando a primeira anotação deste paciente.</div>

            <button onclick="openNewNoteModal()" class="btn success">
                <i class="fas fa-plus"></i> Iniciar Acompanhamento
            </button>
        </div>
    <?php else: ?>

        <div class="notes-timeline" style="display: flex; flex-direction: column; gap: 24px;">
            <?php foreach ($notes as $index => $note): 

                $allowedTypes = ['consulta', 'avaliacao', 'evolucao', 'observacao'];
                $type = in_array(($note['note_type'] ?? ''), $allowedTypes, true) ? $note['note_type'] : 'observacao';
                $title = (string)($note['title'] ?? '');
                $content = nl2br(e($note['content'] ?? ''));
                $psychologist = (string)($note['psychologist_name'] ?? 'Psicólogo');

                $created = !empty($note['created_at']) ? date('d/m/Y H:i', strtotime($note['created_at'])) : '-';

                $behavior = nl2br(e($note['behavior_notes'] ?? ''));
                $recommend = nl2br(e($note['recommendations'] ?? ''));
                $nextSession = !empty($note['next_session']) ? date('d/m/Y H:i', strtotime($note['next_session'])) : null;

                $mood = max(0, min(5, (int)($note['mood_assessment'] ?? 0)));
                $noteId = (int)($note['id'] ?? 0);

                $icons = [
                    'consulta' => 'fa-comments',
                    'avaliacao' => 'fa-clipboard-list',
                    'evolucao' => 'fa-chart-line',
                    'observacao' => 'fa-eye'
                ];

                $icon = $icons[$type] ?? 'fa-file-alt';
            ?>
                <div class="note-card" style="border-radius:12px; padding:24px; border:1px solid var(--border-color); background:var(--card-bg); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">

                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                <span style="display:flex; align-items:center; justify-content:center; width: 32px; height: 32px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color:#0ea5e9; font-size:14px;"><i class="fas <?= $icon ?>"></i></span>
                                <span style="font-weight:600; color:#0ea5e9; text-transform:uppercase; font-size: 12px; letter-spacing: 0.5px;">
                                    <?= e($type) ?>
                                </span>
                            </div>

                            <?php if ($title): ?>
                                <div style="font-size: 18px; font-weight:600; color:var(--text-primary); margin-bottom:4px;">
                                    <?= e($title) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="text-align:right; font-size:12px; color:var(--text-muted);">
                            <div style="font-weight: 600; margin-bottom: 2px;"><i class="fas fa-clock" style="margin-right: 4px;"></i> <?= $created ?></div>
                            <div><i class="fas fa-user-md" style="margin-right: 4px;"></i> <?= e($psychologist) ?></div>
                        </div>
                    </div>

                    <div style="color:var(--text-primary); line-height:1.6; margin-bottom:20px; font-size: 15px;">
                        <?= $content ?>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                        <?php if ($mood > 0): ?>
                            <div style="background:var(--bg-secondary); border: 1px solid var(--border-color); border-radius:8px; padding:16px;">
                                <div style="font-size:12px; font-weight: 600; text-transform: uppercase; color:var(--text-muted); margin-bottom:12px; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-smile"></i> Avaliação de Humor
                                </div>

                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="display:flex; gap:4px;">
                                        <?php
                                            $colors = [1=>'#dc3545',2=>'#fd7e14',3=>'#ffc107',4=>'#20c997',5=>'#28a745'];
                                            for ($i=1; $i<=5; $i++):
                                        ?>
                                            <div style="width:12px; height:12px; border-radius:50%; background:<?= $i <= $mood ? $colors[$mood] : 'var(--border-color)' ?>;"></div>
                                        <?php endfor; ?>
                                    </div>

                                    <span style="font-weight: 600; color:<?= $colors[$mood] ?? 'var(--text-muted)' ?>">
                                        <?= ['','Muito Triste','Triste','Neutro','Alegre','Muito Alegre'][$mood] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($behavior)): ?>
                            <div style="background:var(--bg-secondary); border: 1px solid var(--border-color); border-radius:8px; padding:16px;">
                                <div style="font-size:12px; font-weight: 600; text-transform: uppercase; color:var(--text-muted); margin-bottom:8px; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-eye"></i> Observações Comportamentais
                                </div>
                                <div style="font-size: 14px; color: var(--text-primary);"><?= $behavior ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recommend)): ?>
                            <div style="background:var(--bg-secondary); border: 1px solid var(--border-color); border-radius:8px; padding:16px;">
                                <div style="font-size:12px; font-weight: 600; text-transform: uppercase; color:var(--text-muted); margin-bottom:8px; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-lightbulb"></i> Recomendações
                                </div>
                                <div style="font-size: 14px; color: var(--text-primary);"><?= $recommend ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($nextSession): ?>
                            <div style="background:rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.2); border-left: 4px solid #0ea5e9; border-radius:8px; padding:16px;">
                                <div style="font-size:12px; font-weight: 600; text-transform: uppercase; color:#0ea5e9; margin-bottom:8px; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-calendar-check"></i> Próxima Sessão
                                </div>
                                <div style="color:#0ea5e9; font-weight:600; font-size: 15px;">
                                    <?= $nextSession ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        <button onclick="editNote(<?= $noteId ?>)" class="btn secondary" style="padding: 8px 16px; font-size: 13px;">
                            <i class="fas fa-edit"></i> Editar
                        </button>

                        <button onclick="confirmDeleteNote(<?= $noteId ?>)" class="btn danger" style="padding: 8px 16px; font-size: 13px;">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>


<!-- Seção de Nova Anotação (para âncora) -->
<div id="new-note"></div>

<!-- Modal para Nova Anotação -->
<div id="noteModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; padding:20px; box-sizing:border-box; overflow-y:auto; backdrop-filter: blur(4px);">
    <div class="modal-content" style="background:var(--card-bg); border-radius:12px; max-width:800px; margin:20px auto; padding:0; position:relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div class="modal-header" style="padding:24px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; color:var(--text-primary); font-size: 20px; font-weight: 600; display:flex; align-items:center; gap:12px;">
                <i class="fas fa-file-alt" style="color: #0ea5e9;"></i> <span id="noteModalTitle">Nova Anotação Psicológica</span>
            </h3>
            <button onclick="closeNoteModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="noteForm" style="padding:24px;">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
            <input type="hidden" name="patient_cpf" value="<?php echo e($patient['cpf'] ?? ''); ?>">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                        Tipo de Anotação *
                    </label>
                    <select name="note_type" class="form-control" required style="width:100%;">
                        <option value="">Selecione o tipo</option>
                        <option value="consulta">Consulta</option>
                        <option value="avaliacao">Avaliação</option>
                        <option value="evolucao">Evolução</option>
                        <option value="observacao">Observação</option>
                    </select>
                </div>
                
                <div>
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                        Título (opcional)
                    </label>
                    <input type="text" name="title" class="form-control" style="width:100%;" placeholder="Ex: Avaliação Inicial">
                </div>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                    Conteúdo da Anotação *
                </label>
                <textarea name="content" class="form-control" required rows="6" style="width:100%; resize:vertical;" placeholder="Descreva a sessão, observações, evolução do paciente..."></textarea>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                        Avaliação de Humor
                    </label>
                    <select name="mood_assessment" class="form-control" style="width:100%;">
                        <option value="">Não avaliado</option>
                        <option value="1">Muito Triste</option>
                        <option value="2">Triste</option>
                        <option value="3">Neutro</option>
                        <option value="4">Alegre</option>
                        <option value="5">Muito Alegre</option>
                    </select>
                </div>
                
                <div>
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                        Próxima Sessão
                    </label>
                    <input type="datetime-local" name="next_session" class="form-control" style="width:100%;">
                </div>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                    Observações Comportamentais
                </label>
                <textarea name="behavior_notes" class="form-control" rows="3" style="width:100%; resize:vertical;" placeholder="Comportamentos observados, interações, reações..."></textarea>
            </div>
            
            <div style="margin-bottom:24px;">
                <label style="font-size:13px; color:var(--text-muted); font-weight:600; text-transform: uppercase; display:block; margin-bottom:8px;">
                    Recomendações
                </label>
                <textarea name="recommendations" class="form-control" rows="3" style="width:100%; resize:vertical;" placeholder="Recomendações para o paciente, família ou equipe..."></textarea>
            </div>
            
            <div style="display:flex; gap:12px; justify-content:flex-end; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <button type="button" onclick="closeNoteModal()" class="btn secondary">
                    Cancelar
                </button>
                <button type="submit" class="btn success" id="noteSubmitBtn">
                    <i class="fas fa-save"></i> Salvar Anotação
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmação Padrão -->
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; border-radius: 16px; padding: 32px 24px;">
        <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz" style="width: 80px; margin-bottom: 20px;">
        <h3 id="confirmTitle" style="color: var(--text-primary); margin-bottom: 12px; font-weight: 700;">Atenção</h3>
        <p id="confirmMessage" style="color: var(--text-muted); margin-bottom: 24px; line-height: 1.5;"></p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button onclick="closeConfirmModal()" class="btn secondary" style="flex: 1;">Cancelar</button>
            <button id="confirmBtn" class="btn danger" style="flex: 1;">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal de Sucesso Padrão -->
<div id="successModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; border-radius: 16px; padding: 32px 24px;">
        <div style="width: 64px; height: 64px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px;">
            <i class="fas fa-check"></i>
        </div>
        <h3 id="successTitle" style="color: var(--text-primary); margin-bottom: 12px; font-weight: 700;">Sucesso</h3>
        <p id="successMessage" style="color: var(--text-muted); margin-bottom: 24px; line-height: 1.5;"></p>
        <button onclick="closeSuccessModal()" class="btn success" style="width: 100%;">Concluir</button>
    </div>
</div>

<script>
// Verificar se deve abrir o modal automaticamente
if (window.location.hash === '#new-note') {
    document.getElementById('new-note').scrollIntoView({ behavior: 'smooth' });
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
    
    document.getElementById('noteModalTitle').textContent = 'Nova Anotação Psicológica';
    document.getElementById('noteSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Salvar Anotação';
    
    const hiddenId = document.querySelector('input[name="note_id"]');
    if (hiddenId) {
        hiddenId.remove();
    }
}

// Lógica de Confirmação
let deleteTargetId = null;

function showConfirmModal(title, message, callback) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmModal').style.display = 'flex';
    document.getElementById('confirmBtn').onclick = function() {
        closeConfirmModal();
        callback();
    };
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function confirmDeleteNote(noteId) {
    showConfirmModal(
        'Excluir Anotação',
        'Tem certeza que deseja excluir esta anotação? Esta ação não pode ser desfeita.',
        function() {
            executeDelete(noteId);
        }
    );
}

function executeDelete(noteId) {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    
    fetch(`psychology.php?action=delete_note&id=${noteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Excluído', 'Anotação excluída com sucesso!', () => location.reload());
        } else {
            showConfirmModal('Erro', data.error || data.message || 'Erro desconhecido', () => {});
        }
    })
    .catch(error => {
        console.error(error);
        showConfirmModal('Erro', 'Erro ao excluir anotação.', () => {});
    });
}

// Lógica de Sucesso
let successCallback = null;

function showSuccessModal(title, message, callback) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').style.display = 'flex';
    successCallback = callback;
}

function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
    if (successCallback) {
        successCallback();
    }
}

function editNote(noteId) {
    fetch(`psychology.php?action=get_note&id=${noteId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const note = data.note;
                
                document.querySelector('select[name="note_type"]').value = note.note_type || '';
                document.querySelector('input[name="title"]').value = note.title || '';
                document.querySelector('textarea[name="content"]').value = note.content || '';
                document.querySelector('select[name="mood_assessment"]').value = note.mood_assessment || '';
                document.querySelector('input[name="next_session"]').value = note.next_session ? note.next_session.replace(' ', 'T') : '';
                document.querySelector('textarea[name="behavior_notes"]').value = note.behavior_notes || '';
                document.querySelector('textarea[name="recommendations"]').value = note.recommendations || '';
                
                let hiddenId = document.querySelector('input[name="note_id"]');
                if (!hiddenId) {
                    hiddenId = document.createElement('input');
                    hiddenId.type = 'hidden';
                    hiddenId.name = 'note_id';
                    document.getElementById('noteForm').appendChild(hiddenId);
                }
                hiddenId.value = noteId;
                
                document.getElementById('noteModalTitle').textContent = 'Editar Anotação';
                document.getElementById('noteSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Atualizar Anotação';
                
                openNewNoteModal();
            } else {
                showConfirmModal('Erro', data.error || 'Erro desconhecido', () => {});
            }
        })
        .catch(error => {
            console.error(error);
            showConfirmModal('Erro', 'Erro ao carregar anotação.', () => {});
        });
}

document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const noteId = formData.get('note_id');
    const action = noteId ? 'update_note' : 'save_note';
    
    fetch(`psychology.php?action=${action}`, {
        method: 'POST',
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeNoteModal();
            showSuccessModal('Sessão Registrada', noteId ? 'Sessão editada com sucesso!' : 'Sessão concluída e registrada com sucesso!', () => location.reload());
        } else {
            showConfirmModal('Erro', data.error || data.message || 'Erro desconhecido', () => {});
        }
    })
    .catch(error => {
        console.error(error);
        showConfirmModal('Erro', 'Erro ao salvar anotação.', () => {});
    });
});
</script>
