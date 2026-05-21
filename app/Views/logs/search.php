<?php
/**
 * Resultados de Busca Avançada de Logs
 */
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Busca de Logs - Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .logs-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .logs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logs-header h1 {
            font-size: 28px;
            color: var(--text-primary);
            margin: 0;
        }

        .logs-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-search {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            text-decoration: none;
        }

        .btn-search:hover {
            background: #2980b9;
        }

        .btn-export {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            text-decoration: none;
        }

        .btn-export:hover {
            background: #229954;
        }

        .filters-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #ecf0f1;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
            font-size: 13px;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 13px;
            font-family: inherit;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-filter {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-filter:hover {
            background: #2980b9;
        }

        .btn-clear {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-clear:hover {
            background: #7f8c8d;
        }

        .logs-table-container {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #ecf0f1;
        }

        .logs-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 13px;
        }

        .logs-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .logs-table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.insert {
            background: #d4edda;
            color: #155724;
        }

        .badge.update {
            background: #fff3cd;
            color: #856404;
        }

        .badge.delete {
            background: #f8d7da;
            color: #721c24;
        }

        .log-description {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .log-time {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
            font-size: 13px;
        }

        .pagination a:hover {
            background: #3498db;
            color: white;
        }

        .pagination .active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination .disabled {
            color: #bdc3c7;
            cursor: not-allowed;
        }

        .search-info {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .search-info p {
            margin: 0;
            color: #2c3e50;
            font-size: 14px;
        }

        .btn-view {
            background: #3498db;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="logs-container">
        <!-- Header -->
        <div class="logs-header">
            <h1>🔍 Resultados da Busca</h1>
            <div class="logs-actions">
                <a href="dashboard.php" class="btn-search" style="background: #95a5a6;">← Voltar ao Dashboard</a>
                <a href="logs.php" class="btn-search">📊 Todos os Logs</a>
                <a href="logs.php?action=export&tabela=<?php echo urlencode($filters['tabela'] ?? ''); ?>&acao=<?php echo urlencode($filters['acao'] ?? ''); ?>&usuario_id=<?php echo urlencode($filters['usuario_id'] ?? ''); ?>&data_inicio=<?php echo urlencode($filters['data_inicio'] ?? ''); ?>&data_fim=<?php echo urlencode($filters['data_fim'] ?? ''); ?>&busca=<?php echo urlencode($filters['busca'] ?? ''); ?>" class="btn-export">📥 Exportar CSV</a>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <div class="filters-section">
            <form method="GET" action="logs.php?action=search">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Tabela</label>
                        <select name="tabela">
                            <option value="">Todas</option>
                            <option value="atendido" <?php echo ($filters['tabela'] === 'atendido') ? 'selected' : ''; ?>>Atendido</option>
                            <option value="ficha_acolhimento" <?php echo ($filters['tabela'] === 'ficha_acolhimento') ? 'selected' : ''; ?>>Ficha Acolhimento</option>
                            <option value="ficha_socioeconomico" <?php echo ($filters['tabela'] === 'ficha_socioeconomico') ? 'selected' : ''; ?>>Ficha Socioeconômica</option>
                            <option value="anotacao_psicologica" <?php echo ($filters['tabela'] === 'anotacao_psicologica') ? 'selected' : ''; ?>>Anotação Psicológica</option>
                            <option value="frequencia_dia" <?php echo ($filters['tabela'] === 'frequencia_dia') ? 'selected' : ''; ?>>Frequência</option>
                            <option value="desligamento" <?php echo ($filters['tabela'] === 'desligamento') ? 'selected' : ''; ?>>Desligamento</option>
                            <option value="usuario" <?php echo ($filters['tabela'] === 'usuario') ? 'selected' : ''; ?>>Usuário</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Ação</label>
                        <select name="acao">
                            <option value="">Todas</option>
                            <option value="INSERT" <?php echo ($filters['acao'] === 'INSERT') ? 'selected' : ''; ?>>Criar</option>
                            <option value="UPDATE" <?php echo ($filters['acao'] === 'UPDATE') ? 'selected' : ''; ?>>Editar</option>
                            <option value="DELETE" <?php echo ($filters['acao'] === 'DELETE') ? 'selected' : ''; ?>>Deletar</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Usuário</label>
                        <select name="usuario_id">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $user): ?>
                                <option value="<?php echo $user['idusuario']; ?>" <?php echo ($filters['usuario_id'] == $user['idusuario']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Data Início</label>
                        <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filters['data_inicio'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Data Fim</label>
                        <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filters['data_fim'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Buscar</label>
                        <input type="text" name="busca" placeholder="Nome, CPF, descrição..." value="<?php echo htmlspecialchars($filters['busca'] ?? ''); ?>">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">🔍 Buscar</button>
                    <a href="logs.php" class="btn-clear">✕ Limpar</a>
                </div>
            </form>
        </div>

        <!-- Informações da Busca -->
        <div class="search-info">
            <p>
                <strong>Resultados encontrados:</strong> 
                <?php echo $pagination['total']; ?> log(s) 
                (Página <?php echo $pagination['current_page']; ?> de <?php echo $pagination['last_page']; ?>)
            </p>
        </div>

        <!-- Tabela de Logs -->
        <div class="logs-table-container">
            <table class="logs-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Ação</th>
                        <th style="width: 120px;">Tabela</th>
                        <th style="width: 250px;">Descrição</th>
                        <th style="width: 150px;">Usuário</th>
                        <th style="width: 150px;">Data/Hora</th>
                        <th style="width: 80px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">
                                <p style="color: var(--text-muted);">Nenhum log encontrado com os critérios informados</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="badge <?php echo strtolower($log['acao']); ?>">
                                        <?php 
                                        $acaoLabel = [
                                            'INSERT' => '➕ Criar',
                                            'UPDATE' => '✏️ Editar',
                                            'DELETE' => '🗑️ Deletar'
                                        ];
                                        echo $acaoLabel[$log['acao']] ?? $log['acao'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['tabela_afetada']); ?></strong>
                                </td>
                                <td>
                                    <div class="log-description" title="<?php echo htmlspecialchars($log['registro_alt']); ?>">
                                        <?php echo htmlspecialchars(substr($log['registro_alt'], 0, 80)); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    if ($log['id_usuario']) {
                                        $user = array_filter($usuarios, fn($u) => $u['idusuario'] == $log['id_usuario']);
                                        $user = reset($user);
                                        echo $user ? htmlspecialchars($user['nome']) : 'Sistema';
                                    } else {
                                        echo '<span style="color: var(--text-muted);">Sistema</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="log-time">
                                        <?php echo date('d/m/Y H:i:s', strtotime($log['data_alteracao'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="logs.php?action=show&id=<?php echo $log['id_log']; ?>" class="btn-view">👁️ Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['current_page'] > 1): ?>
                <a href="logs.php?action=search&page=1&tabela=<?php echo urlencode($filters['tabela'] ?? ''); ?>&acao=<?php echo urlencode($filters['acao'] ?? ''); ?>&usuario_id=<?php echo urlencode($filters['usuario_id'] ?? ''); ?>&data_inicio=<?php echo urlencode($filters['data_inicio'] ?? ''); ?>&data_fim=<?php echo urlencode($filters['data_fim'] ?? ''); ?>&busca=<?php echo urlencode($filters['busca'] ?? ''); ?>">« Primeira</a>
                <a href="logs.php?action=search&page=<?php echo $pagination['current_page'] - 1; ?>&tabela=<?php echo urlencode($filters['tabela'] ?? ''); ?>&acao=<?php echo urlencode($filters['acao'] ?? ''); ?>&usuario_id=<?php echo urlencode($filters['usuario_id'] ?? ''); ?>&data_inicio=<?php echo urlencode($filters['data_inicio'] ?? ''); ?>&data_fim=<?php echo urlencode($filters['data_fim'] ?? ''); ?>&busca=<?php echo urlencode($filters['busca'] ?? ''); ?>">‹ Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <?php if ($i == $pagination['current_page']): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="logs.php?action=search&page=<?php echo $i; ?>&tabela=<?php echo urlencode($filters['tabela'] ?? ''); ?>&acao=<?php echo urlencode($filters['acao'] ?? ''); ?>&usuario_id=<?php echo urlencode($filters['usuario_id'] ?? ''); ?>&data_inicio=<?php echo urlencode($filters['data_inicio'] ?? ''); ?>&data_fim=<?php echo urlencode($filters['data_fim'] ?? ''); ?>&busca=<?php echo urlencode($filters['busca'] ?? ''); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <a href="logs.php?action=search&page=<?php echo $pagination['current_page'] + 1; ?>&tabela=<?php echo urlencode($filters['tabela'] ?? ''); ?>&acao=<?php echo urlencode($filters['acao'] ?? ''); ?>&usuario_id=<?php echo urlencode($filters['usuario_id'] ?? ''); ?>&data_inicio=<?php echo urlencode($filters['data_inicio'] ?? ''); ?>&data_fim=<?php echo urlencode($filters['data_fim'] ?? ''); ?>&busca=<?php echo urlencode($filters['busca'] ?? ''); ?>">Próxima ›</a>
                <a href="logs.php?action=search&page=<?php echo $pagination['last_page']; ?>&tabela=<?php echo urlencode($filters['tabela'] ?? ''); ?>&acao=<?php echo urlencode($filters['acao'] ?? ''); ?>&usuario_id=<?php echo urlencode($filters['usuario_id'] ?? ''); ?>&data_inicio=<?php echo urlencode($filters['data_inicio'] ?? ''); ?>&data_fim=<?php echo urlencode($filters['data_fim'] ?? ''); ?>&busca=<?php echo urlencode($filters['busca'] ?? ''); ?>">Última »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
