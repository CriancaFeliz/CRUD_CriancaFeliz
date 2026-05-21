<!-- Controle de Faltas por Dia -->
<style>
    .filtros-container {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .filtros-row {
        display: grid;
        grid-template-columns: 1fr 1fr 2fr auto;
        gap: 15px;
        align-items: end;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-weight: 600;
        font-size: 14px;
        color: #333;
    }
    .form-group input,
    .form-group select {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
    }
    .tabela-frequencia {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .tabela-frequencia table {
        width: 100%;
        border-collapse: collapse;
    }
    .tabela-frequencia th {
        background: #3E6475;
        color: #fff;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    .tabela-frequencia td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    .tabela-frequencia tr:hover {
        background: #f8f9fa;
    }
    .checkbox-container {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .checkbox-item input[type="checkbox"],
    .checkbox-item input[type="radio"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }
    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }
    .btn-icon {
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        background:#80BE43;
        color: #fff;
        text-decoration: none;
        display: inline-block;
    }
    .btn-icon:hover {
        opacity: 0.8;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }
</style>

<!-- Filtros -->
<div class="filtros-container">
    <form method="GET" action="faltas.php">
        <input type="hidden" name="action" value="index">
        <div class="filtros-row">
            <div class="form-group">
                <label>Data</label>
                <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>" required>
            </div>
            <div class="form-group">
                <label>Faixa Etária</label>
                <select name="faixa_etaria" onchange="this.form.submit()">
                    <option value="">Todas as Idades</option>
                    <option value="0-13" <?php echo ($faixa_etaria === '0-13') ? 'selected' : ''; ?>>0 aos 13 anos</option>
                    <option value="13-18" <?php echo ($faixa_etaria === '13-18') ? 'selected' : ''; ?>>13 aos 18 anos</option>
                </select>
            </div>
            <div class="form-group">
                <label>Buscar Atendido</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nome ou CPF...">
            </div>
            <button type="submit" class="btn">
                <i class="fas fa-search"></i> Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabela de Frequência -->
<div class="tabela-frequencia">
    <table>
        <thead>
            <tr>
                <th>Atendido</th>
                <th>CPF</th>
                <th style="text-align: center;">Idade</th>
                <th style="text-align: center;">Status</th>
                <th>Justificativa</th>
                <th style="text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($atendidos)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Nenhum atendido encontrado</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($atendidos as $atendido): ?>
                    <?php
                        $id = $atendido['idatendido'] ?? $atendido['id'];
                        $frequencia = $atendido['frequencia'];
                        $status = $frequencia['status'] ?? null;
                        $statusPresente = ($status === 'P');
                        $statusFalta = ($status === 'F');
                        $statusJustificada = ($status === 'J');
                    ?>
                    <tr data-atendido-id="<?php echo $id; ?>">
                        <td><strong><?php echo htmlspecialchars($atendido['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;">
                            <span style="font-weight: 600; color:rgb(52, 52, 52);">
                                <?php echo $atendido['idade'] ?? 0; ?> anos
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="checkbox-container" style="justify-content: center;">
                                <div class="checkbox-item">
                                    <input type="radio" 
                                           name="status_<?php echo $id; ?>" 
                                           value="P" 
                                           <?php echo $statusPresente ? 'checked' : ''; ?>
                                           onchange="salvarFrequencia(<?php echo $id; ?>, 'P', '<?php echo $data; ?>')">
                                    <label> Presente</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" 
                                           name="status_<?php echo $id; ?>" 
                                           value="F" 
                                           <?php echo $statusFalta ? 'checked' : ''; ?>
                                           onchange="salvarFrequencia(<?php echo $id; ?>, 'F', '<?php echo $data; ?>')">
                                    <label> Falta</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" 
                                           name="status_<?php echo $id; ?>" 
                                           value="J" 
                                           <?php echo $statusJustificada ? 'checked' : ''; ?>
                                           onchange="abrirJustificativa(<?php echo $id; ?>, '<?php echo $data; ?>')">
                                    <label> Justificada</label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span id="just_<?php echo $id; ?>">
                                <?php echo !empty($frequencia['justificativa']) ? htmlspecialchars($frequencia['justificativa']) : '-'; ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="faltas.php?action=historico&id=<?php echo $id; ?>" class="btn-icon" title="Ver Histórico">
                                <i class="fas fa-history"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const csrfToken = '<?php echo $csrf_token; ?>';

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
        } else {
            showToast(data.error || 'Erro ao salvar', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao processar requisição', 'error');
    });
}

function abrirJustificativa(idAtendido, data) {
    const justificativa = prompt('Digite a justificativa para a falta:');
    if (justificativa !== null) {
        const formData = new FormData();
        formData.append('_csrf_token', csrfToken);
        formData.append('id_atendido', idAtendido);
        formData.append('status', 'J');
        formData.append('data', data);
        formData.append('justificativa', justificativa);
        
        fetch('faltas.php?action=salvarDia', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                document.getElementById('just_' + idAtendido).textContent = justificativa;
            } else {
                showToast(data.error || 'Erro ao salvar', 'error');
            }
        });
    } else {
        // Deselect radio
        document.querySelector(`input[name="status_${idAtendido}"][value="J"]`).checked = false;
    }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: #fff;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
