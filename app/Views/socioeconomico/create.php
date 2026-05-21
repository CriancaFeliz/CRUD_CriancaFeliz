<div class="stepper" style="display:flex; gap:8px; margin:16px 0 24px;">
    <div class="chip active" style="padding:8px 12px; border-radius:20px; background:#ffdab9; font-weight:600;">1. Dados Pessoais</div>
    <div class="chip" style="padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600;">2. Composição Familiar</div>
    <div class="chip" style="padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600;">3. Renda e Benefícios</div>
    <div class="chip" style="padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600;">4. Habitação</div>
</div>

<form method="post" style="max-width: 1200px;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <!-- Etapa 1: Dados Pessoais -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <h4 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">👤 Dados Pessoais</h4>
        
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Nome Completo *</label>
                <input type="text" name="nome_completo" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">CPF *</label>
                <input type="text" name="cpf" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">RG</label>
                <input type="text" name="rg" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Data de Nascimento *</label>
                <input type="text" name="data_nascimento" placeholder="dd/mm/aaaa" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Estado Civil</label>
                <select name="estado_civil" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Solteiro(a)</option>
                    <option>Casado(a)</option>
                    <option>Divorciado(a)</option>
                    <option>Viúvo(a)</option>
                    <option>União Estável</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Escolaridade</label>
                <select name="escolaridade" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Analfabeto</option>
                    <option>Fundamental Incompleto</option>
                    <option>Fundamental Completo</option>
                    <option>Médio Incompleto</option>
                    <option>Médio Completo</option>
                    <option>Superior Incompleto</option>
                    <option>Superior Completo</option>
                    <option>Pós-graduação</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Profissão</label>
                <input type="text" name="profissao" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Telefone *</label>
                <input type="text" name="telefone" placeholder="(11) 99999-9999" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
        </div>
    </div>

    <!-- Etapa 2: Composição Familiar -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <h4 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">👨‍👩‍👧‍👦 Composição Familiar</h4>
        
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Número de Pessoas na Casa *</label>
                <input type="number" name="pessoas_casa" min="1" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Crianças (0-12 anos)</label>
                <input type="number" name="criancas" min="0" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Adolescentes (13-17 anos)</label>
                <input type="number" name="adolescentes" min="0" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Adultos (18-59 anos)</label>
                <input type="number" name="adultos" min="0" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Idosos (60+ anos)</label>
                <input type="number" name="idosos" min="0" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Pessoas com Deficiência</label>
                <input type="number" name="pessoas_deficiencia" min="0" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
        </div>
    </div>

    <!-- Etapa 3: Renda e Benefícios -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <h4 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">💰 Renda e Benefícios</h4>
        
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Renda Familiar Total *</label>
                <input type="text" name="renda_familiar" placeholder="R$ 0,00" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Renda Per Capita</label>
                <input type="text" name="renda_per_capita" placeholder="R$ 0,00" readonly style="padding:10px 12px; border:2px solid #dee2e6; border-radius:8px; font-family:Poppins; background:#f8f9fa; width:100%; box-sizing:border-box; color:#6c757d;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Cadastro Único</label>
                <select name="cadastro_unico" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option value="Sim">Sim</option>
                    <option value="Não">Não</option>
                </select>
            </div>
        </div>
        
        <div style="margin-top:16px;">
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">Benefícios Sociais</label>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="bolsa_familia" value="1" style="width:16px; height:16px;">
                    <span style="font-size:14px; color:#495057;">Bolsa Família</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="auxilio_brasil" value="1" style="width:16px; height:16px;">
                    <span style="font-size:14px; color:#495057;">Auxílio Brasil</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="bpc" value="1" style="width:16px; height:16px;">
                    <span style="font-size:14px; color:#495057;">BPC</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="auxilio_emergencial" value="1" style="width:16px; height:16px;">
                    <span style="font-size:14px; color:#495057;">Auxílio Emergencial</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="seguro_desemprego" value="1" style="width:16px; height:16px;">
                    <span style="font-size:14px; color:#495057;">Seguro Desemprego</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="aposentadoria" value="1" style="width:16px; height:16px;">
                    <span style="font-size:14px; color:#495057;">Aposentadoria</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Etapa 4: Habitação -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <h4 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">🏠 Habitação</h4>
        
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Tipo de Moradia</label>
                <select name="tipo_moradia" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Casa</option>
                    <option>Apartamento</option>
                    <option>Barraco</option>
                    <option>Cômodo</option>
                    <option>Outros</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Situação da Moradia</label>
                <select name="situacao_moradia" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Própria</option>
                    <option>Alugada</option>
                    <option>Cedida</option>
                    <option>Financiada</option>
                    <option>Ocupação</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Número de Cômodos</label>
                <input type="number" name="numero_comodos" min="1" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Água</label>
                <select name="agua" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Rede Pública</option>
                    <option>Poço</option>
                    <option>Cisterna</option>
                    <option>Outros</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Esgoto</label>
                <select name="esgoto" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Rede Pública</option>
                    <option>Fossa</option>
                    <option>Céu Aberto</option>
                    <option>Outros</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Energia Elétrica</label>
                <select name="energia" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Rede Pública</option>
                    <option>Gerador</option>
                    <option>Solar</option>
                    <option>Não Possui</option>
                </select>
            </div>
        </div>
        
        <div style="margin-top:16px;">
            <label style="font-size:14px; color:#354047; font-weight:600;">Observações</label>
            <textarea name="observacoes" style="min-height:80px; resize:vertical; padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" placeholder="Informações adicionais sobre a situação socioeconômica..."></textarea>
        </div>
    </div>

    <div class="actions" style="display:flex; gap:10px; justify-content:flex-end;">
        <a href="<?php echo !empty($editId) ? 'socioeconomico_list.php' : 'prontuarios.php'; ?>" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">Voltar</a>
        <button class="btn" type="submit" style="background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer;">
            <?php if (!empty($editId)): ?>
                Salvar Alteração
            <?php else: ?>
                Cadastrar
            <?php endif; ?>
        </button>
    </div>
