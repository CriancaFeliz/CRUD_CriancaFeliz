<div class="alertas-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="attendance.php" style="color: #666; text-decoration: none; font-size: 14px;">← Voltar</a>
            <h1 style="margin: 10px 0 0 0; font-size: 24px; font-weight: 700;">Alertas de Atendimento</h1>
            <p style="margin: 5px 0 0 0; color: #666;">Atendidos que requerem atenção especial</p>
        </div>
        <?php if ($currentUser['role'] === 'admin'): ?>
            <button onclick="processarDesligamentosAutomaticos()" class="btn" style="background: #e74c3c;">
                <i class="fas fa-sync-alt"></i> Processar Desligamentos Automáticos
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($atendidos)): ?>
        <div style="background: #fff; padding: 60px; border-radius: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="font-size: 48px; margin-bottom: 20px; color: #27ae60;"><i class="fas fa-check-circle"></i></div>
            <h2 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 600; color: #27ae60;">Tudo em ordem!</h2>
            <p style="margin: 0; color: #666;">Nenhum atendido com alertas no momento.</p>
        </div>
    <?php else: ?>
        <div class="alertas-list">
            <?php foreach ($atendidos as $atendido): ?>
                <div class="alerta-card" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
                    <!-- Header do Card -->
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 600;">
                                <?php echo htmlspecialchars($atendido['nome_completo'] ?? 'Sem nome'); ?>
                            </h3>
                            <div style="font-size: 13px; color: #666;">
                                CPF: <?php echo htmlspecialchars($atendido['cpf'] ?? 'Não informado'); ?>
                                <?php if (isset($atendido['stats']['atendido']['idade'])): ?>
                                    | Idade: <?php echo $atendido['stats']['atendido']['idade']; ?> anos
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="attendance.php?action=show&id=<?php echo $atendido['id']; ?>" 
                           class="btn" style="background: #3498db;">
                            Ver Detalhes
                        </a>
                    </div>

                    <!-- Estatísticas Resumidas -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <div>
                            <div style="font-size: 24px; font-weight: 700; color: #27ae60;">
                                <?php echo $atendido['stats']['total_presencas'] ?? 0; ?>
                            </div>
                            <div style="font-size: 12px; color: #666;">Presenças</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 700; color: #3498db;">
                                <?php echo $atendido['stats']['faltas_justificadas'] ?? 0; ?>
                            </div>
                            <div style="font-size: 12px; color: #666;">Faltas Justificadas</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 700; color: <?php echo ($atendido['stats']['faltas_nao_justificadas'] ?? 0) >= 5 ? '#e74c3c' : '#f39c12'; ?>;">
                                <?php echo $atendido['stats']['faltas_nao_justificadas'] ?? 0; ?>
                            </div>
                            <div style="font-size: 12px; color: #666;">Faltas Não Justificadas</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 700; color: #9b59b6;">
                                <?php echo $atendido['stats']['percentual_presenca'] ?? 0; ?>%
                            </div>
                            <div style="font-size: 12px; color: #666;">Taxa de Presença</div>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <div class="alertas-detalhes">
                        <?php foreach ($atendido['alertas'] as $alerta): ?>
                            <div class="alerta-item" 
                                 style="background: <?php echo $alerta['nivel'] === 'critico' ? '#fee' : ($alerta['nivel'] === 'atencao' ? '#fff3cd' : '#d1ecf1'); ?>; 
                                        border-left: 4px solid <?php echo $alerta['nivel'] === 'critico' ? '#e74c3c' : ($alerta['nivel'] === 'atencao' ? '#f39c12' : '#3498db'); ?>; 
                                        padding: 12px; border-radius: 6px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: start; gap: 10px;">
                                    <span style="font-size: 20px;"><?php echo $alerta['icone']; ?></span>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; margin-bottom: 3px; font-size: 14px;">
                                            <?php echo htmlspecialchars($alerta['mensagem']); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #666;">
                                            💡 <?php echo htmlspecialchars($alerta['acao_sugerida']); ?>
                                        </div>
                                    </div>
                                    <?php if ($alerta['tipo'] === 'excesso_faltas' && $currentUser['role'] === 'admin'): ?>
                                        <a href="attendance.php?action=desligamento&id=<?php echo $atendido['id']; ?>" 
                                           class="btn" style="background: #e74c3c; font-size: 12px; padding: 6px 12px;">
                                            Desligar
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($alerta['tipo'] === 'idade_limite' && $currentUser['role'] === 'admin'): ?>
                                        <button onclick="desligarPorIdade('<?php echo $atendido['id']; ?>', '<?php echo htmlspecialchars($atendido['nome_completo'] ?? ''); ?>')" 
                                                class="btn" style="background: #e74c3c; font-size: 12px; padding: 6px 12px;">
                                            Desligar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function processarDesligamentosAutomaticos() {
    if (!confirm('Isso irá desligar automaticamente todos os atendidos que completaram 18 anos. Deseja continuar?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'attendance.php?action=processar_desligamentos_automaticos';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
}

function desligarPorIdade(atendidoId, atendidoNome) {
    if (!confirm(`Desligar ${atendidoNome} por completar 18 anos?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'attendance.php?action=processar_desligamento';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    
    const atendidoInput = document.createElement('input');
    atendidoInput.type = 'hidden';
    atendidoInput.name = 'atendido_id';
    atendidoInput.value = atendidoId;
    
    const motivoInput = document.createElement('input');
    motivoInput.type = 'hidden';
    motivoInput.name = 'motivo';
    motivoInput.value = 'idade';
    
    const obsInput = document.createElement('input');
    obsInput.type = 'hidden';
    obsInput.name = 'observacao';
    obsInput.value = 'Desligamento por completar 18 anos';
    
    form.appendChild(csrfInput);
    form.appendChild(atendidoInput);
    form.appendChild(motivoInput);
    form.appendChild(obsInput);
    document.body.appendChild(form);
    form.submit();
}
</script>

<style>
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }
        
        .alerta-card > div:first-child {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>
