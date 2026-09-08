<!-- Lista de Desligamentos -->
<?php
if ($_SESSION['user_role'] !== 'admin') { 
    echo "<h2 style='text-align:center;margin-top:40px;color:var(--error-text);'>Acesso negado</h2>";
    exit;
}
?>

<!-- Estatísticas -->
<div class="stats-row mb-4">
    <div class="stat-card-glass">
        <div class="stat-number" style="color: var(--primary-green);"><?php echo $estatisticas['total'] ?? 0; ?></div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card-glass">
        <div class="stat-number" style="color: #0ea5e9;"><?php echo $estatisticas['por_idade'] ?? 0; ?></div>
        <div class="stat-label">Por Idade</div>
    </div>
    <div class="stat-card-glass">
        <div class="stat-number" style="color: var(--error-text);"><?php echo $estatisticas['por_faltas'] ?? 0; ?></div>
        <div class="stat-label">Excesso Faltas</div>
    </div>
    <div class="stat-card-glass">
        <div class="stat-number" style="color: var(--primary-orange);"><?php echo $estatisticas['por_pedido'] ?? 0; ?></div>
        <div class="stat-label">Pedido Família</div>
    </div>
    <div class="stat-card-glass">
        <div class="stat-number" style="color: #64748b;"><?php echo $estatisticas['automaticos'] ?? 0; ?></div>
        <div class="stat-label">Automáticos</div>
    </div>
</div>

<!-- Ações -->
<div class="actions flex-between flex-wrap gap-2 mb-4">
    <h2 class="m-0" style="color: var(--text-primary); font-size: 22px;">
        <i class="fas fa-list text-muted"></i> Lista de Desligamentos
    </h2>
    <div class="d-flex gap-2">
        <a href="desligamento.php?action=novo" class="btn danger">
            <i class="fas fa-user-times"></i> Novo Desligamento
        </a>
        <button onclick="processarDesligamentoAutomatico()" class="btn secondary">
            <i class="fas fa-robot"></i> Processar Automático
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="filtros-container card-glass mb-4">
    <form method="GET" action="desligamento.php">
        <div class="filtros-row" style="grid-template-columns: 1fr auto;">
            <div class="form-group">
                <label class="form-label-bold">Tipo de Motivo</label>
                <select name="tipo_motivo" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="idade" <?php echo ($filtros['tipo_motivo'] === 'idade') ? 'selected' : ''; ?>>Idade</option>
                    <option value="excesso_faltas" <?php echo ($filtros['tipo_motivo'] === 'excesso_faltas') ? 'selected' : ''; ?>>Excesso de Faltas</option>
                    <option value="pedido_familia" <?php echo ($filtros['tipo_motivo'] === 'pedido_familia') ? 'selected' : ''; ?>>Pedido da Família</option>
                    <option value="transferencia" <?php echo ($filtros['tipo_motivo'] === 'transferencia') ? 'selected' : ''; ?>>Transferência</option>
                    <option value="outros" <?php echo ($filtros['tipo_motivo'] === 'outros') ? 'selected' : ''; ?>>Outros</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn primary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Tabela -->
