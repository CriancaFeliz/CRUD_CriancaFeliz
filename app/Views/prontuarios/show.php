<div class="prontuario-detail">
    <!-- Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="prontuarios.php" style="color: #666; text-decoration: none; font-size: 14px;">← Voltar</a>
            <h1 style="margin: 10px 0 0 0; font-size: 24px; font-weight: 700;">
                <?php echo htmlspecialchars($acolhimento['nome_completo'] ?? $socioeconomico['nome_completo'] ?? 'Prontuário'); ?>
            </h1>
            <p style="margin: 5px 0 0 0; color: #666;">
                CPF: <?php echo htmlspecialchars($cpf); ?>
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <?php if ($acolhimento && $attendanceStats && !$attendanceStats['desligado']): ?>
                <a href="faltas.php?action=historico&id=<?php echo $acolhimento['id']; ?>"
                   class="btn" style="background: #3498db;">
                    <i class="fas fa-calendar-check"></i> Ver Controle de Faltas
                </a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <button onclick="desligarAtendido()" class="btn" style="background: #e74c3c;">
                        <i class="fas fa-times-circle"></i> Desligar Atendido
                    </button>
                <?php endif; ?>
            <?php elseif ($acolhimento && $attendanceStats && $attendanceStats['desligado']): ?>
                <span style="background: #e74c3c; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                    ATENDIDO DESLIGADO
                </span>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <button onclick="reativarAtendido()" class="btn" style="background: #27ae60;">
                        <i class="fas fa-redo"></i> Reativar Atendido
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alertas de Desligamento -->
    <?php if ($acolhimento && $attendanceStats && $attendanceStats['desligado']): ?>
        <div class="alert alert-error" style="background: #fee; border-left: 4px solid #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🔴</span>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 5px;">
                        Atendido Desligado do Programa
                    </div>
                    <div style="font-size: 13px; color: #666;">
                        <strong>Motivo:</strong> <?php echo htmlspecialchars($attendanceStats['desligamento']['motivo'] ?? 'Não informado'); ?><br>
                        <strong>Data:</strong> <?php 
                        $date = DateTime::createFromFormat('Y-m-d', $attendanceStats['desligamento']['data_desligamento']);
                        echo $date ? $date->format('d/m/Y') : $attendanceStats['desligamento']['data_desligamento'];
                        ?><br>
                        <?php if (!empty($attendanceStats['desligamento']['observacao'])): ?>
                            <strong>Observação:</strong> <?php echo htmlspecialchars($attendanceStats['desligamento']['observacao']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Alertas de Faltas -->
    <?php if ($acolhimento && $attendanceStats && !empty($attendanceStats['alertas'])): ?>
        <div class="alertas-section" style="margin-bottom: 20px;">
            <?php foreach ($attendanceStats['alertas'] as $alerta): ?>
                <div class="alert alert-<?php echo $alerta['nivel']; ?>" 
                     style="background: <?php echo $alerta['nivel'] === 'critico' ? '#fee' : ($alerta['nivel'] === 'atencao' ? '#fff3cd' : '#d1ecf1'); ?>; 
                            border-left: 4px solid <?php echo $alerta['nivel'] === 'critico' ? '#e74c3c' : ($alerta['nivel'] === 'atencao' ? '#f39c12' : '#3498db'); ?>; 
                            padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 24px;"><?php echo $alerta['icone']; ?></span>
                        <div style="flex: 1;">
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

    <!-- Estatísticas de Frequência -->
    <?php if ($acolhimento && $attendanceStats): ?>
        <div class="stats-section" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"><i class="fas fa-chart-bar"></i> Estatísticas de Frequência</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 28px; font-weight: 700; color: #27ae60;">
                        <?php echo $attendanceStats['total_presencas']; ?>
                    </div>
                    <div style="font-size: 13px; color: #666; margin-top: 5px;">Presenças</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 28px; font-weight: 700; color: #3498db;">
                        <?php echo $attendanceStats['faltas_justificadas']; ?>
                    </div>
                    <div style="font-size: 13px; color: #666; margin-top: 5px;">Faltas Justificadas</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 28px; font-weight: 700; color: <?php echo $attendanceStats['faltas_nao_justificadas'] >= 5 ? '#e74c3c' : '#f39c12'; ?>;">
                        <?php echo $attendanceStats['faltas_nao_justificadas']; ?>
                    </div>
                    <div style="font-size: 13px; color: #666; margin-top: 5px;">Faltas Não Justificadas</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 28px; font-weight: 700; color: #9b59b6;">
                        <?php echo $attendanceStats['percentual_presenca']; ?>%
                    </div>
                    <div style="font-size: 13px; color: #666; margin-top: 5px;">Taxa de Presença</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Documentos -->
    <div class="documents-section" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"><i class="fas fa-folder-open"></i> Documentos</h2>

        <?php if (($currentUser['role'] ?? '') === 'admin' && !empty($atendidoId)): ?>
            <form method="POST" action="prontuarios.php?action=upload_document" enctype="multipart/form-data" style="display: grid; grid-template-columns: minmax(160px, 220px) 1fr auto; gap: 10px; align-items: end; margin-bottom: 16px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="id_atendido" value="<?php echo htmlspecialchars($atendidoId); ?>">
                <input type="hidden" name="cpf" value="<?php echo htmlspecialchars($cpf); ?>">

                <label style="display: grid; gap: 6px; font-size: 13px; color: #666;">
                    Tipo
                    <select name="tipo" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        <option value="identidade">Identidade</option>
                        <option value="comprovante_residencia">Comprovante de residência</option>
                        <option value="escola">Escola</option>
                        <option value="saude">Saúde</option>
                        <option value="autorizacao">Autorização</option>
                        <option value="outros">Outros</option>
                    </select>
                </label>

                <label style="display: grid; gap: 6px; font-size: 13px; color: #666;">
                    Arquivo
                    <input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required style="padding: 9px; border: 1px solid #ddd; border-radius: 8px;">
                </label>

                <button type="submit" class="btn" style="background: #27ae60;">
                    <i class="fas fa-upload"></i> Anexar
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($documents)): ?>
            <div style="display: grid; gap: 10px;">
                <?php foreach ($documents as $document): ?>
                    <?php
                        $tipoLabels = [
                            'identidade' => 'Identidade',
                            'comprovante_residencia' => 'Comprovante de residência',
                            'escola' => 'Escola',
                            'saude' => 'Saúde',
                            'autorizacao' => 'Autorização',
                            'outros' => 'Outros'
                        ];
                        $tipo = $document['tipo'] ?? 'outros';
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($tipoLabels[$tipo] ?? $tipo); ?></div>
                            <div style="font-size: 12px; color: #666;">
                                <?php echo htmlspecialchars($document['data_upload'] ?? 'Data não informada'); ?>
                            </div>
                        </div>
                        <a class="btn" href="prontuarios.php?action=document&id=<?php echo urlencode($document['iddocumento']); ?>" target="_blank" rel="noopener" style="background: #3498db; font-size: 12px; padding: 8px 12px;">
                            <i class="fas fa-eye"></i> Abrir
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="padding: 12px; background: #f8f9fa; border-radius: 8px; color: #666;">
                Nenhum documento anexado.
            </div>
        <?php endif; ?>
    </div>

    <!-- Fichas -->
    <div class="fichas-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
        <!-- Ficha de Acolhimento -->
        <?php if ($acolhimento): ?>
            <div class="ficha-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 600;"><i class="fas fa-clipboard-list"></i> Ficha de Acolhimento</h2>
                    <a href="acolhimento_view.php?id=<?php echo $acolhimento['id']; ?>" 
                       class="btn" style="background: #3498db; font-size: 12px; padding: 6px 12px;">
                        Ver Completa
                    </a>
                </div>
                
                <div class="ficha-info" style="display: grid; gap: 12px;">
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Nome Completo</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($acolhimento['nome_completo'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Data de Nascimento</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($acolhimento['data_nascimento'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">RG</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($acolhimento['rg'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Responsável</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($acolhimento['nome_responsavel'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Contato</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($acolhimento['contato_1'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Data de Acolhimento</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($acolhimento['data_acolhimento'] ?? 'Não informado'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ficha Socioeconômica -->
        <?php if ($socioeconomico): ?>
            <div class="ficha-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 600;"><i class="fas fa-home"></i> Ficha Socioeconômica</h2>
                    <a href="socioeconomico_view.php?id=<?php echo $socioeconomico['id']; ?>" 
                       class="btn" style="background: #f0a36b; font-size: 12px; padding: 6px 12px;">
                        Ver Completa
                    </a>
                </div>
                
                <div class="ficha-info" style="display: grid; gap: 12px;">
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Nome Completo</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($socioeconomico['nome_completo'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Endereço</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($socioeconomico['endereco'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Bairro</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($socioeconomico['bairro'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Número de Membros</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($socioeconomico['numero_membros'] ?? 'Não informado'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 3px;">Tipo de Moradia</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($socioeconomico['tipo_moradia'] ?? 'Não informado'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function desligarAtendido() {
    if (!confirm('Deseja desligar este atendido do programa?\n\nVocê será redirecionado para o formulário de desligamento.')) {
        return;
    }
    
    window.location.href = 'desligamento.php?action=novo&id=<?php echo $acolhimento['id'] ?? ''; ?>';
}

function reativarAtendido() {
    if (!confirm('Tem certeza que deseja reativar este atendido?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'desligamento.php?action=reativar';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $csrf_token; ?>';
    
    const atendidoInput = document.createElement('input');
    atendidoInput.type = 'hidden';
    atendidoInput.name = 'id_atendido';
    atendidoInput.value = '<?php echo $acolhimento['id'] ?? ''; ?>';
    
    form.appendChild(csrfInput);
    form.appendChild(atendidoInput);
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
        
        .fichas-grid {
            grid-template-columns: 1fr !important;
        }
        
        .stats-section > div:last-child {
            grid-template-columns: 1fr 1fr !important;
        }

        .documents-section form {
            grid-template-columns: 1fr !important;
        }
    }
</style>
