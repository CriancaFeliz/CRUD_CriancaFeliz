<?php
require_once APP_PATH . '/Views/logs/_helpers.php';

$usersById = cfLogUsersById($usuarios ?? []);
$pagination = $pagination ?? ['current_page' => 1, 'last_page' => 1, 'total' => 0];
$acoes = $acoes ?? ['INSERT' => 0, 'UPDATE' => 0, 'DELETE' => 0];
?>

<div class="logs-shell">
    <div class="logs-toolbar">
        <div>
            <h2 class="logs-title">Auditoria do sistema</h2>
            <p class="logs-subtitle">Acompanhe alterações, filtros e exportações administrativas.</p>
        </div>
        <div class="logs-actions">
            <a href="dashboard.php" class="btn secondary">
                <i class="fas fa-arrow-left"></i>
                Dashboard
            </a>
            <button type="button" class="btn secondary" id="toggleFiltersBtn" onclick="toggleLogFilters()" aria-expanded="false">
                <i class="fas fa-filter"></i>
                Filtros
            </button>
            <a href="logs.php?action=export" class="btn">
                <i class="fas fa-file-csv"></i>
                Exportar CSV
            </a>
        </div>
    </div>

    <div id="logsFilters" class="logs-filter-panel card-glass" hidden>
        <form method="GET" action="logs.php" class="logs-filter-grid">
            <input type="hidden" name="action" value="search">

            <div>
                <label class="form-label-bold">Tabela</label>
                <select name="tabela" class="form-select">
                    <option value="">Todas</option>
                    <option value="atendido">Atendido</option>
                    <option value="ficha_acolhimento">Ficha Acolhimento</option>
                    <option value="ficha_socioeconomico">Ficha Socioeconômica</option>
                    <option value="anotacao_psicologica">Anotação Psicológica</option>
                    <option value="frequencia_dia">Frequência</option>
                    <option value="desligamento">Desligamento</option>
                    <option value="usuario">Usuário</option>
                </select>
            </div>

            <div>
                <label class="form-label-bold">Ação</label>
                <select name="acao" class="form-select">
                    <option value="">Todas</option>
                    <option value="INSERT">Criar</option>
                    <option value="UPDATE">Editar</option>
                    <option value="DELETE">Deletar</option>
                </select>
            </div>

            <div>
                <label class="form-label-bold">Usuário</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach (($usuarios ?? []) as $user): ?>
                        <option value="<?php echo cfLogEsc($user['idusuario'] ?? ''); ?>">
                            <?php echo cfLogEsc($user['nome'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label-bold">Data início</label>
                <input type="date" name="data_inicio" class="form-control">
            </div>

            <div>
                <label class="form-label-bold">Data fim</label>
                <input type="date" name="data_fim" class="form-control">
            </div>

            <div>
                <label class="form-label-bold">Buscar</label>
                <input type="text" name="busca" class="form-control" placeholder="Nome, CPF, descrição...">
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

    <div class="logs-stats-grid">
        <div class="stat-card-glass log-stat-card">
            <div class="log-stat-icon blue"><i class="fas fa-clock-rotate-left"></i></div>
            <div>
                <div class="stat-number"><?php echo number_format((int) ($stats['total_logs'] ?? 0), 0, ',', '.'); ?></div>
                <div class="stat-label">Total de logs</div>
            </div>
        </div>

        <div class="stat-card-glass log-stat-card">
            <div class="log-stat-icon green"><i class="fas fa-plus"></i></div>
            <div>
                <div class="stat-number"><?php echo number_format((int) ($acoes['INSERT'] ?? 0), 0, ',', '.'); ?></div>
                <div class="stat-label">Registros criados</div>
            </div>
        </div>

        <div class="stat-card-glass log-stat-card">
            <div class="log-stat-icon orange"><i class="fas fa-pen"></i></div>
            <div>
                <div class="stat-number"><?php echo number_format((int) ($acoes['UPDATE'] ?? 0), 0, ',', '.'); ?></div>
                <div class="stat-label">Registros editados</div>
            </div>
        </div>

        <div class="stat-card-glass log-stat-card">
            <div class="log-stat-icon red"><i class="fas fa-trash"></i></div>
            <div>
                <div class="stat-number"><?php echo number_format((int) ($acoes['DELETE'] ?? 0), 0, ',', '.'); ?></div>
                <div class="stat-label">Registros deletados</div>
            </div>
        </div>
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
                                <div class="empty-state-icon"><i class="fas fa-clock-rotate-left"></i></div>
                                <h3 class="empty-state-title">Nenhum log encontrado</h3>
                                <p class="empty-state-text">As alterações auditadas aparecerão aqui.</p>
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
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="logs.php?page=1" class="btn secondary">Primeira</a>
                    <a href="logs.php?page=<?php echo (int) $pagination['current_page'] - 1; ?>" class="btn secondary">Anterior</a>
                <?php endif; ?>

                <?php for ($i = $window['start']; $i <= $window['end']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="logs-page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="logs.php?page=<?php echo $i; ?>" class="logs-page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="logs.php?page=<?php echo (int) $pagination['current_page'] + 1; ?>" class="btn secondary">Próxima</a>
                    <a href="logs.php?page=<?php echo (int) $pagination['last_page']; ?>" class="btn secondary">Última</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleLogFilters() {
    const panel = document.getElementById('logsFilters');
    const button = document.getElementById('toggleFiltersBtn');
    const isHidden = panel.hasAttribute('hidden');

    if (isHidden) {
        panel.removeAttribute('hidden');
    } else {
        panel.setAttribute('hidden', 'hidden');
    }

    if (button) {
        button.setAttribute('aria-expanded', String(isHidden));
    }
}
</script>
