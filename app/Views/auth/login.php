<div class="login-form">
    <h2 class="login-title">LOGIN</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages" role="alert" aria-live="assertive">
            <?php foreach ($errors as $error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form id="loginForm" action="index.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
        
        <div class="input-group">
            <label class="sr-only" for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="Digite seu email" 
                   autocomplete="email" 
                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
        </div>
        
        <div class="input-group" style="position: relative;">
            <label class="sr-only" for="password">Senha</label>
            <input type="password" id="password" name="password" placeholder="Digite sua senha" 
                   autocomplete="current-password" required style="padding-right: 40px; width: 100%; box-sizing: border-box;">
            <button type="button" id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; padding: 0; outline: none;" title="Mostrar/Ocultar Senha">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        
        <div class="forgot-password">
            <a href="forgot.php" id="forgotPassword">Esqueceu a senha?</a>
        </div>
        
        <button type="submit" class="login-btn">Entrar</button>
    </form>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>
