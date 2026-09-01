<section class="grid" style="display:grid; grid-template-columns: 0.8fr 1fr 260px; gap:20px; margin-top:20px;">
    <div class="card calendar" style="background:#fff; border-radius:14px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <div style="font-weight:700; font-size:18px;" id="currentMonth">Setembro, 2025</div>
            <div style="display:flex; gap:10px;">
                <button onclick="changeMonth(-1)" style="background:#ff7a00; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">‹</button>
                <button onclick="changeMonth(1)" style="background:#ff7a00; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">›</button>
            </div>
        </div>
        <div class="calendar-grid" id="calendarGrid" style="display:grid; grid-template-columns:repeat(7,1fr); gap:4px; width: 100%; max-width: 720px; margin: 0 auto;">
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">D</div>
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">S</div>
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">T</div>
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">Q</div>
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">Q</div>
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">S</div>
            <div class="calendar-header" style="text-align:center; font-weight:600; padding:8px; color:#666;">S</div>
        </div>
    </div>
    
    <div class="card list" style="background:#fff; border-radius:14px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding:16px; display:flex; flex-direction:column; gap:12px;">
        <div style="font-weight:700">Alertas Prioritários</div>
        <?php if (!empty($alertas)): ?>
            <?php foreach ($alertas as $alerta): ?>
                <?php if (!empty($alerta['link'])): ?>
                    <a href="<?php echo $alerta['link']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="pill <?php echo $alerta['tipo']; ?>" style="padding:10px 12px; border-radius:10px; font-size:14px; cursor: pointer; transition: all 0.2s;">
                            <?php echo $alerta['icone']; ?> <?php echo htmlspecialchars($alerta['mensagem']); ?>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="pill <?php echo $alerta['tipo']; ?>" style="padding:10px 12px; border-radius:10px; font-size:14px;">
                        <?php echo $alerta['icone']; ?> <?php echo htmlspecialchars($alerta['mensagem']); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="pill green" style="padding:10px 12px; border-radius:10px; font-size:14px; background:#e8f6ea; border-left:6px solid #6fb64f;">
                ✅ Nenhum alerta no momento
            </div>
        <?php endif; ?>
    </div>
    
    <div class="stats" style="display:grid; gap:12px;">
        <div class="stat" style="background:#fff; border-radius:12px; padding:16px; text-align:center; font-weight:700;">
            <?php echo $statsAcolhimento['ativas'] ?? 0; ?><br>
            <span style="font-weight:500">Acolhimentos Ativos</span>
        </div>
        <div class="stat" style="background:#fff; border-radius:12px; padding:16px; text-align:center; font-weight:700;">
            <?php echo $statsSocioeconomico['ativas'] ?? 0; ?><br>
            <span style="font-weight:500">Socioeconômicos Ativos</span>
        </div>
        <div class="stat" style="background:#fff; border-radius:12px; padding:16px; text-align:center; font-weight:700;">
            <?php echo ($statsAcolhimento['total'] ?? 0) + ($statsSocioeconomico['total'] ?? 0); ?><br>
            <span style="font-weight:500">Total de Fichas</span>
        </div>
    </div>
</section>

