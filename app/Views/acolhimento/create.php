<link rel="stylesheet" href="css/acolhimento-form.css">

<div class="acolhimento-stepper">
    <div class="step active">1. Dados iniciais</div>
    <div class="step">2. Endereço</div>
    <div class="step">3. Responsável</div>
    <div class="step">4. Documentos</div>
</div>

<?php
// Obter mensagens de erro da sessão, se houver
$errorMessage = '';
if (isset($_SESSION['flash_error'])) {
    $errorMessage = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
?>

<?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger" role="alert" style="margin-bottom: 20px; padding: 15px; border-radius: 4px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="acolhimento-form" id="acolhimentoForm">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <?php if (!empty($editId)): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($editId); ?>">
    <?php endif; ?>
    
    <!-- Etapa 1: Dados Iniciais -->
    <div class="form-section">
        <h3 class="form-section-title">1. Dados Iniciais</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Nome Completo <span class="required">*</span></label>
                <input type="text" name="nome_completo" value="<?php echo htmlspecialchars($ficha['nome_completo'] ?? ($_SESSION['old_input']['nome_completo'] ?? '')); ?>" required>
                <?php if (isset($_SESSION['field_errors']['nome_completo'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['nome_completo']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label>RG <span class="required">*</span></label>
                <input type="text" name="rg" value="<?php echo htmlspecialchars($ficha['rg'] ?? ($_SESSION['old_input']['rg'] ?? '')); ?>" required>
                <?php if (isset($_SESSION['field_errors']['rg'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['rg']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label>CPF <span class="required">*</span></label>
                <input type="text" name="cpf" value="<?php echo htmlspecialchars($ficha['cpf'] ?? ($_SESSION['old_input']['cpf'] ?? '')); ?>" required>
                <?php if (isset($_SESSION['field_errors']['cpf'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['cpf']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label>Data de Nascimento <span class="required">*</span></label>
                <input type="text" name="data_nascimento" value="<?php echo htmlspecialchars($ficha['data_nascimento'] ?? ($_SESSION['old_input']['data_nascimento'] ?? '')); ?>" placeholder="dd/mm/aaaa" required>
                <?php if (isset($_SESSION['field_errors']['data_nascimento'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['data_nascimento']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label>Data de Acolhimento <span class="required">*</span></label>
                <input type="text" name="data_acolhimento" value="<?php echo htmlspecialchars($ficha['data_acolhimento'] ?? ($_SESSION['old_input']['data_acolhimento'] ?? '')); ?>" placeholder="dd/mm/aaaa" required>
                <?php if (isset($_SESSION['field_errors']['data_acolhimento'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['data_acolhimento']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label>Encaminhado por</label>
                <input type="text" name="encaminha_por" value="<?php echo htmlspecialchars($ficha['encaminha_por'] ?? ($_SESSION['old_input']['encaminha_por'] ?? '')); ?>">
                <?php if (isset($_SESSION['field_errors']['encaminha_por'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['encaminha_por']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-field" style="grid-column: 1 / 2;">
                <label>Foto 3x4</label>
                <input type="file" name="foto" accept="image/*">
                <small>Formatos aceitos: JPG, PNG, GIF (máx. 2MB)</small>
            </div>
            <div class="form-field" style="grid-column: 1 / -1;">
                <label>Queixa Principal <span class="required">*</span></label>
                <textarea name="queixa_principal" required><?php echo htmlspecialchars($ficha['queixa_principal'] ?? ($_SESSION['old_input']['queixa_principal'] ?? '')); ?></textarea>
                <?php if (isset($_SESSION['field_errors']['queixa_principal'])): ?>
                    <div class="error-message" style="color: #dc3545; font-size: 0.875em; margin-top: 4px;">
                        <?php echo htmlspecialchars($_SESSION['field_errors']['queixa_principal']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Etapa 2: Endereço -->
    <div class="form-section">
        <h3 class="form-section-title">2. Endereço</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Endereço <span class="required">*</span></label>
                <input type="text" name="endereco" value="<?php echo htmlspecialchars($ficha['endereco'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>Número <span class="required">*</span></label>
                <input type="text" name="numero" value="<?php echo htmlspecialchars($ficha['numero'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>CEP <span class="required">*</span></label>
                <input type="text" name="cep" value="<?php echo htmlspecialchars($ficha['cep'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>Bairro <span class="required">*</span></label>
                <input type="text" name="bairro" value="<?php echo htmlspecialchars($ficha['bairro'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>Cidade <span class="required">*</span></label>
                <input type="text" name="cidade" value="<?php echo htmlspecialchars($ficha['cidade'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>Complemento</label>
                <input type="text" name="complemento" value="<?php echo htmlspecialchars($ficha['complemento'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>Ponto de Referência</label>
                <input type="text" name="ponto_referencia" value="<?php echo htmlspecialchars($ficha['ponto_referencia'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>Escola</label>
                <input type="text" name="escola" value="<?php echo htmlspecialchars($ficha['escola'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>Período</label>
                <select name="periodo">
                    <option value="">Selecionar</option>
                    <option <?php echo ($ficha['periodo'] ?? '') === 'Manhã' ? 'selected' : ''; ?>>Manhã</option>
                    <option <?php echo ($ficha['periodo'] ?? '') === 'Tarde' ? 'selected' : ''; ?>>Tarde</option>
                    <option <?php echo ($ficha['periodo'] ?? '') === 'Noite' ? 'selected' : ''; ?>>Noite</option>
                </select>
            </div>
            <div class="form-field">
                <label>CRAS de Referência</label>
                <input type="text" name="cras" value="<?php echo htmlspecialchars($ficha['cras'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>UBS de Referência</label>
                <input type="text" name="ubs" value="<?php echo htmlspecialchars($ficha['ubs'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- Etapa 3: Responsável -->
    <div class="form-section">
        <h3 class="form-section-title">3. Responsável</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Nome do Responsável <span class="required">*</span></label>
                <input type="text" name="nome_responsavel" value="<?php echo htmlspecialchars($ficha['nome_responsavel'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>RG do Responsável <span class="required">*</span></label>
                <input type="text" name="rg_responsavel" value="<?php echo htmlspecialchars($ficha['rg_responsavel'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>CPF do Responsável <span class="required">*</span></label>
                <input type="text" name="cpf_responsavel" value="<?php echo htmlspecialchars($ficha['cpf_responsavel'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>Grau de Parentesco <span class="required">*</span></label>
                <input type="text" name="grau_parentesco" value="<?php echo htmlspecialchars($ficha['grau_parentesco'] ?? ''); ?>" required>
            </div>
            <div class="form-field">
                <label>Contato 1 (obrigatório) <span class="required">*</span></label>
                <input type="text" name="contato_1" value="<?php echo htmlspecialchars($ficha['contato_1'] ?? ''); ?>" placeholder="11999998888 (apenas números)" required>
            </div>
        </div>
    </div>

    <!-- Etapa 4: Documentos -->
    <div class="form-section">
        <h3 class="form-section-title">4. Documentos</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Cadastro Único</label>
                <select name="cad_unico">
                    <option value="">Selecionar</option>
                    <option <?php echo ($ficha['cad_unico'] ?? '') === 'Possuo' ? 'selected' : ''; ?>>Possuo</option>
                    <option <?php echo ($ficha['cad_unico'] ?? '') === 'Não Possuo' ? 'selected' : ''; ?>>Não Possuo</option>
                </select>
            </div>
            <div class="form-field">
                <label>Responsável pelo Acolhimento</label>
                <input type="text" name="acolhimento_responsavel" value="<?php echo htmlspecialchars($ficha['acolhimento_responsavel'] ?? ''); ?>">
            </div>
            <div class="form-field">
                <label>Função</label>
                <input type="text" name="acolhimento_funcao" value="<?php echo htmlspecialchars($ficha['acolhimento_funcao'] ?? ''); ?>">
            </div>
            <div class="form-field" style="grid-column: 1 / -1;">
                <label>Carimbo/Assinatura</label>
                <input type="file" name="carimbo" accept="image/*">
                <small>Envie uma imagem do carimbo ou assinatura (JPG, PNG, GIF - máx. 2MB)</small>
                <?php if (!empty($ficha['carimbo'])): ?>
                    <div style="margin-top:8px;">
                        <small>Arquivo atual: <?php echo basename($ficha['carimbo']); ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo !empty($editId) ? 'acolhimento_list.php' : 'prontuarios.php'; ?>" class="btn-secondary">Voltar</a>
        <button class="btn-primary" type="submit">
            <?php if (!empty($editId)): ?>
                <i class="fas fa-save"></i> Salvar Alteração
            <?php else: ?>
                <i class="fas fa-check"></i> Cadastrar
            <?php endif; ?>
        </button>
    </div>
</form>

<script src="js/acolhimento-multistep.js"></script>
