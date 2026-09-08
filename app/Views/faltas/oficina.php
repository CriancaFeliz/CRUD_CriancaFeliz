<!-- Controle de Faltas por Oficina -->
<div class="filtros-container card-glass mb-4">
    <form method="GET" action="faltas.php">
        <input type="hidden" name="action" value="oficina">
        <div class="filtros-row">
            <div class="form-group">
                <label class="form-label-bold">Selecionar Oficina</label>
                <select name="oficina" class="form-select" required>
                    <option value="">Escolha uma oficina...</option>
                    <?php foreach ($oficinas as $ofc): ?>
                        <option value="<?php echo (int)($ofc['id_oficina'] ?? 0); ?>"
                                <?php echo ($idOficina == $ofc['id_oficina']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ofc['nome']); ?>
                            <?php if ($ofc['dia_semana']): ?>
                                - <?php echo htmlspecialchars($ofc['dia_semana']); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label-bold">Data</label>
                <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>" class="form-control" required>
            </div>
            <div>
                <button type="submit" class="btn primary w-100">
                    <i class="fas fa-search"></i> Carregar
                </button>
            </div>
        </div>
    </form>
</div>

<?php if ($oficinaAtual): ?>
    <!-- Informações da Oficina -->
    <div class="oficina-info card-glass mb-4" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.9), rgba(16, 185, 129, 0.85)); color: #fff; border: none;">
        <h3 class="m-0 mb-2" style="color: #fff;"><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($oficinaAtual['nome']); ?></h3>
        <?php if ($oficinaAtual['descricao']): ?>
            <p class="m-0 mb-2 opacity-90"><?php echo htmlspecialchars($oficinaAtual['descricao']); ?></p>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-3 mt-2" style="font-size: 14px; opacity: 0.95;">
            <?php if ($oficinaAtual['dia_semana']): ?>
                <div><strong>Dia:</strong> <?php echo htmlspecialchars($oficinaAtual['dia_semana']); ?></div>
            <?php endif; ?>
            <?php if ($oficinaAtual['horario_inicio']): ?>
                <div><strong>Horário:</strong> 
                    <?php echo substr($oficinaAtual['horario_inicio'], 0, 5); ?> - 
                    <?php echo substr($oficinaAtual['horario_fim'], 0, 5); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabela de Frequência -->
    <div class="table-responsive card-glass p-0">
        <table class="table-glass">
            <thead>
                <tr>
                    <th>Atendido</th>
                    <th>CPF</th>
                    <th style="text-align: center;">Status</th>
                    <th>Justificativa</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($atendidos)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state text-center p-4 text-muted">
                                <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 12px; display: block;"></i>
                                <p class="m-0">Nenhum atendido encontrado</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($atendidos as $atendido): ?>
                        <?php
                            $id = (int)($atendido['idatendido'] ?? $atendido['id'] ?? 0);
                            $frequencia = $atendido['frequencia'];
                            $status = $frequencia['status'] ?? null;
                            $statusPresente = ($status === 'P');
                            $statusFalta = ($status === 'F');
                            $statusJustificada = ($status === 'J');
                        ?>
                        <tr data-atendido-id="<?php echo $id; ?>" data-nome="<?php echo htmlspecialchars($atendido['nome']); ?>" data-current-status="<?php echo htmlspecialchars($status ?? ''); ?>">
                            <td><strong><?php echo htmlspecialchars($atendido['nome']); ?></strong></td>
                            <td class="text-muted"><?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></td>
                            <td style="text-align: center;">
                                <div class="d-flex justify-content-center align-items-center gap-3">
                                    <label class="form-check m-0">
                                        <input type="radio" 
                                               name="status_<?php echo $id; ?>" 
                                               value="P" 
                                               <?php echo $statusPresente ? 'checked' : ''; ?>
                                               class="workshop-attendance-option" data-atendido-id="<?php echo $id; ?>" data-oficina-id="<?php echo (int)$idOficina; ?>" data-status="P" data-date="<?php echo e($data); ?>">
                                        <span>Presente</span>
                                    </label>
                                    <label class="form-check m-0">
                                        <input type="radio" 
                                               name="status_<?php echo $id; ?>" 
                                               value="F" 
                                               <?php echo $statusFalta ? 'checked' : ''; ?>
                                               class="workshop-attendance-option" data-atendido-id="<?php echo $id; ?>" data-oficina-id="<?php echo (int)$idOficina; ?>" data-status="F" data-date="<?php echo e($data); ?>">
                                        <span>Falta</span>
                                    </label>
                                    <label class="form-check m-0">
                                        <input type="radio" 
                                               name="status_<?php echo $id; ?>" 
                                               value="J" 
                                               <?php echo $statusJustificada ? 'checked' : ''; ?>
                                               class="workshop-attendance-option" data-atendido-id="<?php echo $id; ?>" data-oficina-id="<?php echo (int)$idOficina; ?>" data-status="J" data-date="<?php echo e($data); ?>">
                                        <span>Justificada</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span id="just_<?php echo $id; ?>" class="text-muted">
                                    <?php echo !empty($frequencia['justificativa']) ? htmlspecialchars($frequencia['justificativa']) : '-'; ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="faltas.php?action=historico&amp;id=<?php echo $id; ?>" class="btn-icon view-btn" title="Ver Histórico">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-state card-glass text-center p-5 text-muted">
        <i class="fas fa-chalkboard-teacher" style="font-size: 48px; margin-bottom: 12px; display: block; color: var(--primary-green);"></i>
        <p class="m-0" style="font-size: 16px; font-weight: 600;">Selecione uma oficina e uma data para começar</p>
    </div>
