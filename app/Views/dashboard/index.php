<section class="grid dashboard-grid">
    <div class="card-glass calendar">
        <div class="calendar-header-wrapper">
            <div style="font-weight:700; font-size:18px;" id="currentMonth">Setembro, 2025</div>
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
    
    <div class="card-glass list">
        <div style="font-weight:700">Alertas Prioritários</div>
        <?php if (!empty($alertas)): ?>
            <?php foreach ($alertas as $alerta): ?>
                <?php if (!empty($alerta['link'])): ?>
                    <a href="<?php echo $alerta['link']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="pill <?php echo $alerta['tipo']; ?>">
                            <?php echo $alerta['icone']; ?> <?php echo htmlspecialchars($alerta['mensagem']); ?>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="pill <?php echo $alerta['tipo']; ?>">
                        <?php echo $alerta['icone']; ?> <?php echo htmlspecialchars($alerta['mensagem']); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="pill green">
                ✅ Nenhum alerta no momento
            </div>
        <?php endif; ?>
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
                            <div class="note-date"><?php echo $anotacao['formatted_date']; ?></div>
                            <div class="note-text"><?php echo htmlspecialchars($anotacao['note']); ?></div>
                        </div>
                        <button onclick="deleteNote('<?php echo $anotacao['id']; ?>')" class="delete-note-btn" title="Excluir anotação">&times;</button>
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
                    <div class="note-card-glass">
                        <div class="note-badge green-badge">
                            <?php echo date('d', strtotime($aviso['date'])); ?>
                        </div>
                        <div class="note-content">
                            <div class="note-date"><?php echo $aviso['formatted_date']; ?></div>
                            <div class="note-text"><?php echo htmlspecialchars($aviso['note']); ?></div>
                        </div>
                        <button onclick="deleteNote('<?php echo $aviso['id']; ?>')" class="delete-note-btn" title="Excluir aviso">&times;</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:var(--text-muted); font-style:italic;">Nenhum aviso este mês</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal para escolher tipo de anotação -->
<div id="typeModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="modal-content" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:12px; width:400px; max-width:90vw;">
        <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3>Escolha o tipo</h3>
            <button class="modal-close" onclick="closeTypeModal()" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <div style="display:flex; gap:12px; flex-direction:column;">
            <button onclick="openNoteModal('anotacao')" style="background:#ff7a00; color:white; border:none; padding:15px 20px; border-radius:8px; cursor:pointer; font-size:16px; font-weight:600; transition:all 0.2s;">
                📝 Anotação (Laranja)
            </button>
            <button onclick="openNoteModal('aviso')" style="background:#6fb64f; color:white; border:none; padding:15px 20px; border-radius:8px; cursor:pointer; font-size:16px; font-weight:600; transition:all 0.2s;">
                ⚠️ Aviso (Verde)
            </button>
        </div>
    </div>
</div>

