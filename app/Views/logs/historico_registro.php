<?php
require_once APP_PATH . '/Views/logs/_helpers.php';

$registroId = $registro_id ?? '';
$pagination = $pagination ?? ['current_page' => 1, 'last_page' => 1, 'total' => 0];
?>

<div class="logs-shell">
    <div class="logs-toolbar">
        <div>
            <h2 class="logs-title">Histórico do registro #<?php echo cfLogEsc($registroId); ?></h2>
            <p class="logs-subtitle">Linha do tempo auditável das alterações deste registro.</p>
        </div>
        <div class="logs-actions">
            <a href="logs.php" class="btn secondary">
                <i class="fas fa-arrow-left"></i>
                Voltar aos logs
            </a>
        </div>
    </div>

    <div class="card-glass logs-table-card p-0 overflow-hidden">
        <table class="table-glass logs-table">
            <thead>
                <tr>
                    <th>Ação</th>
                    <th>Tabela</th>
                    <th>Descrição</th>
                    <th>Campo</th>
                    <th>Data/Hora</th>
                    <th class="actions-cell">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-container">
                                <div class="empty-state-icon"><i class="fas fa-timeline"></i></div>
                                <h3 class="empty-state-title">Nenhum log encontrado</h3>
                                <p class="empty-state-text">Este registro ainda não possui histórico auditável.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $field = $log['campo_alterado'] ?? '';
                        $description = cfLogExcerpt($log['registro_alt'] ?? '', $field, 120);
                        $descriptionFull = cfLogMasked($log['registro_alt'] ?? '', $field);
                        ?>
                        <tr>
                            <td><?php echo cfLogActionBadge($log['acao'] ?? ''); ?></td>
                            <td><strong><?php echo cfLogEsc(cfLogTableLabel($log['tabela_afetada'] ?? '')); ?></strong></td>
                            <td>
                                <span class="log-description" title="<?php echo cfLogEsc($descriptionFull); ?>">
                                    <?php echo cfLogEsc($description); ?>
                                </span>
                            </td>
                            <td><?php echo cfLogEsc($log['campo_alterado'] ?? '-'); ?></td>
                            <td><span class="log-time"><?php echo cfLogEsc(cfLogDate($log['data_alteracao'] ?? null)); ?></span></td>
                            <td class="actions-cell">
                                <a href="logs.php?action=show&id=<?php echo urlencode($log['id_log'] ?? ''); ?>" class="btn-icon view-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (($pagination['last_page'] ?? 1) > 1): ?>
            <?php $window = cfLogPaginationWindow($pagination['current_page'], $pagination['last_page']); ?>
            <div class="pagination card-pagination">
                <?php for ($i = $window['start']; $i <= $window['end']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="logs-page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="logs.php?action=historico&id=<?php echo urlencode($registroId); ?>&page=<?php echo $i; ?>" class="logs-page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
