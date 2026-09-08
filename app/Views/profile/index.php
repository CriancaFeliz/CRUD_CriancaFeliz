<?php
// Mapeamento e identificação do perfil de acesso
$rawRole = strtolower($userData['role'] ?? $userData['nivel'] ?? $userRole ?? 'funcionario');
if ($rawRole === 'admin') {
    $roleTitle = 'Administrador do Sistema';
    $roleBadgeClass = 'badge-role-admin';
    $roleIcon = 'fa-shield-alt';
    $roleDesc = 'Acesso irrestrito a todas as funcionalidades: gestão de usuários, fichas de acolhimento, prontuários, turmas e oficinas, relatórios estatísticos, auditoria de logs e parametrizações globais.';
} elseif ($rawRole === 'psicologo') {
    $roleTitle = 'Psicólogo(a) Institucional';
    $roleBadgeClass = 'badge-role-psicologo';
    $roleIcon = 'fa-brain';
    $roleDesc = 'Acesso especializado com sigilo ético e profissional à Área Psicológica, prontuários clínicos, evoluções de atendimento e relatórios psicossociais dos atendidos.';
} else {
    $roleTitle = 'Educador / Funcionário';
    $roleBadgeClass = 'badge-role-funcionario';
    $roleIcon = 'fa-id-badge';
    $roleDesc = 'Acesso operacional aos cadastros de acolhimento social, acompanhamento de faltas diárias, controle de chamadas e registro de presenças nas oficinas da instituição.';
}
?>

