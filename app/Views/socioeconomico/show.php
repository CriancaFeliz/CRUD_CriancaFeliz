<?php 
$isAdmin = (isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin');
?>

<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="socioeconomico_list.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">← Voltar</a>
    <?php if ($isAdmin): ?>
    <a href="socioeconomico_form.php?id=<?php echo urlencode($ficha['id']); ?>" class="btn" style="background:#f0a36b; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;"><i class="fas fa-edit"></i> Editar</a>
    <?php endif; ?>
</div>

<!-- Dados Pessoais -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;"><i class="fas fa-user"></i> Dados Pessoais</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Nome do Entrevistado</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['nome_entrevistado'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">CPF</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php 
                $cpf = $ficha['cpf'] ?? '';
                if ($cpf && strlen($cpf) == 11) {
                    echo substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                } else {
                    echo $cpf ?: 'Não informado';
                }
            ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">RG</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $rg = $ficha['rg'] ?? '';
                if ($rg && strlen(preg_replace('/\D/', '', $rg)) >= 9) {
                    // Formato: XX.XXX.XXX-XX
                    $rgNumeros = preg_replace('/\D/', '', $rg);
                    $rgFormatado = substr($rgNumeros, 0, 2) . '.' . substr($rgNumeros, 2, 3) . '.' . substr($rgNumeros, 5, 3) . '-' . substr($rgNumeros, 8, 2);
                    echo htmlspecialchars($rgFormatado);
                } else {
                    echo $rg ?: 'Não informado';
                }
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Nome do Menor</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['nome_menor'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Data de Acolhimento</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['data_acolhimento'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Assistente Social</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['assistente_social'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Residência</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['residencia'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Construção</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['construcao'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Nº de Cômodos</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['numero_comodos'] ?? 'Não informado'); ?></div>
        </div>
    </div>
</div>

<!-- Composição Familiar -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;"><i class="fas fa-users"></i> Composição Familiar</h3>
    
    <?php 
    // Helper local para formatar datas yyyy-mm-dd -> dd/mm/yyyy
    function _fmtDateView($d) {
        if (empty($d)) return '';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m)) return "{$m[3]}/{$m[2]}/{$m[1]}";
        return $d;
    }

    // Preferir família carregada do banco (array), caso contrário aceitar familia_json
    $familia = [];
    if (!empty($ficha['familia']) && is_array($ficha['familia'])) {
        $familia = $ficha['familia'];
    } elseif (!empty($ficha['familia_json'])) {
        $familia = json_decode($ficha['familia_json'], true) ?: [];
    }
    
    if (!empty($familia) && is_array($familia)): ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8f9fa;">
                    <th style="padding:12px; text-align:left; border-bottom:2px solid #dee2e6;">Nome</th>
                    <th style="padding:12px; text-align:left; border-bottom:2px solid #dee2e6;">Parentesco</th>
                    <th style="padding:12px; text-align:left; border-bottom:2px solid #dee2e6;">Data Nasc.</th>
                    <th style="padding:12px; text-align:left; border-bottom:2px solid #dee2e6;">Formação</th>
                    <th style="padding:12px; text-align:right; border-bottom:2px solid #dee2e6;">Renda</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($familia as $membro): ?>
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:12px;"><?php echo htmlspecialchars($membro['nome'] ?? ''); ?></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars($membro['parentesco'] ?? ''); ?></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars(_fmtDateView($membro['data_nasc'] ?? $membro['data_nascimento'] ?? '')); ?></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars($membro['formacao'] ?? ''); ?></td>
                        <td style="padding:12px; text-align:right;">
                            <?php 
                            $renda = $membro['renda'] ?? 0;
                            echo $renda > 0 ? 'R$ ' . number_format($renda, 2, ',', '.') : '-';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#6c757d; text-align:center; padding:20px;">Nenhum membro da família cadastrado</p>
    <?php endif; ?>
</div>

<!-- Renda e Benefícios -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">💰 Renda e Benefícios</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Renda Familiar</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $renda = $ficha['renda_familiar'] ?? 0;
                echo $renda > 0 ? 'R$ ' . number_format($renda, 2, ',', '.') : 'Não informado';
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Renda Per Capita</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $rendaPerCapita = $ficha['renda_per_capita'] ?? 0;
                echo $rendaPerCapita > 0 ? 'R$ ' . number_format($rendaPerCapita, 2, ',', '.') : 'Não calculado';
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Cadastro Único (CadÚnico)</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php
                $cad = $ficha['cadunico'] ?? null;
                if ($cad === null || $cad === '') {
                    echo 'Não informado';
                } elseif (is_numeric($cad)) {
                    echo ($cad ? 'Sim' : 'Não');
                } else {
                    echo htmlspecialchars($cad);
                }
                ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:8px;">Benefícios Sociais</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $beneficios = [];
                if (!empty($ficha['bolsa_familia'])) $beneficios[] = 'Bolsa Família';
                if (!empty($ficha['auxilio_brasil'])) $beneficios[] = 'Auxílio Brasil';
                if (!empty($ficha['bpc'])) $beneficios[] = 'BPC';
                if (!empty($ficha['auxilio_emergencial'])) $beneficios[] = 'Auxílio Emergencial';
                if (!empty($ficha['seguro_desemprego'])) $beneficios[] = 'Seguro Desemprego';
                if (!empty($ficha['aposentadoria'])) $beneficios[] = 'Aposentadoria';
                
                if (!empty($beneficios)) {
                    foreach ($beneficios as $beneficio) {
                        echo '<span class="badge" style="display:inline-block; padding:4px 8px; margin:2px 4px 2px 0; border-radius:12px; font-size:12px; font-weight:500; background:#e8f6ea; color:#6fb64f;">' . $beneficio . '</span>';
                    }
                } else {
                    echo '<span style="color:#6c757d; font-style:italic;">Nenhum benefício informado</span>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Habitação -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">🏠 Habitação</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Tipo de Moradia</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['tipo_moradia'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Situação da Moradia</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['situacao_moradia'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Número de Cômodos</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['numero_comodos'] ?? 'Não informado'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Água</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php
                // Priorizar mostrar valor de despesa, se disponível
                if (!empty($ficha['despesa_agua'])) {
                    echo 'R$ ' . number_format($ficha['despesa_agua'], 2, ',', '.');
                } elseif (isset($ficha['agua'])) {
                    echo (!empty($ficha['agua']) ? 'Sim' : 'Não');
                } else {
                    echo 'Não informado';
                }
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Esgoto</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php
                if (isset($ficha['esgoto'])) {
                    echo (!empty($ficha['esgoto']) ? 'Sim' : 'Não');
                } else {
                    echo 'Não informado';
                }
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Energia Elétrica</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php
                if (!empty($ficha['despesa_energia'])) {
                    echo 'R$ ' . number_format($ficha['despesa_energia'], 2, ',', '.');
                } elseif (isset($ficha['energia'])) {
                    echo (!empty($ficha['energia']) ? 'Sim' : 'Não');
                } else {
                    echo 'Não informado';
                }
                ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($ficha['observacoes'])): ?>
    <div style="margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Observações</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500; line-height:1.5;"><?php echo nl2br(htmlspecialchars($ficha['observacoes'])); ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Status -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">📋 Status</h3>
    
    <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
        <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Status da Ficha</div>
        <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
            <span class="status" style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                   <?php echo ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'background:#e8f6ea; color:#6fb64f;' : 'background:#f8d7da; color:#721c24;'; ?>">
                <?php echo $ficha['status'] ?? 'Ativo'; ?>
            </span>
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
        
        .badge {
            font-size: 11px !important;
            padding: 3px 6px !important;
        }
    }
</style>
