<!-- Histórico de Frequência do Atendido -->
<style>
    .atendido-header {
        background: linear-gradient(135deg, #3E6475 0%,#348cb4 100%);
        color: #fff;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
    }
    .atendido-header h2 {
        margin: 0 0 10px 0;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        font-size: 32px;
        color:#3E6475;
    }
    .stat-card p {
        margin: 8px 0 0 0;
        color: #666;
        font-size: 14px;
    }
    .section {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .section h3 {
        margin: 0 0 15px 0;
        color: #0e2a33;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #ddd;
    }
    .timeline-item {
        position: relative;
        padding: 15px 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 20px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background:#3E6475;
        border: 3px solid #fff;
    }
    .timeline-item.falta::before {
        background: #dc3545;
    }
    .timeline-item.justificada::before {
        background: #ffc107;
    }
    .timeline-date {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    .timeline-oficina {
        color:#3E6475;
        font-weight: 500;
    }
    .timeline-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }
    .status-presente {
        background: #d4edda;
        color: #155724;
    }
    .status-falta {
        background: #f8d7da;
        color: #721c24;
    }
    .status-justificada {
        background: #fff3cd;
        color: #856404;
    }
    .actions {
        margin-top: 20px;
    }
</style>

<!-- Header do Atendido -->
<div class="atendido-header">
    <h2><i class="fas fa-user"></i> <?php echo htmlspecialchars($atendido['nome']); ?></h2>
    <p><strong>CPF:</strong> <?php echo htmlspecialchars($atendido['cpf'] ?? 'N/A'); ?></p>
    <p><strong>Data Nascimento:</strong> <?php echo isset($atendido['data_nascimento']) ? date('d/m/Y', strtotime($atendido['data_nascimento'])) : 'N/A'; ?></p>
</div>

<!-- Estatísticas Gerais -->
<h3 style="margin-bottom: 15px;"><i class="fas fa-chart-bar"></i> Estatísticas Gerais</h3>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $estatsDia['presencas'] ?? 0; ?></h3>
        <p>Presenças (Dia)</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatsDia['faltas'] ?? 0; ?></h3>
        <p>Faltas (Dia)</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatsDia['justificadas'] ?? 0; ?></h3>
        <p>Justificadas (Dia)</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatsDia['percentual_presenca'] ?? 0; ?>%</h3>
        <p>% Presença (Dia)</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $estatsOficina['presencas'] ?? 0; ?></h3>
        <p>Presenças (Oficinas)</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatsOficina['faltas'] ?? 0; ?></h3>
        <p>Faltas (Oficinas)</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatsOficina['justificadas'] ?? 0; ?></h3>
        <p>Justificadas (Oficinas)</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $estatsOficina['percentual_presenca'] ?? 0; ?>%</h3>
        <p>% Presença (Oficinas)</p>
    </div>
</div>

<!-- Histórico por Dia -->
<div class="section">
    <h3><i class="fas fa-calendar-day"></i> Histórico - Por Dia</h3>
    <?php if (empty($frequenciasDia)): ?>
        <p style="color: #999; text-align: center; padding: 20px;">Nenhum registro encontrado</p>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($frequenciasDia as $freq): ?>
                <div class="timeline-item <?php echo ($freq['status'] === 'F') ? 'falta' : (($freq['status'] === 'J') ? 'justificada' : ''); ?>">
                    <div class="timeline-date">
                        <?php echo date('d/m/Y', strtotime($freq['data'])); ?>
                        <span class="timeline-status status-<?php echo ($freq['status'] === 'P') ? 'presente' : (($freq['status'] === 'J') ? 'justificada' : 'falta'); ?>">
                            <?php 
                                if ($freq['status'] === 'P') echo 'Presente';
                                elseif ($freq['status'] === 'J') echo 'Justificada';
                                else echo 'Falta';
                            ?>
                        </span>
                    </div>
                    <?php if (!empty($freq['justificativa'])): ?>
                        <div style="color: #666; font-size: 14px; margin-top: 5px;">
                            <strong>Justificativa:</strong> <?php echo htmlspecialchars($freq['justificativa']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($freq['registrado_por_nome'])): ?>
                        <div style="color: #999; font-size: 12px; margin-top: 5px;">
                            Registrado por: <?php echo htmlspecialchars($freq['registrado_por_nome']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Histórico por Oficina -->
<div class="section">
    <h3><i class="fas fa-chalkboard-teacher"></i> Histórico - Por Oficina</h3>
    <?php if (empty($frequenciasOficina)): ?>
        <p style="color: #999; text-align: center; padding: 20px;">Nenhum registro encontrado</p>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($frequenciasOficina as $freq): ?>
                <div class="timeline-item <?php echo ($freq['status'] === 'F') ? 'falta' : (($freq['status'] === 'J') ? 'justificada' : ''); ?>">
                    <div class="timeline-date">
                        <?php echo date('d/m/Y', strtotime($freq['data'])); ?>
                        <span class="timeline-oficina"><?php echo htmlspecialchars($freq['oficina_nome']); ?></span>
                        <span class="timeline-status status-<?php echo ($freq['status'] === 'P') ? 'presente' : (($freq['status'] === 'J') ? 'justificada' : 'falta'); ?>">
                            <?php 
                                if ($freq['status'] === 'P') echo 'Presente';
                                elseif ($freq['status'] === 'J') echo 'Justificada';
                                else echo 'Falta';
                            ?>
                        </span>
                    </div>
                    <?php if (!empty($freq['justificativa'])): ?>
                        <div style="color: #666; font-size: 14px; margin-top: 5px;">
                            <strong>Justificativa:</strong> <?php echo htmlspecialchars($freq['justificativa']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($freq['registrado_por_nome'])): ?>
                        <div style="color: #999; font-size: 12px; margin-top: 5px;">
                            Registrado por: <?php echo htmlspecialchars($freq['registrado_por_nome']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Ações -->
<div class="actions">
    <a href="faltas.php" class="btn secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>
