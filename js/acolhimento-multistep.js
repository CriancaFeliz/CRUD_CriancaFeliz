/**
 * Sistema Multi-Step para Ficha de Acolhimento
 * Inspirado no sistema da Ficha Socioeconômica
 */

// Estado atual
let currentStep = 1;
const totalSteps = 4;

// Obter parâmetros da URL
const urlParams = new URLSearchParams(window.location.search);
const editId = urlParams.get('id');

console.log('🎯 Acolhimento Multi-Step Iniciado');
console.log('📄 Etapa atual:', currentStep);
console.log('🔑 ID de edição:', editId || 'Nenhum');

// Inicializar ao carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM Carregado');
    
    // Se NÃO for edição, limpar sessionStorage
    if (!editId) {
        console.log('🆕 Nova ficha - Limpando dados salvos');
        for (let i = 1; i <= totalSteps; i++) {
            sessionStorage.removeItem(`acolhimento_step${i}`);
        }
    } else {
        // Se for edição, carregar dados salvos
        console.log('✏️ Edição - Carregando dados salvos');
        loadSavedData();
    }
    
    // Mostrar apenas a primeira etapa
    showStep(1);
    
    // Aplicar máscaras
    applyAllMasks();
    
    // Configurar navegação
    setupNavigation();
});

/**
 * Mostrar etapa específica
 */
