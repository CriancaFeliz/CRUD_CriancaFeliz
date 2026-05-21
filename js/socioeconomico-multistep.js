// Armazenar dados da família
let familyMembers = [];
let despesasCount = 0;

// Navegação entre etapas
function nextStep() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    const editId = urlParams.get('id');
    
    console.log('⟳ Tentando avançar da etapa', currentStep);
    
    // SEMPRE salvar dados antes de validar (para não perder informações)
    saveStepData(currentStep);
    
    // Validar campos obrigatórios da etapa atual
    if (!validateCurrentStep(currentStep)) {
        console.log('✗ Validação falhou na etapa', currentStep);
        return;
    }
    
    console.log('✓ Validação OK, avançando para etapa', currentStep + 1);
    
    // Ir para próxima etapa, preservando o ID se existir
    let url = `socioeconomico_form.php?step=${currentStep + 1}`;
    if (editId) {
        url += `&id=${editId}`;
    }
    window.location.href = url;
}

function prevStep() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    const editId = urlParams.get('id');
    
    // Voltar para etapa anterior, preservando o ID se existir
    let url = `socioeconomico_form.php?step=${currentStep - 1}`;
    if (editId) {
        url += `&id=${editId}`;
    }
    window.location.href = url;
}

function validateCurrentStep(step) {
    const form = document.getElementById('socioeconomicoForm');
    
    if (!form) {
        console.error('Formulário não encontrado');
        return true; // Permitir navegação se formulário não existir
    }
    
    // Validação específica por etapa
    if (step === 1) {
        // Campos obrigatórios da Etapa 1
        const nomeEntrevistado = form.querySelector('[name="nome_entrevistado"]');
        const nomeMenor = form.querySelector('[name="nome_menor"]');
        const rg = form.querySelector('[name="rg"]');
        const cpf = form.querySelector('[name="cpf"]');
        
        console.log('Validando etapa 1:', {
            nomeEntrevistado: nomeEntrevistado?.value,
            nomeMenor: nomeMenor?.value,
            rg: rg?.value,
            cpf: cpf?.value
        });
        
        // Nome do Entrevistado - OBRIGATÓRIO
        if (!nomeEntrevistado || !nomeEntrevistado.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o Nome do Entrevistado');
            if (nomeEntrevistado) {
                nomeEntrevistado.focus();
                nomeEntrevistado.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // Nome do Menor - OBRIGATÓRIO
        if (!nomeMenor || !nomeMenor.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o Nome do Menor');
            if (nomeMenor) {
                nomeMenor.focus();
                nomeMenor.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // RG - OBRIGATÓRIO
        if (!rg || !rg.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o RG do Entrevistado');
            if (rg) {
                rg.focus();
                rg.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // CPF - OBRIGATÓRIO
        if (!cpf || !cpf.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o CPF do Entrevistado');
            if (cpf) {
                cpf.focus();
                cpf.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // Validar CPF (deve ter 11 dígitos)
        const cpfDigits = cpf.value.replace(/\D/g, '');
        if (cpfDigits.length !== 11) {
            alert('❌ CPF inválido:\n\nO CPF deve ter 11 dígitos');
            cpf.focus();
            cpf.style.borderColor = '#e74c3c';
            return false;
        }
        
        // Validar RG (deve ter 9 dígitos)
        const rgDigits = rg.value.replace(/\D/g, '');
        if (rgDigits.length !== 9) {
            alert('❌ RG inválido:\n\nO RG deve ter 9 dígitos');
            rg.focus();
            rg.style.borderColor = '#e74c3c';
            return false;
        }
        
        // Resetar bordas se tudo estiver OK
        nomeEntrevistado.style.borderColor = '#4a7c8f';
        nomeMenor.style.borderColor = '#4a7c8f';
        rg.style.borderColor = '#4a7c8f';
        cpf.style.borderColor = '#4a7c8f';
    }
    
    return true;
}

function saveStepData(step) {
    const form = document.getElementById('socioeconomicoForm');
    if (!form) {
        console.error('❌ Formulário não encontrado para salvar dados');
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) { // Só salvar se tiver valor
            data[key] = value;
        }
    }
    
    const dataStr = JSON.stringify(data);
    sessionStorage.setItem(`socioeconomico_step${step}`, dataStr);
    
    console.log('💿 Salvando dados da etapa', step, ':', data);
    console.log('📦 Tamanho dos dados:', dataStr.length, 'caracteres');
    
    // Verificar se foi salvo corretamente
    const saved = sessionStorage.getItem(`socioeconomico_step${step}`);
    if (saved) {
        console.log('✓ Dados salvos com sucesso no sessionStorage');
    } else {
        console.error('✗ ERRO: Dados não foram salvos!');
    }
}

/**
 * Consolida todos os dados das etapas anteriores em campos hidden
 * para serem enviados junto com o formulário
 */
function consolidateAllSteps() {
    console.log('📦 Consolidando dados de todas as etapas...');
    
    const form = document.getElementById('socioeconomicoForm');
    if (!form) {
        console.error('❌ Formulário não encontrado');
        return;
    }
    
    // ⚠️ IMPORTANTE: Garantir que o ID de edição seja preservado
    const editIdField = form.querySelector('[name="id"]');
    if (editIdField && editIdField.value) {
        console.log('✓ ID de edição encontrado:', editIdField.value);
    }
    
    // Coletar dados de todas as etapas
    const allData = {};
    
    for (let i = 1; i <= 5; i++) {
        const stepData = sessionStorage.getItem(`socioeconomico_step${i}`);
        if (stepData) {
            try {
                const data = JSON.parse(stepData);
                Object.assign(allData, data); // Mesclar dados
                console.log(`  ✓ Etapa ${i}: ${Object.keys(data).length} campos`);
            } catch (error) {
                console.error(`  ✗ Erro ao ler etapa ${i}:`, error);
            }
        }
    }
    
    console.log('📊 Total de campos consolidados:', Object.keys(allData).length);
    console.log('📋 Dados consolidados:', allData);
    
    // Consolidar despesas (campos despesa_nome_X e despesa_valor_X)
    const despesas = [];
    Object.keys(allData).forEach(key => {
        if (key.startsWith('despesa_nome_')) {
            const index = key.replace('despesa_nome_', '');
            const valorKey = `despesa_valor_${index}`;
            const nome = allData[key];
            const valor = parseFloat(allData[valorKey] || 0);

            if (nome && nome.trim() && valor > 0) {
                despesas.push({ nome: nome.trim(), valor: valor, tipo: nome.trim(), renda: valor });
            }
        }

        // Suportar campos de despesa padrão: desp_agua, desp_luz, desp_gas, desp_telefone, desp_celular, desp_internet, desp_alimentacao
        if (key.startsWith('desp_')) {
            const tipo = key.replace('desp_', '');
            const valor = parseFloat(allData[key] || 0);
            if (!isNaN(valor) && valor > 0) {
                // Normalizar nomes (agua, luz -> energia/luz)
                let nomeTipo = tipo;
                if (tipo === 'luz') nomeTipo = 'energia';
                despesas.push({ nome: nomeTipo, valor: valor, tipo: nomeTipo, renda: valor });
            }
        }
    });
    
    // Salvar despesas como JSON se houver
    if (despesas.length > 0) {
        // Garantir que campo hidden existe
        let despesasField = form.querySelector('[name="despesas_json"]');
        if (!despesasField) {
            despesasField = document.createElement('input');
            despesasField.type = 'hidden';
            despesasField.name = 'despesas_json';
            form.appendChild(despesasField);
        }
        despesasField.value = JSON.stringify(despesas);
        console.log(`  ✓ Despesas consolidadas: ${despesas.length} itens`, despesas);
    }

    // Normalizações e mapeamentos de campos para compatibilidade com o model
    // 1) Número de cômodos: formulário usa 'num_comodos'
    if (allData['num_comodos']) {
        let v = allData['num_comodos'];
        v = parseInt(v) || 0;
        let f = form.querySelector('[name="numero_comodos"]');
        if (!f) { f = document.createElement('input'); f.type = 'hidden'; f.name = 'numero_comodos'; form.appendChild(f); }
        f.value = v;
        let f2 = form.querySelector('[name="nr_comodos"]');
        if (!f2) { f2 = document.createElement('input'); f2.type = 'hidden'; f2.name = 'nr_comodos'; form.appendChild(f2); }
        f2.value = v;
    }

    // 2) Condições -> cond_residencia / situacao_moradia
    if (allData['condicoes']) {
        const v = allData['condicoes'];
        let f = form.querySelector('[name="cond_residencia"]');
        if (!f) { f = document.createElement('input'); f.type = 'hidden'; f.name = 'cond_residencia'; form.appendChild(f); }
        f.value = v;
        let f2 = form.querySelector('[name="situacao_moradia"]');
        if (!f2) { f2 = document.createElement('input'); f2.type = 'hidden'; f2.name = 'situacao_moradia'; form.appendChild(f2); }
        f2.value = v;
    }

    // 3) Residencia (ownership) -> mapar para tipo_moradia / moradia
    if (allData['residencia']) {
        const v = allData['residencia'];
        let f = form.querySelector('[name="tipo_moradia"]');
        if (!f) { f = document.createElement('input'); f.type = 'hidden'; f.name = 'tipo_moradia'; form.appendChild(f); }
        f.value = v;
        let f2 = form.querySelector('[name="moradia"]');
        if (!f2) { f2 = document.createElement('input'); f2.type = 'hidden'; f2.name = 'moradia'; form.appendChild(f2); }
        f2.value = v;
    }

    // 4) Número de veículos: somar campos veiculos_* e preencher nr_veiculos
    let totalVeiculos = 0;
    ['veiculos_motocicleta','veiculos_automovel','veiculos_caminhonete','veiculos_caminhao','veiculos_outros'].forEach(k => {
        if (allData[k]) totalVeiculos += parseInt(allData[k]) || 0;
    });
    if (totalVeiculos > 0) {
        let f = form.querySelector('[name="nr_veiculos"]');
        if (!f) { f = document.createElement('input'); f.type = 'hidden'; f.name = 'nr_veiculos'; form.appendChild(f); }
        f.value = totalVeiculos;
    }

    // 5) Renda familiar: PRIORIZAR familia_json (para evitar dupla contagem)
    // Se houver dados de família com rendas, usar a soma da família
    // Caso contrário, usar renda_salario + renda_bolsa
    let rendaFamiliar = 0;
    let temFamiliaComRenda = false;
    
    try {
        if (allData['familia_json']) {
            const fam = JSON.parse(allData['familia_json']);
            if (Array.isArray(fam)) {
                fam.forEach(m => { 
                    if (m.renda && parseFloat(m.renda) > 0) {
                        rendaFamiliar += parseFloat(m.renda);
                        temFamiliaComRenda = true;
                    }
                });
            }
        }
    } catch (e) { 
        console.log('Erro ao processar familia_json:', e);
    }
    
    // Se NÃO houver renda na família, tentar usar campos diretos
    if (!temFamiliaComRenda) {
        if (allData['renda_salario']) rendaFamiliar += parseFloat(allData['renda_salario']) || 0;
        if (allData['renda_bolsa']) rendaFamiliar += parseFloat(allData['renda_bolsa']) || 0;
    }
    
    if (rendaFamiliar > 0) {
        let f = form.querySelector('[name="renda_familiar"]');
        if (!f) { f = document.createElement('input'); f.type = 'hidden'; f.name = 'renda_familiar'; form.appendChild(f); }
        f.value = rendaFamiliar.toFixed(2);
        
        // Renda per capita: dividir pela quantidade de membros da família
        let membros = parseInt(allData['num_comodos'] || allData['qtd_pessoas'] || 0) || 0;
        // Se houver familia_json, usar o tamanho real da família
        try {
            if (allData['familia_json']) {
                const fam = JSON.parse(allData['familia_json']);
                if (Array.isArray(fam) && fam.length > 0) {
                    membros = fam.length;
                }
            }
        } catch (e) { }
        
        let percapita = membros > 0 ? (rendaFamiliar / membros) : 0;
        let f2 = form.querySelector('[name="renda_per_capita"]');
        if (!f2) { f2 = document.createElement('input'); f2.type = 'hidden'; f2.name = 'renda_per_capita'; form.appendChild(f2); }
        f2.value = percapita.toFixed(2);
    }
    
    // Garantir que familia_json está no formulário
    if (familyMembers.length > 0) {
        let familiaField = form.querySelector('[name="familia_json"]');
        if (!familiaField) {
            familiaField = document.createElement('input');
            familiaField.type = 'hidden';
            familiaField.name = 'familia_json';
            form.appendChild(familiaField);
        }
        familiaField.value = JSON.stringify(familyMembers);
        console.log(`  ✓ Família consolidada: ${familyMembers.length} membros`);
    }
    
    // Adicionar campos hidden ao formulário para cada dado (exceto despesas já processadas)
    Object.keys(allData).forEach(key => {
        // Pular campos de despesas individuais (já foram consolidados)
        if (key.startsWith('despesa_nome_') || key.startsWith('despesa_valor_')) {
            return;
        }
        
        // Verificar se o campo já existe no formulário
        let field = form.querySelector(`[name="${key}"]`);
        
        if (!field) {
            // Se não existe, criar campo hidden
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = key;
            form.appendChild(field);
            console.log(`  + Criado campo hidden: ${key}`);
        }
        
        // Atualizar valor (se o campo estiver vazio, usar o valor salvo)
        if (!field.value || field.value === '') {
            field.value = allData[key];
            console.log(`  ✓ ${key} = ${allData[key]}`);
        }
    });
    
    console.log('✅ Consolidação completa!');
}

// Gestão de Composição Familiar
function openFamilyModal() {
    document.getElementById('familyModal').classList.add('active');
}

function closeFamilyModal() {
    document.getElementById('familyModal').classList.remove('active');
    clearFamilyForm();
}

function clearFamilyForm() {
    document.getElementById('family_nome').value = '';
    document.getElementById('family_parentesco').value = '';
    document.getElementById('family_data_nasc').value = '';
    document.getElementById('family_formacao').value = '';
    document.getElementById('family_renda').value = '';
}

function addFamilyMember() {
    const nome = document.getElementById('family_nome').value.trim();
    const parentesco = document.getElementById('family_parentesco').value;
    const dataNasc = document.getElementById('family_data_nasc').value;
    const formacao = document.getElementById('family_formacao').value;
    const renda = document.getElementById('family_renda').value;
    
    if (!nome || !parentesco) {
        alert('Por favor, preencha o nome e o parentesco');
        return;
    }
    
    // Converter renda: remover R$, pontos e converter vírgula em ponto
    let rendaNum = 0;
    if (renda) {
        let rendaStr = String(renda).replace(/R\$|\.(?=\d{3})|,/g, (m) => {
            if (m === ',') return '.';
            return '';
        });
        rendaNum = parseFloat(rendaStr) || 0;
    }
    
    const member = {
        id: Date.now(),
        nome,
        parentesco,
        dataNasc,
        formacao,
        renda: rendaNum
    };
    
    console.log(`✓ Membro adicionado: ${nome} (${parentesco}) - Renda: R$ ${rendaNum.toFixed(2)}`);
    
    familyMembers.push(member);
    updateFamilyList();
    closeFamilyModal();
    
    // Salvar no campo hidden
    document.getElementById('familia_json').value = JSON.stringify(familyMembers);
}

function removeFamilyMember(id) {
    if (!confirm('Deseja remover este integrante?')) {
        return;
    }
    
    familyMembers = familyMembers.filter(m => m.id !== id);
    updateFamilyList();
    
    // Atualizar campo hidden
    document.getElementById('familia_json').value = JSON.stringify(familyMembers);
}

function updateFamilyList() {
    const container = document.getElementById('familyList');
    
    if (familyMembers.length === 0) {
        container.innerHTML = '<div style="color:#6c757d; font-style:italic; text-align:center; padding:20px;">Nenhum integrante adicionado</div>';
        return;
    }
    
    container.innerHTML = familyMembers.map(member => `
        <div class="family-member">
            <div class="family-member-info">
                <div class="family-member-name">${member.nome}</div>
                <div class="family-member-details">
                    ${member.parentesco} • 
                    ${member.dataNasc || 'Data não informada'} • 
                    ${member.formacao || 'Formação não informada'} • 
                    R$ ${member.renda.toFixed(2)}
                </div>
            </div>
            <button type="button" onclick="removeFamilyMember(${member.id})" class="btn-remove">
                <i class="fas fa-trash"></i> Remover
            </button>
        </div>
    `).join('');
}

// Gestão de Despesas
function addDespesa() {
    despesasCount++;
    const container = document.getElementById('despesasAdicionais');
    
    const div = document.createElement('div');
    div.className = 'form-field';
    div.style.marginBottom = '12px';
    div.innerHTML = `
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" name="despesa_nome_${despesasCount}" placeholder="Nome da despesa" style="flex: 1; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
            <input type="number" name="despesa_valor_${despesasCount}" class="despesa-input" step="0.01" placeholder="Valor" style="width: 120px; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
            <button type="button" onclick="this.parentElement.parentElement.remove(); calcularTotais()" style="background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(div);
    
    // Adicionar listener ao novo input
    const newInput = div.querySelector('.despesa-input');
    newInput.addEventListener('input', calcularTotais);
}

function calcularTotais() {
    let totalDespesas = 0;
    let totalRenda = 0;
    
    // Somar todas as despesas
    const despesaInputs = document.querySelectorAll('.despesa-input');
    despesaInputs.forEach(input => {
        const valor = parseFloat(input.value) || 0;
        totalDespesas += valor;
    });
    
    // Somar todas as rendas
    const rendaInputs = document.querySelectorAll('.renda-input');
    rendaInputs.forEach(input => {
        const valor = parseFloat(input.value) || 0;
        totalRenda += valor;
    });
    
    // Atualizar displays
    const totalDespesasEl = document.getElementById('totalDespesas');
    const totalRendaEl = document.getElementById('totalRenda');
    
    if (totalDespesasEl) {
        totalDespesasEl.textContent = `R$ ${totalDespesas.toFixed(2)}`;
    }
    
    if (totalRendaEl) {
        totalRendaEl.textContent = `R$ ${totalRenda.toFixed(2)}`;
    }
}

// Toggle campos condicionais
function toggleCltField(show) {
    const field = document.getElementById('clt_field');
    if (field) {
        field.style.display = show ? 'block' : 'none';
    }
}

// Garantir que, ao submeter o formulário final, os dados consolidados sejam inseridos nos hidden fields
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('socioeconomicoForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        try {
            // Consolidar todos os passos antes do envio
            consolidateAllSteps();
        } catch (err) {
            console.error('Erro ao consolidar dados antes do submit:', err);
        }
        // Deixar o submit prosseguir (consolidation é síncrona)
    });
});

// Máscaras
function applyMasks() {
    // CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }
    
    // RG
    const rgInput = document.getElementById('rg');
    if (rgInput) {
        rgInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) value = value.substring(0, 9);
            
            if (value.length > 8) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
            } else if (value.length > 5) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }
    
    // Data
    const dataInputs = document.querySelectorAll('#data_acolhimento, #family_data_nasc');
    dataInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            
            if (value.length > 4) {
                value = value.replace(/(\d{2})(\d{2})(\d{4})/, '$1/$2/$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{2})/, '$1/$2');
            }
            
            e.target.value = value;
        });
    });
}

// Calcular total automaticamente quando valores mudarem
document.addEventListener('DOMContentLoaded', function() {
    // Desabilitar validação HTML5 do formulário
    const form = document.getElementById('socioeconomicoForm');
    if (form) {
        form.setAttribute('novalidate', 'novalidate');
        
        // Remover atributo required de todos os campos
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.removeAttribute('required');
        });
        
        // Prevenir submit acidental do formulário (exceto na última etapa)
        form.addEventListener('submit', function(e) {
            const currentStep = parseInt(new URLSearchParams(window.location.search).get('step') || '1');
            
            // Permitir submit apenas na última etapa (Etapa 5)
            if (currentStep === 5) {
                console.log('✓ Submit permitido na etapa final');
                
                // IMPORTANTE: Salvar família ANTES de consolidar
                console.log('📋 Salvando família antes do submit...');
                console.log('   familyMembers.length:', familyMembers.length);
                console.log('   familyMembers:', familyMembers);
                
                const familiaField = form.querySelector('[name="familia_json"]');
                if (familiaField) {
                    const familiaJson = JSON.stringify(familyMembers);
                    familiaField.value = familiaJson;
                    console.log('✓ Família salva antes do submit:', familyMembers.length, 'membros');
                    console.log('   Conteúdo familia_json:', familiaJson.substring(0, 200));
                } else {
                    console.error('✗ Campo familia_json não encontrado no formulário!');
                }
                
                // IMPORTANTE: Consolidar todos os dados das etapas anteriores
                console.log('📦 Consolidando todos os dados...');
                consolidateAllSteps();
                
                // Verificar se campos JSON foram populados APÓS consolidar
                const familiaCheck = form.querySelector('[name="familia_json"]');
                const despesasCheck = form.querySelector('[name="despesas_json"]');
                
                console.log('=== VERIFICAÇÃO FINAL ANTES DO SUBMIT ===');
                if (familiaCheck) {
                    const familiaValue = familiaCheck.value || '';
                    console.log('✓ familia_json encontrado:', familiaValue.length, 'caracteres');
                    if (familiaValue.length > 0) {
                        try {
                            const parsed = JSON.parse(familiaValue);
                            console.log('   ✓ JSON válido:', parsed.length, 'membros');
                            console.log('   Conteúdo:', familiaValue.substring(0, 200));
                        } catch (e) {
                            console.error('   ✗ JSON inválido:', e.message);
                        }
                    } else {
                        console.warn('   ⚠️ Campo está VAZIO');
                    }
                } else {
                    console.error('✗ Campo familia_json NÃO ENCONTRADO');
                }
                
                if (despesasCheck) {
                    const despesasValue = despesasCheck.value || '';
                    console.log('✓ despesas_json encontrado:', despesasValue.length, 'caracteres');
                    if (despesasValue.length > 0) {
                        try {
                            const parsed = JSON.parse(despesasValue);
                            console.log('   ✓ JSON válido:', parsed.length, 'itens');
                            console.log('   Conteúdo:', despesasValue.substring(0, 200));
                        } catch (e) {
                            console.error('   ✗ JSON inválido:', e.message);
                        }
                    } else {
                        console.warn('   ⚠️ Campo está VAZIO');
                    }
                } else {
                    console.error('✗ Campo despesas_json NÃO ENCONTRADO');
                }
                console.log('==========================================');
                
                return true; // Permitir submit
            }
            
            // Bloquear submit em outras etapas
            e.preventDefault();
            console.log('⚠ Submit bloqueado - Use os botões de navegação');
            return false;
        });
        
        console.log('🔧 Validação HTML5 desabilitada e submit bloqueado');
    }
    
    applyMasks();
    
    // Restaurar dados salvos
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    const editId = urlParams.get('id');
    
    // Se for nova ficha (sem ID) E primeira etapa, limpar sessionStorage
    if (!editId && currentStep === 1) {
        console.log('🆕 Nova ficha - Limpando dados salvos');
        for (let i = 1; i <= 5; i++) {
            sessionStorage.removeItem(`socioeconomico_step${i}`);
        }
        sessionStorage.removeItem('familia_json');
    }
    
    const savedData = sessionStorage.getItem(`socioeconomico_step${currentStep}`);
    
    console.log('📄 Etapa atual:', currentStep);
    console.log('🔑 ID de edição:', editId || 'Nenhum (novo cadastro)');
    console.log('💿 Dados salvos encontrados:', savedData ? 'Sim' : 'Não');
    
    // Debug: Mostrar todos os dados salvos
    console.log('📁 Dados salvos em todas as etapas:');
    for (let i = 1; i <= 5; i++) {
        const stepData = sessionStorage.getItem(`socioeconomico_step${i}`);
        if (stepData) {
            console.log(`  Etapa ${i}:`, JSON.parse(stepData));
        } else {
            console.log(`  Etapa ${i}: (vazio)`);
        }
    }
    
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            const form = document.getElementById('socioeconomicoForm');
            
            if (form) {
                console.log('⟳ Restaurando dados:', data);
                let fieldsRestored = 0;
                
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field && data[key]) {
                        field.value = data[key];
                        fieldsRestored++;
                        console.log(`  ✓ ${key}: ${data[key]}`);
                    }
                });
                
                console.log(`✓ ${fieldsRestored} campos restaurados`);
            }
        } catch (error) {
            console.error('✗ Erro ao restaurar dados:', error);
        }
    } else {
        console.log('ℹ Nenhum dado salvo para esta etapa');
    }
    
    // Restaurar família
    const familiaJson = sessionStorage.getItem('familia_json');
    if (familiaJson && currentStep === 3) {
        familyMembers = JSON.parse(familiaJson);
        updateFamilyList();
    }
    
    // Adicionar listeners para cálculo de total
    const despesaInputs = document.querySelectorAll('.despesa-input, .renda-input');
    despesaInputs.forEach(input => {
        input.addEventListener('input', calcularTotais);
    });
    
    // Calcular total inicial
    if (currentStep === 4) {
        calcularTotais();
    }
    
    // Restaurar estado dos campos condicionais
    if (currentStep === 5) {
        const cltRadio = document.querySelector('input[name="trabalho_clt"]:checked');
        if (cltRadio && cltRadio.value === 'Sim') {
            toggleCltField(true);
        }
    }
});

// Salvar família no sessionStorage antes de mudar de etapa
window.addEventListener('beforeunload', function() {
    if (familyMembers.length > 0) {
        sessionStorage.setItem('familia_json', JSON.stringify(familyMembers));
    }
});
