<div class="attendance-detail">
    <!-- Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="attendance.php" style="color: #666; text-decoration: none; font-size: 14px;">← Voltar</a>
            <h1 style="margin: 10px 0 0 0; font-size: 24px; font-weight: 700;">
                <?php echo htmlspecialchars($stats['atendido']['nome'] ?? 'Atendido'); ?>
            </h1>
            <p style="margin: 5px 0 0 0; color: #666;">
                CPF: <?php echo htmlspecialchars($stats['atendido']['cpf'] ?? 'Não informado'); ?>
                | Idade: <?php echo $stats['atendido']['idade'] ?? 'N/A'; ?> anos
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <?php if (!$stats['desligado']): ?>
                <button onclick="openRegisterModal('presenca')" class="btn" style="background: #27ae60;">
                    ✓ Registrar Presença
                </button>
                <button onclick="openRegisterModal('falta')" class="btn" style="background: #f39c12;">
                    ✗ Registrar Falta
                </button>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="attendance.php?action=desligamento&id=<?php echo $stats['atendido']['id']; ?>" 
                       class="btn" style="background: #e74c3c;">
                        Desligar Atendido
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <span style="background: #e74c3c; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                    ATENDIDO DESLIGADO
                </span>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <button onclick="reativarAtendido()" class="btn" style="background: #27ae60;">
                        Reativar Atendido
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (!empty($stats['alertas'])): ?>
        <div class="alertas-section" style="margin-bottom: 20px;">
            <?php foreach ($stats['alertas'] as $alerta): ?>
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

    <!-- Estatísticas -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #27ae60;">
            <div style="font-size: 32px; font-weight: 700; color: #27ae60;">
                <?php echo $stats['total_presencas']; ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Total de Presenças</div>
        </div>
        
        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #3498db;">
            <div style="font-size: 32px; font-weight: 700; color: #3498db;">
                <?php echo $stats['faltas_justificadas']; ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Faltas Justificadas</div>
        </div>
        
        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid <?php echo $stats['faltas_nao_justificadas'] >= 5 ? '#e74c3c' : '#f39c12'; ?>;">
            <div style="font-size: 32px; font-weight: 700; color: <?php echo $stats['faltas_nao_justificadas'] >= 5 ? '#e74c3c' : '#f39c12'; ?>;">
                <?php echo $stats['faltas_nao_justificadas']; ?>
                <?php if ($stats['faltas_nao_justificadas'] >= 5): ?>
                    <span style="font-size: 24px;">⚠️</span>
                <?php endif; ?>
            </div>
            <div style="color: #666; margin-top: 5px;">Faltas Não Justificadas</div>
        </div>
        
        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left: 4px solid #9b59b6;">
            <div style="font-size: 32px; font-weight: 700; color: #9b59b6;">
                <?php echo $stats['percentual_presenca']; ?>%
            </div>
            <div style="color: #666; margin-top: 5px;">Taxa de Presença</div>
        </div>
    </div>

    <!-- Histórico -->
    <div class="historico-section" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h2 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 600;">Histórico de Registros</h2>
        
        <?php if (empty($historico)): ?>
            <p style="text-align: center; color: #999; padding: 40px;">Nenhum registro encontrado</p>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($historico as $registro): ?>
                    <div class="timeline-item" style="display: flex; gap: 15px; padding: 15px; border-bottom: 1px solid #f0f0f0;">
                        <div style="min-width: 60px; text-align: center;">
                            <div style="font-size: 24px; margin-bottom: 5px;">
                                <?php echo $registro['tipo'] === 'presenca' ? '✓' : '✗'; ?>
                            </div>
                            <div style="font-size: 12px; color: #666;">
                                <?php 
                                $date = DateTime::createFromFormat('Y-m-d', $registro['data']);
                                echo $date ? $date->format('d/m/Y') : $registro['data'];
                                ?>
                            </div>
                        </div>
                        
                        <div style="flex: 1;">
                            <div style="font-weight: 600; margin-bottom: 5px;">
                                <span style="background: <?php echo $registro['tipo'] === 'presenca' ? '#27ae60' : '#f39c12'; ?>; 
                                             color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                                    <?php echo $registro['tipo'] === 'presenca' ? 'PRESENÇA' : 'FALTA'; ?>
                                </span>
                                <?php if ($registro['tipo'] === 'falta' && $registro['justificada']): ?>
                                    <span style="background: #3498db; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; margin-left: 5px;">
                                        JUSTIFICADA
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;">
                                <strong>Atividade:</strong> <?php echo htmlspecialchars($registro['atividade']); ?>
                            </div>
                            
                            <?php if (!empty($registro['justificativa'])): ?>
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; margin-top: 8px;">
                                    <strong style="font-size: 13px;">Justificativa:</strong>
                                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #555;">
                                        <?php echo nl2br(htmlspecialchars($registro['justificativa'])); ?>
                                    </p>
                                </div>
                            <?php elseif ($registro['tipo'] === 'falta'): ?>
                                <button onclick="addJustificativa('<?php echo $registro['id']; ?>')" 
                                        class="btn secondary" 
                                        style="margin-top: 8px; font-size: 12px; padding: 6px 12px;">
                                    Adicionar Justificativa
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!empty($registro['observacao'])): ?>
                                <div style="font-size: 13px; color: #666; margin-top: 8px; font-style: italic;">
                                    <strong>Obs:</strong> <?php echo htmlspecialchars($registro['observacao']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <div>
                                <button onclick="removeRecord('<?php echo $registro['id']; ?>')" 
                                        class="btn-icon" 
                                        style="background: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;"
                                        title="Remover registro">
                                    🗑️
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Registrar Presença/Falta -->
<div id="registerModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div class="modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); background: white; padding: 30px; border-radius: 12px; width: 500px; max-width: 90vw;">
        <h3 id="modalTitle" style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600;"></h3>
        
        <form id="registerForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="atendido_id" value="<?php echo $stats['atendido']['id']; ?>">
            <input type="hidden" id="registerType" name="type" value="">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Data:</label>
                <input type="date" name="data" value="<?php echo date('Y-m-d'); ?>" required
                       style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Atividade:</label>
                <select name="atividade" required
                        style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
                    <option value="Atendimento">Atendimento</option>
                    <option value="Oficina">Oficina</option>
                    <option value="Reunião">Reunião</option>
                    <option value="Evento">Evento</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
            
            <div id="justificativaField" style="margin-bottom: 15px; display: none;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Justificativa:</label>
                <textarea name="justificativa" rows="3" placeholder="Descreva o motivo da falta..."
                          style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; resize: vertical;"></textarea>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Observação (opcional):</label>
                <textarea name="observacao" rows="2" placeholder="Informações adicionais..."
                          style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRegisterModal()" class="btn secondary">Cancelar</button>
                <button type="submit" class="btn" id="submitBtn">Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Justificativa -->