<?php endif; ?>

<!-- Modal Moderno de Justificativa Padronizado Criança Feliz -->
<div id="modalJustificativa" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="modalJustificativaTitle">
    <div class="modal-confirm-dialog" style="max-width: 440px; text-align: center;">
        <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz">
        <h3 id="modalJustificativaTitle" class="modal-confirm-title">Justificar Falta</h3>
        <p id="modalJustificativaDesc" class="modal-confirm-desc">
            Informe o motivo da falta de <strong id="modalJustificativaNome"></strong>:
        </p>
        
        <div style="margin: 18px 0; text-align: left;">
            <textarea id="modalJustificativaTexto" 
                      class="form-control" 
                      rows="3" 
                      placeholder="Ex: Consulta médica, atestado, motivo familiar..."
                      style="width: 100%; resize: vertical; border-radius: 12px; padding: 12px 14px; font-size: 14px; box-sizing: border-box;"></textarea>
            <div id="modalJustificativaErro" style="display: none; color: #ef4444; font-size: 12px; margin-top: 6px; font-weight: 500;">
                <i class="fas fa-exclamation-circle me-1"></i> Por favor, informe uma justificativa.
            </div>
        </div>

        <div class="modal-confirm-btn-group">
            <button type="button" class="btn-confirm-cancel" onclick="fecharModalJustificativa()">Cancelar</button>
            <button type="button" id="btnSalvarJustificativa" class="btn primary" style="flex: 1; height: 44px; border-radius: 10px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-check"></i> Salvar
            </button>
        </div>
    </div>
</div>

<script>
const csrfToken = <?php echo json_encode($csrf_token ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

let justificativaTarget = {
    idAtendido: null,
    idOficina: null,
    date: null,
    previousStatus: null
};

document.querySelectorAll('.workshop-attendance-option').forEach(input => {
    input.addEventListener('change', () => {
        const idAtendido = Number.parseInt(input.dataset.atendidoId, 10);
        const idOficina = Number.parseInt(input.dataset.oficinaId, 10);
        if (!Number.isSafeInteger(idAtendido) || idAtendido <= 0 || !Number.isSafeInteger(idOficina) || idOficina <= 0) return;
        
        const row = document.querySelector(`tr[data-atendido-id="${idAtendido}"]`);
        const previousStatus = row ? (row.dataset.currentStatus || '') : '';
        const nomeAtendido = row ? (row.dataset.nome || 'Atendido') : 'Atendido';
        const date = input.dataset.date;

        if (input.dataset.status === 'J') {
            const justElem = document.getElementById('just_' + idAtendido);
            const currentJust = justElem ? justElem.textContent.trim() : '';
            abrirModalJustificativa(idAtendido, idOficina, nomeAtendido, date, currentJust, previousStatus);
        } else {
            salvarFrequenciaOficina(idAtendido, idOficina, input.dataset.status, date);
            if (row) row.dataset.currentStatus = input.dataset.status;
        }
    });
});

function salvarFrequenciaOficina(idAtendido, idOficina, status, data) {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken);
    formData.append('id_atendido', idAtendido);
    formData.append('id_oficina', idOficina);
    formData.append('status', status);
    formData.append('data', data);
    
    fetch('faltas.php?action=salvarOficina', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.error || 'Erro ao salvar', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao processar requisição', 'error');
    });
}