<div class="table-responsive card-glass p-0">
    <table class="table-glass">
        <thead>
            <tr>
                <th>Atendido</th>
                <th>CPF</th>
                <th>Motivo</th>
                <th>Tipo</th>
                <th>Data</th>
                <th>Automático</th>
                <th style="text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($desligamentos)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state text-center p-4 text-muted">
                            <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 12px; display: block;"></i>
                            <p class="m-0">Nenhum desligamento encontrado</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($desligamentos as $desl): ?>
                    <?php $tipoMotivo = array_key_exists(($desl['tipo_motivo'] ?? ''), ['idade' => true, 'faltas' => true, 'pedido' => true, 'transferencia' => true, 'outros' => true]) ? $desl['tipo_motivo'] : 'outros'; ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($desl['atendido_nome']); ?></strong></td>
                        <td class="text-muted"><?php echo htmlspecialchars($desl['cpf'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($desl['motivo']); ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?php 
                                    $tipos = [
                                        'idade' => 'Idade',
                                        'excesso_faltas' => 'Excesso Faltas',
                                        'pedido_familia' => 'Pedido Família',
                                        'transferencia' => 'Transferência',
                                        'outros' => 'Outros'
                                    ];
                                    echo e($tipos[$tipoMotivo] ?? 'N/A');
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($desl['data_desligamento'])); ?></td>
                        <td><?php echo $desl['automatico'] ? 'Sim' : 'Não'; ?></td>
                        <td style="text-align: center;">
                            <?php 
                            // Verificar se o atendido ainda está desligado
                            $isStillDisconnected = true;
                            try {
                                $desligamentoDB = new Desligamento();
                                $isStillDisconnected = $desligamentoDB->isDesligado($desl['id_atendido']);
                            } catch (Exception $e) {
                                // Em caso de erro, assumir que está desligado
                            }
                            
                            if ($isStillDisconnected && $desl['pode_retornar']): ?>
                                <button onclick="reativarAtendido(<?php echo (int)($desl['id_atendido'] ?? 0); ?>)" class="btn success btn-sm">
                                    <i class="fas fa-undo"></i> Reativar
                                </button>
                            <?php else: ?>
                                <span class="text-muted" style="font-size: 13px;">
                                    <?php echo $isStillDisconnected ? 'Não permitido' : 'Já reativado'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
const csrfToken = <?php echo json_encode($csrf_token ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

let confirmacaoCallback = null;

function abrirModalConfirmacao(titulo, descricao, textoBotao, callback) {
    document.getElementById('modalConfirmacaoTitle').textContent = titulo;
    document.getElementById('modalConfirmacaoDesc').textContent = descricao;
    document.getElementById('btnConfirmarAcao').innerHTML = `<i class="fas fa-check"></i> ${textoBotao}`;
    
    confirmacaoCallback = callback;
    
    const modal = document.getElementById('modalConfirmacaoCustom');
    modal.style.display = 'flex';
    modal.classList.add('active');
}

function fecharModalConfirmacao() {
    const modal = document.getElementById('modalConfirmacaoCustom');
    modal.classList.remove('active');
    modal.style.display = 'none';
    confirmacaoCallback = null;
}

document.getElementById('btnConfirmarAcao').addEventListener('click', () => {
    if (confirmacaoCallback) {
        confirmacaoCallback();
    }
});

function showToastDesligamento(message, type) {
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

function reativarAtendido(idAtendido) {
    abrirModalConfirmacao(
        'Reativar Atendido', 
        'Deseja realmente reativar este atendido?', 
        'Sim, Reativar',
        () => {
            fecharModalConfirmacao();
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id_atendido', idAtendido);
            
            fetch('desligamento.php?action=reativar', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || data.message || 'Erro na requisição');
                    }
                    return data;
                } else {
                    throw new Error('Resposta inválida do servidor.');
                }
            })
            .then(data => {
                if (data.success) {
                    showToastDesligamento(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToastDesligamento(data.error || 'Erro ao reativar', 'error');
                }
            })
            .catch(error => {
                console.error('Erro completo:', error);
                showToastDesligamento('Erro ao processar: ' + error.message, 'error');
            });
        }
    );
}

function processarDesligamentoAutomatico() {
    abrirModalConfirmacao(
        'Processar Automático', 
        'Deseja processar os desligamentos automáticos por excesso de faltas?', 
        'Sim, Processar',
        () => {
            fecharModalConfirmacao();
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            
            fetch('desligamento.php?action=automatico', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || data.message || 'Erro na requisição');
                    }
                    return data;
                } else {
                    throw new Error('Resposta inválida do servidor.');
                }
            })
            .then(data => {
                if (data.success) {
                    showToastDesligamento(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToastDesligamento(data.error || 'Erro ao processar', 'error');
                }
            })
            .catch(error => {
                console.error('Erro completo:', error);
                showToastDesligamento('Erro ao processar: ' + error.message, 'error');
            });
        }
    );
}
</script>