<div class="profile-page-wrapper">
    <div class="profile-header-actions mb-4">
        <a href="dashboard.php" class="btn secondary profile-back-btn">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>

    <!-- 1. Cartão Principal: Dados do Usuário & Cargo Institucional -->
    <div class="profile-card profile-hero-card mb-4">
        <div class="profile-hero-content">
            <div class="profile-avatar-wrapper">
                <div class="profile-avatar-container">
                    <?php if (!empty($userData['photo'])): ?>
                        <img id="profilePhoto" src="<?php echo htmlspecialchars($userData['photo']); ?>" alt="Foto de perfil" class="profile-avatar-img">
                    <?php else: ?>
                        <div id="profilePhoto" class="profile-avatar-placeholder">
                            <?php echo e(strtoupper(substr($userData['name'] ?? 'U', 0, 1))); ?>
                        </div>
                    <?php endif; ?>
                    <button type="button" class="profile-avatar-cam-btn" onclick="document.getElementById('photoInput').click()" title="Alterar Foto de Perfil" aria-label="Alterar Foto">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <form id="photoForm" enctype="multipart/form-data" class="d-none" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
                    <input type="file" id="photoInput" name="photo" accept="image/*">
                </form>
            </div>

            <div class="profile-info-content">
                <div class="profile-status-bar">
                    <span class="profile-status-pill">
                        <span class="status-pulse-dot"></span> Ativo no Sistema
                    </span>
                    <span class="profile-user-id">ID #<?php echo (int)($userData['id'] ?? 1); ?></span>
                </div>

                <h2 class="profile-name"><?php echo htmlspecialchars($userData['name'] ?? 'Usuário'); ?></h2>
                <div class="profile-email">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userData['email'] ?? ''); ?>
                </div>

                <!-- Destaque do Cargo e Permissões -->
                <div class="profile-role-container">
                    <div class="profile-role-badge <?php echo $roleBadgeClass; ?>">
                        <i class="fas <?php echo $roleIcon; ?>"></i> <?php echo $roleTitle; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Cartão de Preferências Visuais: Tema Claro e Escuro -->
    <div class="profile-card profile-section-card mb-4">
        <div class="profile-card-header">
            <div class="profile-card-title">
                <i class="fas fa-palette"></i> Aparência do Sistema
            </div>
        </div>

        <div class="theme-selector-grid">
            <!-- Opção Claro -->
            <div class="theme-option-card" id="themeCardLight" data-theme-val="light" onclick="selectTheme('light')">
                <div class="theme-card-badge"><i class="fas fa-check-circle"></i> Ativo</div>
                <div class="theme-card-icon-box light-icon-box">
                    <i class="fas fa-sun"></i>
                </div>
                <div class="theme-card-info">
                    <h4 class="theme-card-title">Tema Claro</h4>
                </div>
                <div class="theme-preview-mockup light-mockup">
                    <div class="mockup-sidebar"></div>
                    <div class="mockup-body">
                        <div class="mockup-line-top"></div>
                        <div class="mockup-box"></div>
                    </div>
                </div>
            </div>

            <!-- Opção Escuro -->
            <div class="theme-option-card" id="themeCardDark" data-theme-val="dark" onclick="selectTheme('dark')">
                <div class="theme-card-badge"><i class="fas fa-check-circle"></i> Ativo</div>
                <div class="theme-card-icon-box dark-icon-box">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="theme-card-info">
                    <h4 class="theme-card-title">Tema Escuro</h4>
                </div>
                <div class="theme-preview-mockup dark-mockup">
                    <div class="mockup-sidebar"></div>
                    <div class="mockup-body">
                        <div class="mockup-line-top"></div>
                        <div class="mockup-box"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($rawRole !== 'admin'): ?>
    <!-- Cartão de Assistente Virtual -->
    <div class="profile-card profile-section-card mb-4">
        <div class="profile-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <div class="profile-card-title">
                    <i class="fas fa-robot"></i> Assistente Virtual
                </div>
            </div>
            <div>
                <button type="button" id="btnToggleChatbot" class="btn secondary" onclick="toggleChatbotVisibility()">
                    <i class="fas fa-eye-slash"></i> Ocultar Assistente
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. Cartão de Segurança: Alteração de Senha -->
    <div class="profile-card profile-section-card mb-4">
        <div class="profile-card-header">
            <div class="profile-card-title">
                <i class="fas fa-lock"></i> Segurança
            </div>
        </div>

        <form method="POST" action="profile.php?action=updatePassword" class="profile-password-form">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">

            <div class="form-group mb-3">
                <label class="form-label-bold">Senha Atual <span class="required-asterisk">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="current_password" id="inputCurrentPass" class="form-control" required placeholder="Digite sua senha atual">
                    <button type="button" class="password-toggle-btn" onclick="togglePassVisibility('inputCurrentPass', this)" tabindex="-1" aria-label="Mostrar ou ocultar senha">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-row password-grid mb-3">
                <div class="form-group col-half">
                    <label class="form-label-bold">Nova Senha <span class="required-asterisk">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" name="new_password" id="inputNewPass" class="form-control" required minlength="12" placeholder="Nova senha segura">
                        <button type="button" class="password-toggle-btn" onclick="togglePassVisibility('inputNewPass', this)" tabindex="-1" aria-label="Mostrar ou ocultar senha">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted">Mínimo de 12 caracteres</small>
                </div>

                <div class="form-group col-half">
                    <label class="form-label-bold">Confirmar Nova Senha <span class="required-asterisk">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" name="confirm_password" id="inputConfirmPass" class="form-control" required minlength="12" placeholder="Repita a nova senha">
                        <button type="button" class="password-toggle-btn" onclick="togglePassVisibility('inputConfirmPass', this)" tabindex="-1" aria-label="Mostrar ou ocultar senha">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="profile-submit-wrapper">
                <button type="submit" class="btn success btn-lg profile-save-btn">
                    <i class="fas fa-shield-alt"></i> Salvar Nova Senha
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Gerenciador de Tema na Página de Perfil
    function selectTheme(theme) {
        if (window.themeManager && typeof window.themeManager.setTheme === 'function') {
            window.themeManager.setTheme(theme, true);
        } else {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            document.cookie = 'theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
        }
        updateThemeCardsUI(theme);
    }

    function updateThemeCardsUI(theme) {
        const lightCard = document.getElementById('themeCardLight');
        const darkCard = document.getElementById('themeCardDark');
        if (lightCard && darkCard) {
            if (theme === 'dark') {
                lightCard.classList.remove('active');
                darkCard.classList.add('active');
            } else {
                darkCard.classList.remove('active');
                lightCard.classList.add('active');
            }
        }
    }

    // Inicializa o estado visual dos cards de tema
    document.addEventListener('DOMContentLoaded', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
        updateThemeCardsUI(currentTheme);
    });

    window.addEventListener('themeChanged', function(e) {
        if (e.detail && e.detail.theme) {
            updateThemeCardsUI(e.detail.theme);
        }
    });

    // Configuração do Chatbot
    document.addEventListener('DOMContentLoaded', function() {
        const isEnabled = localStorage.getItem('chatbot_enabled') !== 'false';
        updateChatbotBtnUI(isEnabled);
    });

    function toggleChatbotVisibility() {
        const isEnabled = localStorage.getItem('chatbot_enabled') !== 'false';
        const newState = !isEnabled;
        localStorage.setItem('chatbot_enabled', newState);
        document.cookie = 'chatbot_enabled=' + newState + ';path=/;max-age=31536000;SameSite=Lax';
        
        updateChatbotBtnUI(newState);
        
        if (window.notificationSystem) {
            window.notificationSystem.success(newState ? 'Assistente ativado com sucesso!' : 'Assistente ocultado da tela.');
        } else {
            alert(newState ? 'Assistente ativado com sucesso!' : 'Assistente ocultado da tela.');
        }

        // Apply immediately
        if (newState) {
            if (window.cfChatbot && window.cfChatbot.init) {
                window.cfChatbot.init();
            } else {
                location.reload();
            }
        } else {
            const botEl = document.getElementById('chatbot');
            if (botEl) botEl.remove();
            if (window.cfChatbot) {
                window.cfChatbot.isOpen = false;
            }
        }
    }

    function updateChatbotBtnUI(isEnabled) {
        const btn = document.getElementById('btnToggleChatbot');
        if (!btn) return;
        
        if (isEnabled) {
            btn.className = 'btn secondary';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar Assistente';
        } else {
            btn.className = 'btn success';
            btn.innerHTML = '<i class="fas fa-eye"></i> Exibir Assistente';
        }
    }

    // Alternar visibilidade de senhas
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
        }
    }

    // Upload assíncrono de foto de perfil com preview instantâneo
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (!file.type.match('image.*')) {
            alert('Por favor, selecione uma imagem válida (JPG, PNG, GIF ou WEBP).');
            return;
        }
        
        if (file.size > 2 * 1024 * 1024) {
            alert('A imagem deve ter no máximo 2MB.');
            return;
        }

        const photoElement = document.getElementById('profilePhoto');
        const previousSrc = (photoElement && photoElement.tagName === 'IMG') ? photoElement.src : null;
        const previousHTML = photoElement ? photoElement.outerHTML : '';

        // Pré-visualização instantânea na tela
        const reader = new FileReader();
        reader.onload = function(evt) {
            if (photoElement.tagName === 'IMG') {
                photoElement.src = evt.target.result;
            } else {
                const image = document.createElement('img');
                image.id = 'profilePhoto';
                image.src = evt.target.result;
                image.alt = 'Foto de perfil';
                image.className = 'profile-avatar-img';
                photoElement.replaceWith(image);
            }
        };
        reader.readAsDataURL(file);
        
        const form = document.getElementById('photoForm');
        const formData = new FormData(form);

        fetch('profile.php?action=updatePhoto', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || !data.success) {
                    throw new Error(data.error || 'Erro ao salvar foto.');
                }

                const separator = data.photo.includes('?') ? '&' : '?';
                const photoUrl = data.photo + separator + 'v=' + Date.now();
                const activePhotoEl = document.getElementById('profilePhoto');
                if (activePhotoEl && activePhotoEl.tagName === 'IMG') {
                    activePhotoEl.src = photoUrl;
                }

                if (window.notificationSystem) {
                    window.notificationSystem.success(data.message || 'Foto atualizada com sucesso!');
                } else {
                    alert(data.message || 'Foto atualizada com sucesso!');
                }

                setTimeout(() => location.reload(), 800);
            })
            .catch(error => {
                console.error('Erro ao salvar foto:', error);
                const activePhotoEl = document.getElementById('profilePhoto');
                if (previousSrc && activePhotoEl && activePhotoEl.tagName === 'IMG') {
                    activePhotoEl.src = previousSrc;
                } else if (activePhotoEl && previousHTML) {
                    activePhotoEl.outerHTML = previousHTML;
                }
                alert(error.message || 'Erro ao salvar foto.');
            });
    });
</script>
