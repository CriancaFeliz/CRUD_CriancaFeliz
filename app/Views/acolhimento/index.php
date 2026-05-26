<?php 
// Verificar permissões de admin
$isAdmin = (isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin');
?>

<div class="actions mb-4">
    <a href="prontuarios.php" class="btn secondary">← Voltar</a>
    <?php if ($isAdmin): ?>
    <a href="acolhimento_form.php" class="btn">+ Cadastrar</a>
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
      const cls = isAtivo ? 'status-ativo' : 'status-inativo';
      return `<span class="status ${cls}">${status || 'Ativo'}</span>`;
    }

    function formatCategoria(cat) {
      const c = (cat || 'Indefinido').toLowerCase();
      let cls = 'badge-indefinido';
      if (c === 'criança' || c === 'crianca') cls = 'badge-crianca';
      else if (c === 'adolescente') cls = 'badge-adolescente';
      else if (c === 'adulto') cls = 'badge-adulto';
      const label = (cat || 'Indefinido');
      return `<span class="badge ${cls}">${label}</span>`;
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
        let btns = `<a href="acolhimento_view.php?id=${id}" class="btn-icon view-btn" title="Visualizar"><i class="fas fa-eye"></i></a>`;
        
        if (isAdmin) {
          btns += `
           <a href="acolhimento_form.php?id=${id}" class="btn-icon edit-btn" title="Editar"><i class="fas fa-edit"></i></a>
           <form method="POST" action="acolhimento_list.php?delete=${id}" class="inline-form" onsubmit="return confirm('Tem certeza que deseja excluir esta ficha?')">
             <input type="hidden" name="csrf_token" value="${csrfToken}">
             <button type="submit" class="btn-icon delete-btn" title="Excluir"><i class="fas fa-trash"></i></button>
           </form>`;
        }
        
        return `<tr>
                  <td>${nome}</td>
                  <td>${cpf}</td>
                  <td>${idade}</td>
                  <td>${categoria}</td>
                  <td>${responsavel}</td>
                  <td>${status}</td>
                  <td class="actions-cell">${id ? btns : '<span class="text-muted-sm">ID inválido</span>'}</td>
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
<div class="search-filters card-glass mb-4">
    <form method="GET" class="search-form-grid">
        <div>
            <label class="form-label-bold">Buscar por nome</label>
            <input type="text" name="q" placeholder="Digite o nome..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="form-control">
        </div>
        <div>
            <label class="form-label-bold">CPF</label>
            <input type="text" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($_GET['cpf'] ?? ''); ?>" class="form-control">
        </div>
        <div>
            <button type="submit" class="btn btn-search">Buscar</button>
        </div>
    </form>
</div>

<!-- Tabela de resultados -->
<div class="table-container card-glass p-0 overflow-hidden">
    <?php if (!empty($fichas)): ?>
        <table class="table-glass">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Idade</th>
                    <th>Categoria</th>
                    <th>Responsável</th>
                    <th>Status</th>
                    <th class="actions-cell">Ações</th>
                </tr>
            </thead>
            <tbody id="fichas-body">
                <?php foreach ($fichas as $ficha): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ficha['nome_completo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($ficha['cpf'] ?? ''); ?></td>
                        <td><?php echo $ficha['idade'] ?? 'N/A'; ?> anos</td>
                        <td>
                            <?php 
                            $catClass = strtolower($ficha['categoria'] ?? 'indefinido');
                            if ($catClass === 'criança') $catClass = 'crianca';
                            ?>
                            <span class="badge badge-<?php echo $catClass; ?>">
                                <?php echo ucfirst($ficha['categoria'] ?? 'Indefinido'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($ficha['nome_responsavel'] ?? ''); ?></td>
                        <td>
                            <?php 
                            $statusClass = ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'ativo' : 'inativo';
                            ?>
                            <span class="status status-<?php echo $statusClass; ?>">
                                <?php echo $ficha['status'] ?? 'Ativo'; ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <?php if (isset($ficha['id']) && !empty($ficha['id'])): ?>
                                <?php 
                                $id = $ficha['id'];
                                
                                // Botão Visualizar (todos veem)
                                echo '<a href="acolhimento_view.php?id=' . $id . '" ';
                                echo 'class="btn-icon view-btn" ';
                                echo 'title="Visualizar">';
                                echo '<i class="fas fa-eye"></i></a> ';
                                
                                // Botão Editar (somente admin)
                                if ($isAdmin) {
                                    echo '<a href="acolhimento_form.php?id=' . $id . '" ';
                                    echo 'class="btn-icon edit-btn" ';
                                    echo 'title="Editar">';
                                    echo '<i class="fas fa-edit"></i></a> ';
                                }
                                
                                // Botão Excluir (somente admin - formulário POST com CSRF)
                                if ($isAdmin) {
                                    echo '<form method="POST" action="acolhimento_list.php?delete=' . $id . '" class="inline-form" onsubmit="return confirm(\'Tem certeza que deseja excluir esta ficha?\')">';
                                    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token ?? '') . '">';
                                    echo '<button type="submit" class="btn-icon delete-btn" title="Excluir">';
                                    echo '<i class="fas fa-trash"></i></button>';
                                    echo '</form>';
                                }
                                ?>
                            <?php else: ?>
                                <span class="text-muted-sm">ID inválido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Paginação -->
        <?php if ($pagination['last_page'] > 1): ?>
            <div class="pagination card-pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" class="btn secondary">
                        ← Anterior
                    </a>
                <?php endif; ?>
                
                <span class="pagination-info">
                    Página <?php echo $pagination['current_page']; ?> de <?php echo $pagination['last_page']; ?>
                    (<?php echo $pagination['total']; ?> registros)
                </span>
                
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" class="btn secondary">
                        Próxima →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="empty-state-container">
            <div class="empty-state-icon">📋</div>
            <h3 class="empty-state-title">Nenhuma ficha encontrada</h3>
            <p class="empty-state-text">
                <?php if (!empty($_GET['q']) || !empty($_GET['cpf'])): ?>
                    Nenhum resultado para os filtros aplicados.
                    <br><a href="acolhimento_list.php" class="link-orange">Ver todas as fichas</a>
                <?php else: ?>
                    Comece cadastrando sua primeira ficha de acolhimento.
                    <br><a href="acolhimento_form.php" class="link-orange">Cadastrar primeira ficha</a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

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
