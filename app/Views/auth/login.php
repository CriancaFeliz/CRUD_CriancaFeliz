<div class="login-form">
    <h2 class="login-title">LOGIN</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form id="loginForm" action="index.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="input-group">
            <input type="email" id="email" name="email" placeholder="Digite seu email" 
                   autocomplete="email" 
                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
        </div>
        
        <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Digite sua senha" 
                   autocomplete="current-password" required>
        </div>
        
        <div class="forgot-password">
            <a href="forgot.php" id="forgotPassword">Esqueceu a senha?</a>
        </div>
        
        <button type="submit" class="login-btn">Entrar</button>
    </form>
</div>
