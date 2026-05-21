<?php
/**
 * Dashboard de Logs - Sistema Criança Feliz
 * Apenas administradores têm acesso
 */
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Sistema de Logs - Criança Feliz</title>
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
        }

        .btn-export:hover {
            background: #229954;
        }

        /* Estatísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-card.insert {
            border-left-color: #27ae60;
        }

        .stat-card.update {
            border-left-color: #f39c12;
        }

        .stat-card.delete {
            border-left-color: #e74c3c;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--text-primary);
        }

        /* Tabela de Logs */
        .logs-table-container {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .logs-table thead {
            background: #f0a36b;
            color: white;
        }

        .logs-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .logs-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
        }

        .logs-table tbody tr:hover {
            background: rgba(111, 182, 79, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
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
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-time {
            color: var(--text-muted);
            font-size: 12px;
        }

        .log-actions {
            display: flex;
            gap: 5px;
        }

        .btn-view {
            background: #3498db;
            color: white;
            padding: 4px 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-view:hover {
            background: #2980b9;
        }

        /* Paginação */
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
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-primary);
            background: var(--card-bg);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination .active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        /* Filtros */
        .filters-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .filters-section.active {
            display: block;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 13px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-filter {
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.3s;
        }

        .btn-filter:hover {
            background: #2980b9;
        }

        .btn-clear {
            background: #95a5a6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.3s;
        }

        .btn-clear:hover {
            background: #7f8c8d;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .logs-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .logs-table {
                font-size: 11px;
            }

            .logs-table th,
            .logs-table td {
                padding: 8px;
            }

            .log-description {
                max-width: 150px;
            }

            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Dark mode */
        [data-theme="dark"] .logs-table thead {
            background: #d4a574;
        }

        [data-theme="dark"] .filter-group select,
        [data-theme="dark"] .filter-group input {
            background: #2c3e50;
            border-color: #34495e;
        }
    </style>
</head>
<body>
    <div class="logs-container">
        <!-- Header -->
        <div class="logs-header">
            <h1>📊 Sistema de Logs</h1>
            <div class="logs-actions">
                <a href="dashboard.php" class="btn-search" style="background: #95a5a6; text-decoration: none; color: white;">← Voltar ao Dashboard</a>
                <button class="btn-search" onclick="toggleFilters()">🔍 Filtros Avançados</button>
                <a href="logs.php?action=export" class="btn-export">📥 Exportar CSV</a>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <div id="filters" class="filters-section">
            <form method="GET" action="logs.php?action=search">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Tabela</label>
                        <select name="tabela">
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

                    <div class="filter-group">
                        <label>Ação</label>
                        <select name="acao">
                            <option value="">Todas</option>
                            <option value="INSERT">Criar</option>
                            <option value="UPDATE">Editar</option>
                            <option value="DELETE">Deletar</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Usuário</label>
                        <select name="usuario_id">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $user): ?>
                                <option value="<?php echo $user['idusuario']; ?>">
                                    <?php echo htmlspecialchars($user['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Data Início</label>
                        <input type="date" name="data_inicio">
                    </div>

                    <div class="filter-group">
                        <label>Data Fim</label>
                        <input type="date" name="data_fim">
                    </div>

                    <div class="filter-group">
                        <label>Buscar</label>
                        <input type="text" name="busca" placeholder="Nome, CPF, descrição...">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">🔍 Buscar</button>
                    <a href="logs.php" class="btn-clear">✕ Limpar</a>
                </div>
            </form>
        </div>

        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total de Logs</div>
                <div class="stat-value"><?php echo number_format($stats['total_logs']); ?></div>
            </div>

            <div class="stat-card insert">
                <div class="stat-label">Registros Criados</div>
                <div class="stat-value"><?php echo $acoes['INSERT']; ?></div>
            </div>

            <div class="stat-card update">
                <div class="stat-label">Registros Editados</div>
                <div class="stat-value"><?php echo $acoes['UPDATE']; ?></div>
            </div>

            <div class="stat-card delete">
                <div class="stat-label">Registros Deletados</div>
                <div class="stat-value"><?php echo $acoes['DELETE']; ?></div>
            </div>
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
                                <p style="color: var(--text-muted);">Nenhum log encontrado</p>
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
                                    <div class="log-actions">
                                        <a href="logs.php?action=show&id=<?php echo $log['id_log']; ?>" class="btn-view">Ver</a>
                                    </div>
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
                    <a href="logs.php?page=1">« Primeira</a>
                    <a href="logs.php?page=<?php echo $pagination['current_page'] - 1; ?>">‹ Anterior</a>
                <?php endif; ?>

                <?php 
                $start = max(1, $pagination['current_page'] - 2);
                $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="logs.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="logs.php?page=<?php echo $pagination['current_page'] + 1; ?>">Próxima ›</a>
                    <a href="logs.php?page=<?php echo $pagination['last_page']; ?>">Última »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleFilters() {
            const filters = document.getElementById('filters');
            filters.classList.toggle('active');
        }

        // Auto-expandir filtros se houver parâmetros
        const params = new URLSearchParams(window.location.search);
        if (params.get('action') === 'search') {
            document.getElementById('filters').classList.add('active');
        }
    </script>
</body>
</html>
