<style>
.filtros-barra-custom {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 14px;
}
.filtros-barra-custom .filtro-campo {
    flex: 1 1 170px;
    min-width: 150px;
    margin-bottom: 0;
}
.filtros-barra-custom .filtro-campo label {
    font-size: 13px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-primary);
    font-weight: 600;
}
.filtros-barra-custom .filtro-campo label i {
    color: inherit;
    opacity: 0.85;
}
.filtros-barra-custom .filtro-campo-busca {
    flex: 1.4 1 200px;
    margin-right: 2px;
}
.filtros-barra-custom .filtro-campo-botoes {
    flex: 0 0 auto;
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin-left: 12px; /* Espaçamento entre o input de texto e o botão de filtrar */
}
.btn-filtrar {
    height: 42px;
    padding: 0 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
    border-radius: 10px;
}
.btn-limpar {
    height: 42px;
    padding: 0 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

@media (max-width: 991px) {
    .filtros-barra-custom {
        gap: 12px;
    }
    .filtros-barra-custom .filtro-campo {
        flex: 1 1 calc(50% - 12px);
    }
    .filtros-barra-custom .filtro-campo-busca {
        flex: 1 1 100%;
        margin-right: 0;
    }
    .filtros-barra-custom .filtro-campo-botoes {
        flex: 1 1 100%;
        margin-left: 0;
        margin-top: 4px;
    }
    .filtros-barra-custom .btn-filtrar {
        flex: 1;
    }
}

@media (max-width: 576px) {
    .filtros-barra-custom {
        flex-direction: column;
        gap: 12px;
    }
    .filtros-barra-custom .filtro-campo {
        width: 100%;
        flex: 1 1 100%;
    }
    .filtros-barra-custom .filtro-campo-botoes {
        width: 100%;
        margin-left: 0;
    }
}
</style>

<!-- Controle de Faltas por Dia -->
<div class="filtros-container card-glass mb-4">
    <form method="GET" action="faltas.php" id="formFiltroFaltas">
        <input type="hidden" name="action" value="index">
        <div class="filtros-barra-custom">
            <div class="filtro-campo">
                <label class="form-label-bold">
                    <i class="fas fa-calendar-day"></i> Data da Chamada
                </label>
                <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>" class="form-control" onchange="this.form.submit()" required>
            </div>
            
            <div class="filtro-campo">
                <label class="form-label-bold">
                    <i class="fas fa-child"></i> Faixa Etária
                </label>
                <select name="faixa_etaria" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas as Idades</option>
                    <option value="0-13" <?php echo ($faixa_etaria === '0-13' || $faixa_etaria === '0-12') ? 'selected' : ''; ?>>Crianças (0 a 12 anos)</option>
                    <option value="13-18" <?php echo ($faixa_etaria === '13-18') ? 'selected' : ''; ?>>Adolescentes (13 a 18 anos)</option>
                </select>
            </div>

            <div class="filtro-campo">
                <label class="form-label-bold">
                    <i class="fas fa-clock"></i> Turno / Período
                </label>
                <select name="periodo" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos os Turnos</option>
                    <option value="Manhã" <?php echo (strcasecmp($periodo ?? '', 'Manhã') === 0) ? 'selected' : ''; ?>>Manhã</option>
                    <option value="Tarde" <?php echo (strcasecmp($periodo ?? '', 'Tarde') === 0) ? 'selected' : ''; ?>>Tarde</option>
                </select>
            </div>

            <div class="filtro-campo">
                <label class="form-label-bold">
                    <i class="fas fa-clipboard-check"></i> Status da Chamada
                </label>
                <select name="status_filtro" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos os Status</option>
                    <option value="P" <?php echo (($status_filtro ?? '') === 'P') ? 'selected' : ''; ?>>Presentes</option>
                    <option value="F" <?php echo (($status_filtro ?? '') === 'F') ? 'selected' : ''; ?>>Faltas</option>
                    <option value="J" <?php echo (($status_filtro ?? '') === 'J') ? 'selected' : ''; ?>>Justificadas</option>
                    <option value="pendente" <?php echo (($status_filtro ?? '') === 'pendente') ? 'selected' : ''; ?>>Não Registrados (Pendentes)</option>
                </select>
            </div>

            <div class="filtro-campo filtro-campo-busca">
                <label class="form-label-bold">
                    <i class="fas fa-search"></i> Buscar Atendido
                </label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nome ou CPF..." class="form-control">
            </div>

            <div class="filtro-campo filtro-campo-botoes">
                <button type="submit" class="btn primary btn-filtrar">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if (!empty($faixa_etaria) || !empty($periodo) || !empty($status_filtro) || !empty($search)): ?>
                    <a href="faltas.php?data=<?php echo urlencode($data); ?>" class="btn secondary btn-limpar" title="Limpar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Tabela de Frequência -->
<div class="table-responsive card-glass p-0">
    <table class="table-glass">
        <thead>
            <tr>
                <th>Atendido</th>
                <th>CPF</th>
                <th style="text-align: center;">Idade</th>
                <th style="text-align: center;">Turno</th>
                <th style="text-align: center;">Status</th>
                <th>Justificativa</th>
                <th style="text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($atendidos)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state text-center p-4 text-muted">
                            <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 12px; display: block;"></i>
                            <p class="m-0">Nenhum atendido encontrado com os filtros selecionados.</p>
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
                        $idade = (int)($atendido['idade'] ?? 0);
                        $badgeIdadeClass = ($idade < 13) ? 'badge-crianca' : 'badge-adolescente';
                        $turno = trim($atendido['periodo'] ?? '');
                    ?>
                    <tr data-atendido-id="<?php echo $id; ?>" 
                        data-nome="<?php echo htmlspecialchars($atendido['nome']); ?>" 
                        data-current-status="<?php echo htmlspecialchars($status ?? ''); ?>">
                        <td><strong><?php echo htmlspecialchars($atendido['nome']); ?></strong></td>
                        <td class="text-muted"><?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;">
                            <span class="badge <?php echo $badgeIdadeClass; ?>" style="font-weight: 600; font-size: 11px;">
                                <?php echo $idade; ?> anos
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="font-weight: 500; font-size: 11px; background: rgba(0,0,0,0.04); color: inherit; border: 1px solid var(--border-color);">
                                <i class="fas fa-clock me-1" style="color: inherit;"></i>
                                <?php echo htmlspecialchars($turno ?: 'N/D'); ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-flex justify-content-center align-items-center gap-3">
                                <label class="form-check m-0">
                                    <input type="radio" 
                                           name="status_<?php echo $id; ?>" 
                                           value="P" 
                                           <?php echo $statusPresente ? 'checked' : ''; ?>
                                           class="attendance-option" data-atendido-id="<?php echo $id; ?>" data-status="P" data-date="<?php echo e($data); ?>">
                                    <span>Presente</span>
                                </label>
                                <label class="form-check m-0">
                                    <input type="radio" 
                                           name="status_<?php echo $id; ?>" 
                                           value="F" 
                                           <?php echo $statusFalta ? 'checked' : ''; ?>
                                           class="attendance-option" data-atendido-id="<?php echo $id; ?>" data-status="F" data-date="<?php echo e($data); ?>">
                                    <span>Falta</span>
                                </label>
                                <label class="form-check m-0">
                                    <input type="radio" 
                                           name="status_<?php echo $id; ?>" 
                                           value="J" 
                                           <?php echo $statusJustificada ? 'checked' : ''; ?>
                                           class="attendance-option" data-atendido-id="<?php echo $id; ?>" data-status="J" data-date="<?php echo e($data); ?>">
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
    id: null,
    date: null,
    previousStatus: null
};

