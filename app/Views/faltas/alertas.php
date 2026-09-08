<!-- Alertas de Faltas -->
<div class="alert-header card-glass mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(239, 68, 68, 0.85)); color: #fff; border: none; text-align: center; padding: 22px;">
    <h2 class="m-0" style="color: #fff;"><i class="fas fa-exclamation-triangle"></i> Alertas de Faltas</h2>
    <p class="m-0 mt-1 opacity-90">Atendidos com 2 ou mais faltas não justificadas</p>
</div>

<?php if (empty($atendidos)): ?>
    <div class="empty-state card-glass text-center p-5">
        <i class="fas fa-check-circle" style="font-size: 54px; color: var(--primary-green); margin-bottom: 12px; display: block;"></i>
        <h3 class="m-0 mb-1" style="color: var(--text-primary); font-size: 20px;">Nenhum alerta!</h3>
        <p class="text-muted m-0">Não há atendidos com excesso de faltas no momento.</p>
    </div>
<?php else: ?>
    <?php foreach ($atendidos as $atendido): ?>
        <?php $isCritico = ($atendido['nivel_alerta'] === 'CRÍTICO'); ?>
        <div class="card-glass mb-3" style="border-left: 5px solid <?php echo $isCritico ? 'var(--error-border)' : 'var(--warning-border)'; ?>;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div style="font-size: 17px; font-weight: 700; color: var(--text-primary);">
                    <i class="fas fa-user text-muted mr-1"></i> <?php echo htmlspecialchars($atendido['nome']); ?>
                </div>
                <span class="badge <?php echo $isCritico ? 'badge-danger' : 'badge-warning'; ?>">
                    <?php echo htmlspecialchars($atendido['nivel_alerta']); ?>
                </span>
            </div>
            
            <div class="d-flex flex-wrap gap-4 my-3 text-muted" style="font-size: 14px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-id-card text-muted"></i>
                    <span><strong>CPF:</strong> <?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-times-circle text-danger"></i>
                    <span><strong>Total de Faltas:</strong> <strong style="color: var(--error-text);"><?php echo (int)($atendido['total_faltas'] ?? 0); ?></strong></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar text-muted"></i>
                    <span><strong>Última Falta:</strong> <?php echo date('d/m/Y', strtotime($atendido['ultima_falta'])); ?></span>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-3 pt-3" style="border-top: 1px solid var(--border-color); padding-top: 16px;">
                <a href="faltas.php?action=historico&amp;id=<?php echo (int)($atendido['idatendido'] ?? 0); ?>" class="btn secondary btn-sm">
                    <i class="fas fa-history"></i> Ver Histórico
                </a>
                <?php if ($isCritico): ?>
                    <a href="desligamento.php?action=novo&amp;id=<?php echo (int)($atendido['idatendido'] ?? 0); ?>" class="btn danger btn-sm">
                        <i class="fas fa-user-times"></i> Desligar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
