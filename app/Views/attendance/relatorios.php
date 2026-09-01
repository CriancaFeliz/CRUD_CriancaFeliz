<div class="relatorios-page">
    <!-- Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>   
            <p style="margin: 5px 0 0 0; color: #666;">Análises e estatísticas completas do sistema</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="attendance.php" class="btn" style="background: #6c757d;"><i class="fas fa-arrow-left"></i> Voltar</a>
            <button onclick="imprimirRelatorio()" class="btn" style="background: #3498db;"><i class="fas fa-print"></i> Imprimir</button>
            <button onclick="exportarCSV()" class="btn" style="background: #27ae60;"><i class="fas fa-file-download"></i> Exportar CSV</button>
        </div>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="stats-overview" style="margin-bottom: 30px;">
        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 15px;"><i class="fas fa-chart-line"></i> Visão Geral</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #3498db;">
                <div style="font-size: 28px; font-weight: 700; color: #3498db;">
                    <?php echo $estatisticas['total_atendidos']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Total de Atendidos</div>
                <div style="font-size: 11px; color: #999; margin-top: 3px;">
                    <?php echo $estatisticas['total_atendidos_ativos']; ?> ativos • 
                    <?php echo $estatisticas['total_atendidos_desligados']; ?> desligados
                </div>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #27ae60;">
                <div style="font-size: 28px; font-weight: 700; color: #27ae60;">
                    <?php echo $estatisticas['total_presencas']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Total de Presenças</div>
                <div style="font-size: 11px; color: #999; margin-top: 3px;">
                    Taxa geral: <?php echo $estatisticas['taxa_presenca_geral']; ?>%
                </div>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #f39c12;">
                <div style="font-size: 28px; font-weight: 700; color: #f39c12;">
                    <?php echo $estatisticas['total_faltas']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Total de Faltas</div>
                <div style="font-size: 11px; color: #999; margin-top: 3px;">
                    <?php echo $estatisticas['total_faltas_justificadas']; ?> justificadas • 
                    <?php echo $estatisticas['total_faltas_nao_justificadas']; ?> não justificadas
                </div>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #e74c3c;">
                <div style="font-size: 28px; font-weight: 700; color: #e74c3c;">
                    <?php echo $estatisticas['atendidos_com_alertas']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Atendidos com Alertas</div>
                <div style="font-size: 11px; color: #999; margin-top: 3px;">
                    <?php echo $estatisticas['atendidos_excesso_faltas']; ?> excesso faltas • 
                    <?php echo $estatisticas['atendidos_idade_limite']; ?> idade limite
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="charts-section" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Gráfico por Atividade -->
        <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;"><i class="fas fa-clipboard-list"></i> Frequência por Atividade</h3>
            <canvas id="chartAtividade" style="max-height: 300px;"></canvas>
        </div>
        
        <!-- Gráfico por Mês -->
        <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600;"><i class="fas fa-calendar-alt"></i> Frequência por Mês</h3>
            <canvas id="chartMes" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Filtros e Relatório Detalhado -->
    <div class="detailed-report" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 15px;"><i class="fas fa-search"></i> Relatório Detalhado</h2>
        
        <!-- Filtros -->
        <form method="GET" action="attendance.php" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <input type="hidden" name="action" value="relatorios">
            
            <select name="status" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                <option value="">Todos os status</option>
                <option value="ativo" <?php echo $filtros['status'] === 'ativo' ? 'selected' : ''; ?>>Apenas Ativos</option>
                <option value="desligado" <?php echo $filtros['status'] === 'desligado' ? 'selected' : ''; ?>>Apenas Desligados</option>
            </select>
            
            <select name="alerta" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                <option value="">Todos os alertas</option>
                <option value="com_alerta" <?php echo $filtros['alerta'] === 'com_alerta' ? 'selected' : ''; ?>>Com Alertas</option>
                <option value="sem_alerta" <?php echo $filtros['alerta'] === 'sem_alerta' ? 'selected' : ''; ?>>Sem Alertas</option>
            </select>
            
            <input type="number" name="min_faltas" placeholder="Mín. faltas não justificadas" 
                   value="<?php echo htmlspecialchars($filtros['min_faltas']); ?>"
                   style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; width: 220px;">
            
            <button type="submit" class="btn" style="background: #3498db; border: none; padding: 10px 20px; border-radius: 8px; color: white; cursor: pointer;">
                Aplicar Filtros
            </button>
            
            <a href="attendance.php?action=relatorios" class="btn" style="background: #6c757d; border: none; padding: 10px 20px; border-radius: 8px; color: white; text-decoration: none; display: inline-block;">
                Limpar
            </a>
        </form>
        
        <!-- Tabela de Resultados -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Nome</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Presenças</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Faltas Just.</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Faltas Não Just.</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Taxa</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($relatorioGeral)): ?>
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: #999;">
                                Nenhum resultado encontrado com os filtros aplicados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($relatorioGeral as $item): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 12px;">
                                    <div style="font-weight: 600;">
                                        <?php echo htmlspecialchars($item['atendido']['nome_completo'] ?? ($item['atendido']['nome'] ?? '')); ?>
                                    </div>
                                    <div style="font-size: 12px; color: #666;">
                                        CPF: <?php echo htmlspecialchars($item['atendido']['cpf'] ?? ''); ?>
                                    </div>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="color: #27ae60; font-weight: 600;">
                                        <?php echo $item['stats']['total_presencas']; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="color: #3498db; font-weight: 600;">
                                        <?php echo $item['stats']['faltas_justificadas']; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="color: <?php echo $item['stats']['faltas_nao_justificadas'] >= 5 ? '#e74c3c' : '#f39c12'; ?>; font-weight: 600;">
                                        <?php echo $item['stats']['faltas_nao_justificadas']; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="font-size: 16px; font-weight: 700; color: <?php 
                                        echo $item['stats']['percentual_presenca'] >= 80 ? '#27ae60' : 
                                             ($item['stats']['percentual_presenca'] >= 60 ? '#f39c12' : '#e74c3c'); 
                                    ?>;">
                                        <?php echo $item['stats']['percentual_presenca']; ?>%
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if ($item['stats']['desligado']): ?>
                                        <span style="background: #e74c3c; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            DESLIGADO
                                        </span>
                                    <?php elseif (!empty($item['stats']['alertas'])): ?>
                                        <span style="background: #f39c12; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            <i class="fas fa-exclamation-triangle"></i> ALERTA
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #27ae60; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            ATIVO
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="attendance.php?action=show&id=<?php echo htmlspecialchars($item['atendido']['id'] ?? ($item['atendido']['idatendido'] ?? '')); ?>" 
                                       style="color: #3498db; text-decoration: none; font-size: 18px;" 
                                       title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Dados para os gráficos