function abrirModalJustificativa(idAtendido, nomeAtendido, data, currentJustificativa, previousStatus) {
    justificativaTarget.id = idAtendido;
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
    if (justificativaTarget.id) {
        const id = justificativaTarget.id;
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
    const id = justificativaTarget.id;
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
    formData.append('status', 'J');
    formData.append('data', data);
    formData.append('justificativa', txt);

    fetch('faltas.php?action=salvarDia', {
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
            justificativaTarget.id = null;
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

document.querySelectorAll('.attendance-option').forEach(input => {
    input.addEventListener('change', () => {
        const id = Number.parseInt(input.dataset.atendidoId, 10);
        if (!Number.isSafeInteger(id) || id <= 0) return;

        const row = document.querySelector(`tr[data-atendido-id="${id}"]`);
        const previousStatus = row ? (row.dataset.currentStatus || '') : '';
        const nomeAtendido = row ? (row.dataset.nome || 'Atendido') : 'Atendido';
        const date = input.dataset.date;

        if (input.dataset.status === 'J') {
            const justElem = document.getElementById('just_' + id);
            const currentJust = justElem ? justElem.textContent.trim() : '';
            abrirModalJustificativa(id, nomeAtendido, date, currentJust, previousStatus);
        } else {
            salvarFrequencia(id, input.dataset.status, date);
            if (row) row.dataset.currentStatus = input.dataset.status;
        }
    });
});

function salvarFrequencia(idAtendido, status, data) {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken);
    formData.append('id_atendido', idAtendido);
    formData.append('status', status);
    formData.append('data', data);
    
    fetch('faltas.php?action=salvarDia', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            const justElem = document.getElementById('just_' + idAtendido);
            if (justElem) justElem.textContent = '-';
        } else {
            showToast(data.error || 'Erro ao salvar', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao processar requisição', 'error');
    });
}

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