<div id="justificativaModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div class="modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); background: white; padding: 30px; border-radius: 12px; width: 500px; max-width: 90vw;">
        <h3 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600;">Adicionar Justificativa</h3>
        
        <form id="justificativaForm" method="POST" action="attendance.php?action=update_justification">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" id="recordId" name="record_id" value="">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Justificativa:</label>
                <textarea name="justificativa" rows="4" required placeholder="Descreva o motivo da falta..."
                          style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeJustificativaModal()" class="btn secondary">Cancelar</button>
                <button type="submit" class="btn">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRegisterModal(type) {
    const modal = document.getElementById('registerModal');
    const title = document.getElementById('modalTitle');
    const typeInput = document.getElementById('registerType');
    const justificativaField = document.getElementById('justificativaField');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('registerForm');
    
    typeInput.value = type;
    
    if (type === 'presenca') {
        title.textContent = 'Registrar Presença';
        justificativaField.style.display = 'none';
        submitBtn.style.background = '#27ae60';
        form.action = 'attendance.php?action=register_presence';
    } else {
        title.textContent = 'Registrar Falta';
        justificativaField.style.display = 'block';
        submitBtn.style.background = '#f39c12';
        form.action = 'attendance.php?action=register_absence';
    }
    
    modal.style.display = 'block';
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';
    document.getElementById('registerForm').reset();
}

function addJustificativa(recordId) {
    document.getElementById('recordId').value = recordId;
    document.getElementById('justificativaModal').style.display = 'block';
}

function closeJustificativaModal() {
    document.getElementById('justificativaModal').style.display = 'none';
    document.getElementById('justificativaForm').reset();
}

function removeRecord(recordId) {
    if (!confirm('Tem certeza que deseja remover este registro?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'attendance.php?action=remove_record';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $csrf_token; ?>';
    
    const recordInput = document.createElement('input');
    recordInput.type = 'hidden';
    recordInput.name = 'record_id';
    recordInput.value = recordId;
    
    form.appendChild(csrfInput);
    form.appendChild(recordInput);
    document.body.appendChild(form);
    form.submit();
}

function reativarAtendido() {
    if (!confirm('Tem certeza que deseja reativar este atendido?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'attendance.php?action=cancelar_desligamento';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $csrf_token; ?>';
    
    const atendidoInput = document.createElement('input');
    atendidoInput.type = 'hidden';
    atendidoInput.name = 'atendido_id';
    atendidoInput.value = '<?php echo $stats['atendido']['id']; ?>';
    
    form.appendChild(csrfInput);
    form.appendChild(atendidoInput);
    document.body.appendChild(form);
    form.submit();
}

// Fechar modais ao clicar fora
window.onclick = function(event) {
    const registerModal = document.getElementById('registerModal');
    const justificativaModal = document.getElementById('justificativaModal');
    
    if (event.target === registerModal) {
        closeRegisterModal();
    }
    if (event.target === justificativaModal) {
        closeJustificativaModal();
    }
}
</script>

<style>
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        
        .modal-content {
            width: 95vw !important;
            padding: 20px !important;
        }
    }
</style>
