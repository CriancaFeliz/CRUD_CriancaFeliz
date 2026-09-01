<?php
    // Se for requisição AJAX, retorna só os resultados e encerra
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {

        $query = $_GET['q'] ?? '';
        $categoria = $_GET['categoria'] ?? '';

        if (strlen($query) < 2) {
            echo json_encode([]);
            exit;
        }

        // limpa o CPF (aceita com ou sem máscara)
        $cpfLimpo = preg_replace('/[^0-9]/', '', $query);

        $sqlAcolhimento = "
            SELECT 
                nome_completo AS nome,
                cpf,
                'acolhimento' AS categoria,
                data_nascimento
            FROM ficha_acolhimento
            WHERE nome_completo LIKE :query
            OR REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE :cpf
        ";

        $sqlSocio = "
            SELECT 
                nome_completo AS nome,
                cpf,
                'socioeconomico' AS categoria,
                data_nascimento
            FROM ficha_socioeconomico
            WHERE nome_completo LIKE :query
            OR REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE :cpf
        ";

        $stmt = $pdo->prepare($sqlAcolhimento);
        $stmt->bindValue(':query', "%$query%");
        $stmt->bindValue(':cpf', "%$cpfLimpo%");
        $stmt->execute();
        $resultAcolhimento = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare($sqlSocio);
        $stmt->bindValue(':query', "%$query%");
        $stmt->bindValue(':cpf', "%$cpfLimpo%");
        $stmt->execute();
        $resultSocio = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array_merge($resultAcolhimento, $resultSocio));
        exit;


        if (!empty($categoria)) {
            $sql .= " AND categoria = :categoria";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':query', "%$query%");
        $stmt->bindValue(':cpf', "%$cpfLimpo%");

        if (!empty($categoria)) {
            $stmt->bindValue(':categoria', $categoria);
        }

        $stmt->execute();

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
?>

<div id="searchResults" style="display:none;">
    <div class="results-header" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0; color:#495057;"><i class="fas fa-clipboard-list"></i> Resultados da Busca</h3>
        <div id="resultsCount" style="color:#6c757d; font-size:14px; margin-top:4px;"></div>
    </div>
    
    <div id="resultsContainer"></div>
</div>

<div id="defaultView">
    <!-- Estatísticas -->
    <div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-bottom:30px;">
        <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:48px; height:48px; background:#e8f6ea; border-radius:12px; display:grid; place-items:center; font-size:24px; color:#6fb64f;"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div style="font-size:24px; font-weight:700; color:#495057;"><?php echo count($acolhimentos); ?></div>
                    <div style="color:#6c757d; font-size:14px;">Fichas de Acolhimento</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:48px; height:48px; background:#fff3e0; border-radius:12px; display:grid; place-items:center; font-size:24px; color:#f0a36b;"><i class="fas fa-home"></i></div>
                <div>
                    <div style="font-size:24px; font-weight:700; color:#495057;"><?php echo count($socioeconomicos); ?></div>
                    <div style="color:#6c757d; font-size:14px;">Fichas Socioeconômicas</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:48px; height:48px; background:#e3f2fd; border-radius:12px; display:grid; place-items:center; font-size:24px; color:#2196f3;"><i class="fas fa-users"></i></div>
                <div>
                    <?php 
                    $totalProntuarios = count(array_unique(array_merge(
                        array_column($acolhimentos, 'cpf'),
                        array_column($socioeconomicos, 'cpf')
                    )));
                    ?>
                    <div style="font-size:24px; font-weight:700; color:#495057;"><?php echo $totalProntuarios; ?></div>
                    <div style="color:#6c757d; font-size:14px;">Total de Prontuários</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações Rápidas -->
<div class="quick-actions" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:30px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057;"><i class="fas fa-bolt"></i> Ações Rápidas</h3>
    
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        
            <!-- BOTÃO VISÍVEL APENAS PARA ADMIN -->
            <a href="acolhimento_form.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #e8f6ea; border-radius:12px; text-decoration:none; color:#495057;">
                <div style="width:40px; height:40px; background:#6fb64f; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <div style="font-weight:600;">Nova Ficha de Acolhimento</div>
                    <div style="font-size:12px; color:#6c757d;">Cadastrar nova ficha</div>
                </div>
            </a>

            <!-- BOTÃO VISÍVEL APENAS PARA ADMIN -->
            <a href="socioeconomico_form.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #fff3e0; border-radius:12px; text-decoration:none; color:#495057;">
                <div style="width:40px; height:40px; background:#f0a36b; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">
                    <i class="fas fa-home"></i>
                </div>
                <div>
                    <div style="font-weight:600;">Nova Ficha Socioeconômica</div>
                    <div style="font-size:12px; color:#6c757d;">Cadastrar nova ficha</div>
                </div>
            </a>

        <?php endif; ?>

        <!-- Estes dois TODOS PODEM VER -->
        <a href="acolhimento_list.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #e3f2fd; border-radius:12px; text-decoration:none; color:#495057;">
            <div style="width:40px; height:40px; background:#2196f3; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div style="font-weight:600;">Listar Acolhimentos</div>
                <div style="font-size:12px; color:#6c757d;">Ver todas as fichas</div>
            </div>
        </a>

        <a href="socioeconomico_list.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #f3e5f5; border-radius:12px; text-decoration:none; color:#495057;">
            <div style="width:40px; height:40px; background:#9c27b0; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div>
                <div style="font-weight:600;">Listar Socioeconômicas</div>
                <div style="font-size:12px; color:#6c757d;">Ver todas as fichas</div>
            </div>
        </a>

    </div>
</div>

<style>
    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .search-form input:focus,
    .search-form select:focus {
        outline: none;
        border-color: #6fb64f;
        box-shadow: 0 0 0 3px rgba(111, 182, 79, 0.1);
    }
    
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-form input,
        .search-form select,
        .search-form button {
            width: 100%;
            min-width: auto;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions > div {
            grid-template-columns: 1fr;
        }
    }
</style>

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
            resultsContainer.innerHTML = `<div style="padding:20px; background:#fff; border-radius:12px; text-align:center; color:#6c757d;">Nenhum registro encontrado.</div>`;
            return;
        }
        data.forEach(item => {
            const nome = item.nome || item.nome_completo || item.nome_entrevistado || '—';
            resultsContainer.innerHTML += `
                <div style="background:#fff; padding:16px; border-radius:12px; margin-bottom:12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                    <div style="font-size:18px; font-weight:600; color:#495057;">${nome}</div>
                    <div style="margin-top:6px; color:#6c757d;">
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

        // Chamar controller (rota padrão do seu projeto)
        const url = `prontuarios.php?action=buscar&ajax=1&q=${encodeURIComponent(query)}&categoria=${encodeURIComponent(category)}`;

        // Opcional: mostrar carregando
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