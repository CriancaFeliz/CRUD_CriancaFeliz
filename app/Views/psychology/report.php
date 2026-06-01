<?php
$filters = $filters ?? [];
$rows = $rows ?? [];

$query = http_build_query(array_filter([
    'action' => 'report',
    'format' => 'csv',
    'cpf' => $filters['cpf'] ?? '',
    'tipo' => $filters['tipo'] ?? '',
    'data_inicio' => $filters['data_inicio'] ?? '',
    'data_fim' => $filters['data_fim'] ?? ''
]));
?>

<div class="actions no-print" style="display:flex; gap:10px; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <a href="psychology.php" class="btn secondary" style="background:#6c757d; color:#fff;">Voltar</a>
    <div style="display:flex; gap:10px;">
        <button type="button" onclick="window.print()" class="btn" style="background:#17a2b8; color:#fff;">Imprimir / PDF</button>
        <a href="psychology.php?<?php echo htmlspecialchars($query); ?>" class="btn" style="background:#28a745; color:#fff;">Exportar Excel CSV</a>
    </div>
</div>

<form method="GET" action="psychology.php" class="no-print" style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,.08); display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; margin-bottom:20px;">
    <input type="hidden" name="action" value="report">
    <label style="display:grid; gap:6px;">
        CPF
        <input type="text" name="cpf" value="<?php echo htmlspecialchars($filters['cpf'] ?? ''); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
    </label>
    <label style="display:grid; gap:6px;">
        Tipo
        <select name="tipo" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
            <option value="">Todos</option>
            <?php foreach (['consulta' => 'Consulta', 'avaliacao' => 'Avaliação', 'evolucao' => 'Evolução', 'observacao' => 'Observação'] as $value => $label): ?>
                <option value="<?php echo $value; ?>" <?php echo (($filters['tipo'] ?? '') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label style="display:grid; gap:6px;">
        Data início
        <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filters['data_inicio'] ?? ''); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
    </label>
    <label style="display:grid; gap:6px;">
        Data fim
        <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filters['data_fim'] ?? ''); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
    </label>
    <button type="submit" class="btn" style="background:#17a2b8; color:#fff; align-self:end;">Filtrar</button>
</form>

<div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.08);">
    <h2 style="margin-top:0;">Relatório Psicológico</h2>
    <p style="color:#6c757d; margin-top:-8px;">Total de registros: <?php echo count($rows); ?></p>

    <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:760px;">
            <thead>
                <tr style="background:#f8f9fa;">
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">Data</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">Paciente</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">CPF</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">Tipo</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">Título</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">Humor</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid #dee2e6;">Psicólogo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" style="padding:16px; color:#6c757d;">Nenhum registro encontrado.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['data_anotacao'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['paciente_nome'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['cpf'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['tipo'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['titulo'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['humor'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['psicologo_nome'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .no-print,
    .sidebar,
    .topbar {
        display: none !important;
    }

    .content {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
