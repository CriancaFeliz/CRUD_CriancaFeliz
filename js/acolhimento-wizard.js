(function () {
    'use strict';

    const form = document.getElementById('acolhimentoForm');
    if (!form) {
        return;
    }

    const sections = Array.from(form.querySelectorAll('.form-section'));
    const indicators = Array.from(document.querySelectorAll('.acolhimento-stepper .step'));
    const actions = form.querySelector('.form-actions');
    const totalSteps = sections.length;
    const isEditing = Boolean(form.querySelector('input[name="id"]')?.value);
    let currentStep = 1;

    function createButton(label, className, type, onClick) {
        const button = document.createElement('button');
        button.type = type;
        button.className = className;
        button.textContent = label;
        if (onClick) {
            button.addEventListener('click', onClick);
        }
        return button;
    }

    function renderActions() {
        if (!actions) {
            return;
        }

        actions.replaceChildren();

        const cancel = document.createElement('a');
        cancel.href = isEditing ? 'acolhimento_list.php' : 'prontuarios.php';
        cancel.className = 'btn-secondary';
        cancel.textContent = 'Cancelar';
        actions.appendChild(cancel);

        if (currentStep > 1) {
            actions.appendChild(createButton('← Anterior', 'btn-secondary', 'button', previousStep));
        }

        if (currentStep < totalSteps) {
            actions.appendChild(createButton('Próximo →', 'btn-primary', 'button', nextStep));
        } else {
            actions.appendChild(createButton(isEditing ? 'Salvar alteração' : 'Cadastrar', 'btn-primary', 'submit'));
        }
    }

    function showStep(step) {
        currentStep = Math.max(1, Math.min(totalSteps, Number(step) || 1));

        sections.forEach((section, index) => {
            section.hidden = index + 1 !== currentStep;
        });

        indicators.forEach((indicator, index) => {
            const indicatorStep = index + 1;
            indicator.classList.toggle('active', indicatorStep === currentStep);
            indicator.classList.toggle('completed', indicatorStep < currentStep);
            if (indicatorStep === currentStep) {
                indicator.setAttribute('aria-current', 'step');
            } else {
                indicator.removeAttribute('aria-current');
            }
        });

        renderActions();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        const section = sections[currentStep - 1];
        const fields = Array.from(section.querySelectorAll('input, select, textarea'));
        for (const field of fields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }
        return true;
    }

    function nextStep() {
        if (validateCurrentStep()) {
            showStep(currentStep + 1);
        }
    }

    function previousStep() {
        showStep(currentStep - 1);
    }

    function maskDigits(input, maxLength, formatter) {
        input.addEventListener('input', (event) => {
            const digits = event.target.value.replace(/\D/g, '').slice(0, maxLength);
            event.target.value = formatter(digits);
        });
    }

    function applyMasks() {
        form.querySelectorAll('input[name="cpf"], input[name="cpf_responsavel"]').forEach((input) => {
            maskDigits(input, 11, (digits) => digits
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2'));
        });

        form.querySelectorAll('input[name="rg"], input[name="rg_responsavel"]').forEach((input) => {
            maskDigits(input, 9, (digits) => digits
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/(\d{3})(\d)$/, '$1-$2'));
        });

        form.querySelectorAll('input[name="contato_1"]').forEach((input) => {
            maskDigits(input, 11, (digits) => {
                if (digits.length <= 10) {
                    return digits.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)$/, '$1-$2');
                }
                return digits.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)$/, '$1-$2');
            });
        });

        form.querySelectorAll('input[name="cep"]').forEach((input) => {
            maskDigits(input, 8, (digits) => digits.replace(/(\d{5})(\d)$/, '$1-$2'));
        });

        form.querySelectorAll('input[name="data_nascimento"], input[name="data_acolhimento"]').forEach((input) => {
            maskDigits(input, 8, (digits) => digits
                .replace(/^(\d{2})(\d)/, '$1/$2')
                .replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3'));
        });
    }

    form.addEventListener('submit', (event) => {
        if (currentStep < totalSteps) {
            event.preventDefault();
            nextStep();
            return;
        }

        if (!validateCurrentStep()) {
            event.preventDefault();
        }
    });

    applyMasks();
    showStep(1);
})();