function showStep(step) {
    console.log(`📍 Mostrando etapa ${step}`);
    
    currentStep = step;
    
    // Esconder todas as seções
    document.querySelectorAll('.form-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Mostrar seção atual
    const sections = document.querySelectorAll('.form-section');
    if (sections[step - 1]) {
        sections[step - 1].style.display = 'block';
    }
    
    // Atualizar indicadores visuais
    updateStepIndicators();
    
    // Atualizar botões
    updateNavigationButtons();
}

/**
 * Atualizar indicadores visuais das etapas
 */
function updateStepIndicators() {
    const indicators = document.querySelectorAll('.acolhimento-stepper .step');
    indicators.forEach((indicator, index) => {
        if (index + 1 === currentStep) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

/**
 * Atualizar botões de navegação
 */
function updateNavigationButtons() {
    const formActions = document.querySelector('.form-actions');
    if (!formActions) return;
    
    // Limpar botões existentes
    formActions.innerHTML = '';
    
    // Botão Voltar (sempre presente)
    const btnBack = document.createElement('a');
    btnBack.href = 'prontuarios.php';
    btnBack.className = 'btn-secondary';
    btnBack.textContent = 'Voltar';
    formActions.appendChild(btnBack);
    
    // Botão Anterior (se não for primeira etapa)
    if (currentStep > 1) {
        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.className = 'btn-secondary';
        btnPrev.innerHTML = '<i class="fas fa-arrow-left"></i> Anterior';
        btnPrev.onclick = previousStep;
        formActions.appendChild(btnPrev);
    }
    
    // Botão Próximo ou Finalizar
    if (currentStep < totalSteps) {
        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.className = 'btn-primary';
        btnNext.innerHTML = 'Próximo <i class="fas fa-arrow-right"></i>';
        btnNext.onclick = nextStep;
        formActions.appendChild(btnNext);
    } else {
        const btnSubmit = document.createElement('button');
        btnSubmit.type = 'submit';
        btnSubmit.className = 'btn-primary';
        if (editId) {
            btnSubmit.innerHTML = '<i class="fas fa-save"></i> Salvar Alteração';
        } else {
            btnSubmit.innerHTML = '<i class="fas fa-check"></i> Cadastrar';
        }
        formActions.appendChild(btnSubmit);
    }
}

/**
 * Próxima etapa
 */
function nextStep() {
    console.log('➡️ Avançando para próxima etapa');
    
    // Salvar dados da etapa atual
    saveStepData(currentStep);
    
    // Validar campos obrigatórios da etapa atual
    if (!validateCurrentStep()) {
        alert('Por favor, preencha todos os campos obrigatórios antes de continuar.');
        return;
    }
    
    if (currentStep < totalSteps) {
        showStep(currentStep + 1);
        window.scrollTo(0, 0);
    }
}

/**
 * Etapa anterior
 */
function previousStep() {
    console.log('⬅️ Voltando para etapa anterior');
    
    // Salvar dados da etapa atual
    saveStepData(currentStep);
    
    if (currentStep > 1) {
        showStep(currentStep - 1);
        window.scrollTo(0, 0);
    }
}

/**
 * Validar campos obrigatórios da etapa atual
 */
function validateCurrentStep() {
    const currentSection = document.querySelectorAll('.form-section')[currentStep - 1];
    const requiredFields = currentSection.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.focus();
            return false;
        }
    }
    
    return true;
}

/**
 * Salvar dados da etapa no sessionStorage
 */
function saveStepData(step) {
    console.log(`💾 Salvando dados da etapa ${step}`);
    
    const section = document.querySelectorAll('.form-section')[step - 1];
    const inputs = section.querySelectorAll('input, select, textarea');
    
    const data = {};
    inputs.forEach(input => {
        if (input.name && input.type !== 'file') {
            data[input.name] = input.value;
        }
    });
    
    sessionStorage.setItem(`acolhimento_step${step}`, JSON.stringify(data));
    console.log(`✅ Dados da etapa ${step} salvos:`, data);
}

/**
 * Carregar dados salvos do sessionStorage
 */
function loadSavedData() {
    console.log('📂 Carregando dados salvos...');
    
    for (let step = 1; step <= totalSteps; step++) {
        const savedData = sessionStorage.getItem(`acolhimento_step${step}`);
        if (savedData) {
            const data = JSON.parse(savedData);
            console.log(`✅ Dados da etapa ${step} encontrados:`, data);
            
            // Preencher campos
            Object.keys(data).forEach(name => {
                const input = document.querySelector(`[name="${name}"]`);
                if (input && input.type !== 'file') {
                    input.value = data[name];
                }
            });
        }
    }
}

/**
 * Consolidar dados de todas as etapas antes de enviar
 */
function consolidateAllSteps() {
    console.log('📦 Consolidando dados de todas as etapas...');
    
    const form = document.getElementById('acolhimentoForm');
    const allData = {};
    
    // Coletar dados de todas as etapas
    for (let i = 1; i <= totalSteps; i++) {
        const stepData = sessionStorage.getItem(`acolhimento_step${i}`);
        if (stepData) {
            Object.assign(allData, JSON.parse(stepData));
        }
    }
    
    // Adicionar campos hidden para cada dado
    Object.keys(allData).forEach(key => {
        let field = form.querySelector(`[name="${key}"]`);
        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = key;
            form.appendChild(field);
        }
        field.value = allData[key];
    });
    
    console.log('✅ Consolidação completa!', allData);
}

/**
 * Configurar navegação e submit
 */
function setupNavigation() {
    const form = document.getElementById('acolhimentoForm');
    
    form.addEventListener('submit', function(e) {
        if (currentStep === totalSteps) {
            console.log('📤 Enviando formulário...');
            
            // Salvar última etapa
            saveStepData(totalSteps);
            
            // Consolidar todos os dados
            consolidateAllSteps();
            
            // Limpar sessionStorage após envio
            setTimeout(() => {
                for (let i = 1; i <= totalSteps; i++) {
                    sessionStorage.removeItem(`acolhimento_step${i}`);
                }
            }, 100);
            
            return true; // Permitir envio
        } else {
            e.preventDefault();
            nextStep();
        }
    });
}

/**
 * Aplicar máscaras em todos os campos
 */
function applyAllMasks() {
    // CPF
    document.querySelectorAll('input[name="cpf"], input[name="cpf_responsavel"]').forEach(input => {
        if (input.getAttribute('data-mask-applied')) return;
        input.setAttribute('data-mask-applied', 'true');
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.substring(0, 11);
            
            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    });
    
    // RG
    document.querySelectorAll('input[name="rg"], input[name="rg_responsavel"]').forEach(input => {
        if (input.getAttribute('data-mask-applied')) return;
        input.setAttribute('data-mask-applied', 'true');
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.substring(0, 9);
            
            if (value.length > 8) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
            } else if (value.length > 5) {
                value = value.replace(/(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    });
    
    // Telefone
    document.querySelectorAll('input[name="contato_1"]').forEach(input => {
        if (input.getAttribute('data-mask-applied')) return;
        input.setAttribute('data-mask-applied', 'true');
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.substring(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            
            e.target.value = value;
        });
    });
    
    // CEP
    document.querySelectorAll('input[name="cep"]').forEach(input => {
        if (input.getAttribute('data-mask-applied')) return;
        input.setAttribute('data-mask-applied', 'true');
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.substring(0, 8);
            
            if (value.length > 5) {
                value = value.replace(/(\d{5})(\d{0,3})/, '$1-$2');
            }
            
            e.target.value = value;
        });
    });
    
    // Data
    document.querySelectorAll('input[name="data_nascimento"], input[name="data_acolhimento"]').forEach(input => {
        if (input.getAttribute('data-mask-applied')) return;
        input.setAttribute('data-mask-applied', 'true');
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.substring(0, 8);
            
            if (value.length > 4) {
                value = value.replace(/(\d{2})(\d{2})(\d{0,4})/, '$1/$2/$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,2})/, '$1/$2');
            }
            
            e.target.value = value;
        });
    });
    
    console.log('✅ Máscaras aplicadas');
}
