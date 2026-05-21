<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="users.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
        ← Voltar
    </a>
</div>

<form method="post" style="max-width: 800px;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <div class="form-section" style="background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 20px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">
            ✏️ Editar Usuário
        </h3>
        
        <div class="user-info" style="background:#f8f9fa; padding:16px; border-radius:8px; margin-bottom:20px; display:flex; align-items:center; gap:16px;">
            <div class="avatar" style="width:60px; height:60px; border-radius:50%; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-weight:600; color:#495057; font-size:24px;">
                <?php echo strtoupper(substr($user['name'] ?? $user['nome'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight:600; font-size:18px; color:#212529;"><?php echo htmlspecialchars($user['name'] ?? $user['nome'] ?? 'Sem nome'); ?></div>
                <div style="color:#6c757d; font-size:14px;"><?php echo htmlspecialchars($user['email'] ?? 'Sem email'); ?></div>
                <div style="font-size:12px; color:#6c757d;">Criado em: <?php echo isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?></div>
            </div>
        </div>
        
        <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Nome Completo *
                </label>
                <input type="text" 
                       name="name" 
                       required
                       value="<?php echo htmlspecialchars($user['name'] ?? $user['nome'] ?? ''); ?>"
                       style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;"
                       placeholder="Digite o nome completo">
            </div>
            
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Email *
                </label>
                <input type="email" 
                       name="email" 
                       required
                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                       style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;"
                       placeholder="exemplo@email.com">
            </div>
            
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Nova Senha
                    <small style="color:#6c757d; font-weight:400;">(deixe em branco para manter a atual)</small>
                </label>
                <input type="password" 
                       name="password" 
                       minlength="6"
                       style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;"
                       placeholder="Nova senha (opcional)">
            </div>
            
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Nível de Acesso *
                </label>
                <select name="role" 
                        required
                        style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <?php $userRole = $user['role'] ?? $user['nivel'] ?? ''; ?>
                    <option value="">Selecione o nível de acesso</option>
                    <option value="admin" <?php echo ($userRole === 'admin' || $userRole === 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="psicologo" <?php echo ($userRole === 'psicologo' || $userRole === 'Psicólogo') ? 'selected' : ''; ?>>Psicólogo / Assistente Social</option>
                    <option value="funcionario" <?php echo ($userRole === 'funcionario' || $userRole === 'Funcionário') ? 'selected' : ''; ?>>Funcionário</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="current-permissions" style="background:#e3f2fd; border-radius:12px; padding:20px; margin-bottom:20px; border-left:4px solid #2196f3;">
        <h4 style="margin:0 0 16px 0; color:#1976d2; display:flex; align-items:center; gap:8px;">
            🔍 Permissões Atuais
        </h4>
        
        <div class="current-role" style="background:white; padding:16px; border-radius:8px;">
            <?php
            $roleInfo = [
                'admin' => [
                    'name' => '👑 Administrador',
                    'color' => '#dc3545',
                    'permissions' => [
                        '✅ Acesso total ao sistema',
                        '✅ Gerenciamento de usuários',
                        '✅ Criação, edição e exclusão de fichas',
                        '✅ Visualização de relatórios',
                        '✅ Configurações do sistema',
                        '❌ NÃO tem acesso à área psicológica'
                    ]
                ],
                'psicologo' => [
                    'name' => '🧠 Psicólogo',
                    'color' => '#17a2b8',
                    'permissions' => [
                        '✅ Visualização de todas as fichas',
                        '✅ Acesso exclusivo à área psicológica',
                        '✅ Criação e edição de anotações psicológicas',
                        '✅ Avaliações e evolução das crianças',
                        '🔐 Área psicológica é privada e exclusiva'
                    ]
                ],
                'funcionario' => [
                    'name' => '👥 Funcionário',
                    'color' => '#28a745',
                    'permissions' => [
                        '✅ Apenas visualização de informações',
                        '❌ Não pode criar ou editar fichas',
                        '❌ Não pode criar anotações na agenda',
                        '❌ Acesso limitado para consulta',
                        '📖 Somente leitura'
                    ]
                ]
            ];
            
            $userRoleKey = strtolower($user['role'] ?? $user['nivel'] ?? '');
            if ($userRoleKey === 'administrador') $userRoleKey = 'admin';
            if ($userRoleKey === 'psicólogo') $userRoleKey = 'psicologo';
            if ($userRoleKey === 'funcionário') $userRoleKey = 'funcionario';
            $currentRole = $roleInfo[$userRoleKey] ?? null;
            ?>
            
            <?php if ($currentRole): ?>
                <div style="font-weight:600; color:<?php echo $currentRole['color']; ?>; margin-bottom:12px; font-size:16px;">
                    <?php echo $currentRole['name']; ?>
                </div>
                <div style="font-size:14px; color:#6c757d; line-height:1.6;">
                    <?php foreach ($currentRole['permissions'] as $permission): ?>
                        <?php echo $permission; ?><br>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="color:#dc3545;">Nível de acesso não reconhecido</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="actions" style="display:flex; gap:10px; justify-content:flex-end;">
        <a href="users.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; text-decoration:none;">
            Cancelar
        </a>
        <button type="submit" class="btn" style="background:#f0a36b; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
            💾 Salvar Alterações
        </button>
    </div>
</form>

<script>
// Validação em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    
    // Validação de email
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.style.borderColor = '#dc3545';
            showFieldError(this, 'Email inválido');
        } else {
            this.style.borderColor = '#f0a36b';
            hideFieldError(this);
        }
    });
    
    // Validação de senha
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length > 0 && password.length < 6) {
            this.style.borderColor = '#dc3545';
            showFieldError(this, 'Senha deve ter pelo menos 6 caracteres');
        } else {
            this.style.borderColor = '#f0a36b';
            hideFieldError(this);
        }
    });
    
    function showFieldError(field, message) {
        hideFieldError(field);
        const error = document.createElement('div');
        error.className = 'field-error';
        error.style.cssText = 'color:#dc3545; font-size:12px; margin-top:4px;';
        error.textContent = message;
        field.parentNode.appendChild(error);
    }
    
    function hideFieldError(field) {
        const existing = field.parentNode.querySelector('.field-error');
        if (existing) {
            existing.remove();
        }
    }
});
</script>

<style>
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr !important;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>
