<!-- Lista de Desligamentos -->
<style>
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }
    .stat-card h3 {
        margin: 0;
        font-size: 28px;
        color:#3E6475;
    }
    .stat-card p {
        margin: 8px 0 0 0;
        color: #666;
        font-size: 13px;
    }
    .filtros-container {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .filtros-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-weight: 600;
        font-size: 14px;
    }
    .form-group select,
    .form-group input {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
    }
    .tabela-desligamentos {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .tabela-desligamentos table {
        width: 100%;
        border-collapse: collapse;
    }
    .tabela-desligamentos th {
        background:#3E6475;
        color: #fff;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    .tabela-desligamentos td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    .tabela-desligamentos tr:hover {
        background: #f8f9fa;
    }
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-idade { background: #d1ecf1; color: #0c5460; }
    .badge-faltas { background: #f8d7da; color: #721c24; }
    .badge-pedido { background: #d4edda; color: #155724; }
    .badge-transferencia { background: #fff3cd; color: #856404; }
    .badge-outros { background: #e2e3e5; color: #383d41; }
    .btn-reativar {
        padding: 6px 12px;
        background: #28a745;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }
    .btn-reativar:hover {
        opacity: 0.9;
    }
    .actions-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
</style>

<?php
if ($_SESSION['user_role'] !== 'admin') { 
    echo "<h2 style='text-align:center;margin-top:40px;color:red;'>Acesso negado</h2>";
    exit;
}
?>

<!-- Estatísticas -->
<div class="stats-row">
    <div class="stat-card">
        <h3><?php echo $estatisticas['total'] ?? 0; ?></h3>
        <p>Total</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatisticas['por_idade'] ?? 0; ?></h3>
        <p>Por Idade</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatisticas['por_faltas'] ?? 0; ?></h3>
        <p>Excesso Faltas</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatisticas['por_pedido'] ?? 0; ?></h3>
        <p>Pedido Família</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatisticas['automaticos'] ?? 0; ?></h3>
        <p>Automáticos</p>
    </div>
</div>

<!-- Ações -->
<div class="actions-top">
    <h3 style="margin: 0;"><i class="fas fa-list"></i> Lista de Desligamentos</h3>
    <div style="display: flex; gap: 10px;">
        <a href="desligamento.php?action=novo" class="btn" style="background: #dc3545;">
            <i class="fas fa-user-times"></i> Novo Desligamento
        </a>
        <button onclick="processarDesligamentoAutomatico()" class="btn">
            <i class="fas fa-robot"></i> Processar Automático
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="filtros-container">
    <form method="GET" action="desligamento.php">
        <div class="filtros-row" style="grid-template-columns: 1fr auto;">
            <div class="form-group">
                <label>Tipo de Motivo</label>
                <select name="tipo_motivo" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="idade" <?php echo ($filtros['tipo_motivo'] === 'idade') ? 'selected' : ''; ?>>Idade</option>
                    <option value="excesso_faltas" <?php echo ($filtros['tipo_motivo'] === 'excesso_faltas') ? 'selected' : ''; ?>>Excesso de Faltas</option>
                    <option value="pedido_familia" <?php echo ($filtros['tipo_motivo'] === 'pedido_familia') ? 'selected' : ''; ?>>Pedido da Família</option>
                    <option value="transferencia" <?php echo ($filtros['tipo_motivo'] === 'transferencia') ? 'selected' : ''; ?>>Transferência</option>
                    <option value="outros" <?php echo ($filtros['tipo_motivo'] === 'outros') ? 'selected' : ''; ?>>Outros</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Tabela -->
<div class="tabela-desligamentos">
    <table>
        <thead>
            <tr>
                <th>Atendido</th>
                <th>CPF</th>
                <th>Motivo</th>
                <th>Tipo</th>
                <th>Data</th>
                <th>Automático</th>
                <th style="text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($desligamentos)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                        Nenhum desligamento encontrado
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($desligamentos as $desl): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($desl['atendido_nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($desl['cpf'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($desl['motivo']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $desl['tipo_motivo']; ?>">
                                <?php 
                                    $tipos = [
                                        'idade' => 'Idade',
                                        'excesso_faltas' => 'Excesso Faltas',
                                        'pedido_familia' => 'Pedido Família',
                                        'transferencia' => 'Transferência',
                                        'outros' => 'Outros'
                                    ];
                                    echo $tipos[$desl['tipo_motivo']] ?? 'N/A';
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($desl['data_desligamento'])); ?></td>
                        <td><?php echo $desl['automatico'] ? 'Sim' : 'Não'; ?></td>
                        <td style="text-align: center;">
                            <?php 
                            // Verificar se o atendido ainda está desligado
                            $isStillDisconnected = true;
                            try {
                                $desligamentoDB = new DesligamentoDB();
                                $isStillDisconnected = $desligamentoDB->isDesligado($desl['id_atendido']);
                            } catch (Exception $e) {
                                // Em caso de erro, assumir que está desligado
                            }
                            
                            if ($isStillDisconnected && $desl['pode_retornar']): ?>
                                <button onclick="reativarAtendido(<?php echo $desl['id_atendido']; ?>)" class="btn-reativar">
                                    <i class="fas fa-undo"></i> Reativar
                                </button>
                            <?php else: ?>
                                <span style="color: #999; font-size: 13px;">
                                    <?php echo $isStillDisconnected ? 'Não permitido' : 'Já reativado'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const csrfToken = '<?php echo $csrf_token ?? ''; ?>';

function reativarAtendido(idAtendido) {
    if (!confirm('Deseja realmente reativar este atendido?')) return;
    
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('id_atendido', idAtendido);
    
    fetch('desligamento.php?action=reativar', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(async response => {
        // Tentar fazer parse do JSON mesmo se status não for ok
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Erro na requisição');
            }
            return data;
        } else {
            // Se não for JSON, pegar texto para debug
            const text = await response.text();
            console.error('Resposta não é JSON:', text.substring(0, 200));
            throw new Error('Resposta inválida do servidor. Status: ' + response.status);
        }
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Erro ao reativar');
        }
    })
    .catch(error => {
        console.error('Erro completo:', error);
        alert('Erro ao processar requisição: ' + error.message + '\n\nTente recarregar a página.');
    });
}

function processarDesligamentoAutomatico() {
    if (!confirm('Deseja processar desligamentos automáticos por excesso de faltas?')) return;
    
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    
    fetch('desligamento.php?action=automatico', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(async response => {
        // Tentar fazer parse do JSON mesmo se status não for ok
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Erro na requisição');
            }
            return data;
        } else {
            // Se não for JSON, pegar texto para debug
            const text = await response.text();
            console.error('Resposta não é JSON:', text.substring(0, 200));
            throw new Error('Resposta inválida do servidor. Status: ' + response.status);
        }
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Erro ao processar');
        }
    })
    .catch(error => {
        console.error('Erro completo:', error);
        alert('Erro ao processar requisição: ' + error.message + '\n\nTente recarregar a página.');
    });
}
</script>