const dadosAtividade = <?php echo json_encode($estatisticas['por_atividade']); ?>;
const dadosMes = <?php echo json_encode($estatisticas['por_mes']); ?>;

// Gráfico por Atividade
const ctxAtividade = document.getElementById('chartAtividade').getContext('2d');
new Chart(ctxAtividade, {
    type: 'bar',
    data: {
        labels: Object.keys(dadosAtividade),
        datasets: [
            {
                label: 'Presenças',
                data: Object.values(dadosAtividade).map(d => d.presencas),
                backgroundColor: '#27ae60',
                borderRadius: 8
            },
            {
                label: 'Faltas',
                data: Object.values(dadosAtividade).map(d => d.faltas),
                backgroundColor: '#e74c3c',
                borderRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico por Mês
const ctxMes = document.getElementById('chartMes').getContext('2d');
const mesesLabels = Object.keys(dadosMes).map(m => {
    const [ano, mes] = m.split('-');
    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return meses[parseInt(mes) - 1] + '/' + ano;
});

new Chart(ctxMes, {
    type: 'line',
    data: {
        labels: mesesLabels,
        datasets: [
            {
                label: 'Presenças',
                data: Object.values(dadosMes).map(d => d.presencas),
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Faltas',
                data: Object.values(dadosMes).map(d => d.faltas),
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Função para imprimir
function imprimirRelatorio() {
    window.print();
}

// Função para exportar CSV melhorada
function exportarCSV() {
    const relatorio = <?php echo json_encode($relatorioGeral); ?>;
    const estatisticas = <?php echo json_encode($estatisticas); ?>;
    
    // Cabeçalho do relatório
    let csv = '\ufeff'; // BOM para UTF-8
    csv += '"RELAT\u00d3RIO DE FREQU\u00caNCIA - ASSOCIA\u00c7\u00c3O CRIAN\u00c7A FELIZ"\n';
    csv += '"Data de Gera\u00e7\u00e3o: ' + new Date().toLocaleDateString('pt-BR') + '"\n';
    csv += '\n';
    
    // Estatísticas Gerais
    csv += '"ESTAT\u00cdSTICAS GERAIS"\n';
    csv += '"Total de Atendidos","' + estatisticas.total_atendidos + '"\n';
    csv += '"Atendidos Ativos","' + estatisticas.total_atendidos_ativos + '"\n';
    csv += '"Atendidos Desligados","' + estatisticas.total_atendidos_desligados + '"\n';
    csv += '"Total de Presen\u00e7as","' + estatisticas.total_presencas + '"\n';
    csv += '"Total de Faltas","' + estatisticas.total_faltas + '"\n';
    csv += '"Faltas Justificadas","' + estatisticas.total_faltas_justificadas + '"\n';
    csv += '"Faltas N\u00e3o Justificadas","' + estatisticas.total_faltas_nao_justificadas + '"\n';
    csv += '"Taxa de Presen\u00e7a Geral","' + estatisticas.taxa_presenca_geral + '%"\n';
    csv += '"Atendidos com Alertas","' + estatisticas.atendidos_com_alertas + '"\n';
    csv += '\n';
    
    // Detalhamento por Atendido
    csv += '"DETALHAMENTO POR ATENDIDO"\n';
    csv += '"N\u00ba","Nome Completo","CPF","Presen\u00e7as","Faltas Justificadas","Faltas N\u00e3o Justificadas","Total de Faltas","Taxa de Presen\u00e7a","Status","Observa\u00e7\u00f5es"\n';
    
    relatorio.forEach((item, index) => {
        const status = item.stats.desligado ? 'DESLIGADO' : (item.stats.alertas.length > 0 ? 'COM ALERTA' : 'ATIVO');
        const observacoes = item.stats.alertas.length > 0 ? item.stats.alertas.map(a => a.mensagem).join('; ') : '-';
        
        csv += `"${index + 1}",`;
        csv += `"${item.atendido.nome_completo}",`;
        csv += `"${item.atendido.cpf}",`;
        csv += `"${item.stats.total_presencas}",`;
        csv += `"${item.stats.faltas_justificadas}",`;
        csv += `"${item.stats.faltas_nao_justificadas}",`;
        csv += `"${item.stats.total_faltas}",`;
        csv += `"${item.stats.percentual_presenca}%",`;
        csv += `"${status}",`;
        csv += `"${observacoes}"\n`;
    });
    
    // Criar e baixar arquivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const dataAtual = new Date().toISOString().split('T')[0];
    
    link.setAttribute('href', url);
    link.setAttribute('download', `Relatorio_Frequencia_CriancaFeliz_${dataAtual}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Feedback visual
    alert('✅ Relatório exportado com sucesso!\n\nO arquivo foi salvo como:\nRelatorio_Frequencia_CriancaFeliz_' + dataAtual + '.csv');
}
</script>

<style>
    @media print {
        .page-header button,
        .detailed-report form {
            display: none !important;
        }
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }
        
        .charts-section,
        .rankings-section {
            grid-template-columns: 1fr !important;
        }
        
        table {
            font-size: 12px;
        }
        
        table th,
        table td {
            padding: 8px !important;
        }
    }
</style>