<section class="notes-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:15px;">
    <div class="card list" style="background:#fff; border-radius:14px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding:16px; display:flex; flex-direction:column; gap:12px;">
        <div style="font-weight:700">Anotações do Calendário</div>
        <div id="notesList">
            <?php if (!empty($anotacoes['anotacoes'])): ?>
                <?php foreach ($anotacoes['anotacoes'] as $anotacao): ?>
                    <div class="note" style="background:#fff; border-radius:12px; padding:14px; display:flex; gap:10px; align-items:flex-start; position:relative;">
                        <div class="badge orange" style="width:36px; height:36px; border-radius:10px; display:grid; place-items:center; font-weight:700; color:#fff; background:#ff7a00;">
                            <?php echo date('d', strtotime($anotacao['date'])); ?>
                        </div>
                        <div class="note-content" style="flex: 1;">
                            <div style="font-weight:600"><?php echo $anotacao['formatted_date']; ?></div>
                            <div style="font-size:12px;color:#666"><?php echo htmlspecialchars($anotacao['note']); ?></div>
                        </div>
                        <button onclick="deleteNote('<?php echo $anotacao['id']; ?>')" class="delete-note-btn" style="background:none; border:none; color:#e74c3c; cursor:pointer; font-size:18px; padding:0 8px; transition:all 0.2s;" title="Excluir anotação">&times;</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:#666; font-style:italic;">Nenhuma anotação este mês</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card list" style="background:#fff; border-radius:14px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding:16px; display:flex; flex-direction:column; gap:12px;">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="font-weight:700">Avisos</div>
        </div>
        <div id="avisosList">
            <?php if (!empty($anotacoes['avisos'])): ?>
                <?php foreach ($anotacoes['avisos'] as $aviso): ?>
                    <div class="note" style="background:#fff; border-radius:12px; padding:14px; display:flex; gap:10px; align-items:flex-start; position:relative; margin-bottom:10px;">
                        <div class="badge green" style="width:36px; height:36px; border-radius:10px; display:grid; place-items:center; font-weight:700; color:#fff; background:#6fb64f;">
                            <?php echo date('d', strtotime($aviso['date'])); ?>
                        </div>
                        <div class="note-content" style="flex: 1;">
                            <div style="font-weight:600"><?php echo $aviso['formatted_date']; ?></div>
                            <div style="font-size:12px;color:#666"><?php echo htmlspecialchars($aviso['note']); ?></div>
                        </div>
                        <button onclick="deleteNote('<?php echo $aviso['id']; ?>')" class="delete-note-btn" style="background:none; border:none; color:#e74c3c; cursor:pointer; font-size:18px; padding:0 8px; transition:all 0.2s;" title="Excluir aviso">&times;</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:#666; font-style:italic;">Nenhum aviso este mês</div>
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
        const headers = calendarGrid.querySelectorAll('.calendar-header');
        calendarGrid.innerHTML = '';
        headers.forEach(header => calendarGrid.appendChild(header));

        // Adicionar dias vazios no início
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day';
            emptyDay.style.cssText = 'aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:8px; cursor:pointer; transition:all 0.2s; font-weight:500; background:#f8f9fa; color:#333;';
            calendarGrid.appendChild(emptyDay);
        }

        // Adicionar dias do mês
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            dayElement.style.cssText = 'aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:8px; cursor:pointer; transition:all 0.2s; font-weight:500; background:#f8f9fa; color:#333;';
            
            const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            // Marcar hoje
            const today = new Date();
            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                dayElement.style.background = '#6fb64f';
                dayElement.style.color = 'white';
                dayElement.style.fontWeight = '700';
            }
            
            // Marcar dias com anotações (laranja se tiver anotação, verde se tiver aviso)
            if (allNotes[dateKey] && allNotes[dateKey].length > 0) {
                const hasAviso = allNotes[dateKey].some(n => n.type === 'aviso');
                const hasAnotacao = allNotes[dateKey].some(n => n.type === 'anotacao');
                
                if (hasAviso && hasAnotacao) {
                    // Ambos: gradiente laranja-verde
                    dayElement.style.background = 'linear-gradient(135deg, #ff7a00 50%, #6fb64f 50%)';
                } else if (hasAviso) {
                    dayElement.style.background = '#6fb64f';
                } else {
                    dayElement.style.background = '#ff7a00';
                }
                dayElement.style.color = 'white';
                dayElement.style.fontWeight = '700';
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
                    // Não recarregar a página para manter o mês atual
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
                // Não recarregar a página para manter o mês atual
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
            notesList.innerHTML = '<div style=\"color:#666; font-style:italic;\">Nenhuma anotação este mês</div>';
        } else {
            notesList.innerHTML = anotacoes.map(anotacao => {
                const day = new Date(anotacao.date).getDate();
                const formattedDate = new Date(anotacao.date).toLocaleDateString('pt-BR');
                return `
                    <div class=\"note\" style=\"background:#fff; border-radius:12px; padding:14px; display:flex; gap:10px; align-items:flex-start; position:relative; margin-bottom:10px;\">
                        <div class=\"badge orange\" style=\"width:36px; height:36px; border-radius:10px; display:grid; place-items:center; font-weight:700; color:#fff; background:#ff7a00;\">
                            ${day}
                        </div>
                        <div class=\"note-content\" style=\"flex: 1;\">
                            <div style=\"font-weight:600\">${formattedDate}</div>
                            <div style=\"font-size:12px;color:#666\">${anotacao.note}</div>
                        </div>
                        <button onclick=\"deleteNote('${anotacao.id}')\" class=\"delete-note-btn\" style=\"background:none; border:none; color:#e74c3c; cursor:pointer; font-size:18px; padding:0 8px; transition:all 0.2s;\" title=\"Excluir anotação\">&times;</button>
                    </div>
                `;
            }).join('');
        }
        
        // Atualizar lista de avisos
        const avisosList = document.getElementById('avisosList');
        if (avisos.length === 0) {
            avisosList.innerHTML = '<div style=\"color:#666; font-style:italic;\">Nenhum aviso este mês</div>';
        } else {
            avisosList.innerHTML = avisos.map(aviso => {
                const day = new Date(aviso.date).getDate();
                const formattedDate = new Date(aviso.date).toLocaleDateString('pt-BR');
                return `
                    <div class=\"note\" style=\"background:#fff; border-radius:12px; padding:14px; display:flex; gap:10px; align-items:flex-start; position:relative; margin-bottom:10px;\">
                        <div class=\"badge green\" style=\"width:36px; height:36px; border-radius:10px; display:grid; place-items:center; font-weight:700; color:#fff; background:#6fb64f;\">
                            ${day}
                        </div>
                        <div class=\"note-content\" style=\"flex: 1;\">
                            <div style=\"font-weight:600\">${formattedDate}</div>
                            <div style=\"font-size:12px;color:#666\">${aviso.note}</div>
                        </div>
                        <button onclick=\"deleteNote('${aviso.id}')\" class=\"delete-note-btn\" style=\"background:none; border:none; color:#e74c3c; cursor:pointer; font-size:18px; padding:0 8px; transition:all 0.2s;\" title=\"Excluir aviso\">&times;</button>
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

<style>
    .pill.red { background:#ffe5e5; border-left:6px solid #e06b6b; }
    .pill.green { background:#e8f6ea; border-left:6px solid #6fb64f; }
    .pill.warning { background:#fff3cd; border-left:6px solid #ffc107; }
    .pill.info { background:#d1ecf1; border-left:6px solid #17a2b8; }
    .pill.error { background:#f8d7da; border-left:6px solid #dc3545; }
    .pill.success { background:#d4edda; border-left:6px solid #28a745; }
    
    /* Botão de exclusão */
    .delete-note-btn:hover {
        color: #c0392b !important;
        transform: scale(1.2);
        font-weight: bold;
    }
    
    a:has(.pill):hover .pill {
        opacity: 0.8;
        transform: translateX(5px);
    }
    
    /* Modo escuro */
    [data-theme="dark"] .modal-content {
        background: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .modal-content h3,
    [data-theme="dark"] .modal-content label {
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] #noteText {
        background: var(--input-bg) !important;
        color: var(--text-secondary) !important;
        border-color: var(--border-color) !important;
    }
    
    /* Responsividade */
    @media (max-width: 1200px) {
        .grid { grid-template-columns: 0.9fr 1fr !important; }
        .stats { grid-column: 1 / -1; grid-template-columns: repeat(3, 1fr); }
        .notes-grid { grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 12px; }
    }
    
    @media (max-width: 1000px) {
        .grid { grid-template-columns: 1fr !important; }
        .stats { grid-template-columns: repeat(3, 1fr); }
        .notes-grid { grid-template-columns: 1fr 1fr; margin-top: 10px; }
    }
    
    @media (max-width: 768px) {
        .grid { grid-template-columns: 1fr !important; gap: 15px; }
        .notes-grid { grid-template-columns: 1fr; margin-top: 10px; gap: 15px; }
        .stats { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    }
    
    @media (max-width: 480px) {
        .stats { grid-template-columns: 1fr; }
        .notes-grid { grid-template-columns: 1fr; margin-top: 8px; gap: 10px; }
    }
</style>
