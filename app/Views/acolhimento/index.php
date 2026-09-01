<?php 
// Verificar permissões de admin
$isAdmin = (isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin');
?>

<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="prontuarios.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">← Voltar</a>
    <?php if ($isAdmin): ?>
    <a href="acolhimento_form.php" class="btn" style="background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">+ Cadastrar</a>
    <?php endif; ?>
</div>

<script>
 document.addEventListener('DOMContentLoaded', function() {
   const inputNome = document.querySelector('input[name="q"]');
   const inputCpf = document.querySelector('input[name="cpf"]');
   const tbody = document.getElementById('fichas-body');
  const initialTbodyHTML = tbody ? tbody.innerHTML : '';
   const pagination = document.querySelector('.pagination');
   const csrfToken = '<?php echo htmlspecialchars($csrf_token ?? ""); ?>';
   const isAdmin = <?php echo ((isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin') ? 'true' : 'false'); ?>;

   function formatStatus(status) {
     const isAtivo = (status || 'Ativo') === 'Ativo';
     const style = isAtivo ? 'background:#e8f6ea; color:#6fb64f;' : 'background:#f8d7da; color:#721c24;';
     return `<span class="status" style="${style}">${status || 'Ativo'}</span>`;
   }

   function formatCategoria(cat) {
     const c = (cat || 'Indefinido').toLowerCase();
     let style = 'background:#f8d7da; color:#721c24;';
     if (c === 'criança' || c === 'crianca') style = 'background:#e8f6ea; color:#6fb64f;';
     else if (c === 'adolescente') style = 'background:#fff3cd; color:#856404;';
     else if (c === 'adulto') style = 'background:#d1ecf1; color:#0c5460;';
     const label = (cat || 'Indefinido');
     return `<span class="badge" style="${style}">${label}</span>`;
   }

   function renderRows(items) {
     if (!Array.isArray(items)) return;
     tbody.innerHTML = items.map(it => {
       const id = it.id || '';
       const nome = it.nome_completo || '';
       const cpf = it.cpf || '';
       const idade = (it.idade != null && it.idade !== '') ? `${it.idade} anos` : 'N/A anos';
       const categoria = formatCategoria(it.categoria);
       const responsavel = it.responsavel || '';
       const status = formatStatus(it.status);
       
       // Botões de ação (somente admin pode editar/deletar)
       let btns = `<a href="acolhimento_view.php?id=${id}" class="btn-icon" title="Visualizar" style="background:#17a2b8; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;"><i class="fas fa-eye"></i></a>`;
       
       if (isAdmin) {
         btns += `
          <a href="acolhimento_form.php?id=${id}" class="btn-icon" title="Editar" style="background:#ffc107; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;"><i class="fas fa-edit"></i></a>
          <form method="POST" action="acolhimento_list.php?delete=${id}" style="display:inline; margin:0 4px;" onsubmit="return confirm('Tem certeza que deseja excluir esta ficha?')">
            <input type="hidden" name="csrf_token" value="${csrfToken}">
            <button type="submit" class="btn-icon" title="Excluir" style="background:#e74c3c; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; font-size:14px;"><i class="fas fa-trash"></i></button>
          </form>`;
       }
       
       return `<tr style="border-bottom:1px solid #dee2e6;">
                 <td style="padding:12px; color:#212529;">${nome}</td>
                 <td style="padding:12px; color:#212529;">${cpf}</td>
                 <td style="padding:12px; color:#212529;">${idade}</td>
                 <td style="padding:12px; color:#212529;">${categoria}</td>
                 <td style="padding:12px; color:#212529;">${responsavel}</td>
                 <td style="padding:12px; color:#212529;">${status}</td>
                 <td style="padding:12px; text-align:center;">${id ? btns : '<span style="color:#999; font-size:12px;">ID inválido</span>'}</td>
               </tr>`;
     }).join('');
   }

   let timer = null;
   function triggerSearch() {
     const q = (inputNome?.value || '').trim();
     const cpf = (inputCpf?.value || '').trim();
     if (!q) {
       // Sem texto: restaurar lista completa renderizada no servidor
       if (tbody) tbody.innerHTML = initialTbodyHTML;
       if (pagination) pagination.style.display = '';
       return;
     }
     if (pagination) pagination.style.display = 'none';
     clearTimeout(timer);
     timer = setTimeout(async () => {
       try {
         const params = new URLSearchParams();
         params.set('q', q);
         const url = 'acolhimento_search.php' + (params.toString() ? ('?' + params.toString()) : '');
         const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
         const data = await res.json();
         renderRows(Array.isArray(data) ? data : []);
       } catch (e) {
         console.error(e);
       }
     }, 300);
   }

   if (inputNome) inputNome.addEventListener('input', triggerSearch);
   if (inputCpf) inputCpf.addEventListener('input', triggerSearch);
 });
</script>

<!-- Filtros de busca -->
<div class="search-filters" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <form method="GET" style="display:grid; grid-template-columns: 1fr 200px 120px; gap:12px; align-items:end;">
        <div>
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:4px;">Buscar por nome</label>
            <input type="text" name="q" placeholder="Digite o nome..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                   style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:4px;">CPF</label>
            <input type="text" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($_GET['cpf'] ?? ''); ?>" 
                   style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
        </div>
        <div>
            <button type="submit" class="btn" style="background:#6fb64f; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; width:100%;">Buscar</button>
        </div>
    </form>
</div>

<!-- Tabela de resultados -->
<div class="table-container" style="background:#fff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <?php if (!empty($fichas)): ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Nome</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">CPF</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Idade</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Categoria</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Responsável</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Status</th>
                    <th style="padding:12px; text-align:center; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057; width:160px;">Ações</th>
                </tr>
            </thead>
            <tbody id="fichas-body">
                <?php foreach ($fichas as $ficha): ?>
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:12px; color:#212529;"><?php echo htmlspecialchars($ficha['nome_completo'] ?? ''); ?></td>
                        <td style="padding:12px; color:#212529;"><?php echo htmlspecialchars($ficha['cpf'] ?? ''); ?></td>
                        <td style="padding:12px; color:#212529;"><?php echo $ficha['idade'] ?? 'N/A'; ?> anos</td>
                        <td style="padding:12px; color:#212529;">
                            <span class="badge <?php echo strtolower($ficha['categoria'] ?? 'indefinido'); ?>" 
                                  style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                         <?php 
                                         $categoria = strtolower($ficha['categoria'] ?? 'indefinido');
                                         if ($categoria === 'criança') echo 'background:#e8f6ea; color:#6fb64f;';
                                         elseif ($categoria === 'adolescente') echo 'background:#fff3cd; color:#856404;';
                                         elseif ($categoria === 'adulto') echo 'background:#d1ecf1; color:#0c5460;';
                                         else echo 'background:#f8d7da; color:#721c24;';
                                         ?>">
                                <?php echo ucfirst($ficha['categoria'] ?? 'Indefinido'); ?>
                            </span>
                        </td>
                        <td style="padding:12px; color:#212529;"><?php echo htmlspecialchars($ficha['nome_responsavel'] ?? ''); ?></td>
                        <td style="padding:12px; color:#212529;">
                            <span class="status <?php echo strtolower($ficha['status'] ?? 'ativo'); ?>" 
                                  style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                         <?php echo ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'background:#e8f6ea; color:#6fb64f;' : 'background:#f8d7da; color:#721c24;'; ?>">
                                <?php echo $ficha['status'] ?? 'Ativo'; ?>
                            </span>
                        </td>
                        <td style="padding:12px; text-align:center;">
                            <?php if (isset($ficha['id']) && !empty($ficha['id'])): ?>
                                <?php 
                                $id = $ficha['id'];
                                // Determinar se pode editar/deletar (apenas admin)
                                $isAdmin = (isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin');
                                
                                // Botão Visualizar (todos veem)
                                echo '<a href="acolhimento_view.php?id=' . $id . '" ';
                                echo 'class="btn-icon" ';
                                echo 'title="Visualizar" ';
                                echo 'style="background:#17a2b8; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;">';
                                echo '<i class="fas fa-eye"></i></a> ';
                                
                                // Botão Editar (somente admin)
                                if ($isAdmin) {
                                    echo '<a href="acolhimento_form.php?id=' . $id . '" ';
                                    echo 'class="btn-icon" ';
                                    echo 'title="Editar" ';
                                    echo 'style="background:#ffc107; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;">';
                                    echo '<i class="fas fa-edit"></i></a> ';
                                }
                                
                                // Botão Excluir (somente admin - formulário POST com CSRF)
                                if ($isAdmin) {
                                    echo '<form method="POST" action="acolhimento_list.php?delete=' . $id . '" style="display:inline; margin:0 4px;" onsubmit="return confirm(\'Tem certeza que deseja excluir esta ficha?\')">';
                                    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token ?? '') . '">';
                                    echo '<button type="submit" class="btn-icon" title="Excluir" style="background:#e74c3c; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; font-size:14px;">';
                                    echo '<i class="fas fa-trash"></i></button>';
                                    echo '</form>';
                                }
                                ?>
                            <?php else: ?>
                                <span style="color: #999; font-size: 12px;">ID inválido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Paginação -->
        <?php if ($pagination['last_page'] > 1): ?>
            <div class="pagination" style="padding:16px; text-align:center; border-top:1px solid #dee2e6;">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" 
                       class="btn btn-sm secondary" style="background:#6b7b84; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; text-decoration:none; margin:0 2px;">
                        ← Anterior
                    </a>
                <?php endif; ?>
                
                <span style="margin:0 10px; color:#495057;">
                    Página <?php echo $pagination['current_page']; ?> de <?php echo $pagination['last_page']; ?>
                    (<?php echo $pagination['total']; ?> registros)
                </span>
                
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" 
                       class="btn btn-sm secondary" style="background:#6b7b84; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; text-decoration:none; margin:0 2px;">
                        Próxima →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div style="padding:40px; text-align:center; color:#6c757d;">
            <div style="font-size:48px; margin-bottom:16px;">📋</div>
            <h3 style="margin:0 0 8px 0; color:#495057;">Nenhuma ficha encontrada</h3>
            <p style="margin:0; color:#6c757d;">
                <?php if (!empty($_GET['q']) || !empty($_GET['cpf'])): ?>
                    Nenhum resultado para os filtros aplicados.
                    <br><a href="acolhimento_list.php" style="color:#ff7a00;">Ver todas as fichas</a>
                <?php else: ?>
                    Comece cadastrando sua primeira ficha de acolhimento.
                    <br><a href="acolhimento_form.php" style="color:#ff7a00;">Cadastrar primeira ficha</a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Responsividade para tabela */
    @media (max-width: 768px) {
        .search-filters form {
            grid-template-columns: 1fr !important;
            gap: 8px !important;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            min-width: 800px;
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
        .search-filters {
            padding: 12px !important;
        }
        
        table th,
        table td {
            padding: 8px !important;
            font-size: 14px;
        }
        
        .btn-sm {
            padding: 4px 8px !important;
            font-size: 11px !important;
            min-width: 50px !important;
        }
    }
</style>

<script>
    // Detectar parâmetros de notificação
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('saved') === '1') {
        if (window.notificationSystem) {
            window.notificationSystem.save('Ficha de acolhimento cadastrada com sucesso!');
        }
        // Limpar URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    if (urlParams.get('deleted') === '1') {
        if (window.notificationSystem) {
            window.notificationSystem.delete('Ficha de acolhimento excluída com sucesso!');
        }
        // Limpar URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
