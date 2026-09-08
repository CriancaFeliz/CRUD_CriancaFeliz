<!-- Gerenciar Oficinas -->
<style>
    .modal {
        display: none;
    }
    .modal.active {
        display: flex;
    }
</style>

<!-- Header -->
<div class="actions flex-between flex-wrap gap-2 mb-4">
    <h2 class="m-0" style="color: var(--text-primary); font-size: 22px;">
        <i class="fas fa-chalkboard-teacher text-muted"></i> Gerenciar Oficinas
    </h2>
    <button onclick="abrirModalNova()" class="btn success">
        <i class="fas fa-plus"></i> Nova Oficina
    </button>
</div>

<!-- Grid de Oficinas -->
<div class="stats-row mb-4">
    <?php if (empty($oficinas)): ?>
        <div class="empty-state card-glass text-center p-5 text-muted" style="grid-column: 1/-1;">
            <i class="fas fa-chalkboard-teacher" style="font-size: 48px; margin-bottom: 12px; display: block;"></i>
            <p class="m-0" style="font-size: 16px; font-weight: 600;">Nenhuma oficina cadastrada</p>
        </div>
    <?php else: ?>
        <?php foreach ($oficinas as $oficina): ?>
            <div class="card-glass d-flex flex-column justify-content-between p-4" style="<?php echo !$oficina['ativo'] ? 'opacity: 0.65;' : ''; ?> border-left: 4px solid <?php echo $oficina['ativo'] ? 'var(--primary-green)' : 'var(--text-muted)'; ?>;">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div style="font-size: 17px; font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($oficina['nome']); ?></div>
                        <span class="status <?php echo $oficina['ativo'] ? 'ativo' : 'inativo'; ?>">
                            <?php echo $oficina['ativo'] ? 'Ativa' : 'Inativa'; ?>
                        </span>
                    </div>
                    
                    <?php if ($oficina['descricao']): ?>
                        <div class="text-muted mb-2" style="font-size: 14px;">
                            <i class="fas fa-align-left mr-1"></i> <?php echo htmlspecialchars($oficina['descricao']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($oficina['dia_semana']): ?>
                        <div class="text-muted mb-1" style="font-size: 13px;">
                            <i class="fas fa-calendar mr-1"></i> <?php echo htmlspecialchars($oficina['dia_semana']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($oficina['horario_inicio']): ?>
                        <div class="text-muted mb-3" style="font-size: 13px;">
                            <i class="fas fa-clock mr-1"></i> 
                            <?php echo substr($oficina['horario_inicio'], 0, 5); ?> - 
                            <?php echo substr($oficina['horario_fim'], 0, 5); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex gap-2 pt-3 mt-3" style="border-top: 1px solid var(--border-color); margin-top: 16px;">
                    <button type="button" data-action="edit-workshop" data-workshop="<?php echo e(json_encode($oficina, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)); ?>" class="btn secondary btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button type="button" data-action="toggle-workshop" data-workshop-id="<?php echo (int)($oficina['id_oficina'] ?? 0); ?>" class="btn <?php echo $oficina['ativo'] ? 'danger' : 'success'; ?> btn-sm" <?php echo $oficina['ativo'] ? 'style="background-color: #ef4444 !important; color: white !important; border-color: #dc2626 !important;"' : ''; ?>>
                        <i class="fas fa-power-off" <?php echo $oficina['ativo'] ? 'style="color: white !important;"' : ''; ?>></i> <?php echo $oficina['ativo'] ? 'Desativar' : 'Ativar'; ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="modalOficina" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Nova Oficina</h3>
            <button class="modal-close" onclick="fecharModal()" aria-label="Fechar">&times;</button>
        </div>
        
        <form id="formOficina" method="POST" action="faltas.php?action=salvarOficinaConfig">
            <input type="hidden" name="_csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
            <input type="hidden" name="id_oficina" id="id_oficina" value="">
            
            <div class="form-group">
                <label class="form-label-bold">Nome da Oficina <span class="required-asterisk">*</span></label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label-bold">Descrição</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label-bold">Dia da Semana</label>
                <select name="dia_semana" id="dia_semana" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="Segunda">Segunda-feira</option>
                    <option value="Terça">Terça-feira</option>
                    <option value="Quarta">Quarta-feira</option>
                    <option value="Quinta">Quinta-feira</option>
                    <option value="Sexta">Sexta-feira</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label-bold">Horário Início</label>
                <input type="time" name="horario_inicio" id="horario_inicio" class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label-bold">Horário Fim</label>
                <input type="time" name="horario_fim" id="horario_fim" class="form-control">
            </div>
            
            <div class="modal-buttons">
                <button type="button" onclick="fecharModal()" class="btn secondary">
                    Cancelar
                </button>
                <button type="submit" class="btn success">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Moderno de Confirmação Padronizado Criança Feliz -->
<div id="modalConfirmacaoCustom" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="modalConfirmacaoTitle">
    <div class="modal-confirm-dialog" style="max-width: 440px; text-align: center;">
        <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz">
        <h3 id="modalConfirmacaoTitle" class="modal-confirm-title">Confirmação</h3>
        <p id="modalConfirmacaoDesc" class="modal-confirm-desc">
            Deseja continuar?
        </p>
        
        <div class="modal-confirm-btn-group" style="margin-top: 24px;">
            <button type="button" class="btn-confirm-cancel" onclick="fecharModalConfirmacao()">Cancelar</button>
            <button type="button" id="btnConfirmarAcao" class="btn primary" style="flex: 1; height: 44px; border-radius: 10px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-check"></i> Confirmar
            </button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-action="edit-workshop"]').forEach(button => {
    button.addEventListener('click', () => {
        try {
            abrirModalEditar(JSON.parse(button.dataset.workshop));
        } catch (error) {
            alert('Não foi possível carregar os dados da oficina.');
        }
    });
});

