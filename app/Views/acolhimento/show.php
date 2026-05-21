<?php 
$isAdmin = (isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin');
?>

<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="acolhimento_list.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">← Voltar</a>
    <?php if ($isAdmin): ?>
    <a href="acolhimento_form.php?edit=<?php echo urlencode($ficha['id']); ?>" class="btn" style="background:#f0a36b; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">✏️ Editar</a>
    <?php endif; ?>
</div>

<!-- Dados Pessoais -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">👤 Dados Pessoais</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 120px 1fr 1fr 1fr; gap:16px; align-items:start;">
        <!-- Foto -->
        <div class="photo-container" style="grid-row: span 3;">
            <?php if (!empty($ficha['foto']) && file_exists($ficha['foto'])): ?>
                <img src="<?php echo htmlspecialchars($ficha['foto']); ?>" alt="Foto 3x4" 
                     style="width:120px; height:160px; object-fit:cover; border-radius:8px; border:2px solid #f0a36b;">
            <?php else: ?>
                <div style="width:120px; height:160px; background:#f8f9fa; border:2px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#6c757d; font-size:12px; text-align:center;">
                    Sem foto
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Dados básicos -->
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Nome Completo</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['nome_completo'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">RG</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $rg = $ficha['rg'] ?? '';
                if ($rg && strlen($rg) == 9) {
                    echo substr($rg, 0, 2) . '.' . substr($rg, 2, 3) . '.' . substr($rg, 5, 3) . '-' . substr($rg, 8, 1);
                } else {
                    echo htmlspecialchars($rg);
                }
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">CPF</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $cpf = $ficha['cpf'] ?? '';
                if ($cpf && strlen($cpf) == 11) {
                    echo substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                } else {
                    echo htmlspecialchars($cpf);
                }
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Data de Nascimento</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['data_nascimento'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Idade</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['idade'] ?? 'N/A'; ?> anos</div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Categoria</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <span class="badge" style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                           <?php 
                                           $categoria = strtolower($ficha['categoria'] ?? 'indefinido');
                                           if ($categoria === 'criança') echo 'background:#e8f6ea; color:#6fb64f;';
                                           elseif ($categoria === 'adolescente') echo 'background:#fff3cd; color:#856404;';
                                           elseif ($categoria === 'adulto') echo 'background:#d1ecf1; color:#0c5460;';
                                           else echo 'background:#f8d7da; color:#721c24;';
                                           ?>">
                    <?php echo ucfirst($ficha['categoria'] ?? 'Indefinido'); ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Data de Acolhimento</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['data_acolhimento'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Encaminhado por</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['encaminha_por'] ?? 'Não informado'); ?></div>
        </div>
    </div>
    
    <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; margin-top:16px; transition:background-color 0.3s ease;">
        <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Queixa Principal</div>
        <div class="value" style="color:var(--text-primary, #212529); font-weight:500; line-height:1.5;"><?php echo nl2br(htmlspecialchars($ficha['queixa_principal'] ?? '')); ?></div>
    </div>
</div>

<!-- Endereço -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">🏠 Endereço</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Endereço</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['endereco'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Número</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['numero'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">CEP</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $cep = $ficha['cep'] ?? '';
                if ($cep && strlen($cep) == 8) {
                    echo substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
                } else {
                    echo htmlspecialchars($cep);
                }
                ?>
            </div>
        </div>
    </div>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Bairro</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['bairro'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Cidade</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['cidade'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Complemento</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['complemento'] ?? 'Não informado'); ?></div>
        </div>
    </div>
</div>

<!-- Responsável -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">👨‍👩‍👧‍👦 Responsável</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Nome do Responsável</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['nome_responsavel'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">RG</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $rg_resp = $ficha['rg_responsavel'] ?? '';
                if ($rg_resp && strlen($rg_resp) == 9) {
                    echo substr($rg_resp, 0, 2) . '.' . substr($rg_resp, 2, 3) . '.' . substr($rg_resp, 5, 3) . '-' . substr($rg_resp, 8, 1);
                } else {
                    echo htmlspecialchars($rg_resp);
                }
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">CPF</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $cpf_resp = $ficha['cpf_responsavel'] ?? '';
                if ($cpf_resp && strlen($cpf_resp) == 11) {
                    echo substr($cpf_resp, 0, 3) . '.' . substr($cpf_resp, 3, 3) . '.' . substr($cpf_resp, 6, 3) . '-' . substr($cpf_resp, 9, 2);
                } else {
                    echo htmlspecialchars($cpf_resp);
                }
                ?>
            </div>
        </div>
    </div>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Grau de Parentesco</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['grau_parentesco'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Contato</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $contato = $ficha['contato_1'] ?? '';
                if ($contato && strlen($contato) >= 10) {
                    $contato = preg_replace('/\D/', '', $contato);
                    if (strlen($contato) == 11) {
                        echo '(' . substr($contato, 0, 2) . ') ' . substr($contato, 2, 5) . '-' . substr($contato, 7, 4);
                    } elseif (strlen($contato) == 10) {
                        echo '(' . substr($contato, 0, 2) . ') ' . substr($contato, 2, 4) . '-' . substr($contato, 6, 4);
                    } else {
                        echo htmlspecialchars($ficha['contato_1']);
                    }
                } else {
                    echo htmlspecialchars($ficha['contato_1'] ?? '');
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Informações Adicionais -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">📋 Informações Adicionais</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Cadastro Único</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['cad_unico'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Responsável pelo Acolhimento</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['acolhimento_responsavel'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Status</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <span class="status" style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                           <?php echo ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'background:#e8f6ea; color:#6fb64f;' : 'background:#f8d7da; color:#721c24;'; ?>">
                    <?php echo $ficha['status'] ?? 'Ativo'; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Responsividade */
    @media (max-width: 768px) {
        .fields-grid {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        
        .actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            text-align: center;
        }
        
        .section {
            padding: 16px !important;
        }
        
        .photo-container {
            grid-row: span 1 !important;
            justify-self: center;
        }
        
        .photo-container img,
        .photo-container div {
            width: 100px !important;
            height: 133px !important;
        }
    }
    
    @media (max-width: 480px) {
        .section {
            padding: 12px !important;
        }
        
        .field {
            padding: 10px !important;
        }
        
        .label {
            font-size: 11px !important;
        }
        
        .value {
            font-size: 14px !important;
        }
    }
</style>
