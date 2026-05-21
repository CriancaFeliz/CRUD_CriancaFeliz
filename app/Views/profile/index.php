<div class="profile-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="dashboard.php" class="btn" style="background: #6fb64f; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
            ← Voltar ao Dashboard
        </a>
    </div>
    
    <div class="card" style="background: var(--bg-primary); border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding: 24px; margin-bottom: 20px;">
        <h2 style="margin-bottom: 24px; color: var(--text-primary);">Meu Perfil</h2>
        
        <!-- Seção de Foto -->
        <div class="profile-photo-section" style="display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 24px; background: var(--bg-secondary); border-radius: 12px; margin-bottom: 24px;">
            <div class="photo-preview" style="position: relative;">
                <?php if (!empty($userData['photo'])): ?>
                    <img id="profilePhoto" src="<?php echo htmlspecialchars($userData['photo']); ?>" alt="Foto do perfil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border-color);">
                <?php else: ?>
                    <div id="profilePhoto" style="width: 150px; height: 150px; border-radius: 50%; background: var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 48px; color: white; border: 4px solid var(--border-color);">
                        <?php echo strtoupper(substr($userData['name'] ?? 'U', 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center;">
                <h3 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($userData['name'] ?? 'Usuário'); ?></h3>
                <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 14px;"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></p>
            </div>
            
            <form id="photoForm" enctype="multipart/form-data" style="width: 100%; max-width: 300px;">
                <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;">
                <button type="button" onclick="document.getElementById('photoInput').click()" style="width: 100%; background: var(--primary-orange); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                    📷 Alterar Foto
                </button>
                <small style="display: block; text-align: center; margin-top: 8px; color: var(--text-muted);">JPG, PNG, GIF ou WEBP (máx. 2MB)</small>
            </form>
        </div>
        
        <!-- Seção de Alteração de Senha -->
        <div class="password-section" style="padding: 24px; background: var(--bg-secondary); border-radius: 12px;">
            <h3 style="margin: 0 0 20px 0; color: var(--text-primary);">Alterar Senha</h3>
            
            <form method="POST" action="profile.php?action=updatePassword">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 600;">Senha Atual *</label>
                    <input type="password" name="current_password" required style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; font-family: Poppins; background: var(--input-bg); color: var(--text-primary); box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 600;">Nova Senha *</label>
                    <input type="password" name="new_password" required minlength="6" style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; font-family: Poppins; background: var(--input-bg); color: var(--text-primary); box-sizing: border-box;">
                    <small style="color: var(--text-muted); font-size: 12px;">Mínimo de 6 caracteres</small>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 600;">Confirmar Nova Senha *</label>
                    <input type="password" name="confirm_password" required minlength="6" style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; font-family: Poppins; background: var(--input-bg); color: var(--text-primary); box-sizing: border-box;">
                </div>
                
                <button type="submit" style="width: 100%; background: var(--primary-green); color: white; border: none; padding: 14px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.2s;">
                    🔒 Alterar Senha
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Obter email do usuário atual
    const currentUserEmail = '<?php echo $userData['email'] ?? ''; ?>';
    
    // Carregar foto do sessionStorage ao carregar a página
    window.addEventListener('DOMContentLoaded', function() {
        const savedPhoto = sessionStorage.getItem(`profile_photo_${currentUserEmail}`);
        if (savedPhoto) {
            const photoElement = document.getElementById('profilePhoto');
            if (photoElement.tagName === 'IMG') {
                photoElement.src = savedPhoto;
            } else {
                photoElement.outerHTML = `<img id="profilePhoto" src="${savedPhoto}" alt="Foto do perfil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border-color);">`;
            }
        }
    });
    
    // Upload de foto (salva em Base64 no sessionStorage)
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validar tipo
        if (!file.type.match('image.*')) {
            alert('Por favor, selecione uma imagem válida');
            return;
        }
        
        // Validar tamanho (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('A imagem deve ter no máximo 2MB');
            return;
        }
        
        // Ler arquivo e salvar em Base64
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64Image = e.target.result;
            
            // Salvar no sessionStorage com email do usuário
            try {
                sessionStorage.setItem(`profile_photo_${currentUserEmail}`, base64Image);
                console.log('✓ Foto salva no sessionStorage para:', currentUserEmail);
                
                // Atualizar preview
                const photoElement = document.getElementById('profilePhoto');
                if (photoElement.tagName === 'IMG') {
                    photoElement.src = base64Image;
                } else {
                    photoElement.outerHTML = `<img id="profilePhoto" src="${base64Image}" alt="Foto do perfil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border-color);">`;
                }
                
                // Mostrar notificação de sucesso
                if (window.notificationSystem) {
                    window.notificationSystem.success('Foto atualizada com sucesso!');
                } else {
                    alert('Foto atualizada com sucesso! Recarregue a página para ver em todas as telas.');
                }
                
                // Recarregar página para atualizar avatar no topbar
                setTimeout(() => location.reload(), 1000);
            } catch (error) {
                console.error('Erro ao salvar foto:', error);
                alert('Erro ao salvar foto. A imagem pode ser muito grande.');
            }
        };
        
        reader.onerror = function() {
            alert('Erro ao ler o arquivo de imagem');
        };
        
        reader.readAsDataURL(file);
    });
</script>

<style>
    .profile-photo-section button:hover {
        background: var(--primary-orange-hover) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(240, 163, 107, 0.3);
    }
    
    .password-section button:hover {
        background: #5da43e !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(111, 182, 79, 0.3);
    }
    
    /* Modo escuro */
    [data-theme="dark"] .card {
        background: var(--bg-secondary) !important;
    }
    
    [data-theme="dark"] .profile-photo-section,
    [data-theme="dark"] .password-section {
        background: var(--bg-tertiary) !important;
    }
</style>
