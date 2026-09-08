<?php
    $mesesPt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
    $currentMonthLabel = ($mesesPt[(int)date('n')] ?? date('F')) . ', ' . date('Y');
?>
<section class="grid dashboard-grid">
    <div class="card-glass calendar">
        <div class="calendar-header-wrapper">
            <div style="font-weight:700; font-size:18px;" id="currentMonth"><?php echo e($currentMonthLabel); ?></div>
            <div class="calendar-btn-group">
                <button onclick="changeMonth(-1)" class="calendar-btn">‹</button>
                <button onclick="changeMonth(1)" class="calendar-btn">›</button>
            </div>
        </div>
        <div class="calendar-grid" id="calendarGrid">
            <div class="calendar-header-day">D</div>
            <div class="calendar-header-day">S</div>
            <div class="calendar-header-day">T</div>
            <div class="calendar-header-day">Q</div>
            <div class="calendar-header-day">Q</div>
            <div class="calendar-header-day">S</div>
            <div class="calendar-header-day">S</div>
        </div>
    </div>
    
    <div class="card-glass list alerts-card">
        <?php
            $totalAlertas = count($alertas ?? []);
            $hasCritical = false;
            $hasWarning = false;
            foreach (($alertas ?? []) as $a) {
                $t = $a['tipo'] ?? '';
                if ($t === 'error') {
                    $hasCritical = true;
                } elseif ($t === 'warning') {
                    $hasWarning = true;
                }
            }
            $badgeClass = $hasCritical ? 'badge-danger' : ($hasWarning ? 'badge-warning' : 'badge-success');
        ?>
        <div class="alerts-card-header">
            <div class="alerts-header-flex">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="font-weight:700; font-size:16px;">Alertas Prioritários</div>
                        <span class="alerts-badge <?php echo $badgeClass; ?>" id="alertsCountBadge">
                            <?php echo $totalAlertas; ?>
                        </span>
                    </div>
                    <div class="alerts-subtitle">Ações recomendadas e rotinas pendentes</div>
                </div>
                <button type="button" id="btnRestoreAlerts" class="btn-restore-alerts" onclick="restoreAllAlerts()" style="display:none;" title="Voltar todos os alertas que você ocultou">
                    <i class="fas fa-undo-alt"></i> <span>Restaurar (<span id="hiddenAlertsCount">0</span>)</span>
                </button>
            </div>
        </div>

        <div class="alerts-list-container">
            <?php if (!empty($alertas)): ?>
                <div id="alertsPillsList" class="alerts-pills-stack">
                    <?php foreach ($alertas as $alerta): ?>
                        <?php
                            $alertId = $alerta['id'] ?? '';
                            $alertType = in_array(($alerta['tipo'] ?? ''), ['success', 'warning', 'error', 'info'], true)
                                ? $alerta['tipo']
                                : 'info';
                            
                            $isBirthdayModal = (($alerta['action'] ?? '') === 'open_birthday_modal');
                            
                            $rawLink = $alerta['link'] ?? '';
                            $allowedPrefixes = ['desligamento.php', 'faltas.php', 'socioeconomico.php', 'acolhimento.php', 'psychology.php', 'prontuarios.php'];
                            $alertLink = '';
                            foreach ($allowedPrefixes as $prefix) {
                                if (strpos($rawLink, $prefix) === 0) {
                                    $alertLink = $rawLink;
                                    break;
                                }
                            }
                            
                            $iconClass = !empty($alerta['icone']) ? $alerta['icone'] : 'fa-info-circle';
                        ?>
                        <div class="alert-item-wrapper" data-alert-id="<?php echo e($alertId); ?>">
                            <?php if ($isBirthdayModal): ?>
                                <div class="pill-link pill-birthday-trigger" onclick="openBirthdayModal(event)" role="button" tabindex="0" title="Clique para ver os aniversariantes do mês">
                                    <div class="pill <?php echo e($alertType); ?>">
                                        <div class="pill-content">
                                            <i class="fas <?php echo e($iconClass); ?>"></i>
                                            <span><?php echo e($alerta['mensagem'] ?? ''); ?></span>
                                        </div>
                                        <div class="pill-actions-group">
                                            <span class="pill-open-btn">
                                                <i class="fas fa-users"></i> <span>Ver lista</span> <i class="fas fa-chevron-right"></i>
                                            </span>
                                            <button type="button" class="pill-dismiss-btn" onclick="dismissAlert(event, '<?php echo e($alertId); ?>')" title="Ocultar este alerta para você" aria-label="Ocultar alerta">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($alertLink): ?>
                                <a href="<?php echo e($alertLink); ?>" class="pill-link" title="Clique para gerenciar esta pendência">
                                    <div class="pill <?php echo e($alertType); ?>">
                                        <div class="pill-content">
                                            <i class="fas <?php echo e($iconClass); ?>"></i>
                                            <span><?php echo e($alerta['mensagem'] ?? ''); ?></span>
                                        </div>
                                        <div class="pill-actions-group">
                                            <i class="fas fa-chevron-right pill-arrow"></i>
                                            <button type="button" class="pill-dismiss-btn" onclick="dismissAlert(event, '<?php echo e($alertId); ?>')" title="Ocultar este alerta para você" aria-label="Ocultar alerta">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="pill <?php echo e($alertType); ?>">
                                    <div class="pill-content">
                                        <i class="fas <?php echo e($iconClass); ?>"></i>
                                        <span><?php echo e($alerta['mensagem'] ?? ''); ?></span>
                                    </div>
                                    <div class="pill-actions-group">
                                        <button type="button" class="pill-dismiss-btn" onclick="dismissAlert(event, '<?php echo e($alertId); ?>')" title="Ocultar este alerta para você" aria-label="Ocultar alerta">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="alertsAllDismissed" class="alerts-dismissed-box" style="display:none;">
                    <div class="alerts-dismissed-text">Alertas ocultados para o seu usuário.</div>
                    <button type="button" class="btn-restore-inline" onclick="restoreAllAlerts()" title="Restaurar alertas na sua visualização">
                        <i class="fas fa-undo-alt"></i> Restaurar
                    </button>
                </div>
            <?php else: ?>
                <div class="alerts-empty-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="empty-title">Tudo em dia!</div>
                    <div class="empty-desc">Nenhum alerta prioritário pendente. Todas as rotinas e fichas estão regulares.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="stats dashboard-stats">
        <div class="stat-card-glass">
            <div class="stat-number"><?php echo $statsAcolhimento['ativas'] ?? 0; ?></div>
            <div class="stat-label">Acolhimentos Ativos</div>
        </div>
        <div class="stat-card-glass">
            <div class="stat-number"><?php echo $statsSocioeconomico['ativas'] ?? 0; ?></div>
            <div class="stat-label">Socioeconômicos Ativos</div>
        </div>
        <div class="stat-card-glass">
            <div class="stat-number"><?php echo ($statsAcolhimento['total'] ?? 0) + ($statsSocioeconomico['total'] ?? 0); ?></div>
            <div class="stat-label">Total de Fichas</div>
        </div>
    </div>
