<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="users.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
        ← Voltar
    </a>
</div>

<form method="post" style="max-width: 800px;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <div class="form-section" style="background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 20px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">
            👤 Informações do Usuário
        </h3>
        
        <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Nome Completo *
                </label>
                <input type="text" 
                       name="name" 
                       required
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
                       style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;"
                       placeholder="exemplo@email.com">
            </div>
            
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Senha *
                </label>
                <input type="password" 
                       name="password" 
                       required
                       minlength="6"
                       style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;"
                       placeholder="Mínimo 6 caracteres">
            </div>
            
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">
                    Nível de Acesso *
                </label>
                <select name="role" 
                        required
                        style="padding:12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecione o nível de acesso</option>
                    <option value="admin">Administrador</option>
                    <option value="psicologo">Psicólogo - Assistente Social</option>
                    <option value="funcionario">Funcionário</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="permissions-info" style="background:#f8f9fa; border-radius:12px; padding:20px; margin-bottom:20px; border-left:4px solid #17a2b8;">
        <h4 style="margin:0 0 16px 0; color:#495057; display:flex; align-items:center; gap:8px;">
            🔒 Permissões por Nível de Acesso
        </h4>
        
        <div class="permission-grid" style="display:grid; gap:16px;">
            <div class="permission-item" style="background:white; padding:16px; border-radius:8px; border-left:4px solid #dc3545;">
                <div style="font-weight:600; color:#dc3545; margin-bottom:8px;">👑 Administrador</div>
                <div style="font-size:14px; color:#6c757d; line-height:1.5;">
                    • Acesso total ao sistema<br>
                    • Gerenciamento de usuários<br>
                    • Criação, edição e exclusão de fichas<br>
                    • Visualização de relatórios<br>
                    • Configurações do sistema<br>
                    <strong style="color:#dc3545;">⚠️ NÃO tem acesso à área psicológica</strong>
                </div>
            </div>
            
            <div class="permission-item" style="background:white; padding:16px; border-radius:8px; border-left:4px solid #17a2b8;">
                <div style="font-weight:600; color:#17a2b8; margin-bottom:8px;">🧠 Psicólogo - Assistente Social</div>
                <div style="font-size:14px; color:#6c757d; line-height:1.5;">
                    • Visualização de todas as fichas<br>
                    • Acesso exclusivo à área psicológica<br>
                    • Criação e edição de anotações psicológicas<br>
                    • Avaliações e evolução das crianças<br>
                    <strong style="color:#17a2b8;">🔐 Área psicológica é privada e exclusiva</strong>
                </div>
            </div>
            
            <div class="permission-item" style="background:white; padding:16px; border-radius:8px; border-left:4px solid #28a745;">
                <div style="font-weight:600; color:#28a745; margin-bottom:8px;">👥 Funcionário</div>
                <div style="font-size:14px; color:#6c757d; line-height:1.5;">
                    • Apenas visualização de informações<br>
                    • Não pode criar ou editar fichas<br>
                    • Não pode criar anotações na agenda<br>
                    • Acesso limitado para consulta<br>
                    <strong style="color:#28a745;">📖 Somente leitura</strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="actions" style="display:flex; gap:10px; justify-content:flex-end;">
        <a href="users.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; text-decoration:none;">
            Cancelar
        </a>
        <button type="submit" class="btn" style="background:#6fb64f; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
            👤 Criar Usuário
        </button>
    </div>
</form>

<script>
// Validação em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
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
    
    .permission-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
