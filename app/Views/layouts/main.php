<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo htmlspecialchars($_COOKIE['theme'] ?? 'light'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Sistema Criança Feliz'); ?></title>
    <script>
        (function() {
            var theme = localStorage.getItem('theme') || '<?php echo addslashes($_COOKIE['theme'] ?? 'light'); ?>' || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if (!empty($additionalStyles) && is_array($additionalStyles)): ?>
        <?php foreach ($additionalStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo e($style); ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

</head>
<body>
    <?php
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $currentPath = trim(parse_url($currentUri, PHP_URL_PATH) ?: '', '/');
        $currentBase = basename($currentPath ?: ($_SERVER['PHP_SELF'] ?? ''));
        $currentAction = $_GET['action'] ?? null;
        $routeContains = static function ($needle) use ($currentPath, $currentBase) {
            return strpos($currentPath, $needle) !== false || strpos($currentBase, $needle) !== false;
        };
        $isRoute = static function (array $routes) use ($currentBase) {
            return in_array($currentBase, $routes, true);
        };
    ?>
    <div class="app">
        <aside class="sidebar">
            <img src="img/logo.png" class="logo" alt="logo">
            <a class="nav-icon <?php echo $isRoute(['dashboard', 'dashboard.php']) ? 'active' : ''; ?>" href="dashboard.php" title="Início"><i class="fas fa-home"></i></a>
            <a class="nav-icon <?php echo $routeContains('prontuarios') ? 'active' : ''; ?>" href="prontuarios.php" title="Prontuários"><i class="fas fa-users"></i></a>
            
            <!-- Sistema de Faltas Novo -->
            <?php if ($currentUser['role'] !== 'psicologo'): ?>
                <a class="nav-icon <?php echo ($routeContains('faltas') && (!$currentAction || $currentAction === 'index')) ? 'active' : ''; ?>" href="faltas.php" title="Faltas - Por Dia"><i class="fas fa-calendar-day"></i></a>
                <a class="nav-icon <?php echo ($routeContains('faltas') && $currentAction === 'oficina') ? 'active' : ''; ?>" href="faltas.php?action=oficina" title="Faltas - Por Oficina"><i class="fas fa-chalkboard-teacher"></i></a>
                <a class="nav-icon <?php echo ($routeContains('faltas') && $currentAction === 'alertas') ? 'active' : ''; ?>" href="faltas.php?action=alertas" title="Alertas de Faltas"><i class="fas fa-exclamation-triangle"></i></a>

                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a class="nav-icon <?php echo $routeContains('desligamento') ? 'active' : ''; ?>" href="desligamento.php" title="Desligamentos"><i class="fas fa-user-times"></i></a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($currentUser['role'] === 'psicologo'): ?>
                <a class="nav-icon <?php echo $routeContains('psychology') ? 'active' : ''; ?>" href="psychology.php" title="Área Psicológica"><i class="fas fa-brain"></i></a>
            <?php endif; ?>
            <?php if ($currentUser['role'] === 'admin'): ?>
                <a class="nav-icon <?php echo $routeContains('users') ? 'active' : ''; ?>" href="users.php" title="Gerenciar Usuários"><i class="fas fa-user-cog"></i></a>
                <a class="nav-icon <?php echo $routeContains('logs') ? 'active' : ''; ?>" href="logs.php" title="Sistema de Logs"><i class="fas fa-history"></i></a>
                <a class="nav-icon <?php echo $routeContains('reports') ? 'active' : ''; ?>" href="reports.php" title="Relatórios"><i class="fas fa-chart-pie"></i></a>
                <a class="nav-icon <?php echo ($routeContains('faltas') && $currentAction === 'gerenciarOficinas') ? 'active' : ''; ?>" href="faltas.php?action=gerenciarOficinas" title="Gerenciar Oficinas"><i class="fas fa-cogs"></i></a>
            <?php endif; ?>
            <a class="nav-icon nav-icon-profile <?php echo $routeContains('profile') ? 'active' : ''; ?>" href="profile.php" title="Meu Perfil"><i class="fas fa-cog"></i></a>
        </aside>
        
        <main class="content">
            <div class="topbar">
                <div>
                    <div class="topbar-title"><?php echo e($pageTitle ?? $title ?? 'Sistema Criança Feliz'); ?></div>
                </div>
                <div class="user">
                    <a href="profile.php" class="user-profile-link" title="Meu Perfil">
                        <?php if (!empty($currentUser['photo'])): ?>
                            <img src="<?php echo e($currentUser['photo']); ?>" class="avatar" alt="Foto do perfil">
                        <?php else: ?>
                            <div class="avatar avatar-placeholder">
                                <?php echo e(strtoupper(substr($currentUser['name'] ?? 'U', 0, 1))); ?>
                            </div>
                        <?php endif; ?>
                        <div><?php echo e(!empty($currentUser['name']) ? $currentUser['name'] : ($currentUser['email'] ?? 'Usuário')); ?></div>
                    </a>
                    <form action="logout.php" method="POST" class="logout-form">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
                        <button type="submit" class="btn secondary">Sair</button>
                    </form>
                </div>
            </div>
            
            <!-- Flash Messages -->
            <?php if (!empty($messages)): ?>
                <div class="flash-messages">
                    <?php foreach ($messages as $type => $message): ?>
                        <?php $safeMessageType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info'; ?>
                        <div class="flash-message flash-<?php echo e($safeMessageType); ?>">
                            <?php echo e($message); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <script>
                    setTimeout(function() {
                        const flashes = document.querySelectorAll('.flash-message');
                        flashes.forEach(f => {
                            f.style.transition = 'opacity 0.5s ease';
                            f.style.opacity = '0';
                            setTimeout(() => f.remove(), 500);
                        });
                    }, 10000);
                </script>
            <?php endif; ?>
            
            <!-- Conteúdo da página -->
            <?php echo $content; ?>
        </main>
    </div>
    
    <!-- Scripts -->
    <script>
        window.APP_DEBUG = <?php echo appDebugEnabled() ? 'true' : 'false'; ?>;
        window.userRole = '<?php echo addslashes($_SESSION['user_role'] ?? 'funcionario'); ?>';
        window.debugLog = function() {
            if (window.APP_DEBUG && window.console && typeof window.console.log === 'function') {
                window.console.log.apply(window.console, arguments);
            }
        };
        if (!window.APP_DEBUG && window.console && typeof window.console.log === 'function') {
            window.console.log = function() {};
        }
    </script>
    <script src="js/script.js?v=<?php echo @filemtime(BASE_PATH . '/js/script.js') ?: time(); ?>"></script>
    <script src="js/chatbot.js?v=<?php echo @filemtime(BASE_PATH . '/js/chatbot.js') ?: time(); ?>"></script>
    <script src="js/theme-toggle.js?v=<?php echo @filemtime(BASE_PATH . '/js/theme-toggle.js') ?: time(); ?>"></script>
    <script src="js/notifications.js?v=<?php echo @filemtime(BASE_PATH . '/js/notifications.js') ?: time(); ?>"></script>
    <?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo e($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div id="globalConfirmModal" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="globalConfirmTitle">
        <div class="modal-confirm-dialog" style="max-width: 440px; text-align: center;">
            <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz">
            <h3 id="globalConfirmTitle" class="modal-confirm-title">Confirmar</h3>
            <p id="globalConfirmDesc" class="modal-confirm-desc">Tem certeza?</p>
            
            <div class="modal-confirm-btn-group" style="margin-top: 24px;">
                <button type="button" class="btn-confirm-cancel" onclick="closeGlobalConfirmModal()">Cancelar</button>
                <button type="button" id="btnGlobalConfirm" class="btn primary" style="flex: 1; height: 44px; border-radius: 10px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #e74c3c !important; border: none !important; color: white !important;">
                    <i class="fas fa-sign-out-alt"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
    
    <script>
    let globalConfirmCallback = null;
    function openGlobalConfirmModal(title, desc, btnText, callback) {
        document.getElementById('globalConfirmTitle').textContent = title;
        document.getElementById('globalConfirmDesc').textContent = desc;
        document.getElementById('btnGlobalConfirm').innerHTML = `<i class="fas fa-sign-out-alt"></i> ${btnText}`;
        globalConfirmCallback = callback;
        const modal = document.getElementById('globalConfirmModal');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }
    function closeGlobalConfirmModal() {
        const modal = document.getElementById('globalConfirmModal');
        modal.classList.remove('active');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
        globalConfirmCallback = null;
    }
    document.getElementById('btnGlobalConfirm').addEventListener('click', () => {
        if (globalConfirmCallback) globalConfirmCallback();
        closeGlobalConfirmModal();
    });
    
    // Interceptar form de logout
    document.querySelectorAll('.logout-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            openGlobalConfirmModal('Sair do Sistema', 'Tem certeza que deseja encerrar sua sessão?', 'Sair', () => {
                form.submit();
            });
        });
    });
    </script>
</body>
</html>
