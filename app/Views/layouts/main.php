<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema Criança Feliz'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <img src="img/logo.png" class="logo" alt="logo">
            <a class="nav-icon <?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php" title="Início"><i class="fas fa-home"></i></a>
            <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'prontuarios') !== false) ? 'active' : ''; ?>" href="prontuarios.php" title="Prontuários"><i class="fas fa-users"></i></a>
            
            <!-- Sistema de Faltas Novo -->
            <?php if ($currentUser['role'] !== 'psicologo'): ?>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'faltas') !== false && (!isset($_GET['action']) || $_GET['action']==='index')) ? 'active' : ''; ?>" href="faltas.php" title="Faltas - Por Dia"><i class="fas fa-calendar-day"></i></a>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'faltas') !== false && isset($_GET['action']) && $_GET['action'] === 'oficina') ? 'active' : ''; ?>" href="faltas.php?action=oficina" title="Faltas - Por Oficina"><i class="fas fa-chalkboard-teacher"></i></a>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'faltas') !== false && isset($_GET['action']) && $_GET['action'] === 'alertas') ? 'active' : ''; ?>" href="faltas.php?action=alertas" title="Alertas de Faltas"><i class="fas fa-exclamation-triangle"></i></a>

                <!-- AGORA SOMENTE ADMIN VÊ O DESLIGAMENTO -->
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'desligamento') !== false) ? 'active' : ''; ?>" href="desligamento.php" title="Desligamentos"><i class="fas fa-user-times"></i></a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($currentUser['role'] === 'psicologo'): ?>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'psychology') !== false) ? 'active' : ''; ?>" href="psychology.php" title="Área Psicológica"><i class="fas fa-brain"></i></a>
            <?php endif; ?>
            <?php if ($currentUser['role'] === 'admin'): ?>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'users') !== false) ? 'active' : ''; ?>" href="users.php" title="Gerenciar Usuários"><i class="fas fa-user-cog"></i></a>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'logs') !== false) ? 'active' : ''; ?>" href="logs.php" title="Sistema de Logs"><i class="fas fa-history"></i></a>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'faltas') !== false && isset($_GET['action']) && $_GET['action'] === 'gerenciarOficinas') ? 'active' : ''; ?>" href="faltas.php?action=gerenciarOficinas" title="Gerenciar Oficinas"><i class="fas fa-cogs"></i></a>
            <?php endif; ?>
            <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'profile') !== false) ? 'active' : ''; ?>" href="profile.php" title="Meu Perfil"><i class="fas fa-cog"></i></a>
        </aside>
        
        <main class="content">
            <div class="topbar">
                <div>
                    <div class="topbar-title"><?php echo $pageTitle ?? $title ?? 'Sistema Criança Feliz'; ?></div>
                </div>
                <div class="user">
                    <a href="profile.php" class="user-profile-link" title="Meu Perfil">
                        <?php if (!empty($currentUser['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($currentUser['photo']); ?>" class="avatar">
                        <?php else: ?>
                            <div class="avatar avatar-placeholder">
                                <?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div><?php echo $currentUser['email'] ?? 'Usuário'; ?></div>
                    </a>
                    <a href="logout.php" class="btn secondary">Sair</a>
                </div>
            </div>
            
            <!-- Flash Messages -->
            <?php if (!empty($messages)): ?>
                <div class="flash-messages">
                    <?php foreach ($messages as $type => $message): ?>
                        <div class="flash-message flash-<?php echo $type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Conteúdo da página -->
            <?php echo $content; ?>
        </main>
    </div>
    
    <!-- Scripts -->
    <script>
        window.APP_DEBUG = <?php echo appDebugEnabled() ? 'true' : 'false'; ?>;
        window.debugLog = function() {
            if (window.APP_DEBUG && window.console && typeof window.console.log === 'function') {
                window.console.log.apply(window.console, arguments);
            }
        };
        if (!window.APP_DEBUG && window.console && typeof window.console.log === 'function') {
            window.console.log = function() {};
        }
    </script>
    <script src="js/script.js"></script>
    <script src="js/chatbot.js"></script>
    <script src="js/theme-toggle.js"></script>
    <script src="js/notifications.js"></script>
    
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
