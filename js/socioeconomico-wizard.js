(function () {
    'use strict';

    const form = document.getElementById('socioeconomicoForm');
    if (!form) {
        return;
    }

    let currentStep = 1;
    let familyMembers = [];
    let expenseSequence = 0;

    function parseInitialData() {
        const node = document.getElementById('socioeconomicoInitialData');
        if (!node || !node.textContent.trim()) {
            return {};
        }

        try {
            const data = JSON.parse(node.textContent);
            return data && typeof data === 'object' ? data : {};
        } catch (_) {
            return {};
        }
    }

    function parseMoney(value) {
        if (typeof value === 'number') {
            return Number.isFinite(value) ? value : 0;
        }

        let normalized = String(value || '').trim().replace(/R\$\s?/g, '');
        if (normalized.includes(',')) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        }

        const number = Number.parseFloat(normalized);
        return Number.isFinite(number) ? number : 0;
    }

    function formatMoney(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(parseMoney(value));
    }

    function formatDate(value) {
        const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
        return match ? `${match[3]}/${match[2]}/${match[1]}` : String(value || '');
    }

    function setFieldValue(name, value) {
        if (value === undefined || value === null || value === '') {
            return;
        }

        const fields = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);
        fields.forEach((field) => {
            if (field.type === 'radio' || field.type === 'checkbox') {
                field.checked = String(field.value).toLowerCase() === String(value).toLowerCase();
            } else {
                field.value = value;
            }
        });
    }

    function setHidden(name, value) {
        let field = form.querySelector(`input[type="hidden"][name="${CSS.escape(name)}"]`);
        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            form.appendChild(field);
        }
        field.value = value;
    }

    function showStep(step) {
        currentStep = Math.max(1, Math.min(5, Number(step) || 1));

        document.querySelectorAll('.form-step').forEach((section) => {
            section.classList.toggle('active', Number(section.dataset.step) === currentStep);
        });

        document.querySelectorAll('[data-step-indicator]').forEach((indicator) => {
            const indicatorStep = Number(indicator.dataset.stepIndicator);
            indicator.classList.toggle('active', indicatorStep === currentStep);
            indicator.classList.toggle('completed', indicatorStep < currentStep);
            if (indicatorStep === currentStep) {
                indicator.setAttribute('aria-current', 'step');
            } else {
                indicator.removeAttribute('aria-current');
            }
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function markInvalid(field, message) {
        field.setCustomValidity(message);
        field.reportValidity();
        field.focus();
        field.addEventListener('input', () => field.setCustomValidity(''), { once: true });
        return false;
    }

    function validateStep(step) {
        if (step !== 1) {
            return true;
        }

        const required = [
            ['nome_entrevistado', 'Informe o nome do entrevistado.'],
            ['nome_menor', 'Informe o nome da criança ou adolescente.'],
            ['rg', 'Informe o RG.'],
            ['cpf', 'Informe o CPF.']
        ];

        for (const [name, message] of required) {
            const field = form.elements.namedItem(name);
            if (!field || !String(field.value || '').trim()) {
                return markInvalid(field, message);
            }
        }

        const cpf = form.elements.namedItem('cpf');
        if (String(cpf.value).replace(/\D/g, '').length !== 11) {
            return markInvalid(cpf, 'O CPF deve conter 11 dígitos.');
        }

        return true;
    }

    function nextStep() {
        if (validateStep(currentStep)) {
            showStep(currentStep + 1);
        }
    }

    function prevStep() {
        showStep(currentStep - 1);
    }

    function openFamilyModal() {
        const modal = document.getElementById('familyModal');
        if (modal) {
            modal.classList.add('active');
        }
    }

    function clearFamilyForm() {
        ['family_nome', 'family_parentesco', 'family_data_nasc', 'family_formacao', 'family_renda'].forEach((id) => {
            const field = document.getElementById(id);
            if (field) {
                field.value = '';
            }
        });
    }

    function closeFamilyModal() {
        const modal = document.getElementById('familyModal');
        if (modal) {
            modal.classList.remove('active');
        }
        clearFamilyForm();
    }

    function memberId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        return `member-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }

    function addFamilyMember() {
        const nameField = document.getElementById('family_nome');
        const relationshipField = document.getElementById('family_parentesco');
        const nome = String(nameField?.value || '').trim();
        const parentesco = String(relationshipField?.value || '').trim();

        if (!nome) {
            markInvalid(nameField, 'Informe o nome do integrante.');
            return;
        }
        if (!parentesco) {
            markInvalid(relationshipField, 'Informe o parentesco.');
            return;
        }

        familyMembers.push({
            id: memberId(),
            nome,
            parentesco,
            dataNasc: String(document.getElementById('family_data_nasc')?.value || '').trim(),
            formacao: String(document.getElementById('family_formacao')?.value || '').trim(),
            renda: parseMoney(document.getElementById('family_renda')?.value)
        });

        updateFamilyList();
        closeFamilyModal();
    }

    function removeFamilyMember(id) {
        familyMembers = familyMembers.filter((member) => member.id !== id);
        updateFamilyList();
    }

    function updateFamilyList() {
        const container = document.getElementById('familyList');
        if (!container) {
            return;
        }

        container.replaceChildren();
        if (familyMembers.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-family-list';
            empty.textContent = 'Nenhum integrante adicionado';
            container.appendChild(empty);
            return;
        }

        familyMembers.forEach((member) => {
            const row = document.createElement('div');
            row.className = 'family-member';

            const info = document.createElement('div');
            info.className = 'family-member-info';

            const name = document.createElement('div');
            name.className = 'family-member-name';
            name.textContent = member.nome;

            const details = document.createElement('div');
            details.className = 'family-member-details';
            details.textContent = [
                member.parentesco,
                member.dataNasc || 'Data não informada',
                member.formacao || 'Formação não informada',
                formatMoney(member.renda)
            ].join(' • ');

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn-remove';
            remove.textContent = 'Remover';
            remove.addEventListener('click', () => removeFamilyMember(member.id));

            info.append(name, details);
            row.append(info, remove);
            container.appendChild(row);
        });

        document.getElementById('familia_json').value = JSON.stringify(familyMembers);
        calcularTotais();
    }

    function addDespesa(name = '', value = '') {
        expenseSequence += 1;
        const container = document.getElementById('despesasAdicionais');
        if (!container) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'form-field additional-expense';
        row.dataset.expenseId = String(expenseSequence);

        const fields = document.createElement('div');
        fields.className = 'additional-expense-fields';

        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.name = `despesa_nome_${expenseSequence}`;
        nameInput.placeholder = 'Nome da despesa';
        nameInput.value = name;

        const valueInput = document.createElement('input');
        valueInput.type = 'number';
        valueInput.name = `despesa_valor_${expenseSequence}`;
        valueInput.className = 'despesa-input';
        valueInput.step = '0.01';
        valueInput.min = '0';
        valueInput.placeholder = 'Valor';
        valueInput.value = value;
        valueInput.addEventListener('input', calcularTotais);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn-remove';
        remove.setAttribute('aria-label', 'Remover despesa');
        remove.textContent = 'Remover';
        remove.addEventListener('click', () => {
            row.remove();
            calcularTotais();
        });

        fields.append(nameInput, valueInput, remove);
        row.appendChild(fields);
        container.appendChild(row);
    }

    function calcularTotais() {
        let totalExpenses = 0;
        form.querySelectorAll('.despesa-input').forEach((input) => {
            totalExpenses += parseMoney(input.value);
        });

        let totalIncome = 0;
        form.querySelectorAll('.renda-input').forEach((input) => {
            totalIncome += parseMoney(input.value);
        });

        const expensesOutput = document.getElementById('totalDespesas');
        const incomeOutput = document.getElementById('totalRenda');
        if (expensesOutput) expensesOutput.textContent = formatMoney(totalExpenses);
        if (incomeOutput) incomeOutput.textContent = formatMoney(totalIncome);
    }

    function toggleCltField(show) {
        const field = document.getElementById('clt_field');
        if (field) {
            field.style.display = show ? 'block' : 'none';
        }
    }

    function buildExpenses() {
        const expenses = [];
        const labels = {
            desp_agua: 'Água',
            desp_luz: 'Energia',
            desp_gas: 'Gás',
            desp_telefone: 'Telefone',
            desp_celular: 'Celular',
            desp_internet: 'Internet',
            desp_alimentacao: 'Alimentação'
        };

        Object.entries(labels).forEach(([name, label]) => {
            const value = parseMoney(form.elements.namedItem(name)?.value);
            if (value > 0) {
                expenses.push({ tipo: label, valor, renda: 0 });
            }
        });

        form.querySelectorAll('.additional-expense').forEach((row) => {
            const name = String(row.querySelector('input[type="text"]')?.value || '').trim();
            const value = parseMoney(row.querySelector('input[type="number"]')?.value);
            if (name && value > 0) {
                expenses.push({ tipo: name, valor, renda: 0 });
            }
        });

        return expenses;
    }

    function prepareSubmission() {
        const roomCount = Number.parseInt(form.elements.namedItem('num_comodos')?.value || '0', 10) || 0;
        const vehicleFields = [
            'veiculos_motocicleta', 'veiculos_automovel', 'veiculos_caminhonete',
            'veiculos_caminhao', 'veiculos_outros'
        ];
        const vehicleCount = vehicleFields.reduce((total, name) => {
            return total + (Number.parseInt(form.elements.namedItem(name)?.value || '0', 10) || 0);
        }, 0);

        const salary = parseMoney(form.elements.namedItem('renda_salario')?.value);
        const benefit = parseMoney(form.elements.namedItem('renda_bolsa')?.value);
        const declaredIncome = salary + benefit;
        const familyIncome = familyMembers.reduce((total, member) => total + parseMoney(member.renda), 0);
        const totalIncome = declaredIncome > 0 ? declaredIncome : familyIncome;
        const peopleCount = familyMembers.length;

        setHidden('numero_comodos', roomCount);
        setHidden('cond_residencia', form.elements.namedItem('condicoes')?.value || '');
        setHidden('moradia', form.elements.namedItem('residencia')?.value || '');
        setHidden('nr_veiculos', vehicleCount);
        setHidden('qtd_pessoas', peopleCount);
        setHidden('renda_familiar', totalIncome.toFixed(2));
        setHidden('renda_per_capita', peopleCount > 0 ? (totalIncome / peopleCount).toFixed(2) : '0.00');
        setHidden('bolsa_familia', benefit > 0 ? '1' : '0');

        document.getElementById('familia_json').value = JSON.stringify(familyMembers);
        document.getElementById('despesas_json').value = JSON.stringify(buildExpenses());
    }

    function hydrate(initial) {
        const mappings = {
            nome_entrevistado: initial.nome_entrevistado || initial.nome,
            num_comodos: initial.numero_comodos || initial.nr_comodos,
            condicoes: initial.cond_residencia || initial.situacao_moradia,
            residencia: initial.residencia || initial.moradia,
            trabalho_clt: Number(initial.trabalho_clt) === 1 ? 'Sim' : (initial.trabalho_clt === 0 || initial.trabalho_clt === '0' ? 'Não' : ''),
            convenio_medico: Number(initial.convenio_medico) === 1 ? 'Sim' : (initial.convenio_medico === 0 || initial.convenio_medico === '0' ? 'Não' : '')
        };

        Object.keys(initial).forEach((key) => setFieldValue(key, initial[key]));
        Object.entries(mappings).forEach(([key, value]) => setFieldValue(key, value));

        if (Number(initial.agua) === 1 && !form.elements.namedItem('agua').value) setFieldValue('agua', initial.tipo_agua || 'Rede Pública');
        if (Number(initial.esgoto) === 1 && !form.elements.namedItem('esgoto').value) setFieldValue('esgoto', initial.tipo_esgoto || 'Rede Pública');
        if (Number(initial.energia) === 1 && !form.elements.namedItem('energia').value) setFieldValue('energia', initial.tipo_energia || 'Relógio Próprio');

        if (Array.isArray(initial.familia)) {
            familyMembers = initial.familia.map((member) => ({
                id: memberId(),
                nome: String(member.nome || ''),
                parentesco: String(member.parentesco || ''),
                dataNasc: formatDate(member.dataNasc || member.data_nasc || ''),
                formacao: String(member.formacao || ''),
                renda: parseMoney(member.renda)
            }));
        }

        const standardExpenseFields = {
            'água': 'desp_agua', agua: 'desp_agua', energia: 'desp_luz', luz: 'desp_luz',
            'gás': 'desp_gas', gas: 'desp_gas', telefone: 'desp_telefone', celular: 'desp_celular',
            internet: 'desp_internet', 'alimentação': 'desp_alimentacao', alimentacao: 'desp_alimentacao'
        };
        if (Array.isArray(initial.despesas)) {
            initial.despesas.forEach((expense) => {
                const type = String(expense.tipo || expense.tipo_renda || '').trim();
                const normalizedType = type.toLocaleLowerCase('pt-BR');
                const value = parseMoney(expense.valor ?? expense.valor_despesa ?? expense.valor_renda);
                const fieldName = standardExpenseFields[normalizedType];
                if (fieldName && form.elements.namedItem(fieldName)) {
                    form.elements.namedItem(fieldName).value = value || '';
                } else if (type) {
                    addDespesa(type, value || '');
                }
            });
        }

        if (parseMoney(initial.renda_salario) > 0) {
            setFieldValue('renda_salario', initial.renda_salario);
        } else if (parseMoney(initial.renda_familiar) > 0) {
            setFieldValue('renda_salario', initial.renda_familiar);
        }
        setFieldValue('renda_bolsa', initial.renda_bolsa);

        if (Number(initial.nr_veiculos) > 0 && !form.elements.namedItem('veiculos_automovel').value) {
            setFieldValue('veiculos_automovel', initial.nr_veiculos);
        }

        updateFamilyList();
        calcularTotais();
        const clt = form.querySelector('input[name="trabalho_clt"]:checked');
        toggleCltField(clt?.value === 'Sim');
    }

    form.addEventListener('submit', (event) => {
        if (currentStep < 5) {
            event.preventDefault();
            nextStep();
            return;
        }
        prepareSubmission();
    });

    form.querySelectorAll('.despesa-input, .renda-input').forEach((input) => {
        input.addEventListener('input', calcularTotais);
    });

    const modal = document.getElementById('familyModal');
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeFamilyModal();
    });

    window.nextStep = nextStep;
    window.prevStep = prevStep;
    window.openFamilyModal = openFamilyModal;
    window.closeFamilyModal = closeFamilyModal;
    window.addFamilyMember = addFamilyMember;
    window.removeFamilyMember = removeFamilyMember;
    window.addDespesa = () => addDespesa();
    window.calcularTotais = calcularTotais;
    window.toggleCltField = toggleCltField;

    hydrate(parseInitialData());
    showStep(1);
})();