</section>

<section class="grid dashboard-notes-grid">
    <div class="card-glass list">
        <div style="font-weight:700">Anotações do Calendário</div>
        <div id="notesList">
            <?php if (!empty($anotacoes['anotacoes'])): ?>
                <?php foreach ($anotacoes['anotacoes'] as $anotacao): ?>
                    <div class="note-card-glass">
                        <div class="note-badge orange-badge">
                            <?php echo date('d', strtotime($anotacao['date'])); ?>
                        </div>
                        <div class="note-content">
                            <div class="note-date"><?php echo e($anotacao['formatted_date'] ?? ''); ?></div>
                            <div class="note-text"><?php echo e($anotacao['note'] ?? ''); ?></div>
                        </div>
                        <button onclick="deleteNote(<?php echo (int)($anotacao['id'] ?? 0); ?>)" class="delete-note-btn" title="Excluir anotação"><i class="fas fa-trash-alt"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:var(--text-muted); font-style:italic;">Nenhuma anotação este mês</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-glass list">
        <div style="font-weight:700">Avisos</div>
        <div id="avisosList">
            <?php if (!empty($anotacoes['avisos'])): ?>
                <?php foreach ($anotacoes['avisos'] as $aviso): ?>
                    <div class="note-card-glass aviso">
                        <div class="note-badge green-badge">
                            <?php echo date('d', strtotime($aviso['date'])); ?>
                        </div>
                        <div class="note-content">
                            <div class="note-date"><?php echo e($aviso['formatted_date'] ?? ''); ?></div>
                            <div class="note-text"><?php echo e($aviso['note'] ?? ''); ?></div>
                        </div>
                        <button onclick="deleteNote(<?php echo (int)($aviso['id'] ?? 0); ?>)" class="delete-note-btn" title="Excluir aviso"><i class="fas fa-trash-alt"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:var(--text-muted); font-style:italic;">Nenhum aviso este mês</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal para escolher tipo de anotação -->
