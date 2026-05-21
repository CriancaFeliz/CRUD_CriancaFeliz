<?php
// Obter etapa atual e ID (se for edição)
$step = $_GET['step'] ?? 1;
$step = max(1, min(5, (int)$step)); // Limitar entre 1 e 5
$editId = $_GET['id'] ?? ($ficha['id'] ?? null);
?>

<style>
    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        padding: 0 20px;
    }
    
    .step-item {
        flex: 1;
        text-align: center;
        position: relative;
        padding: 10px;
    }
    
    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 25px;
        right: -50%;
        width: 100%;
        height: 2px;
        background: #dee2e6;
        z-index: -1;
    }
    
    .step-item.active:not(:last-child)::after {
        background: #ff7a00;
    }
    
    .step-item.completed:not(:last-child)::after {
        background: #6fb64f;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #dee2e6;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: 700;
        font-size: 18px;
    }
    
    .step-item.active .step-number {
        background: #ff7a00;
        color: white;
    }
    
    .step-item.completed .step-number {
        background: #6fb64f;
        color: white;
    }
    
    .step-title {
        font-size: 14px;
        font-weight: 600;
        color: #6c757d;
    }
    
    .step-item.active .step-title {
        color: #ff7a00;
    }
    
    .step-item.completed .step-title {
        color: #6fb64f;
    }
    
    .form-section {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    
    .form-grid.two-cols {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .form-field label {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .form-field input,
    .form-field select,
    .form-field textarea {
        padding: 12px;
        border: 2px solid #4a7c8f;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
    }
    
    .form-field input:focus,
    .form-field select:focus {
        outline: none;
        border-color: #3e6475;
        box-shadow: 0 0 0 3px rgba(74, 124, 143, 0.1);
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 30px;
    }
    
    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-secondary {
        background: #6b7b84;
        color: white;
    }
    
    .btn-primary {
        background: #ff7a00;
        color: white;
    }
    
    .btn-success {
        background: #6fb64f;
        color: white;
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
    }
    
    .family-member {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .family-member-info {
        flex: 1;
    }
    
    .family-member-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .family-member-details {
        font-size: 12px;
        color: #6c757d;
    }
    
    .btn-remove {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr !important;
        }
        
        .step-indicator {
            flex-wrap: wrap;
        }
        
        .step-item {
            flex-basis: 50%;
            margin-bottom: 20px;
        }
        
        .step-item::after {
            display: none;
        }
    }
</style>

<!-- Indicador de Etapas -->
<div class="step-indicator">
    <div class="step-item <?php echo $step >= 1 ? ($step == 1 ? 'active' : 'completed') : ''; ?>">
        <div class="step-number">1</div>
        <div class="step-title">Dados Iniciais</div>
    </div>
    <div class="step-item <?php echo $step >= 2 ? ($step == 2 ? 'active' : 'completed') : ''; ?>">
        <div class="step-number">2</div>
        <div class="step-title">Domicílio</div>
    </div>
    <div class="step-item <?php echo $step >= 3 ? ($step == 3 ? 'active' : 'completed') : ''; ?>">
        <div class="step-number">3</div>
        <div class="step-title">Composição Familiar</div>
    </div>
    <div class="step-item <?php echo $step >= 4 ? ($step == 4 ? 'active' : 'completed') : ''; ?>">
        <div class="step-number">4</div>
        <div class="step-title">Despesas</div>
    </div>
    <div class="step-item <?php echo $step >= 5 ? ($step == 5 ? 'active' : 'completed') : ''; ?>">
        <div class="step-number">5</div>
        <div class="step-title">Outras Informações</div>
    </div>
</div>

<form method="post" id="socioeconomicoForm" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="step" value="<?php echo $step; ?>">
    <input type="hidden" name="familia_json" id="familia_json" value="">
    <input type="hidden" name="despesas_json" id="despesas_json" value="">
    <?php if ($editId): ?>
        <input type="hidden" name="id" id="edit_id" value="<?php echo htmlspecialchars($editId); ?>">
    <?php endif; ?>
    
    <?php if ($step == 1): ?>
        <!-- ETAPA 1: Dados Iniciais -->
        <div class="form-section">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Dados Iniciais</h3>
            
            <div class="form-grid">
                <div class="form-field">
                    <label>Nome do Entrevistado <span style="color:#e74c3c;">*</span></label>
                    <input type="text" name="nome_entrevistado" placeholder="Digite o nome completo" style="border: 2px solid #4a7c8f;" value="<?php echo htmlspecialchars($ficha['nome_entrevistado'] ?? ''); ?>">
                </div>
                
                <div class="form-field">
                    <label>RG <span style="color:#e74c3c;">*</span></label>
                    <input type="text" name="rg" id="rg" placeholder="00.000.000-0" style="border: 2px solid #4a7c8f;" value="<?php echo htmlspecialchars($ficha['rg'] ?? ''); ?>">
                </div>
                
                <div class="form-field">
                    <label>CPF <span style="color:#e74c3c;">*</span></label>
                    <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" style="border: 2px solid #4a7c8f;" value="<?php echo htmlspecialchars($ficha['cpf'] ?? ''); ?>">
                </div>
                
                <div class="form-field">
                    <label>Nome do Menor <span style="color:#e74c3c;">*</span></label>
                    <input type="text" name="nome_menor" placeholder="Digite o nome completo" style="border: 2px solid #4a7c8f;" value="<?php echo htmlspecialchars($ficha['nome_menor'] ?? ''); ?>">
                </div>
                
                <div class="form-field">
                    <label>Data de Acolhimento</label>
                    <input type="text" name="data_acolhimento" id="data_acolhimento" placeholder="dd/mm/aaaa" value="<?php echo htmlspecialchars($ficha['data_acolhimento'] ?? ''); ?>">
                </div>
                
                <div class="form-field">
                    <label>Assistente Social Responsável</label>
                    <input type="text" name="assistente_social" value="<?php echo htmlspecialchars($ficha['assistente_social'] ?? ''); ?>">
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="prontuarios.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button type="button" onclick="nextStep()" class="btn btn-primary">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>
        
        <!-- Debug: Botão para limpar dados salvos (remover em produção) -->
        <div style="text-align: center; margin-top: 10px;">
            <button type="button" onclick="sessionStorage.clear(); alert('Dados limpos! Recarregando...'); location.reload();" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer;">
                <i class="fas fa-trash"></i> Limpar Dados Salvos (Debug)
            </button>
        </div>
        
    <?php elseif ($step == 2): ?>
        <!-- ETAPA 2: Características do Domicílio -->
        <div class="form-section">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Características do Domicílio</h3>
            
            <!-- Residência -->
            <div style="margin-bottom: 24px;">
                <label style="font-size: 14px; font-weight: 600; color: #2c3e50; display: block; margin-bottom: 12px;">Residência</label>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="residencia" value="Própria"> Própria
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="residencia" value="Alugada"> Alugada
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="residencia" value="Cedida"> Cedida
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="residencia" value="Financiada"> Financiada
                    </label>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-field">
                    <label>Número de Cômodos</label>
                    <input type="number" name="num_comodos" min="1">
                </div>
                
                <div class="form-field">
                    <label>Quartos</label>
                    <input type="number" name="quartos" min="0">
                </div>
                
                <div class="form-field">
                    <label>Banheiro</label>
                    <input type="number" name="banheiro" min="0" placeholder="Quantidade de banheiros">
                </div>
            </div>
            
            <!-- Construção -->
            <div style="margin: 24px 0;">
                <label style="font-size: 14px; font-weight: 600; color: #2c3e50; display: block; margin-bottom: 12px;">Construção</label>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="construcao" value="Alvenaria"> Alvenaria
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="construcao" value="Madeira"> Madeira
                    </label>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-field">
                    <label>Água</label>
                    <select name="agua">
                        <option value="">Selecionar</option>
                        <option value="Rede Pública">Rede Pública</option>
                        <option value="Poço">Poço</option>
                        <option value="Fossa">Fossa</option>
                    </select>
                </div>
                
                <div class="form-field">
                    <label>Esgoto</label>
                    <select name="esgoto">
                        <option value="">Selecionar</option>
                        <option value="Rede Pública">Rede Pública</option>
                        <option value="Fossa">Fossa</option>
                    </select>
                </div>
                
                <div class="form-field">
                    <label>Energia Elétrica</label>
                    <select name="energia">
                        <option value="">Selecionar</option>
                        <option value="Relógio Próprio">Relógio Próprio</option>
                        <option value="Relógio Comunitário">Relógio Comunitário</option>
                    </select>
                </div>
            </div>
            
            <!-- Condições Gerais -->
            <div style="margin: 24px 0;">
                <label style="font-size: 14px; font-weight: 600; color: #2c3e50; display: block; margin-bottom: 12px;">Condições Gerais da Residência</label>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="condicoes" value="Ótima"> Ótima
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="condicoes" value="Boa"> Boa
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="condicoes" value="Regular"> Regular
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="condicoes" value="Precária"> Precária
                    </label>
                </div>
            </div>
            
            <!-- Número de Veículos -->
            <div style="margin: 24px 0;">
                <label style="font-size: 14px; font-weight: 600; color: #2c3e50; display: block; margin-bottom: 12px;">Número de Veículos</label>
                <div class="form-grid">
                    <div class="form-field">
                        <label>Motocicleta</label>
                        <input type="number" name="veiculos_motocicleta" min="0" placeholder="Quantidade">
                    </div>
                    <div class="form-field">
                        <label>Automóvel</label>
                        <input type="number" name="veiculos_automovel" min="0" placeholder="Quantidade">
                    </div>
                    <div class="form-field">
                        <label>Caminhonete</label>
                        <input type="number" name="veiculos_caminhonete" min="0" placeholder="Quantidade">
                    </div>
                    <div class="form-field">
                        <label>Caminhão</label>
                        <input type="number" name="veiculos_caminhao" min="0" placeholder="Quantidade">
                    </div>
                    <div class="form-field">
                        <label>Outros</label>
                        <input type="number" name="veiculos_outros" min="0" placeholder="Quantidade">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" onclick="prevStep()" class="btn btn-secondary">← Anterior</button>
            <button type="button" onclick="nextStep()" class="btn btn-primary">Próximo →</button>
        </div>
        
    <?php elseif ($step == 3): ?>
        <!-- ETAPA 3: Composição Familiar -->
        <div class="form-section">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Composição Familiar</h3>
            
            <div style="margin-bottom: 20px;">
                <button type="button" onclick="openFamilyModal()" class="btn btn-success">+ Adicionar Integrante</button>
            </div>
            
            <div id="familyList">
                <!-- Lista de membros da família será preenchida via JavaScript -->
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" onclick="prevStep()" class="btn btn-secondary">← Anterior</button>
            <button type="button" onclick="nextStep()" class="btn btn-primary">Próximo →</button>
        </div>
        
    <?php elseif ($step == 4): ?>
        <!-- ETAPA 4: Despesas -->
        <div class="form-section">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Despesas</h3>
            
            <div class="form-grid two-cols">
                <div>
                    <h4 style="color: #ff7a00; margin-bottom: 16px;">Residência</h4>
                    <div class="form-field">
                        <label>Água</label>
                        <input type="number" name="desp_agua" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Luz</label>
                        <input type="number" name="desp_luz" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Gás</label>
                        <input type="number" name="desp_gas" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Telefone</label>
                        <input type="number" name="desp_telefone" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Celular</label>
                        <input type="number" name="desp_celular" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Internet</label>
                        <input type="number" name="desp_internet" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Alimentação</label>
                        <input type="number" name="desp_alimentacao" class="despesa-input" step="0.01" placeholder="0,00">
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="button" onclick="addDespesa()" class="btn btn-success">+ Adicionar Despesa</button>
                    </div>
                    
                    <div id="despesasAdicionais" style="margin-top: 16px;">
                        <!-- Despesas adicionais serão adicionadas aqui -->
                    </div>
                    
                    <div style="margin-top: 24px; padding: 16px; background: #ffe5e5; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 16px; color: #e74c3c;">
                            <span>Total Despesas:</span>
                            <span id="totalDespesas">R$ 0,00</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 style="color: #6fb64f; margin-bottom: 16px;">Renda/Benefício</h4>
                    <div class="form-field">
                        <label>Salário</label>
                        <input type="number" name="renda_salario" class="renda-input" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-field">
                        <label>Bolsa Família</label>
                        <input type="number" name="renda_bolsa" class="renda-input" step="0.01" placeholder="0,00">
                    </div>
                    
                    <div style="margin-top: 24px; padding: 16px; background: #e8f6ea; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 16px; color: #6fb64f;">
                            <span>Total Renda/Benefício:</span>
                            <span id="totalRenda">R$ 0,00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" onclick="prevStep()" class="btn btn-secondary">← Anterior</button>
            <button type="button" onclick="nextStep()" class="btn btn-primary">Próximo →</button>
        </div>
        
    <?php elseif ($step == 5): ?>
        <!-- ETAPA 5: Outras Informações -->
        <div class="form-section">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Outras Informações</h3>
            
            <div class="form-field" style="margin-bottom: 20px;">
                <label>Alguém na família trabalha registrado / CLT?</label>
                <div style="display: flex; gap: 16px; margin-top: 8px; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="trabalho_clt" value="Sim" onchange="toggleCltField(true)"> Sim
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="trabalho_clt" value="Não" onchange="toggleCltField(false)"> Não
                    </label>
                </div>
                <div id="clt_field" style="display: none; margin-top: 12px;">
                    <label style="font-size: 14px; color: #6c757d; margin-bottom: 6px; display: block;">Se sim, com o quê?</label>
                    <input type="text" name="trabalho_clt_qual" placeholder="Especifique a ocupação" style="width: 100%; padding: 12px; border: 2px solid #4a7c8f; border-radius: 8px; font-family: 'Poppins', sans-serif;">
                </div>
            </div>
            
            <div class="form-field" style="margin-bottom: 20px;">
                <label>Possui convênio médico?</label>
                <div style="display: flex; gap: 16px; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="convenio_medico" value="Sim"> Sim
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="convenio_medico" value="Não"> Não
                    </label>
                </div>
            </div>
            
            <div class="form-field">
                <label>Tem Cadastro Único (CadÚnico)?</label>
                <div style="display: flex; gap: 16px; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="cadunico" value="Sim"> Sim
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="cadunico" value="Não"> Não
                    </label>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" onclick="prevStep()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Anterior
            </button>
            <button type="submit" class="btn btn-success">
                <?php if ($editId): ?>
                    <i class="fas fa-save"></i> Salvar Edição
                <?php else: ?>
                    <i class="fas fa-check"></i> Cadastrar
                <?php endif; ?>
            </button>
        </div>
    <?php endif; ?>
</form>

<!-- Modal de Composição Familiar -->
<div id="familyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;">Adicionar Parente</h3>
            <button type="button" class="modal-close" onclick="closeFamilyModal()">&times;</button>
        </div>
        
        <div class="form-field">
            <label>Nome</label>
            <input type="text" id="family_nome" placeholder="Digite o nome completo">
        </div>
        
        <div class="form-field">
            <label>Parentesco</label>
            <select id="family_parentesco">
                <option value="">Selecionar</option>
                <option value="Pai">Pai</option>
                <option value="Mãe">Mãe</option>
                <option value="Filho(a)">Filho(a)</option>
                <option value="Avô/Avó">Avô/Avó</option>
                <option value="Tio/Tia">Tio/Tia</option>
                <option value="Irmão/Irmã">Irmão/Irmã</option>
                <option value="Primo(a)">Primo(a)</option>
                <option value="Neto(a)">Neto(a)</option>
                <option value="Outros">Outros</option>
            </select>
        </div>
        
        <div class="form-field">
            <label>Data de Nascimento</label>
            <input type="text" id="family_data_nasc" placeholder="dd/mm/aaaa">
        </div>
        
        <div class="form-field">
            <label>Formação</label>
            <select id="family_formacao">
                <option value="">Selecionar</option>
                <option value="Analfabeto">Analfabeto</option>
                <option value="Educação Infantil">Educação Infantil</option>
                <option value="Ensino Fundamental">Ensino Fundamental</option>
                <option value="Ensino Médio">Ensino Médio</option>
                <option value="Superior">Superior</option>
                <option value="Completo">Completo</option>
                <option value="Incompleto">Incompleto</option>
                <option value="Pós-graduação (Completo)">Pós-graduação (Completo)</option>
                <option value="Pós-graduação (Incompleto)">Pós-graduação (Incompleto)</option>
            </select>
        </div>
        
        <div class="form-field">
            <label>Renda R$</label>
            <input type="number" id="family_renda" step="0.01" placeholder="0,00">
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button type="button" onclick="closeFamilyModal()" class="btn btn-secondary" style="flex: 1;">Cancelar</button>
            <button type="button" onclick="addFamilyMember()" class="btn btn-success" style="flex: 1;">Adicionar</button>
        </div>
    </div>
</div>

<script src="js/socioeconomico-multistep.js"></script>