</form>

<style>
    /* Responsividade */
    @media (max-width: 768px) {
        .grid {
            grid-template-columns: 1fr !important;
        }
        
        .stepper {
            flex-wrap: wrap;
            gap: 4px !important;
        }
        
        .chip {
            font-size: 12px !important;
            padding: 6px 10px !important;
        }
        
        .actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            text-align: center;
        }
    }
    
    @media (max-width: 480px) {
        .box {
            padding: 12px !important;
        }
        
        .grid {
            gap: 8px !important;
        }
        
        input, select, textarea {
            font-size: 16px; /* Evita zoom no iOS */
        }
    }
</style>

<script>
    // Calcular renda per capita automaticamente
    function calculatePerCapita() {
        const rendaFamiliar = document.querySelector('input[name="renda_familiar"]');
        const pessoasCasa = document.querySelector('input[name="pessoas_casa"]');
        const rendaPerCapita = document.querySelector('input[name="renda_per_capita"]');
        
        if (rendaFamiliar && pessoasCasa && rendaPerCapita) {
            const renda = parseFloat(rendaFamiliar.value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
            const pessoas = parseInt(pessoasCasa.value) || 1;
            const perCapita = renda / pessoas;
            
            rendaPerCapita.value = 'R$ ' + perCapita.toFixed(2).replace('.', ',');
        }
    }
    
    // Aplicar máscara de dinheiro
    function applyMoneyMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            e.target.value = 'R$ ' + value;
            
            // Recalcular per capita se for renda familiar
            if (e.target.name === 'renda_familiar') {
                calculatePerCapita();
            }
        });
    }
    
    // Aplicar máscaras quando o documento carregar
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara de CPF
        const cpfInput = document.querySelector('input[name="cpf"]');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            });
        }
        
        // Máscara de telefone
        const telefoneInput = document.querySelector('input[name="telefone"]');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                if (value.length >= 11) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 10) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 6) {
                    value = value.replace(/(\d{2})(\d{4})(\d)/, '($1) $2-$3');
                } else if (value.length >= 2) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                }
                e.target.value = value;
            });
        }
        
        // Máscara de data
        const dataInput = document.querySelector('input[name="data_nascimento"]');
        if (dataInput) {
            dataInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                value = value.replace(/(\d{2})(\d)/, '$1/$2');
                value = value.replace(/(\d{2})(\d)/, '$1/$2');
                e.target.value = value;
            });
        }
        
        // Máscara de dinheiro
        const rendaInput = document.querySelector('input[name="renda_familiar"]');
        if (rendaInput) {
            applyMoneyMask(rendaInput);
        }
        
        // Listener para recalcular per capita
        const pessoasInput = document.querySelector('input[name="pessoas_casa"]');
        if (pessoasInput) {
            pessoasInput.addEventListener('input', calculatePerCapita);
        }
    });
</script>
