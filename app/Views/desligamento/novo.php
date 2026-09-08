<!-- Formulário de Desligamento -->
<style>
    .form-container {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: 0 auto;
    }
    .atendido-info {
        background: linear-gradient(135deg, #3E6475 0%, #5a8599 100%);
        color: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
    }
    .atendido-info h3 {
        margin: 0 0 10px 0;
    }
    .stats-mini {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }
    .stat-mini {
        background: rgba(255,255,255,0.2);
        padding: 10px;
        border-radius: 8px;
        text-align: center;
    }
    .stat-mini h4 {
        margin: 0;
        font-size: 24px;
    }
    .stat-mini p {
        margin: 5px 0 0 0;
        font-size: 12px;
        opacity: 0.9;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    .alert-warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-warning i {
        color: #ffc107;
        margin-right: 10px;
    }
</style>

<div class="form-container">
    <!-- Informações do Atendido -->
    <div class="atendido-info">
        <h3><i class="fas fa-user"></i> <?php echo htmlspecialchars($atendido['nome']); ?></h3>
        <p><strong>CPF:</strong> <?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></p>
        
        <div class="stats-mini">
            <div class="stat-mini">
                <h4><?php echo $estatsFaltas['presencas'] ?? 0; ?></h4>
                <p>Presenças</p>
            </div>
            <div class="stat-mini">
                <h4><?php echo $estatsFaltas['faltas'] ?? 0; ?></h4>
                <p>Faltas</p>
            </div>
            <div class="stat-mini">
                <h4><?php echo $estatsFaltas['justificadas'] ?? 0; ?></h4>
                <p>Justificadas</p>
            </div>
            <div class="stat-mini">
                <h4><?php echo $estatsFaltas['percentual_presenca'] ?? 0; ?>%</h4>
                <p>% Presença</p>
            </div>
        </div>
    </div>

    <!-- Alerta -->
    <div class="alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Atenção:</strong> Esta ação irá desligar o atendido do sistema. Certifique-se de que todos os dados estão corretos.
    </div>

    <!-- Formulário -->
    <form id="formDesligamento" method="POST" action="desligamento.php?action=salvar">
        <input type="hidden" name="_csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
        <input type="hidden" name="id_atendido" value="<?php echo (int)($atendido['idatendido'] ?? $atendido['id'] ?? 0); ?>">

        <div class="form-group">
            <label>Tipo de Motivo *</label>
            <select name="tipo_motivo" required>
                <option value="">Selecione...</option>
                <option value="idade" <?php echo old('tipo_motivo') === 'idade' ? 'selected' : ''; ?>>Idade</option>
                <option value="excesso_faltas" <?php echo old('tipo_motivo') === 'excesso_faltas' ? 'selected' : ''; ?>>Excesso de Faltas</option>
                <option value="pedido_familia" <?php echo old('tipo_motivo') === 'pedido_familia' ? 'selected' : ''; ?>>Pedido da Família</option>
                <option value="transferencia" <?php echo old('tipo_motivo') === 'transferencia' ? 'selected' : ''; ?>>Transferência</option>
                <option value="outros" <?php echo old('tipo_motivo') === 'outros' ? 'selected' : ''; ?>>Outros</option>
            </select>
        </div>

        <div class="form-group">
            <label>Motivo/Descrição *</label>
            <textarea name="motivo" required placeholder="Descreva o motivo do desligamento..."><?php echo old('motivo'); ?></textarea>
        </div>

        <div class="form-group">
            <label>Observações Adicionais</label>
            <textarea name="observacao" placeholder="Observações adicionais (opcional)..."><?php echo old('observacao'); ?></textarea>
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" name="pode_retornar" value="1" id="pode_retornar" 
                       <?php echo (old('pode_retornar', '1') === '1') ? 'checked' : ''; ?>>
                <label for="pode_retornar" style="margin: 0;">Permitir retorno futuro</label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn" style="background: #dc3545;">
                <i class="fas fa-user-times"></i> Confirmar Desligamento
            </button>
            <a href="desligamento.php" class="btn secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Modal Moderno de Confirmação Padronizado Criança Feliz -->
<div id="modalConfirmacaoCustom" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="modalConfirmacaoTitle">
    <div class="modal-confirm-dialog" style="max-width: 440px; text-align: center;">
        <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz">
        <h3 id="modalConfirmacaoTitle" class="modal-confirm-title">Confirmar Desligamento</h3>
        <p id="modalConfirmacaoDesc" class="modal-confirm-desc">
            Deseja realmente desligar este atendido do sistema?
        </p>
        
        <div class="modal-confirm-btn-group" style="margin-top: 24px;">
            <button type="button" class="btn-confirm-cancel" onclick="fecharModalConfirmacao()">Cancelar</button>
            <button type="button" id="btnConfirmarAcao" class="btn primary" style="background: #dc3545; border-color: #dc3545; flex: 1; height: 44px; border-radius: 10px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-check"></i> Sim, Desligar
            </button>
        </div>
    </div>
</div>

<script>
document.getElementById('formDesligamento').addEventListener('submit', function(e) {
    e.preventDefault(); // Impede o envio imediato
    
    // Mostra o modal customizado
    const modal = document.getElementById('modalConfirmacaoCustom');
    modal.style.display = 'flex';
    modal.classList.add('active');
});

function fecharModalConfirmacao() {
    const modal = document.getElementById('modalConfirmacaoCustom');
    modal.classList.remove('active');
    modal.style.display = 'none';
}

document.getElementById('btnConfirmarAcao').addEventListener('click', function() {
    // Quando o usuário clica em "Sim, Desligar", envia o formulário diretamente
    const form = document.getElementById('formDesligamento');
    HTMLFormElement.prototype.submit.call(form);
});
</script>