function abrirModalJustificativa(idAtendido, idOficina, nomeAtendido, data, currentJustificativa, previousStatus) {
    justificativaTarget.idAtendido = idAtendido;
    justificativaTarget.idOficina = idOficina;
    justificativaTarget.date = data;
    justificativaTarget.previousStatus = previousStatus;

    document.getElementById('modalJustificativaNome').textContent = nomeAtendido;
    const txtArea = document.getElementById('modalJustificativaTexto');
    txtArea.value = (currentJustificativa && currentJustificativa !== '-') ? currentJustificativa : '';
    
    document.getElementById('modalJustificativaErro').style.display = 'none';

    const modal = document.getElementById('modalJustificativa');
    modal.style.display = 'flex';
    modal.classList.add('active');
    
    setTimeout(() => {
        txtArea.focus();
    }, 100);
}

function fecharModalJustificativa() {
    const modal = document.getElementById('modalJustificativa');
    modal.classList.remove('active');
    modal.style.display = 'none';

    // Se cancelou, restaura a opção anterior
    if (justificativaTarget.idAtendido) {
        const id = justificativaTarget.idAtendido;
        const prev = justificativaTarget.previousStatus;
        if (prev && prev !== 'J') {
            const prevRadio = document.querySelector(`input[name="status_${id}"][value="${prev}"]`);
            if (prevRadio) {
                prevRadio.checked = true;
            } else {
                const radioJ = document.querySelector(`input[name="status_${id}"][value="J"]`);
                if (radioJ) radioJ.checked = false;
            }
        } else if (!prev) {
            const radioJ = document.querySelector(`input[name="status_${id}"][value="J"]`);
            if (radioJ) radioJ.checked = false;
        }
    }
}

// Fechar com ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalJustificativa');
        if (modal && modal.classList.contains('active')) {
            fecharModalJustificativa();
        }
    }
});

// Fechar clicando fora do dialog
document.getElementById('modalJustificativa').addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalJustificativa')) {
        fecharModalJustificativa();
    }
});

// Salvar justificativa via modal
document.getElementById('btnSalvarJustificativa').addEventListener('click', () => {
    const id = justificativaTarget.idAtendido;
    const idOficina = justificativaTarget.idOficina;
    const data = justificativaTarget.date;
    const txt = document.getElementById('modalJustificativaTexto').value.trim();
    const erroDiv = document.getElementById('modalJustificativaErro');

    if (!txt) {
        erroDiv.style.display = 'block';
        document.getElementById('modalJustificativaTexto').focus();
        return;
    }

    erroDiv.style.display = 'none';
    const btn = document.getElementById('btnSalvarJustificativa');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

    const formData = new FormData();
    formData.append('_csrf_token', csrfToken);
    formData.append('id_atendido', id);
    formData.append('id_oficina', idOficina);
    formData.append('status', 'J');
    formData.append('data', data);
    formData.append('justificativa', txt);

    fetch('faltas.php?action=salvarOficina', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (res.success) {
            showToast(res.message, 'success');
            const justElem = document.getElementById('just_' + id);
            if (justElem) justElem.textContent = txt;
            
            // Atualizar status na linha
            const row = document.querySelector(`tr[data-atendido-id="${id}"]`);
            if (row) row.dataset.currentStatus = 'J';

            // Fechar modal sem reverter radio
            justificativaTarget.idAtendido = null;
            document.getElementById('modalJustificativa').classList.remove('active');
            document.getElementById('modalJustificativa').style.display = 'none';
        } else {
            showToast(res.error || 'Erro ao salvar justificativa', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        console.error('Erro:', err);
        showToast('Erro ao processar requisição', 'error');
    });
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 14px 20px;
        border-radius: 10px;
        color: #fff;
        font-weight: 500;
        z-index: 9999;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: fadeIn 0.3s ease;
        background: ${type === 'success' ? 'var(--gradient-green)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
