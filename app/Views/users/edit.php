<div class="actions flex-between mb-4">
    <div></div>
    <div class="d-flex gap-2">
        <a href="users.php" class="btn secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form method="post" class="user-form" style="max-width: 800px;">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
    
    <div class="card-glass form-section p-4 mb-4">
        <h3 class="form-section-title mb-3">
            <i class="fas fa-user-edit text-orange"></i> Editar Usuário
        </h3>
        
        <div class="user-info card-glass p-3 mb-4 d-flex align-items-center gap-3">
            <div class="avatar avatar-placeholder" style="width:54px; height:54px; font-size:20px;">
                <?php echo strtoupper(substr($user['name'] ?? $user['nome'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight:700; font-size:17px; color:var(--text-primary);"><?php echo htmlspecialchars($user['name'] ?? $user['nome'] ?? 'Sem nome'); ?></div>
                <div class="text-muted" style="font-size:14px;"><?php echo htmlspecialchars($user['email'] ?? 'Sem email'); ?></div>
                <div class="text-muted" style="font-size:12px;">Criado em: <?php echo isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?></div>
            </div>
        </div>
        
        <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div>
                <label class="form-label-bold">Nome Completo *</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name'] ?? $user['nome'] ?? ''); ?>" class="form-control" placeholder="Digite o nome completo">
            </div>
            
            <div>
                <label class="form-label-bold">Email *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="form-control" placeholder="exemplo@email.com">
            </div>
            
            <div>
                <label class="form-label-bold">
                    Nova Senha
                    <small class="text-muted font-weight-normal">(deixe em branco para manter a atual)</small>
                </label>
                <input type="password" name="password" minlength="12" class="form-control" placeholder="Nova senha (opcional)">
            </div>
            
            <div>
                <label class="form-label-bold">Nível de Acesso *</label>
                <select name="role" required class="form-select">
                    <?php $userRole = $user['role'] ?? $user['nivel'] ?? ''; ?>
                    <option value="">Selecione o nível de acesso</option>
                    <option value="admin" <?php echo ($userRole === 'admin' || $userRole === 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="psicologo" <?php echo ($userRole === 'psicologo' || $userRole === 'Psicólogo') ? 'selected' : ''; ?>>Psicólogo / Assistente Social</option>
                    <option value="funcionario" <?php echo ($userRole === 'funcionario' || $userRole === 'Funcionário') ? 'selected' : ''; ?>>Funcionário</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="card-glass p-4 mb-4" style="border-left:4px solid var(--primary-blue, #17a2b8);">
        <h4 class="mb-3" style="color:var(--text-primary); display:flex; align-items:center; gap:8px;">
            <i class="fas fa-shield-alt text-blue"></i> Permissões Atuais
        </h4>
        
        <div class="current-role card-glass p-3">
            <?php
            $roleInfo = [
                'admin' => [
                    'name' => 'Administrador',
                    'icon' => 'fa-crown',
                    'color' => 'var(--primary-red, #dc3545)',
                    'permissions' => [
                        'Acesso total ao sistema',
                        'Gerenciamento de usuários',
                        'Criação, edição e exclusão de fichas',
                        'Visualização de relatórios',
                        'Configurações do sistema',
                        'NÃO tem acesso à área psicológica'
                    ]
                ],
                'psicologo' => [
                    'name' => 'Psicólogo',
                    'icon' => 'fa-brain',
                    'color' => 'var(--primary-blue, #17a2b8)',
                    'permissions' => [
                        'Visualização de todas as fichas',
                        'Acesso exclusivo à área psicológica',
                        'Criação e edição de anotações psicológicas',
                        'Avaliações e evolução das crianças',
                        'Área psicológica é privada e exclusiva'
                    ]
                ],
                'funcionario' => [
                    'name' => 'Funcionário',
                    'icon' => 'fa-user-friends',
                    'color' => 'var(--primary-green, #28a745)',
                    'permissions' => [
                        'Apenas visualização de informações',
                        'Não pode criar ou editar fichas',
                        'Não pode criar anotações na agenda',
                        'Acesso limitado para consulta',
                        'Somente leitura'
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
                <div style="font-weight:700; color:<?php echo $currentRole['color']; ?>; margin-bottom:12px; font-size:16px;">
                    <i class="fas <?php echo $currentRole['icon']; ?>"></i> <?php echo e($currentRole['name'] ?? ''); ?>
                </div>
                <div class="text-muted" style="font-size:14px; line-height:1.7;">
                    <?php foreach ($currentRole['permissions'] as $permission): ?>
                        • <?php echo e($permission); ?><br>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="color:var(--primary-red, #dc3545);">Nível de acesso não reconhecido</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="actions d-flex gap-2 justify-content-end">
        <a href="users.php" class="btn secondary">
            Cancelar
        </a>
        <button type="submit" class="btn primary">
            <i class="fas fa-save"></i> Salvar Alterações
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                this.classList.add('input-error');
            } else {
                this.classList.remove('input-error');
            }
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 12) {
                this.classList.add('input-error');
            } else {
                this.classList.remove('input-error');
            }
        });
    }
});
</script>
