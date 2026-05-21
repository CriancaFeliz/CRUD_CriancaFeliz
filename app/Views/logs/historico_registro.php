<?php
$registroId = $registro_id ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historico do Registro - Crianca Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .logs-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .logs-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .btn-link { background: #6b7b84; color: #fff; padding: 10px 14px; border-radius: 6px; text-decoration: none; }
        .logs-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        .logs-table th { background: #f0a36b; color: #fff; padding: 12px; text-align: left; }
        .logs-table td { padding: 12px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; }
        .badge.insert { background: #d4edda; color: #155724; }
        .badge.update { background: #fff3cd; color: #856404; }
        .badge.delete { background: #f8d7da; color: #721c24; }
        .pagination { display: flex; justify-content: center; gap: 6px; margin-top: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; }
        .pagination .active { background: #3498db; color: #fff; border-color: #3498db; }
    </style>
</head>
<body>
    <div class="logs-container">
        <div class="logs-header">
            <h1>Historico do registro #<?php echo htmlspecialchars($registroId); ?></h1>
            <a class="btn-link" href="logs.php">Voltar aos logs</a>
        </div>

        <table class="logs-table">
            <thead>
                <tr>
                    <th>Acao</th>
                    <th>Tabela</th>
                    <th>Descricao</th>
                    <th>Campo</th>
                    <th>Data/Hora</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px;">Nenhum log encontrado para este registro.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><span class="badge <?php echo strtolower($log['acao'] ?? ''); ?>"><?php echo htmlspecialchars($log['acao'] ?? ''); ?></span></td>
                            <td><?php echo htmlspecialchars($log['tabela_afetada'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($log['registro_alt'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($log['campo_alterado'] ?? '-'); ?></td>
                            <td><?php echo !empty($log['data_alteracao']) ? date('d/m/Y H:i:s', strtotime($log['data_alteracao'])) : '-'; ?></td>
                            <td><a href="logs.php?action=show&id=<?php echo urlencode($log['id_log'] ?? ''); ?>">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (($pagination['last_page'] ?? 1) > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="logs.php?action=historico&id=<?php echo urlencode($registroId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
