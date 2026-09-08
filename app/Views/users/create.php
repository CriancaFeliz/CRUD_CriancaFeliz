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
            <i class="fas fa-user-circle text-orange"></i> Informações do Usuário
        </h3>
        
        <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div>
                <label class="form-label-bold">Nome Completo *</label>
                <input type="text" name="name" required class="form-control" placeholder="Digite o nome completo">
            </div>
            
            <div>
                <label class="form-label-bold">Email *</label>
                <input type="email" name="email" required class="form-control" placeholder="exemplo@email.com">
            </div>
            
            <div>
                <label class="form-label-bold">Senha *</label>
                <input type="password" name="password" required minlength="12" class="form-control" placeholder="Mínimo 12 caracteres">
            </div>
            
            <div>
                <label class="form-label-bold">Nível de Acesso *</label>
                <select name="role" required class="form-select">
                    <option value="">Selecione o nível de acesso</option>
                    <option value="admin">Administrador</option>
                    <option value="psicologo">Psicólogo - Assistente Social</option>
                    <option value="funcionario">Funcionário</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="card-glass p-4 mb-4" style="border-left:4px solid var(--primary-blue, #17a2b8);">
        <h4 class="mb-3" style="color:var(--text-primary); display:flex; align-items:center; gap:8px;">
            <i class="fas fa-lock text-blue"></i> Permissões por Nível de Acesso
        </h4>
        
        <div class="permission-grid" style="display:grid; gap:16px;">
            <div class="permission-item card-glass p-3" style="border-left:4px solid var(--primary-red, #dc3545);">
                <div style="font-weight:700; color:var(--primary-red, #dc3545); margin-bottom:8px;">
                    <i class="fas fa-crown"></i> Administrador
                </div>
                <div class="text-muted" style="font-size:14px; line-height:1.5;">
                    • Acesso total ao sistema<br>
                    • Gerenciamento de usuários<br>
                    • Criação, edição e exclusão de fichas<br>
                    • Visualização de relatórios<br>
                    • Configurações do sistema<br>
                    <strong style="color:var(--primary-red, #dc3545);"><i class="fas fa-exclamation-triangle"></i> NÃO tem acesso à área psicológica</strong>
                </div>
            </div>
            
            <div class="permission-item card-glass p-3" style="border-left:4px solid var(--primary-blue, #17a2b8);">
                <div style="font-weight:700; color:var(--primary-blue, #17a2b8); margin-bottom:8px;">
                    <i class="fas fa-brain"></i> Psicólogo - Assistente Social
                </div>
                <div class="text-muted" style="font-size:14px; line-height:1.5;">
                    • Visualização de todas as fichas<br>
                    • Acesso exclusivo à área psicológica<br>
                    • Criação e edição de anotações psicológicas<br>
                    • Avaliações e evolução das crianças<br>
                    <strong style="color:var(--primary-blue, #17a2b8);"><i class="fas fa-shield-alt"></i> Área psicológica é privada e exclusiva</strong>
                </div>
            </div>
            
            <div class="permission-item card-glass p-3" style="border-left:4px solid var(--primary-green, #28a745);">
                <div style="font-weight:700; color:var(--primary-green, #28a745); margin-bottom:8px;">
                    <i class="fas fa-user-friends"></i> Funcionário
                </div>
                <div class="text-muted" style="font-size:14px; line-height:1.5;">
                    • Apenas visualização de informações<br>
                    • Não pode criar ou editar fichas<br>
                    • Não pode criar anotações na agenda<br>
                    • Acesso limitado para consulta<br>
                    <strong style="color:var(--primary-green, #28a745);"><i class="fas fa-book-open"></i> Somente leitura</strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="actions d-flex gap-2 justify-content-end">
        <a href="users.php" class="btn secondary">
            Cancelar
        </a>
        <button type="submit" class="btn success">
            <i class="fas fa-user-plus"></i> Criar Usuário
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
