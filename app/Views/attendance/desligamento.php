<div class="desligamento-container">
    <div class="page-header" style="margin-bottom: 20px;">
        <a href="attendance.php?action=show&id=<?php echo $stats['atendido']['id']; ?>" 
           style="color: #666; text-decoration: none; font-size: 14px;">← Voltar</a>
        <h1 style="margin: 10px 0 0 0; font-size: 24px; font-weight: 700; color: #e74c3c;">
            ⚠️ Desligamento de Atendido
        </h1>
        <p style="margin: 5px 0 0 0; color: #666;">
            Esta ação irá desligar o atendido do programa
        </p>
    </div>

    <!-- Informações do Atendido -->
    <div class="atendido-info" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">Informações do Atendido</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Nome Completo</div>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($stats['atendido']['nome'] ?? 'Não informado'); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">CPF</div>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($stats['atendido']['cpf'] ?? 'Não informado'); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Idade</div>
                <div style="font-weight: 600;"><?php echo $stats['atendido']['idade'] ?? 'N/A'; ?> anos</div>
            </div>
            <div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Data de Nascimento</div>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($stats['atendido']['data_nascimento'] ?? 'Não informado'); ?></div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-summary" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">Resumo de Frequência</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 28px; font-weight: 700; color: #27ae60;">
                    <?php echo $stats['total_presencas']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Presenças</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 28px; font-weight: 700; color: #3498db;">
                    <?php echo $stats['faltas_justificadas']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Faltas Justificadas</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 28px; font-weight: 700; color: #e74c3c;">
                    <?php echo $stats['faltas_nao_justificadas']; ?>
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Faltas Não Justificadas</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 28px; font-weight: 700; color: #9b59b6;">
                    <?php echo $stats['percentual_presenca']; ?>%
                </div>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">Taxa de Presença</div>
            </div>
        </div>
    </div>

    <!-- Alertas Ativos -->
    <?php if (!empty($stats['alertas'])): ?>
        <div class="alertas-ativos" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">Alertas Ativos</h2>
            
            <?php foreach ($stats['alertas'] as $alerta): ?>
                <div class="alerta-item" 
                     style="background: <?php echo $alerta['nivel'] === 'critico' ? '#fee' : ($alerta['nivel'] === 'atencao' ? '#fff3cd' : '#d1ecf1'); ?>; 
                            border-left: 4px solid <?php echo $alerta['nivel'] === 'critico' ? '#e74c3c' : ($alerta['nivel'] === 'atencao' ? '#f39c12' : '#3498db'); ?>; 
                            padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 24px;"><?php echo $alerta['icone']; ?></span>
                        <div>
                            <div style="font-weight: 600; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($alerta['mensagem']); ?>
                            </div>
                            <div style="font-size: 13px; color: #666;">
                                <?php echo htmlspecialchars($alerta['acao_sugerida']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Desligamento -->
    <div class="desligamento-form" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border: 2px solid #e74c3c;">
        <h2 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 600; color: #e74c3c;">
            Confirmar Desligamento
        </h2>
        
        <form method="POST" action="attendance.php?action=processar_desligamento" onsubmit="return confirmDesligamento()">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="atendido_id" value="<?php echo $stats['atendido']['id']; ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Motivo do Desligamento: <span style="color: #e74c3c;">*</span>
                </label>
                <select name="motivo" required 
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                    <option value="">Selecione o motivo...</option>
                    <option value="excesso_faltas">Excesso de faltas não justificadas</option>
                    <option value="idade">Completou 18 anos</option>
                    <option value="mudanca_cidade">Mudança de cidade</option>
                    <option value="transferencia">Transferência para outro programa</option>
                    <option value="solicitacao_familia">Solicitação da família</option>
                    <option value="comportamento">Questões comportamentais</option>
                    <option value="outro">Outro motivo</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Observações: <span style="color: #e74c3c;">*</span>
                </label>
                <textarea name="observacao" rows="5" required
                          placeholder="Descreva detalhadamente o motivo do desligamento e informações relevantes..."
                          style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                    Esta informação ficará registrada permanentemente no histórico do atendido.
                </div>
            </div>
            
            <div style="background: #fff3cd; border-left: 4px solid #f39c12; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-weight: 600; margin-bottom: 5px; color: #856404;">
                    ⚠️ Atenção
                </div>
                <div style="font-size: 13px; color: #856404;">
                    Esta ação é <strong>permanente</strong> e o atendido será marcado como desligado do programa. 
                    Não será possível registrar novas presenças ou faltas após o desligamento.
                    O histórico será preservado para fins de registro.
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                <a href="attendance.php?action=show&id=<?php echo $stats['atendido']['id']; ?>" 
                   class="btn secondary" style="text-decoration: none;">
                    Cancelar
                </a>
                <button type="submit" class="btn" style="background: #e74c3c;">
                    Confirmar Desligamento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDesligamento() {
    const atendidoNome = '<?php echo addslashes($stats['atendido']['nome'] ?? 'este atendido'); ?>';
    return confirm(`Tem certeza que deseja desligar ${atendidoNome} do programa?\n\nEsta ação não poderá ser desfeita facilmente.`);
}
</script>

<style>
    @media (max-width: 768px) {
        .stats-summary > div:last-child {
            grid-template-columns: 1fr 1fr !important;
        }
    }
</style>