document.querySelectorAll('[data-action="toggle-workshop"]').forEach(button => {
    button.addEventListener('click', () => {
        const workshopId = Number.parseInt(button.dataset.workshopId, 10);
        if (Number.isSafeInteger(workshopId) && workshopId > 0) toggleOficina(workshopId);
    });
});

function abrirModalNova() {
    document.getElementById('modalTitulo').textContent = 'Nova Oficina';
    document.getElementById('formOficina').reset();
    document.getElementById('id_oficina').value = '';
    document.getElementById('modalOficina').classList.add('active');
}

function abrirModalEditar(oficina) {
    document.getElementById('modalTitulo').textContent = 'Editar Oficina';
    document.getElementById('id_oficina').value = oficina.id_oficina;
    document.getElementById('nome').value = oficina.nome;
    document.getElementById('descricao').value = oficina.descricao || '';
    document.getElementById('dia_semana').value = oficina.dia_semana || '';
    document.getElementById('horario_inicio').value = oficina.horario_inicio || '';
    document.getElementById('horario_fim').value = oficina.horario_fim || '';
    document.getElementById('modalOficina').classList.add('active');
}

function fecharModal() {
    document.getElementById('modalOficina').classList.remove('active');
}

function toggleOficina(id) {
    abrirModalConfirmacao(
        'Alterar Status',
        'Tem certeza que deseja alterar o status desta oficina?',
        'Sim, alterar',
        () => {
    
    // Fazer requisição AJAX
    fetch('faltas.php?action=toggleOficina', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            id_oficina: id,
            csrf_token: <?php echo json_encode($csrf_token); ?>
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Erro ao alterar status da oficina');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar requisição');
        });
    });
}

let confirmacaoCallback = null;

function abrirModalConfirmacao(titulo, descricao, textoBotao, callback) {
    document.getElementById('modalConfirmacaoTitle').textContent = titulo;
    document.getElementById('modalConfirmacaoDesc').textContent = descricao;
    document.getElementById('btnConfirmarAcao').innerHTML = `<i class="fas fa-check"></i> ${textoBotao}`;
    
    confirmacaoCallback = callback;
    
    const modal = document.getElementById('modalConfirmacaoCustom');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function fecharModalConfirmacao() {
    const modal = document.getElementById('modalConfirmacaoCustom');
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
    confirmacaoCallback = null;
}

document.getElementById('btnConfirmarAcao').addEventListener('click', () => {
    if (confirmacaoCallback) confirmacaoCallback();
    fecharModalConfirmacao();
});

// Fechar modal ao clicar fora
document.getElementById('modalOficina').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>
