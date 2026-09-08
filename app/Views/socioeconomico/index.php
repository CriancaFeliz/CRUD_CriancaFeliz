<?php 
// Verificar permissões de admin
$isAdmin = (isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin');
?>

<div class="actions flex-between mb-4">
<style>
.table-glass.table-compact th, 
.table-glass.table-compact td {
    padding: 4px 6px !important;
    font-size: 12px !important;
}
.actions-cell {
    white-space: nowrap !important;
    text-align: center !important;
    padding-right: 25px !important;
}
.actions-cell form {
    display: inline-block !important;
}
.actions-cell .btn-icon {
    width: 22px !important;
    height: 22px !important;
    padding: 0 !important;
    font-size: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 1px !important;
    border-radius: 4px !important;
}
</style>
    <div></div>
    <div class="d-flex gap-2">
        <a href="prontuarios.php" class="btn secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <?php if ($isAdmin): ?>
        <a href="socioeconomico_form.php" class="btn success">
            <i class="fas fa-plus"></i> Cadastrar
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtros de busca -->
<div class="search-filters card-glass mb-4">
    <form method="GET" action="socioeconomico_list.php" class="search-form-grid" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div class="filter-field" style="flex-grow: 1; min-width: 250px;">
            <label class="form-label-bold" for="socio_q">
                <i class="fas fa-search"></i> Buscar por nome
            </label>
            <input type="text" id="socio_q" name="q" placeholder="Digite o nome..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="form-control" style="width: 100%;">
        </div>
        <div class="filter-field filter-field-cpf" style="min-width: 150px;">
            <label class="form-label-bold" for="socio_cpf">
                <i class="fas fa-id-card"></i> CPF
            </label>
            <input type="text" id="socio_cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" value="<?php echo htmlspecialchars($_GET['cpf'] ?? ''); ?>" class="form-control" inputmode="numeric" style="width: 100%;">
        </div>
        <div class="filter-actions" style="display: flex; gap: 10px;">
            <button type="submit" class="btn primary btn-search" style="height: 42px;">
                <i class="fas fa-search"></i> Buscar
            </button>
            <?php if (!empty($_GET['q']) || !empty($_GET['cpf'])): ?>
                <a href="socioeconomico_list.php" class="btn secondary btn-clear-search" title="Limpar filtros" style="height: 42px; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-times"></i> Limpar
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabela de resultados -->
<div class="table-responsive card-glass p-0" style="overflow-x: auto; max-width: 100%;">
    <?php if (!empty($fichas)): ?>
        <table class="table-glass table-compact">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Data Acolhimento</th>
                    <th>Renda</th>
                    <th>Benefícios</th>
                    <th>Status</th>
                    <th class="actions-cell">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fichas as $ficha): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ficha['nome_menor'] ?? $ficha['nome_entrevistado'] ?? $ficha['nome_completo'] ?? ''); ?></strong></td>
                        <td class="text-muted">
                            <?php 
                            $cpf = $ficha['cpf'] ?? '';
                            if ($cpf && strlen($cpf) == 11) {
                                echo substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                            } else {
                                echo e($cpf);
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $acolhimento = $ficha['data_acolhimento'] ?? ($ficha['data_de_acolhimento'] ?? null);
                            if (!empty($acolhimento)) {
                                echo htmlspecialchars($acolhimento);
                            } else {
                                echo '<span class="text-muted">N/I</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $renda = $ficha['renda_familiar'] ?? 0;
                            if (is_string($renda)) {
                                $renda = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $renda));
                            } else {
                                $renda = floatval($renda);
                            }
                            if ($renda > 0) {
                                echo 'R$ ' . number_format($renda, 2, ',', '.');
                            } else {
                                echo '<span class="text-muted">N/I</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $beneficios = $ficha['beneficios_list'] ?? [];
                            if (is_string($beneficios) && strlen(trim($beneficios))>0) {
                                echo htmlspecialchars($beneficios);
                            } elseif (is_array($beneficios) && count($beneficios)>0) {
                                echo htmlspecialchars(implode(', ', $beneficios));
                            } else {
                                echo '<span class="text-muted">N/I</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php $statusAtivo = ($ficha['status'] ?? 'Ativo') === 'Ativo'; ?>
                            <span class="status <?php echo $statusAtivo ? 'status-ativo' : 'status-inativo'; ?>">
                                <?php echo e($ficha['status'] ?? 'Ativo'); ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <?php 
                            $id = (int)($ficha['id'] ?? $ficha['idatendido'] ?? 0);
                            
                            if (!empty($id)) { 
                                // Botão Visualizar
                                echo '<a href="socioeconomico_view.php?id=' . $id . '" class="btn-icon view-btn" title="Visualizar">';
                                echo '<i class="fas fa-eye"></i></a> ';
                                
                                // Botão Prontuário
                                echo '<a href="prontuarios.php?action=show&id=' . $id . '" class="btn-icon" style="color: #3b82f6;" title="Abrir Prontuário">';
                                echo '<i class="fas fa-address-card"></i></a> ';
                                
                                // Botão Editar
                                if ($isAdmin) {
                                    echo '<a href="socioeconomico_form.php?id=' . $id . '" class="btn-icon edit-btn" title="Editar">';
                                    echo '<i class="fas fa-edit"></i></a> ';
                                }
                                
                                // Botão Excluir
                                if ($isAdmin) {
                                    echo '<form method="POST" action="socioeconomico_list.php?delete=' . $id . '" class="inline-form" onsubmit="return confirm(\'Tem certeza que deseja excluir esta ficha?\')">';
                                    echo '<input type="hidden" name="csrf_token" value="' . e($csrf_token ?? '') . '">';
                                    echo '<button type="submit" class="btn-icon delete-btn" title="Excluir">';
                                    echo '<i class="fas fa-trash"></i></button>';
                                    echo '</form>';
                                }
                            } else {
                                echo '<span class="text-muted-sm">ID inválido</span>';
                            } 
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Paginação -->
        <?php if ($pagination['last_page'] > 1): ?>
            <div class="pagination card-pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" class="pagination-link">
                        ← Anterior
                    </a>
                <?php endif; ?>
                
                <span class="pagination-info">
                    Página <?php echo $pagination['current_page']; ?> de <?php echo $pagination['last_page']; ?>
                    (<?php echo $pagination['total']; ?> registros)
                </span>
                
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" class="pagination-link">
                        Próxima →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="empty-state text-center p-5">
            <div style="font-size:48px; margin-bottom:16px;">
                <i class="fas fa-folder-open text-muted"></i>
            </div>
            <h3 class="m-0 mb-2">Nenhuma ficha encontrada</h3>
            <p class="text-muted m-0">
                <?php if (!empty($_GET['q']) || !empty($_GET['cpf'])): ?>
                    Nenhum resultado para os filtros aplicados.
                    <br><a href="socioeconomico_list.php" class="link-orange font-weight-bold">Ver todas as fichas</a>
                <?php else: ?>
                    Comece cadastrando sua primeira ficha socioeconômica.
                    <br><a href="socioeconomico_form.php" class="link-orange font-weight-bold">Cadastrar primeira ficha</a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputCpf = document.getElementById('socio_cpf');
        if (inputCpf) {
            inputCpf.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
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
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('saved') === '1') {
        if (window.notificationSystem) {
            window.notificationSystem.save('Ficha socioeconômica cadastrada com sucesso!');
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if (urlParams.get('deleted') === '1') {
        if (window.notificationSystem) {
            window.notificationSystem.delete('Ficha socioeconômica excluída com sucesso!');
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
