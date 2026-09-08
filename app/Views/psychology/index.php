<?php
$stats = $stats ?? [];

$stats['total_patients'] = $stats['total_patients'] ?? 0;
$stats['total_notes'] = $stats['total_notes'] ?? 0;
$stats['notes_this_month'] = $stats['notes_this_month'] ?? 0;
$stats['active_treatments'] = $stats['active_treatments'] ?? 0;

// Faixas etárias com as regras solicitadas
$stats['by_age_group'] = $stats['by_age_group'] ?? [
    'crianca' => 0,         // 6 a 12 anos
    'preadolescente' => 0,  // 12 a 15 anos
    'adolescente' => 0,     // 15 a 18 anos
];

$stats['by_note_type'] = $stats['by_note_type'] ?? [
    'consulta' => 0,
    'avaliacao' => 0,
    'evolucao' => 0,
    'observacao' => 0,
];
?>
<div class="psychology-header card-glass mb-4" style="border-left: 4px solid var(--primary-blue); padding: 24px;">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div style="font-size: 32px; color: var(--primary-blue);">
            <i class="fas fa-brain"></i>
        </div>
        <div>
            <h1 class="m-0" style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Área Psicológica</h1>
            <p class="m-0 text-muted" style="font-size: 15px;">Acompanhamento e avaliação psicológica especializada</p>
        </div>
    </div>
    
    <div style="background: rgba(0, 0, 0, 0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px;">
        <div style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">
            <i class="fas fa-user-shield text-muted"></i> Área Confidencial e Exclusiva
        </div>
        <div class="text-muted" style="font-size: 13px; line-height: 1.4;">
            Esta área é privada e exclusiva para psicólogos. Todas as informações aqui são confidenciais e protegidas pelo sigilo profissional.
        </div>
    </div>
</div>

<div class="actions flex-between flex-wrap gap-2 mb-4">
    <div></div>
    <div class="d-flex gap-2">
        <a href="psychology.php?action=patients" class="btn secondary">
            <i class="fas fa-users"></i> Ver Pacientes
        </a>
        <a href="psychology.php?action=report" class="btn success">
            <i class="fas fa-chart-line"></i> Relatórios
        </a>
    </div>
</div>

<!-- Estatísticas -->
<div class="stats-row mb-4">
    <div class="stat-card-glass">
        <div class="d-flex align-items-center gap-3">
            <div style="font-size: 28px; color: var(--text-muted);">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="stat-number" style="color: var(--text-primary); font-size: 28px;"><?php echo $stats['total_patients']; ?></div>
                <div class="stat-label text-muted">Total de Pacientes</div>
            </div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="d-flex align-items-center gap-3">
            <div style="font-size: 28px; color: var(--text-muted);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="stat-number" style="color: var(--text-primary); font-size: 28px;"><?php echo $stats['total_notes']; ?></div>
                <div class="stat-label text-muted">Total de Anotações</div>
            </div>
        </div>
    </div>
    
    <div class="stat-card-glass">
        <div class="d-flex align-items-center gap-3">
            <div style="font-size: 28px; color: var(--text-muted);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-number" style="color: var(--text-primary); font-size: 28px;"><?php echo $stats['notes_this_month']; ?></div>
                <div class="stat-label text-muted">Anotações este Mês</div>
            </div>
        </div>
    </div>
</div>

<!-- Distribuição por Faixa Etária e Tipos -->
<div class="charts-grid mb-4">
    <div class="card-glass p-4">
        <h3 class="d-flex align-items-center gap-2 mb-3" style="color: var(--text-primary); font-size: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <i class="fas fa-chart-pie text-muted"></i> Distribuição por Faixa Etária
        </h3>
        
        <div class="age-groups">
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-child"></i>
                    <span>Crianças (6 a 12 anos)</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_age_group']['crianca']; ?></span>
            </div>
            
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-user"></i>
                    <span>Pré-Adolescentes (12 a 15 anos)</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_age_group']['preadolescente']; ?></span>
            </div>
            
            <div class="d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-user-graduate"></i>
                    <span>Adolescentes (15 a 18 anos)</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_age_group']['adolescente']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="card-glass p-4">
        <h3 class="d-flex align-items-center gap-2 mb-3" style="color: var(--text-primary); font-size: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <i class="fas fa-tags text-muted"></i> Tipos de Anotações
        </h3>
        
        <div class="note-types">
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-comments"></i>
                    <span>Consultas</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_note_type']['consulta']; ?></span>
            </div>
            
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Avaliações</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_note_type']['avaliacao']; ?></span>
            </div>
            
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-chart-line"></i>
                    <span>Evoluções</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_note_type']['evolucao']; ?></span>
            </div>
            
            <div class="d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 14px;">
                    <i class="fas fa-eye"></i>
                    <span>Observações</span>
                </div>
                <span style="font-weight: 600; color: var(--text-primary);"><?php echo $stats['by_note_type']['observacao']; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Anotações Recentes -->
<div class="recent-notes card-glass p-4">
    <h3 class="d-flex align-items-center gap-2 mb-3" style="color: var(--text-primary); font-size: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
        <i class="fas fa-history text-muted"></i> Anotações Recentes
    </h3>
    
    <?php if (empty($recentNotes)): ?>
        <div class="empty-state text-center p-4 text-muted">
            <div style="font-size: 32px; margin-bottom: 12px;"><i class="fas fa-folder-open"></i></div>
            <div style="font-size: 16px; font-weight: 600; margin-bottom: 6px;">Nenhuma anotação ainda</div>
            <div style="font-size: 14px;">Comece criando anotações para seus pacientes</div>
        </div>
    <?php else: ?>
        <div class="notes-list">
            <?php foreach ($recentNotes as $note): ?>
                <div class="note-item py-3" style="border-bottom: 1px solid var(--border-color); display: flex; gap: 16px;">
                    <div class="note-icon text-muted" style="font-size: 20px; margin-top: 2px;">
                        <?php
                        $icons = [
                            'consulta' => 'fa-comments',
                            'avaliacao' => 'fa-clipboard-list',
                            'evolucao' => 'fa-chart-line',
                            'observacao' => 'fa-eye'
                        ];
                        $iconClass = $icons[$note['note_type']] ?? 'fa-file-alt';
                        echo "<i class=\"fas {$iconClass}\"></i>";
                        ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($note['title'] ?: ucfirst($note['note_type'])); ?>
                                </div>
                                <div class="text-muted" style="font-size: 13px;">
                                    Paciente: <strong><?php echo htmlspecialchars($note['patient_cpf']); ?></strong>
                                </div>
                            </div>
                            <div class="text-muted" style="font-size: 12px;">
                                <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div style="font-size: 14px; color: var(--text-muted); line-height: 1.5; margin-top: 6px;">
                            <?php 
                            $content = htmlspecialchars($note['content']);
                            echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="psychology.php?action=patients" class="btn secondary">
                Ver Todos os Pacientes
            </a>
        </div>
    <?php endif; ?>
</div>
