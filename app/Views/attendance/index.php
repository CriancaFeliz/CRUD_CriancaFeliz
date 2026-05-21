<div class="attendance-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <p style="margin: 5px 0 0 0; color: #666;">Gerencie presenças, faltas e desligamentos</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="attendance.php?action=relatorios" class="btn" style="background: #3498db;">
                <i class="fas fa-chart-bar"></i> Relatórios
            </a>
            <a href="attendance.php?action=alertas" class="btn" style="background: #ff9800;">
                <i class="fas fa-exclamation-triangle"></i> Ver Alertas
            </a>
        </div>
    </div>

    <!-- Busca -->
    <div class="search-box" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <form method="GET" action="attendance.php" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Buscar por nome ou CPF..." 
                   value="<?php echo htmlspecialchars($search ?? ''); ?>"
                   style="flex: 1; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            <button type="submit" class="btn" style="background: #ff7a00;"><i class="fas fa-search"></i> Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="attendance.php" class="btn secondary">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabela de Atendidos -->
    <div class="table-container" style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #ff7a00; color: white;">
                <tr>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Nº</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Nome</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600;">Total de Presenças</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600;">Faltas Justificadas</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600;">Faltas Não Justificadas</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600;">Última Atividade</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($atendidos)): ?>
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #999;">
                            Nenhum atendido encontrado
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($atendidos as $index => $atendido): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0; <?php echo $atendido['desligado'] ? 'opacity: 0.5; background: #f5f5f5;' : ''; ?>">
                            <td style="padding: 15px;"><?php echo $index + 1; ?></td>
                            <td style="padding: 15px;">
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($atendido['nome_completo'] ?? 'Sem nome'); ?>
                                    <?php if ($atendido['desligado']): ?>
                                        <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;">DESLIGADO</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 12px; color: #666;">
                                    CPF: <?php echo htmlspecialchars($atendido['cpf'] ?? 'Não informado'); ?>
                                    <?php if (isset($atendido['idade'])): ?>
                                        | Idade: <?php echo $atendido['idade']; ?> anos
                                    <?php endif; ?>
                                </div>
                                <?php if ($atendido['tem_alerta']): ?>
                                    <div style="margin-top: 5px;">
                                        <span style="background: #ff9800; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px;">
                                            ⚠️ ATENÇÃO
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 600; color: #27ae60;">
                                <?php echo $atendido['total_presencas'] ?? 0; ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 600; color: #3498db;">
                                <?php echo $atendido['faltas_justificadas'] ?? 0; ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 600; color: <?php echo ($atendido['faltas_nao_justificadas'] ?? 0) >= 5 ? '#e74c3c' : '#f39c12'; ?>">
                                <?php echo $atendido['faltas_nao_justificadas'] ?? 0; ?>
                                <?php if (($atendido['faltas_nao_justificadas'] ?? 0) >= 5): ?>
                                    <span style="font-size: 16px; margin-left: 5px;">⚠️</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-size: 13px; color: #666;">
                                <?php 
                                if (!empty($atendido['ultima_atividade'])) {
                                    $date = DateTime::createFromFormat('Y-m-d', $atendido['ultima_atividade']);
                                    echo $date ? $date->format('d/m/Y') : $atendido['ultima_atividade'];
                                } else {
                                    echo 'Sem registro';
                                }
                                ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="attendance.php?action=show&id=<?php echo htmlspecialchars($atendido['id'] ?? ($atendido['idatendido'] ?? '')); ?>" 
                                   class="btn-icon" 
                                   title="Ver detalhes"
                                   style="background: #3498db; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; display: inline-block; font-size: 18px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <a href="attendance.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   class="btn <?php echo $i === $pagination['current_page'] ? '' : 'secondary'; ?>"
                   style="min-width: 40px; text-align: center;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <!-- Legenda -->
    <div class="legend" style="background: #fff; padding: 20px; border-radius: 12px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">Legenda</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 20px; height: 20px; background: #27ae60; border-radius: 4px;"></div>
                <span>Presenças registradas</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 20px; height: 20px; background: #3498db; border-radius: 4px;"></div>
                <span>Faltas justificadas</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 20px; height: 20px; background: #f39c12; border-radius: 4px;"></div>
                <span>Faltas não justificadas (1-4)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 20px; height: 20px; background: #e74c3c; border-radius: 4px;"></div>
                <span>Excesso de faltas (5+) ⚠️</span>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-icon:hover {
        opacity: 0.8;
        transform: scale(1.05);
        transition: all 0.2s;
    }
    
    table tbody tr:hover {
        background: #f8f9fa;
    }
    
    @media (max-width: 768px) {
        .table-container {
            overflow-x: auto;
        }
        
        table {
            min-width: 800px;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }
    }
</style>
