/**
 * Script específico para a Ficha de Acolhimento
 * Máscaras, validações e funcionalidades dinâmicas
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== MÁSCARAS DE ENTRADA =====
    
    // Máscara para CPF (000.000.000-00)
    function applyCPFMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 11 dígitos
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplicar máscara
            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }

    // Máscara para RG (00.000.000-0)
    function applyRGMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 9 dígitos
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            
            // Aplicar máscara
            if (value.length > 7) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
            } else if (value.length > 5) {
                value = value.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{1,3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }

    // Máscara para Data (dd/mm/aaaa)
    function applyDateMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 8 dígitos
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            
            // Aplicar máscara
            if (value.length > 4) {
                value = value.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{1,2})/, '$1/$2');
            }
            
            e.target.value = value;
        });
    }

    // Máscara para Telefone com formatação (00) 00000-0000
    function applyPhoneMask(input) {
        input.addEventListener('input', function(e) {
            // Remover tudo que não é número
            let value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 11 dígitos
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplicar máscara de telefone
            if (value.length > 10) {
                // Celular: (00) 00000-0000
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                // Fixo ou celular incompleto
                if (value.length === 10) {
                    // Fixo: (00) 0000-0000
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                } else {
                    // Em digitação: (00) 00000-
                    value = value.replace(/(\d{2})(\d{4,5})(\d{0,4})/, '($1) $2-$3');
                }
            } else if (value.length > 2) {
                // Início: (00) 0000
                value = value.replace(/(\d{2})(\d{1,5})/, '($1) $2');
            } else if (value.length > 0) {
                // Apenas DDD: (00
                value = value.replace(/(\d{1,2})/, '($1');
            }
            
            e.target.value = value;
        });
        
        // Prevenir entrada de caracteres não numéricos
        input.addEventListener('keypress', function(e) {
            // Permitir apenas números, backspace, delete, tab, escape, enter
            if (!/[0-9]/.test(e.key) && 
                !['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }

    // Máscara para CEP (00000-000)
    function applyCEPMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 8 dígitos
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            
            // Aplicar máscara
            if (value.length > 5) {
                value = value.replace(/(\d{5})(\d{1,3})/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }

    // Máscara para apenas números
    function applyNumberMask(input) {
        input.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }

    // ===== FUNÇÃO PARA APLICAR TODAS AS MÁSCARAS =====
    
    function applyAllMasks() {
        // CPF
        const cpfInputs = document.querySelectorAll('input[name="cpf"], input[name="cpf_responsavel"]');
        cpfInputs.forEach(input => {
            if (!input.hasAttribute('data-mask-applied')) {
                applyCPFMask(input);
                input.setAttribute('data-mask-applied', 'true');
            }
        });

        // RG
        const rgInputs = document.querySelectorAll('input[name="rg"], input[name="rg_responsavel"]');
        rgInputs.forEach(input => {
            if (!input.hasAttribute('data-mask-applied')) {
                applyRGMask(input);
                input.setAttribute('data-mask-applied', 'true');
            }
        });

        // Datas
        const dateInputs = document.querySelectorAll('input[name="data_nascimento"], input[name="data_acolhimento"]');
        dateInputs.forEach(input => {
            if (!input.hasAttribute('data-mask-applied')) {
                applyDateMask(input);
                input.setAttribute('data-mask-applied', 'true');
            }
        });

        // Telefones
        const phoneInputs = document.querySelectorAll('input[name="contato_1"], input[name="contato_2"], input[name="contato_3"], input[name="contato_4"]');
        phoneInputs.forEach(input => {
            if (!input.hasAttribute('data-mask-applied')) {
                applyPhoneMask(input);
                input.setAttribute('data-mask-applied', 'true');
            }
        });

        // CEP
        const cepInputs = document.querySelectorAll('input[name="cep"]');
        cepInputs.forEach(input => {
            if (!input.hasAttribute('data-mask-applied')) {
                applyCEPMask(input);
                input.setAttribute('data-mask-applied', 'true');
            }
        });

        // Números (endereço)
        const numberInputs = document.querySelectorAll('input[name="numero"]');
        numberInputs.forEach(input => {
            if (!input.hasAttribute('data-mask-applied')) {
                applyNumberMask(input);
                input.setAttribute('data-mask-applied', 'true');
            }
        });
    }

    // ===== VALIDAÇÕES =====
    
    function validateCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        
        // Verificar se tem 11 dígitos
        if (cpf.length !== 11) return false;
        
        // Verificar se todos os dígitos são iguais (CPF inválido)
        if (/^(\d)\1{10}$/.test(cpf)) return false;
        
        // Para simplificar, vamos aceitar qualquer CPF que tenha 11 dígitos e não seja sequência
        return true;
    }

    function validateDate(dateString) {
        const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        const match = dateString.match(regex);
        if (!match) return false;
        
        const day = parseInt(match[1]);
        const month = parseInt(match[2]);
        const year = parseInt(match[3]);
        
        const date = new Date(year, month - 1, day);
        return date.getFullYear() === year && 
               date.getMonth() === month - 1 && 
               date.getDate() === day;
    }

    function validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        // Aceitar telefones com 10 ou 11 dígitos (mínimo 8 para ser flexível)
        return cleaned.length >= 8 && cleaned.length <= 11;
    }

    // ===== SISTEMA DE CONTATOS DINÂMICOS =====
    
    let contactCount = 1;
    const maxContacts = 4;

    function createAddContactButton() {
        if (contactCount >= maxContacts) return;

        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'btn-add-contact';
        addButton.innerHTML = '+ Adicionar outro contato';
        addButton.style.cssText = `
            background: #6fb64f;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: background 0.3s ease;
        `;

        addButton.addEventListener('mouseover', function() {
            this.style.background = '#5aa03f';
        });

        addButton.addEventListener('mouseout', function() {
            this.style.background = '#6fb64f';
        });

        addButton.addEventListener('click', function() {
            addContactField();
            this.remove();
        });

        return addButton;
    }

    function addContactField() {
        if (contactCount >= maxContacts) return;

        contactCount++;
        
        // Encontrar onde inserir o novo contato (após o último contato existente)
        const contact1Container = document.querySelector('input[name="contato_1"]')?.parentElement?.parentElement;
        if (!contact1Container) {
            console.error('Container de contato 1 não encontrado');
            return;
        }

        // Criar novo campo de contato
        const newContactDiv = document.createElement('div');
        newContactDiv.innerHTML = `
            <label>Contato ${contactCount}</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="contato_${contactCount}" placeholder="(11) 99999-8888">
                <button type="button" class="btn-remove-contact" style="background: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">×</button>
            </div>
        `;

        // Inserir o novo contato após o contato 1 (ou após o último contato adicionado)
        const lastContact = document.querySelector(`input[name="contato_${contactCount-1}"]`)?.parentElement?.parentElement || contact1Container;
        lastContact.parentElement.insertBefore(newContactDiv, lastContact.nextSibling);

        // Aplicar máscara ao novo campo
        const newInput = newContactDiv.querySelector('input');
        if (newInput && !newInput.hasAttribute('data-mask-applied')) {
            applyPhoneMask(newInput);
            newInput.setAttribute('data-mask-applied', 'true');
        }

        // Adicionar evento de remoção
        const removeBtn = newContactDiv.querySelector('.btn-remove-contact');
        removeBtn.addEventListener('click', function() {
            newContactDiv.remove();
            contactCount--;
            updateAddContactButton();
        });

        // Adicionar botão para próximo contato se necessário
        updateAddContactButton();
    }

    function updateAddContactButton() {
        // Remover botão existente
        const existingBtn = document.querySelector('.btn-add-contact');
        if (existingBtn) existingBtn.remove();

        // Adicionar novo botão se ainda há espaço
        if (contactCount < maxContacts) {
            const buttonContainer = document.querySelector('.contacts-container');
            if (buttonContainer) {
                buttonContainer.appendChild(createAddContactButton());
            }
        }
    }

    // ===== VALIDAÇÃO DO FORMULÁRIO =====
    
    function validateForm() {
        const requiredFields = [
            'nome_completo', 'data_nascimento', 'rg', 'cpf', 'queixa_principal',
            'endereco', 'numero', 'bairro', 'cidade', 'cep',
            'nome_responsavel', 'rg_responsavel', 'cpf_responsavel', 'contato_1'
        ];

        let isValid = true;
        const errors = [];

        requiredFields.forEach(fieldName => {
            const field = document.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"], select[name="${fieldName}"]`);
            if (field && !field.value.trim()) {
                isValid = false;
                errors.push(`O campo "${field.previousElementSibling?.textContent || fieldName}" é obrigatório.`);
                field.style.borderColor = '#e74c3c';
            } else if (field) {
                field.style.borderColor = '#f0a36b';
            }
        });

        // Validações específicas
        const cpfField = document.querySelector('input[name="cpf"]');
        if (cpfField && cpfField.value && !validateCPF(cpfField.value)) {
            isValid = false;
            errors.push('CPF inválido.');
            cpfField.style.borderColor = '#e74c3c';
        }

        const cpfRespField = document.querySelector('input[name="cpf_responsavel"]');
        if (cpfRespField && cpfRespField.value && !validateCPF(cpfRespField.value)) {
            isValid = false;
            errors.push('CPF do responsável inválido.');
            cpfRespField.style.borderColor = '#e74c3c';
        }

        const dateFields = ['data_nascimento', 'data_acolhimento'];
        dateFields.forEach(fieldName => {
            const field = document.querySelector(`input[name="${fieldName}"]`);
            if (field && field.value && !validateDate(field.value)) {
                isValid = false;
                errors.push(`Data inválida no campo "${field.previousElementSibling?.textContent || fieldName}".`);
                field.style.borderColor = '#e74c3c';
            }
        });

        const phoneField = document.querySelector('input[name="contato_1"]');
        if (phoneField && phoneField.value && !validatePhone(phoneField.value)) {
            isValid = false;
            errors.push('Telefone de contato inválido.');
            phoneField.style.borderColor = '#e74c3c';
        }

        if (!isValid) {
            showValidationErrors(errors);
        }

        return isValid;
    }

    function showValidationErrors(errors) {
        // Remover mensagens anteriores
        const existingErrors = document.querySelectorAll('.validation-error');
        existingErrors.forEach(error => error.remove());

        // Criar container de erros
        const errorContainer = document.createElement('div');
        errorContainer.className = 'validation-error';
        errorContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            max-width: 400px;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        `;

        const title = document.createElement('h4');
        title.textContent = 'Erros encontrados:';
        title.style.margin = '0 0 10px 0';
        errorContainer.appendChild(title);

        const errorList = document.createElement('ul');
        errorList.style.margin = '0';
        errorList.style.paddingLeft = '20px';
        
        errors.forEach(error => {
            const listItem = document.createElement('li');
            listItem.textContent = error;
            listItem.style.marginBottom = '5px';
            errorList.appendChild(listItem);
        });

        errorContainer.appendChild(errorList);
        document.body.appendChild(errorContainer);

        // Remover após 8 segundos
        setTimeout(() => {
            if (errorContainer.parentNode) {
                errorContainer.remove();
            }
        }, 8000);
    }

    // ===== FUNÇÃO PARA ADICIONAR ASTERISCOS VERMELHOS =====
    
    function addRequiredAsterisks() {
        const requiredFields = [
            'nome_completo', 'rg', 'cpf', 'data_nascimento', 'data_acolhimento', 'queixa_principal',
            'endereco', 'numero', 'cep', 'bairro', 'cidade',
            'nome_responsavel', 'rg_responsavel', 'cpf_responsavel', 'grau_parentesco', 'contato_1'
        ];

        requiredFields.forEach(fieldName => {
            const field = document.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"], select[name="${fieldName}"]`);
            if (field) {
                const label = field.previousElementSibling;
                if (label && label.tagName === 'LABEL') {
                    // Verificar se já não tem asterisco
                    if (!label.querySelector('.required-asterisk')) {
                        const asterisk = document.createElement('span');
                        asterisk.className = 'required-asterisk';
                        asterisk.innerHTML = ' *';
                        asterisk.style.cssText = 'color: #e74c3c; font-weight: bold; margin-left: 2px;';
                        label.appendChild(asterisk);
                    }
                }
            }
        });
    }

    // ===== INICIALIZAÇÃO =====
    
    // Interceptar submissão do formulário
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Inicializar sistema de contatos dinâmicos - usar campos existentes
    const contact1Input = document.querySelector('input[name="contato_1"]');
    if (contact1Input) {
        // Remover campos de contato 2, 3, 4 se existirem (serão criados dinamicamente)
        const extraContacts = document.querySelectorAll('input[name="contato_2"], input[name="contato_3"], input[name="contato_4"]');
        extraContacts.forEach(input => {
            const container = input.parentElement?.parentElement;
            if (container) container.remove();
        });
        
        // Encontrar onde adicionar o botão "Adicionar contato"
        const contact1Container = contact1Input.parentElement?.parentElement;
        if (contact1Container && contact1Container.parentElement) {
            // Criar container para o botão após o contato 1
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'contacts-container';
            buttonContainer.style.gridColumn = '1 / -1'; // Ocupar toda a largura
            
            // Inserir após o contato 1
            contact1Container.parentElement.insertBefore(buttonContainer, contact1Container.nextSibling);
            
            // Adicionar botão inicial
            updateAddContactButton();
        }
    }

    // Aplicar todas as máscaras
    applyAllMasks();

    // Adicionar asteriscos vermelhos nos campos obrigatórios
    addRequiredAsterisks();

    console.log('🎯 Script da Ficha de Acolhimento carregado com sucesso!');
});