<!-- Modal para adicionar/editar anotações -->
<div id="noteModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1001;">
    <div class="modal-content" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:12px; width:400px; max-width:90vw;">
        <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 id="modalTitle">Adicionar Anotação</h3>
            <button class="modal-close" onclick="closeModal()" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <div>
            <label for="noteText"><span id="noteTypeLabel">Anotação</span> para <span id="selectedDate"></span>:</label>
            <textarea id="noteText" class="note-textarea" placeholder="Digite aqui..." style="width:100%; height:100px; border:2px solid #f0a36b; border-radius:8px; padding:12px; resize:vertical; font-family:Poppins;"></textarea>
        </div>
        <div class="modal-buttons" style="display:flex; gap:12px; justify-content:flex-end; margin-top:16px;">
            <button class="btn-cancel" onclick="closeModal()" style="background:#6c757d; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer;">Cancelar</button>
            <button class="btn-save" onclick="saveNote()" style="background:#6fb64f; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer;">Salvar</button>
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
            console.log('Carregando notas para o mês:', monthParam);
            
            const response = await fetch('dashboard.php?action=getCalendarNotes&month=' + monthParam);
            const notes = await response.json();
            
            console.log('Notas recebidas:', notes);
            
            allNotes = {};
            notes.forEach(note => {
                if (!allNotes[note.date]) {
                    allNotes[note.date] = [];
                }
                allNotes[note.date].push(note);
            });
            
            console.log('allNotes processado:', allNotes);
            
            generateCalendar();
            updateNotesList();
        } catch (error) {
            console.error('Erro ao carregar anotações:', error);
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

            console.log('Salvando nota:', {
                date: selectedDate,
                note: noteText,
                type: selectedType,
                currentMonth: currentDate.getMonth() + 1,
                currentYear: currentDate.getFullYear()
            });

            try {
                const formData = new FormData();
                formData.append('date', selectedDate);
                formData.append('note', noteText);
                formData.append('type', selectedType);

                const response = await fetch('dashboard.php?action=saveCalendarNote', {
                    method: 'POST',
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

    async function deleteNote(id) {
        if (!confirm('Deseja realmente excluir esta anotação?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch('dashboard.php?action=deleteCalendarNote', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                await loadNotes();
            } else {
                alert('Erro ao excluir: ' + result.error);
            }
        } catch (error) {
            console.error('Erro ao excluir anotação:', error);
            alert('Erro ao excluir anotação');
        }
    }

    function updateNotesList() {
        // Atualizar lista de anotações
        const notesList = document.getElementById('notesList');
        const currentMonth = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
        
        const anotacoes = [];
        const avisos = [];
        
        // Filtrar anotações do mês atual
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
        
        // Ordenar por data
        anotacoes.sort((a, b) => a.date.localeCompare(b.date));
        avisos.sort((a, b) => a.date.localeCompare(b.date));
        
        // Atualizar lista de anotações
        if (anotacoes.length === 0) {
            notesList.innerHTML = '<div style="color:var(--text-muted); font-style:italic;">Nenhuma anotação este mês</div>';
        } else {
            notesList.innerHTML = anotacoes.map(anotacao => {
                const day = new Date(anotacao.date).getDate();
                const formattedDate = new Date(anotacao.date).toLocaleDateString('pt-BR');
                return `
                    <div class="note-card-glass">
                        <div class="note-badge orange-badge">
                            ${day}
                        </div>
                        <div class="note-content">
                            <div class="note-date">${formattedDate}</div>
                            <div class="note-text">${anotacao.note}</div>
                        </div>
                        <button onclick="deleteNote('${anotacao.id}')" class="delete-note-btn" title="Excluir anotação">&times;</button>
                    </div>
                `;
            }).join('');
        }
        
        // Atualizar lista de avisos
        const avisosList = document.getElementById('avisosList');
        if (avisos.length === 0) {
            avisosList.innerHTML = '<div style="color:var(--text-muted); font-style:italic;">Nenhum aviso este mês</div>';
        } else {
            avisosList.innerHTML = avisos.map(aviso => {
                const day = new Date(aviso.date).getDate();
                const formattedDate = new Date(aviso.date).toLocaleDateString('pt-BR');
                return `
                    <div class="note-card-glass">
                        <div class="note-badge green-badge">
                            ${day}
                        </div>
                        <div class="note-content">
                            <div class="note-date">${formattedDate}</div>
                            <div class="note-text">${aviso.note}</div>
                        </div>
                        <button onclick="deleteNote('${aviso.id}')" class="delete-note-btn" title="Excluir aviso">&times;</button>
                    </div>
                `;
            }).join('');
        }
    }

    // Fechar modal clicando fora
    window.onclick = function(event) {
        const noteModal = document.getElementById('noteModal');
        const typeModal = document.getElementById('typeModal');
        if (event.target === noteModal) {
            closeModal();
        }
        if (event.target === typeModal) {
            closeTypeModal();
        }
    }

    // Inicializar calendário
    loadNotes();
</script>
