<div id="searchResults" style="display:none;">
    <div class="results-header card-glass mb-4">
        <h3 class="m-0"><i class="fas fa-clipboard-list"></i> Resultados da Busca</h3>
        <div id="resultsCount" class="text-muted font-sm mt-1"></div>
    </div>
    
    <div id="resultsContainer"></div>
</div>

<div id="defaultView">
    <!-- Estatísticas -->
    <div class="stats-grid grid-auto-fit mb-4">
        <div class="stat-card-glass">
            <div class="flex-align-center gap-3">
                <div class="icon-wrapper green"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-number"><?php echo count($acolhimentos); ?></div>
                    <div class="stat-label">Fichas de Acolhimento</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card-glass">
            <div class="flex-align-center gap-3">
                <div class="icon-wrapper orange"><i class="fas fa-home"></i></div>
                <div>
                    <div class="stat-number"><?php echo count($socioeconomicos); ?></div>
                    <div class="stat-label">Fichas Socioeconômicas</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card-glass">
            <div class="flex-align-center gap-3">
                <div class="icon-wrapper blue"><i class="fas fa-users"></i></div>
                <div>
                    <?php 
                    $totalProntuarios = count(array_unique(array_merge(
                        array_column($acolhimentos, 'cpf'),
                        array_column($socioeconomicos, 'cpf')
                    )));
                    ?>
                    <div class="stat-number"><?php echo $totalProntuarios; ?></div>
                    <div class="stat-label">Total de Prontuários</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações Rápidas -->
    <div class="card-glass mb-4">
        <h3 class="card-title mb-3"><i class="fas fa-bolt"></i> Ações Rápidas</h3>
        
        <div class="grid-actions">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <!-- BOTÃO VISÍVEL APENAS PARA ADMIN -->
                <a href="acolhimento_form.php" class="action-card green-card">
                    <div class="action-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="action-title">Nova Ficha de Acolhimento</div>
                        <div class="action-desc">Cadastrar nova ficha</div>
                    </div>
                </a>

                <!-- BOTÃO VISÍVEL APENAS PARA ADMIN -->
                <a href="socioeconomico_form.php" class="action-card orange-card">
                    <div class="action-icon"><i class="fas fa-home"></i></div>
                    <div>
                        <div class="action-title">Nova Ficha Socioeconômica</div>
                        <div class="action-desc">Cadastrar nova ficha</div>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Estes dois TODOS PODEM VER -->
            <a href="acolhimento_list.php" class="action-card blue-card">
                <div class="action-icon"><i class="fas fa-file-alt"></i></div>
                <div>
                    <div class="action-title">Listar Acolhimentos</div>
                    <div class="action-desc">Ver todas as fichas</div>
                </div>
            </a>

            <a href="socioeconomico_list.php" class="action-card purple-card">
                <div class="action-icon"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div class="action-title">Listar Socioeconômicas</div>
                    <div class="action-desc">Ver todas as fichas</div>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearBtn = document.getElementById('clearBtn');
    const searchResults = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsCount = document.getElementById('resultsCount');
    const defaultView = document.getElementById('defaultView');

    function showResults(data) {
        defaultView.style.display = 'none';
        searchResults.style.display = 'block';
        resultsContainer.innerHTML = '';
        resultsCount.textContent = `${data.length} resultado(s) encontrado(s)`;
        if (data.length === 0) {
            resultsContainer.innerHTML = `<div class="card-glass text-center text-muted">Nenhum registro encontrado.</div>`;
            return;
        }
        data.forEach(item => {
            const nome = item.nome || item.nome_completo || item.nome_entrevistado || '—';
            const prontuarioUrl = item.cpf ? `prontuarios.php?action=show&cpf=${encodeURIComponent(item.cpf)}` : '#';
            resultsContainer.innerHTML += `
                <div class="card-glass mb-3">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                        <div style="font-size:18px; font-weight:600; color:var(--text-primary);">${nome}</div>
                        <a href="${prontuarioUrl}" class="btn" style="background:#3498db; font-size:12px; padding:8px 12px;">Abrir</a>
                    </div>
                    <div style="margin-top:6px; color:var(--text-secondary);">
                        <strong>CPF:</strong> ${item.cpf ?? '-'} <br>
                        <strong>Categoria:</strong> ${item.categoria ?? '-'} <br>
                        <strong>Nascimento:</strong> ${item.data_nascimento ?? '-'}
                    </div>
                </div>
            `;
        });
    }

    searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        const category = categoryFilter.value;

        if (query.length < 2) {
            alert('Digite pelo menos 2 caracteres.');
            return;
        }

        const url = `prontuarios.php?action=buscar&ajax=1&q=${encodeURIComponent(query)}&categoria=${encodeURIComponent(category)}`;

        searchBtn.disabled = true;
        searchBtn.textContent = 'Buscando...';

        fetch(url, { credentials: 'same-origin' })
            .then(res => {
                if (!res.ok) throw new Error('Erro na resposta: ' + res.status);
                return res.json();
            })
            .then(data => {
                showResults(data);
            })
            .catch(err => {
                console.error('Erro busca:', err);
                alert('Erro ao buscar. Verifique o console (F12) para detalhes.');
            })
            .finally(() => {
                searchBtn.disabled = false;
                searchBtn.textContent = 'Buscar';
            });
    });

    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        categoryFilter.value = '';
        searchResults.style.display = 'none';
        defaultView.style.display = 'block';
    });
});
</script>
