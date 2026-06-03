<?php
require_once APP_PATH . '/Views/logs/_helpers.php';

$filters = $filters ?? [];
$usersById = cfLogUsersById($usuarios ?? []);
$pagination = $pagination ?? ['current_page' => 1, 'last_page' => 1, 'total' => 0];
$exportParams = cfLogFilterParams($filters);
$exportParams['action'] = 'export';
?>

<div class="logs-shell">
    <div class="logs-toolbar">
        <div>
            <h2 class="logs-title">Resultados da busca</h2>
            <p class="logs-subtitle">
                <?php echo number_format((int) ($pagination['total'] ?? 0), 0, ',', '.'); ?>
                log(s) encontrados
            </p>
        </div>
        <div class="logs-actions">
            <a href="logs.php" class="btn secondary">
                <i class="fas fa-clock-rotate-left"></i>
                Todos os logs
            </a>
            <a href="<?php echo cfLogEsc(cfLogUrl($exportParams)); ?>" class="btn">
                <i class="fas fa-file-csv"></i>
                Exportar CSV
            </a>
        </div>
    </div>

    <div class="logs-result-summary card-glass">
        <i class="fas fa-circle-info"></i>
        <span>
            Página <?php echo (int) ($pagination['current_page'] ?? 1); ?>
            de <?php echo (int) ($pagination['last_page'] ?? 1); ?>.
            Ajuste os filtros abaixo para refinar a auditoria.
        </span>
    </div>

    <div class="logs-filter-panel card-glass">
        <form method="GET" action="logs.php" class="logs-filter-grid">
            <input type="hidden" name="action" value="search">

            <div>
                <label class="form-label-bold">Tabela</label>
                <select name="tabela" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach (['atendido', 'ficha_acolhimento', 'ficha_socioeconomico', 'anotacao_psicologica', 'frequencia_dia', 'desligamento', 'usuario'] as $table): ?>
                        <option value="<?php echo cfLogEsc($table); ?>" <?php echo (($filters['tabela'] ?? '') === $table) ? 'selected' : ''; ?>>
                            <?php echo cfLogEsc(cfLogTableLabel($table)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label-bold">Ação</label>
                <select name="acao" class="form-select">
                    <option value="">Todas</option>
                    <option value="INSERT" <?php echo (($filters['acao'] ?? '') === 'INSERT') ? 'selected' : ''; ?>>Criar</option>
                    <option value="UPDATE" <?php echo (($filters['acao'] ?? '') === 'UPDATE') ? 'selected' : ''; ?>>Editar</option>
                    <option value="DELETE" <?php echo (($filters['acao'] ?? '') === 'DELETE') ? 'selected' : ''; ?>>Deletar</option>
                </select>
            </div>

            <div>
                <label class="form-label-bold">Usuário</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach (($usuarios ?? []) as $user): ?>
                        <?php $userId = (string) ($user['idusuario'] ?? ''); ?>
                        <option value="<?php echo cfLogEsc($userId); ?>" <?php echo ((string) ($filters['usuario_id'] ?? '') === $userId) ? 'selected' : ''; ?>>
                            <?php echo cfLogEsc($user['nome'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label-bold">Data início</label>
                <input type="date" name="data_inicio" class="form-control" value="<?php echo cfLogEsc($filters['data_inicio'] ?? ''); ?>">
            </div>

            <div>
                <label class="form-label-bold">Data fim</label>
                <input type="date" name="data_fim" class="form-control" value="<?php echo cfLogEsc($filters['data_fim'] ?? ''); ?>">
            </div>

            <div>
                <label class="form-label-bold">Buscar</label>
                <input type="text" name="busca" class="form-control" placeholder="Nome, CPF, descrição..." value="<?php echo cfLogEsc($filters['busca'] ?? ''); ?>">
            </div>

            <div class="logs-filter-actions">
                <button type="submit" class="btn">
                    <i class="fas fa-magnifying-glass"></i>
                    Buscar
                </button>
                <a href="logs.php" class="btn secondary">
                    <i class="fas fa-rotate-left"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="card-glass logs-table-card p-0 overflow-hidden">
        <table class="table-glass logs-table">
            <thead>
                <tr>
                    <th>Ação</th>
                    <th>Tabela</th>
                    <th>Descrição</th>
                    <th>Usuário</th>
                    <th>Data/Hora</th>
                    <th class="actions-cell">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-container">
                                <div class="empty-state-icon"><i class="fas fa-magnifying-glass"></i></div>
                                <h3 class="empty-state-title">Nenhum resultado encontrado</h3>
                                <p class="empty-state-text">Revise os filtros e tente novamente.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $field = $log['campo_alterado'] ?? '';
                        $description = cfLogExcerpt($log['registro_alt'] ?? '', $field);
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
                            <td><?php echo cfLogEsc(cfLogUserName($log, $usersById)); ?></td>
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
                <?php
                $baseParams = cfLogFilterParams($filters);
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                    <?php $baseParams['page'] = 1; ?>
                    <a href="<?php echo cfLogEsc(cfLogUrl($baseParams)); ?>" class="btn secondary">Primeira</a>
                    <?php $baseParams['page'] = (int) $pagination['current_page'] - 1; ?>
                    <a href="<?php echo cfLogEsc(cfLogUrl($baseParams)); ?>" class="btn secondary">Anterior</a>
                <?php endif; ?>

                <?php for ($i = $window['start']; $i <= $window['end']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="logs-page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <?php $baseParams['page'] = $i; ?>
                        <a href="<?php echo cfLogEsc(cfLogUrl($baseParams)); ?>" class="logs-page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <?php $baseParams['page'] = (int) $pagination['current_page'] + 1; ?>
                    <a href="<?php echo cfLogEsc(cfLogUrl($baseParams)); ?>" class="btn secondary">Próxima</a>
                    <?php $baseParams['page'] = (int) $pagination['last_page']; ?>
                    <a href="<?php echo cfLogEsc(cfLogUrl($baseParams)); ?>" class="btn secondary">Última</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