<div id="typeModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Escolha o tipo</h3>
            <button class="modal-close" onclick="closeTypeModal()" aria-label="Fechar">&times;</button>
        </div>
        <div class="d-flex flex-column gap-3">
            <button onclick="openNoteModal('anotacao')" class="modal-choice-btn orange">
                <i class="fas fa-edit"></i> Anotação (Laranja)
            </button>
            <button onclick="openNoteModal('aviso')" class="modal-choice-btn green">
                <i class="fas fa-exclamation-triangle"></i> Aviso (Verde)
            </button>
        </div>
    </div>
</div>

<!-- Modal para adicionar/editar anotações -->
<div id="noteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Adicionar Anotação</h3>
            <button class="modal-close" onclick="closeModal()" aria-label="Fechar">&times;</button>
        </div>
        <div class="form-group">
            <label for="noteText" class="form-label-bold"><span id="noteTypeLabel">Anotação</span> para <span id="selectedDate"></span>:</label>
            <textarea id="noteText" class="form-control" placeholder="Digite sua anotação ou aviso aqui..."></textarea>
        </div>
        <div class="modal-buttons">
            <button type="button" class="btn secondary" onclick="closeModal()">Cancelar</button>
            <button type="button" class="btn success" onclick="saveNote()">Salvar</button>
        </div>
    </div>
</div>

<!-- Modal Moderno de Confirmação de Exclusão -->
<div id="deleteConfirmModal" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-confirm-dialog">
        <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz">
        <h3 id="deleteModalTitle" class="modal-confirm-title">Confirmar Exclusão</h3>
        <p class="modal-confirm-desc">Tem certeza de que deseja excluir este item do calendário?</p>
        <p class="modal-confirm-subtext">Esta ação não poderá ser desfeita.</p>
        <div class="modal-confirm-btn-group">
            <button type="button" class="btn-confirm-cancel" onclick="closeDeleteModal()">Cancelar</button>
            <button type="button" class="btn-confirm-delete" onclick="confirmDeleteNote()">
                <i class="fas fa-trash-alt"></i> Sim, Excluir
            </button>
        </div>
    </div>
</div>

