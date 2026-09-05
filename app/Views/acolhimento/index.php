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
   const csrfToken = <?php echo json_encode($csrf_token ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
   const isAdmin = <?php echo ((isset($currentUser) && isset($currentUser['role']) && $currentUser['role'] === 'admin') ? 'true' : 'false'); ?>;

    function appendTextCell(row, value) {
      const cell = document.createElement('td');
      cell.textContent = String(value ?? '');
      row.appendChild(cell);
      return cell;
    }

    function renderRows(items) {
      if (!tbody || !Array.isArray(items)) return;
      const fragment = document.createDocumentFragment();

      items.forEach(it => {
        const row = document.createElement('tr');
        const parsedId = Number.parseInt(it.id, 10);
        const id = Number.isSafeInteger(parsedId) && parsedId > 0 ? parsedId : null;
        const idade = (it.idade != null && it.idade !== '') ? `${it.idade} anos` : 'N/A anos';

        appendTextCell(row, it.nome_completo || '');
        appendTextCell(row, it.cpf || '');
        appendTextCell(row, idade);

        const categoriaCell = document.createElement('td');
        const categoria = String(it.categoria || 'Indefinido');
        const categoriaKey = categoria.toLocaleLowerCase('pt-BR');
        const categoriaClasses = {
          'criança': 'badge-crianca',
          'crianca': 'badge-crianca',
          'adolescente': 'badge-adolescente',
          'adulto': 'badge-adulto'
        };
        const categoriaBadge = document.createElement('span');
        categoriaBadge.className = `badge ${categoriaClasses[categoriaKey] || 'badge-indefinido'}`;
        categoriaBadge.textContent = categoria;
        categoriaCell.appendChild(categoriaBadge);
        row.appendChild(categoriaCell);

        appendTextCell(row, it.responsavel || '');

        const statusCell = document.createElement('td');
        const statusLabel = String(it.status || 'Ativo');
        const statusBadge = document.createElement('span');
        statusBadge.className = `status ${statusLabel === 'Ativo' ? 'status-ativo' : 'status-inativo'}`;
        statusBadge.textContent = statusLabel;
        statusCell.appendChild(statusBadge);
        row.appendChild(statusCell);

        const actionsCell = document.createElement('td');
        actionsCell.className = 'actions-cell';
        if (!id) {
          const invalidId = document.createElement('span');
          invalidId.className = 'text-muted-sm';
          invalidId.textContent = 'ID inválido';
          actionsCell.appendChild(invalidId);
        } else {
          const viewLink = document.createElement('a');
          viewLink.href = `acolhimento_view.php?id=${id}`;
          viewLink.className = 'btn-icon view-btn';
          viewLink.title = 'Visualizar';
          viewLink.setAttribute('aria-label', 'Visualizar');
          const viewIcon = document.createElement('i');
          viewIcon.className = 'fas fa-eye';
          viewLink.appendChild(viewIcon);
          actionsCell.appendChild(viewLink);

          if (isAdmin) {
            const editLink = document.createElement('a');
            editLink.href = `acolhimento_form.php?id=${id}`;
            editLink.className = 'btn-icon edit-btn';
            editLink.title = 'Editar';
            editLink.setAttribute('aria-label', 'Editar');
            const editIcon = document.createElement('i');
            editIcon.className = 'fas fa-edit';
            editLink.appendChild(editIcon);
            actionsCell.appendChild(editLink);

            const deleteForm = document.createElement('form');
            deleteForm.method = 'POST';
            deleteForm.action = `acolhimento_list.php?delete=${id}`;
            deleteForm.className = 'inline-form';
            deleteForm.addEventListener('submit', event => {
              if (!window.confirm('Tem certeza que deseja excluir esta ficha?')) event.preventDefault();
            });

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'csrf_token';
            tokenInput.value = csrfToken;
            deleteForm.appendChild(tokenInput);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'submit';
            deleteButton.className = 'btn-icon delete-btn';
            deleteButton.title = 'Excluir';
            deleteButton.setAttribute('aria-label', 'Excluir');
            const deleteIcon = document.createElement('i');
            deleteIcon.className = 'fas fa-trash';
            deleteButton.appendChild(deleteIcon);
            deleteForm.appendChild(deleteButton);
            actionsCell.appendChild(deleteForm);
          }
        }

        row.appendChild(actionsCell);
        fragment.appendChild(row);
      });

      tbody.replaceChildren(fragment);
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
                        <td><?php echo e($ficha['idade'] ?? 'N/A'); ?> anos</td>
                        <td>
                            <?php 
                            $catClass = strtolower($ficha['categoria'] ?? 'indefinido');
                            if ($catClass === 'criança') $catClass = 'crianca';
                            ?>
                            <?php $catClass = in_array($catClass, ['crianca', 'adolescente', 'adulto'], true) ? $catClass : 'indefinido'; ?>
                            <span class="badge badge-<?php echo e($catClass); ?>">
                                <?php echo e(ucfirst($ficha['categoria'] ?? 'Indefinido')); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($ficha['nome_responsavel'] ?? ''); ?></td>
                        <td>
                            <?php 
                            $statusClass = ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'ativo' : 'inativo';
                            ?>
                            <span class="status status-<?php echo $statusClass; ?>">
                                <?php echo e($ficha['status'] ?? 'Ativo'); ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <?php if (isset($ficha['id']) && !empty($ficha['id'])): ?>
                                <?php 
                                $id = (int) $ficha['id'];
                                
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
