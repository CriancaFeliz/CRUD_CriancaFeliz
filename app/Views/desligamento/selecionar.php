<!-- Selecionar Atendido para Desligar -->
<style>
    .busca-container {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .busca-row {
        display: flex;
        gap: 15px;
    }
    .busca-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
    }
    .tabela-atendidos {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .tabela-atendidos table {
        width: 100%;
        border-collapse: collapse;
    }
    .tabela-atendidos th {
        background: #3E6475;
        color: #fff;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    .tabela-atendidos td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    .tabela-atendidos tr:hover {
        background: #f8f9fa;
    }
    .btn-desligar {
        padding: 8px 16px;
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
    }
    .btn-desligar:hover {
        opacity: 0.9;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
</style>

<!-- Busca -->
<div class="busca-container">
    <form method="GET" action="desligamento.php">
        <input type="hidden" name="action" value="novo">
        <div class="busca-row">
            <input type="text" name="search" class="busca-input" 
                   placeholder="Buscar por nome ou CPF..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </form>
</div>

<!-- Tabela de Atendidos -->
<div class="tabela-atendidos">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Data Nascimento</th>
                <th style="text-align: center;">Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($atendidos)): ?>
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Nenhum atendido encontrado</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($atendidos as $atendido): ?>
                    <?php $id = $atendido['idatendido'] ?? $atendido['id']; ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($atendido['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            if (!empty($atendido['data_nascimento'])) {
                                $data = new DateTime($atendido['data_nascimento']);
                                echo $data->format('d/m/Y');
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="desligamento.php?action=novo&id=<?php echo $id; ?>" class="btn-desligar">
                                <i class="fas fa-user-times"></i> Desligar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