<!-- Modal Padronizado de Aniversariantes do Mês -->
<div id="birthdayModal" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="birthdayModalTitle">
    <div class="modal-birthday-dialog">
        <button type="button" class="modal-corner-close" onclick="closeBirthdayModal()" aria-label="Fechar modal">&times;</button>
        
        <div class="modal-birthday-header">
            <div class="d-flex align-items-center gap-3">
                <img src="img/logo.png" class="modal-birthday-logo" alt="Criança Feliz">
                <div>
                    <h3 id="birthdayModalTitle" class="modal-birthday-title">
                        <i class="fas fa-calendar-alt"></i> Aniversariantes de <?php echo e($mesesPt[(int)date('n')] ?? date('F')); ?>
                    </h3>
                    <p class="modal-birthday-desc">
                        <?php $totalAniv = count($aniversariantesDetalhes ?? []); ?>
                        <?php echo $totalAniv > 0 ? "<strong>{$totalAniv}</strong> atendido(s) comemoram aniversário neste mês." : "Nenhum atendido comemora aniversário neste mês."; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="birthday-list-container">
            <?php if (!empty($aniversariantesDetalhes)): ?>
                <?php foreach ($aniversariantesDetalhes as $person): ?>
                    <div class="birthday-person-card <?php echo !empty($person['is_hoje']) ? 'is-today' : ''; ?>">
                        <div class="birthday-person-left">
                            <div class="birthday-date-badge <?php echo !empty($person['is_hoje']) ? 'today-badge' : ''; ?>">
                                <span class="badge-day"><?php echo e($person['dia_formatado']); ?></span>
                                <span class="badge-month"><?php echo strtoupper(substr($mesesPt[(int)date('n')] ?? '', 0, 3)); ?></span>
                            </div>
                            
                            <div class="birthday-avatar-col">
                                <?php if (!empty($person['foto'])): ?>
                                    <img src="<?php echo e($person['foto']); ?>" alt="<?php echo e($person['nome']); ?>" class="birthday-avatar-img">
                                <?php else: ?>
                                    <div class="birthday-avatar-initials">
                                        <?php
                                            $initials = '';
                                            $nameParts = explode(' ', trim($person['nome']));
                                            if (count($nameParts) > 0) $initials .= mb_substr($nameParts[0], 0, 1);
                                            if (count($nameParts) > 1) $initials .= mb_substr($nameParts[count($nameParts)-1], 0, 1);
                                            echo strtoupper($initials ?: 'CF');
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="birthday-info-col">
                                <div class="birthday-person-name">
                                    <span><?php echo e($person['nome']); ?></span>
                                    <?php if (!empty($person['is_hoje'])): ?>
                                        <span class="today-tag-clean">Hoje</span>
                                    <?php endif; ?>
                                </div>
                                <div class="birthday-person-sub">
                                    <span><?php echo (int)$person['idade_completando']; ?> anos</span>
                                    <span class="sub-sep">•</span>
                                    <span>Nascimento: <?php echo e($person['data_nascimento']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="birthday-action-col">
                            <a href="<?php echo e($person['link_prontuario']); ?>" class="btn-birthday-prontuario" title="Abrir prontuário do atendido">
                                <i class="fas fa-folder-open"></i> <span>Prontuário</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="birthday-empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <div>Nenhum atendido comemora aniversário neste mês.</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="modal-birthday-footer">
            <button type="button" class="btn-birthday-close" onclick="closeBirthdayModal()">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
    let currentDate = new Date();
    let allNotes = {}; // Armazenará todas as anotações do servidor
    let selectedDate = null;
    let selectedType = 'anotacao';

    // Carregar anotações do servidor
    async function loadNotes() {
        try {
            const monthParam = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
            const response = await fetch('dashboard.php?action=getCalendarNotes&month=' + monthParam, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const notes = await response.json();
                allNotes = {};
                if (Array.isArray(notes)) {
                    notes.forEach(note => {
                        if (!allNotes[note.date]) {
                            allNotes[note.date] = [];
                        }
                        allNotes[note.date].push(note);
                    });
                }
            }
        } catch (error) {
            console.error('Erro ao carregar anotações:', error);
        } finally {
            generateCalendar();
            updateNotesList();
        }
    }

    function generateCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        const monthNames = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        document.getElementById('currentMonth').textContent = `${monthNames[month]}, ${year}`;

        const calendarGrid = document.getElementById('calendarGrid');
        // Limpar dias existentes (manter headers)
        const headers = calendarGrid.querySelectorAll('.calendar-header-day');
        calendarGrid.innerHTML = '';
        headers.forEach(header => calendarGrid.appendChild(header));

        // Adicionar dias vazios no início
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        // Adicionar dias do mês
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            
            const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            // Marcar hoje
            const today = new Date();
            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                dayElement.classList.add('today');
            }
            
            // Marcar dias com anotações (laranja se tiver anotação, verde se tiver aviso)
            if (allNotes[dateKey] && allNotes[dateKey].length > 0) {
                const hasAviso = allNotes[dateKey].some(n => n.type === 'aviso');
                const hasAnotacao = allNotes[dateKey].some(n => n.type === 'anotacao');
                
                if (hasAviso && hasAnotacao) {
                    dayElement.classList.add('has-both');
                } else if (hasAviso) {
                    dayElement.classList.add('has-aviso');
                } else {
                    dayElement.classList.add('has-anotacao');
                }
            }
            
            dayElement.onclick = () => openTypeModal(dateKey, day);
            calendarGrid.appendChild(dayElement);
        }
    }

    function changeMonth(direction) {
        currentDate.setMonth(currentDate.getMonth() + direction);
        generateCalendar();
        loadNotes();
    }

    function openTypeModal(dateKey, day) {
        selectedDate = dateKey;
        // Extrair mês e ano da dateKey para garantir consistência
        const [year, month, dayNum] = dateKey.split('-');
        const formattedDate = `${dayNum}/${month}/${year}`;
        document.getElementById('selectedDate').textContent = formattedDate;
        document.getElementById('typeModal').style.display = 'block';
    }

    function closeTypeModal() {
        document.getElementById('typeModal').style.display = 'none';
    }

    function openNoteModal(type) {
        selectedType = type;
        document.getElementById('typeModal').style.display = 'none';
        document.getElementById('noteTypeLabel').textContent = type === 'aviso' ? 'Aviso' : 'Anotação';
        document.getElementById('noteText').value = '';
        document.getElementById('noteModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('noteModal').style.display = 'none';
        selectedDate = null;
    }

    async function saveNote() {
        if (selectedDate) {
            const noteText = document.getElementById('noteText').value.trim();
            if (!noteText) {
                alert('Por favor, digite uma anotação');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('date', selectedDate);
                formData.append('note', noteText);
                formData.append('type', selectedType);
                formData.append('csrf_token', <?php echo json_encode($csrf_token); ?>);

                const response = await fetch('dashboard.php?action=saveCalendarNote', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    closeModal();
                    await loadNotes();
                } else {
                    alert('Erro ao salvar: ' + result.error);
                }
            } catch (error) {
                console.error('Erro ao salvar anotação:', error);
                alert('Erro ao salvar anotação');
            }
        }
    }

    let noteIdToDelete = null;

    function deleteNote(id) {
        noteIdToDelete = id;
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
        }
    }

    function closeDeleteModal() {
        noteIdToDelete = null;
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
    }

    async function confirmDeleteNote() {
        if (!noteIdToDelete) return;
        const id = noteIdToDelete;
        closeDeleteModal();

        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('csrf_token', <?php echo json_encode($csrf_token); ?>);

            const response = await fetch('dashboard.php?action=deleteCalendarNote', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                await loadNotes();
            } else {
                alert('Erro ao excluir: ' + (result.error || 'Falha ao processar'));
            }
        } catch (error) {
            console.error('Erro ao excluir anotação:', error);
            alert('Erro ao excluir anotação');
        }
    }

    function createEmptyNoteState(message) {
        const emptyState = document.createElement('div');
        emptyState.style.cssText = 'color:var(--text-muted); font-style:italic;';
        emptyState.textContent = message;
        return emptyState;
    }

    function createNoteCard(note, badgeClass, deleteTitle) {
        const parts = String(note.date || '').split('-').map(Number);
        const date = parts.length === 3
            ? new Date(parts[0], parts[1] - 1, parts[2])
            : new Date(NaN);
        const validDate = !Number.isNaN(date.getTime());

        const isAviso = badgeClass === 'green-badge' || note.type === 'aviso';
        const card = document.createElement('div');
        card.className = 'note-card-glass' + (isAviso ? ' aviso' : '');

        const badge = document.createElement('div');
        badge.className = `note-badge ${badgeClass}`;
        badge.textContent = validDate ? String(date.getDate()) : '—';

        const content = document.createElement('div');
        content.className = 'note-content';
        const dateElement = document.createElement('div');
        dateElement.className = 'note-date';
        dateElement.textContent = validDate ? date.toLocaleDateString('pt-BR') : 'Data inválida';
        const textElement = document.createElement('div');
        textElement.className = 'note-text';
        textElement.textContent = String(note.note || '');
        content.append(dateElement, textElement);

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'delete-note-btn';
        deleteButton.title = deleteTitle;
        deleteButton.setAttribute('aria-label', deleteTitle);
        deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
        const noteId = Number.parseInt(note.id, 10);
        if (Number.isSafeInteger(noteId) && noteId > 0) {
            deleteButton.addEventListener('click', () => deleteNote(noteId));
        } else {
            deleteButton.disabled = true;
        }

        card.append(badge, content, deleteButton);
        return card;
    }

    function updateNotesList() {
        const notesList = document.getElementById('notesList');
        const currentMonth = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
        const anotacoes = [];
        const avisos = [];

        Object.entries(allNotes).forEach(([date, notes]) => {
            if (date.startsWith(currentMonth)) {
                notes.forEach(note => {
                    if (note.type === 'aviso') {
                        avisos.push(note);
                    } else {
                        anotacoes.push(note);
                    }
                });
            }
        });

        anotacoes.sort((a, b) => a.date.localeCompare(b.date));
        avisos.sort((a, b) => a.date.localeCompare(b.date));

        if (anotacoes.length === 0) {
            notesList.replaceChildren(createEmptyNoteState('Nenhuma anotação este mês'));
        } else {
            notesList.replaceChildren(...anotacoes.map(note => createNoteCard(note, 'orange-badge', 'Excluir anotação')));
        }

        const avisosList = document.getElementById('avisosList');
        if (avisos.length === 0) {
            avisosList.replaceChildren(createEmptyNoteState('Nenhum aviso este mês'));
        } else {
            avisosList.replaceChildren(...avisos.map(note => createNoteCard(note, 'green-badge', 'Excluir aviso')));
        }
    }

    // ==========================================
    // SISTEMA DE DESCARTE E RESTAURAÇÃO DE ALERTAS (POR USUÁRIO)
    // ==========================================
    const currentUserId = <?php echo (int)($userId ?? $_SESSION['user_id'] ?? 0); ?>;
    const dismissedStorageKey = 'cf_dismissed_alerts_user_' + currentUserId;

    function getDismissedAlerts() {
        try {
            const data = localStorage.getItem(dismissedStorageKey);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    function setDismissedAlerts(ids) {
        try {
            localStorage.setItem(dismissedStorageKey, JSON.stringify(ids));
        } catch (e) {}
    }

    function initAlertsDismissSystem() {
        const dismissed = getDismissedAlerts();
        const alertItems = document.querySelectorAll('.alert-item-wrapper');
        if (alertItems.length === 0) return;

        let visibleCount = 0;
        let hiddenCount = 0;

        alertItems.forEach(el => {
            const id = el.getAttribute('data-alert-id');
            if (id && dismissed.includes(id)) {
                el.style.display = 'none';
                hiddenCount++;
            } else {
                el.style.display = '';
                visibleCount++;
            }
        });

        updateAlertsCounters(visibleCount, hiddenCount);
    }

    function dismissAlert(event, alertId) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        if (!alertId) return;

        const el = document.querySelector(`.alert-item-wrapper[data-alert-id="${alertId}"]`);
        if (!el) return;

        el.classList.add('pill-dismissing');
        setTimeout(() => {
            el.style.display = 'none';
            el.classList.remove('pill-dismissing');

            const dismissed = getDismissedAlerts();
            if (!dismissed.includes(alertId)) {
                dismissed.push(alertId);
                setDismissedAlerts(dismissed);
            }

            const alertItems = document.querySelectorAll('.alert-item-wrapper');
            let visibleCount = 0;
            let hiddenCount = 0;
            alertItems.forEach(item => {
                if (item.style.display === 'none') {
                    hiddenCount++;
                } else {
                    visibleCount++;
                }
            });

            updateAlertsCounters(visibleCount, hiddenCount);
        }, 200);
    }

    function restoreAllAlerts() {
        try {
            localStorage.removeItem(dismissedStorageKey);
        } catch (e) {}

        const alertItems = document.querySelectorAll('.alert-item-wrapper');
        alertItems.forEach(el => {
            el.style.display = '';
            el.classList.add('pill-restoring');
            setTimeout(() => el.classList.remove('pill-restoring'), 300);
        });

        updateAlertsCounters(alertItems.length, 0);
    }

    function updateAlertsCounters(visibleCount, hiddenCount) {
        const badge = document.getElementById('alertsCountBadge');
        if (badge) {
            badge.textContent = visibleCount;
        }

        const restoreBtn = document.getElementById('btnRestoreAlerts');
        const hiddenCountEl = document.getElementById('hiddenAlertsCount');
        if (restoreBtn && hiddenCountEl) {
            if (hiddenCount > 0) {
                restoreBtn.style.display = 'inline-flex';
                hiddenCountEl.textContent = hiddenCount;
            } else {
                restoreBtn.style.display = 'none';
            }
        }

        const allDismissedBox = document.getElementById('alertsAllDismissed');
        const pillsList = document.getElementById('alertsPillsList');
        const totalItems = document.querySelectorAll('.alert-item-wrapper').length;

        if (allDismissedBox) {
            if (visibleCount === 0 && totalItems > 0) {
                allDismissedBox.style.display = 'flex';
                if (pillsList) pillsList.style.display = 'none';
            } else {
                allDismissedBox.style.display = 'none';
                if (pillsList) pillsList.style.display = '';
            }
        }
    }

    // ==========================================
    // MODAL DE ANIVERSARIANTES DO MÊS
    // ==========================================
    function openBirthdayModal(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const modal = document.getElementById('birthdayModal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeBirthdayModal() {
        const modal = document.getElementById('birthdayModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Fechar modal clicando fora
    window.onclick = function(event) {
        const noteModal = document.getElementById('noteModal');
        const typeModal = document.getElementById('typeModal');
        const deleteModal = document.getElementById('deleteConfirmModal');
        const birthdayModal = document.getElementById('birthdayModal');
        if (event.target === noteModal) {
            closeModal();
        }
        if (event.target === typeModal) {
            closeTypeModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
        if (event.target === birthdayModal) {
            closeBirthdayModal();
        }
    }

    // Tecla ESC para fechar modais
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBirthdayModal();
            closeDeleteModal();
            closeModal();
            closeTypeModal();
        }
    });

    // Inicializar calendário, anotações e sistema de alertas
    generateCalendar();
    loadNotes();
    initAlertsDismissSystem();
</script>
