<?php
require_once APP_PATH . '/Views/logs/_helpers.php';

$log = $log ?? [];
$maskedLog = LogHelper::maskLogRow($log);
$field = $log['campo_alterado'] ?? '';
$hasComparison = (($log['valor_anterior'] ?? '') !== '') || (($log['valor_atual'] ?? '') !== '');
?>

<div class="logs-shell log-detail-shell">
    <div class="logs-toolbar">
        <div>
            <h2 class="logs-title">Detalhes do log #<?php echo cfLogEsc($log['id_log'] ?? ''); ?></h2>
            <p class="logs-subtitle">Consulta administrativa com valores sensíveis mascarados.</p>
        </div>
        <div class="logs-actions">
            <a href="logs.php" class="btn secondary">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
            <?php if (!empty($log['id_registro'])): ?>
                <a href="logs.php?action=historico&id=<?php echo urlencode($log['id_registro']); ?>" class="btn">
                    <i class="fas fa-timeline"></i>
                    Histórico do registro
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-glass log-detail-card">
        <div class="log-section-heading">
            <i class="fas fa-circle-info"></i>
            Informações gerais
        </div>

        <div class="log-detail-grid">
            <div class="log-detail-row">
                <span class="log-detail-label">Ação</span>
                <span class="log-detail-value"><?php echo cfLogActionBadge($log['acao'] ?? ''); ?></span>
            </div>
            <div class="log-detail-row">
                <span class="log-detail-label">Tabela afetada</span>
                <span class="log-detail-value"><strong><?php echo cfLogEsc(cfLogTableLabel($log['tabela_afetada'] ?? '')); ?></strong></span>
            </div>
            <div class="log-detail-row">
                <span class="log-detail-label">Data/Hora</span>
                <span class="log-detail-value"><?php echo cfLogEsc(cfLogDate($log['data_alteracao'] ?? null)); ?></span>
            </div>
            <div class="log-detail-row">
                <span class="log-detail-label">Usuário</span>
                <span class="log-detail-value">
                    <?php if (!empty($usuario)): ?>
                        <?php echo cfLogEsc(($usuario['nome'] ?? 'Usuário') . ' (' . ($usuario['email'] ?? 'sem email') . ')'); ?>
                    <?php else: ?>
                        <span class="log-muted">Sistema automático</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($log['ip_usuario'])): ?>
                <div class="log-detail-row">
                    <span class="log-detail-label">IP do usuário</span>
                    <span class="log-detail-value"><?php echo cfLogEsc($log['ip_usuario']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-glass log-detail-card">
        <div class="log-section-heading">
            <i class="fas fa-file-lines"></i>
            Registro alterado
        </div>

        <div class="log-detail-grid">
            <div class="log-detail-row">
                <span class="log-detail-label">Descrição</span>
                <span class="log-detail-value"><?php echo cfLogEsc($maskedLog['registro_alt'] ?? '-'); ?></span>
            </div>
            <?php if (!empty($log['id_registro'])): ?>
                <div class="log-detail-row">
                    <span class="log-detail-label">ID do registro</span>
                    <span class="log-detail-value">
                        <a class="log-record-link" href="logs.php?action=historico&id=<?php echo urlencode($log['id_registro']); ?>">
                            #<?php echo cfLogEsc($log['id_registro']); ?>
                        </a>
                    </span>
                </div>
            <?php endif; ?>
            <?php if (!empty($log['campo_alterado'])): ?>
                <div class="log-detail-row">
                    <span class="log-detail-label">Campo alterado</span>
                    <span class="log-detail-value"><?php echo cfLogEsc($log['campo_alterado']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($hasComparison): ?>
        <div class="card-glass log-detail-card">
            <div class="log-section-heading">
                <i class="fas fa-code-compare"></i>
                Comparação de valores
            </div>

            <div class="log-comparison-grid">
                <div class="log-comparison-item old">
                    <div class="log-comparison-label">Valor anterior</div>
                    <pre><?php echo cfLogEsc($maskedLog['valor_anterior'] ?? 'Sem valor anterior'); ?></pre>
                </div>
                <div class="log-comparison-item new">
                    <div class="log-comparison-label">Valor atual</div>
                    <pre><?php echo cfLogEsc($maskedLog['valor_atual'] ?? 'Sem valor atual'); ?></pre>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card-glass log-detail-card">
        <div class="log-section-heading">
            <i class="fas fa-shield-halved"></i>
            Dados brutos mascarados
        </div>

        <div class="log-code-block">
            <pre><?php echo cfLogEsc(json_encode($maskedLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
    </div>
</div>
