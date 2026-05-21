<!-- Alertas de Faltas -->
<style>
    .alert-header {
        background: linear-gradient(135deg,#3E6475 0%,#348cb4 100%);
        color: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        text-align: center;
    }
    .alert-header h2 {
        margin: 0;
    }
    .alerta-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 5px solid #ffc107;
    }
    .alerta-card.critico {
        border-left-color: #dc3545;
    }
    .alerta-header-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .alerta-nome {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    .badge-alerta {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-critico {
        background: #dc3545;
        color: #fff;
    }
    .badge-alerta-warn {
        background:rgb(255, 217, 103);
        color: #333;
    }
    .alerta-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 14px;
    }
    .info-item i {
        color:#3E6475;
    }
    .actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
    }
    .btn-small {
        padding: 8px 14px;
        font-size: 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-primary {
        background: #348cb4;
        color: #fff;
    }
    .btn-danger {
        background: #dc3545;
        color: #fff;
    }
    .btn-small:hover {
        opacity: 0.9;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
    }
    .empty-state i {
        font-size: 64px;
        color: #28a745;
        margin-bottom: 15px;
    }
</style>

<div class="alert-header">
    <h2><i class="fas fa-exclamation-triangle"></i> Alertas de Faltas</h2>
    <p>Atendidos com 2 ou mais faltas não justificadas</p>
</div>

<?php if (empty($atendidos)): ?>
    <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <h3>Nenhum alerta!</h3>
        <p>Não há atendidos com excesso de faltas no momento.</p>
    </div>
<?php else: ?>
    <?php foreach ($atendidos as $atendido): ?>
        <div class="alerta-card <?php echo ($atendido['nivel_alerta'] === 'CRÍTICO') ? 'critico' : ''; ?>">
            <div class="alerta-header-card">
                <div class="alerta-nome">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($atendido['nome']); ?>
                </div>
                <span class="badge-alerta <?php echo ($atendido['nivel_alerta'] === 'CRÍTICO') ? 'badge-critico' : 'badge-alerta-warn'; ?>">
                    <?php echo htmlspecialchars($atendido['nivel_alerta']); ?>
                </span>
            </div>
            
            <div class="alerta-info">
                <div class="info-item">
                    <i class="fas fa-id-card"></i>
                    <span><strong>CPF:</strong> <?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-times-circle"></i>
                    <span><strong>Total de Faltas:</strong> <?php echo $atendido['total_faltas']; ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-calendar"></i>
                    <span><strong>Última Falta:</strong> <?php echo date('d/m/Y', strtotime($atendido['ultima_falta'])); ?></span>
                </div>
            </div>
            
            <div class="actions">
                <a href="faltas.php?action=historico&id=<?php echo $atendido['idatendido']; ?>" class="btn-small btn-primary">
                    <i class="fas fa-history"></i> Ver Histórico
                </a>
                <?php if ($atendido['nivel_alerta'] === 'CRÍTICO'): ?>
                    <a href="desligamento.php?action=novo&id=<?php echo $atendido['idatendido']; ?>" class="btn-small btn-danger">
                        <i class="fas fa-user-times"></i> Desligar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
