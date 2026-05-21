<?php
/**
 * Detalhes de um Log específico
 */
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Log - Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .log-detail-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .log-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .log-detail-header h1 {
            font-size: 24px;
            color: var(--text-primary);
            margin: 0;
        }

        .btn-back {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-back:hover {
            background: #7f8c8d;
        }

        .log-detail-card {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .log-detail-section {
            margin-bottom: 25px;
        }

        .log-detail-section:last-child {
            margin-bottom: 0;
        }

        .log-detail-title {
            font-size: 14px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .log-detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .log-detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .log-detail-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 13px;
        }

        .log-detail-value {
            color: var(--text-secondary);
            font-size: 13px;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
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

        .code-block {
            background: rgba(0, 0, 0, 0.05);
            border-left: 3px solid #3498db;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
        }

        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .comparison-item {
            background: rgba(0, 0, 0, 0.02);
            padding: 12px;
            border-radius: 4px;
        }

        .comparison-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .comparison-value {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: var(--text-primary);
            word-break: break-word;
            max-height: 200px;
            overflow-y: auto;
        }

        .comparison-old {
            border-left: 3px solid #e74c3c;
        }

        .comparison-new {
            border-left: 3px solid #27ae60;
        }

        @media (max-width: 768px) {
            .log-detail-row {
                grid-template-columns: 1fr;
            }

            .comparison {
                grid-template-columns: 1fr;
            }

            .log-detail-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="log-detail-container">
        <!-- Header -->
        <div class="log-detail-header">
            <h1>📋 Detalhes do Log</h1>
            <a href="logs.php" class="btn-back">← Voltar</a>
        </div>

        <!-- Informações Principais -->
        <div class="log-detail-card">
            <div class="log-detail-section">
                <div class="log-detail-title">Informações Gerais</div>
                
                <div class="log-detail-row">
                    <div class="log-detail-label">ID do Log</div>
                    <div class="log-detail-value">#<?php echo $log['id_log']; ?></div>
                </div>

                <div class="log-detail-row">
                    <div class="log-detail-label">Ação</div>
                    <div class="log-detail-value">
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
                    </div>
                </div>

                <div class="log-detail-row">
                    <div class="log-detail-label">Tabela Afetada</div>
                    <div class="log-detail-value">
                        <strong><?php echo htmlspecialchars($log['tabela_afetada']); ?></strong>
                    </div>
                </div>

                <div class="log-detail-row">
                    <div class="log-detail-label">Data/Hora</div>
                    <div class="log-detail-value">
                        <?php echo date('d/m/Y H:i:s', strtotime($log['data_alteracao'])); ?>
                    </div>
                </div>

                <div class="log-detail-row">
                    <div class="log-detail-label">Usuário</div>
                    <div class="log-detail-value">
                        <?php 
                        if ($usuario) {
                            echo htmlspecialchars($usuario['nome']) . ' (' . htmlspecialchars($usuario['email']) . ')';
                        } else {
                            echo '<span style="color: var(--text-muted);">Sistema (Automático)</span>';
                        }
                        ?>
                    </div>
                </div>

                <?php if ($log['ip_usuario']): ?>
                    <div class="log-detail-row">
                        <div class="log-detail-label">IP do Usuário</div>
                        <div class="log-detail-value"><?php echo htmlspecialchars($log['ip_usuario']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Descrição do Registro -->
        <div class="log-detail-card">
            <div class="log-detail-section">
                <div class="log-detail-title">Registro Alterado</div>
                
                <div class="log-detail-row">
                    <div class="log-detail-label">Descrição</div>
                    <div class="log-detail-value">
                        <?php echo htmlspecialchars($log['registro_alt']); ?>
                    </div>
                </div>

                <?php if ($log['id_registro']): ?>
                    <div class="log-detail-row">
                        <div class="log-detail-label">ID do Registro</div>
                        <div class="log-detail-value">
                            <a href="logs.php?action=historico&id=<?php echo $log['id_registro']; ?>" style="color: #3498db; text-decoration: none;">
                                #<?php echo $log['id_registro']; ?> (Ver histórico)
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($log['campo_alterado']): ?>
                    <div class="log-detail-row">
                        <div class="log-detail-label">Campo Alterado</div>
                        <div class="log-detail-value"><?php echo htmlspecialchars($log['campo_alterado']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comparação de Valores -->
        <?php if ($log['valor_anterior'] || $log['valor_atual']): ?>
            <div class="log-detail-card">
                <div class="log-detail-section">
                    <div class="log-detail-title">Comparação de Valores</div>
                    
                    <div class="comparison">
                        <div class="comparison-item comparison-old">
                            <div class="comparison-label">❌ Valor Anterior</div>
                            <div class="comparison-value">
                                <?php 
                                if ($log['valor_anterior']) {
                                    echo htmlspecialchars($log['valor_anterior']);
                                } else {
                                    echo '<span style="color: var(--text-muted);">Novo registro (sem valor anterior)</span>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="comparison-item comparison-new">
                            <div class="comparison-label">✅ Valor Atual</div>
                            <div class="comparison-value">
                                <?php 
                                if ($log['valor_atual']) {
                                    echo htmlspecialchars($log['valor_atual']);
                                } else {
                                    echo '<span style="color: var(--text-muted);">Registro deletado</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- JSON Raw (para desenvolvedores) -->
        <div class="log-detail-card">
            <div class="log-detail-section">
                <div class="log-detail-title">Dados Brutos (JSON)</div>
                <div class="code-block">
                    <pre><?php echo json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
