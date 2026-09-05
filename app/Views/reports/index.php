<?php
$filters = $report['filters'] ?? [];
$baseParams = [
    'type' => $report['type'] ?? 'atendidos',
    'data_inicio' => $filters['data_inicio'] ?? '',
    'data_fim' => $filters['data_fim'] ?? '',
    'origem' => $filters['origem'] ?? 'todos',
    'q' => $filters['q'] ?? ''
];
$exportUrl = 'reports.php?' . http_build_query(array_merge($baseParams, ['action' => 'export']));
$printUrl = 'reports.php?' . http_build_query(array_merge($baseParams, ['print' => '1']));
?>

<section class="reports-page">
    <header class="report-hero">
        <div>
            <span class="report-eyebrow">Associação Criança Feliz</span>
            <h1>Central de Relatórios</h1>
            <p>Indicadores operacionais para acompanhamento da equipe e prestação de contas.</p>
        </div>
        <div class="report-privacy-badge">
            <i class="fas fa-shield-alt"></i>
            CPF protegido nas exportações
        </div>
    </header>

    <form class="report-filters card-glass" method="GET" action="reports.php">
        <div class="report-filter report-filter-wide">
            <label for="reportType">Relatório</label>
            <select id="reportType" name="type">
                <?php foreach ($types as $value => $label): ?>
                    <option value="<?php echo e($value); ?>" <?php echo ($report['type'] ?? '') === $value ? 'selected' : ''; ?>>
                        <?php echo e($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="report-filter">
            <label for="dateStart">Data inicial</label>
            <input id="dateStart" type="date" name="data_inicio" value="<?php echo e($filters['data_inicio'] ?? ''); ?>">
        </div>

        <div class="report-filter">
            <label for="dateEnd">Data final</label>
            <input id="dateEnd" type="date" name="data_fim" value="<?php echo e($filters['data_fim'] ?? ''); ?>">
        </div>

        <div class="report-filter" id="originFilter" <?php echo ($report['type'] ?? '') === 'frequencia' ? '' : 'hidden'; ?>>
            <label for="reportOrigin">Origem</label>
            <select id="reportOrigin" name="origem">
                <option value="todos" <?php echo ($filters['origem'] ?? '') === 'todos' ? 'selected' : ''; ?>>Todos</option>
                <option value="dia" <?php echo ($filters['origem'] ?? '') === 'dia' ? 'selected' : ''; ?>>Frequência diária</option>
                <option value="oficina" <?php echo ($filters['origem'] ?? '') === 'oficina' ? 'selected' : ''; ?>>Oficinas</option>
            </select>
        </div>

        <div class="report-filter report-filter-wide">
            <label for="reportSearch">Nome ou CPF</label>
            <input id="reportSearch" type="search" name="q" maxlength="100" placeholder="Filtro opcional" value="<?php echo e($filters['q'] ?? ''); ?>">
        </div>

        <div class="report-filter-actions">
            <button type="submit" class="btn report-primary-action">
                <i class="fas fa-filter"></i> Gerar relatório
            </button>
            <a class="btn report-export-action" href="<?php echo e($exportUrl); ?>">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
            <a class="btn secondary report-print-action" href="<?php echo e($printUrl); ?>" target="_blank" rel="noopener">
                <i class="fas fa-print"></i> Imprimir / PDF
            </a>
        </div>
    </form>

    <section class="report-document card-glass">
        <header class="report-document-header">
            <div class="report-brand">
                <img src="img/logo.png" alt="Associação Criança Feliz">
                <div>
                    <span>Associação Criança Feliz</span>
                    <strong><?php echo e($report['title'] ?? 'Relatório'); ?></strong>
                </div>
            </div>
            <div class="report-generated">
                <span>Gerado em</span>
                <strong><?php echo e($report['generated_at'] ?? ''); ?></strong>
                <span>por <?php echo e($currentUser['name'] ?? 'Usuário'); ?></span>
            </div>
        </header>

        <div class="report-document-intro">
            <div>
                <h2><?php echo e($report['title'] ?? ''); ?></h2>
                <p><?php echo e($report['description'] ?? ''); ?></p>
            </div>
            <?php if (!empty($filters['data_inicio']) || !empty($filters['data_fim'])): ?>
                <div class="report-period">
                    <span>Período analisado</span>
                    <strong>
                        <?php echo e(!empty($filters['data_inicio']) ? formatDateToBr($filters['data_inicio']) : 'início'); ?>
                        até
                        <?php echo e(!empty($filters['data_fim']) ? formatDateToBr($filters['data_fim']) : 'hoje'); ?>
                    </strong>
                </div>
            <?php endif; ?>
        </div>

        <div class="report-summary-grid">
            <?php foreach (($report['summary'] ?? []) as $item): ?>
                <?php $tone = in_array(($item['tone'] ?? ''), ['green', 'blue', 'orange', 'purple', 'red'], true) ? $item['tone'] : 'blue'; ?>
                <article class="report-summary-card report-tone-<?php echo e($tone); ?>">
                    <span><?php echo e($item['label'] ?? ''); ?></span>
                    <strong><?php echo e($item['value'] ?? 0); ?></strong>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($report['note'])): ?>
            <div class="report-note"><i class="fas fa-info-circle"></i> <?php echo e($report['note']); ?></div>
        <?php endif; ?>

        <?php if (!empty($report['truncated'])): ?>
            <div class="report-warning">O resultado foi limitado a 10.000 linhas. Aplique filtros para reduzir o período.</div>
        <?php endif; ?>

        <div class="report-table-wrap">
            <?php if (empty($report['rows'])): ?>
                <div class="report-empty">
                    <i class="fas fa-search"></i>
                    <strong>Nenhum registro encontrado</strong>
                    <span>Ajuste os filtros e gere o relatório novamente.</span>
                </div>
            <?php else: ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <?php foreach (($report['headers'] ?? []) as $label): ?>
                                <th><?php echo e($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['rows'] as $row): ?>
                            <tr>
                                <?php foreach (($report['headers'] ?? []) as $key => $label): ?>
                                    <td><?php echo e($row[$key] ?? ''); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <footer class="report-document-footer">
            <span>Documento de uso interno — contém dados pessoais protegidos.</span>
            <span><?php echo count($report['rows'] ?? []); ?> registro(s)</span>
        </footer>
    </section>
</section>

<script>
document.getElementById('reportType').addEventListener('change', function () {
    document.getElementById('originFilter').hidden = this.value !== 'frequencia';
});

<?php if (!empty($printMode)): ?>
window.addEventListener('load', () => window.print());
<?php endif; ?>
</script>
