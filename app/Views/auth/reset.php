<div class="login-form">
    <h2 class="login-title">NOVA SENHA</h2>

    <?php if (!empty($messages['error'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($messages['error']); ?></div>
    <?php endif; ?>

    <form action="reset_password.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="input-group">
            <input type="password" name="password" placeholder="Nova senha" autocomplete="new-password" required>
        </div>

        <div class="input-group">
            <input type="password" name="confirm_password" placeholder="Confirmar senha" autocomplete="new-password" required>
        </div>

        <button type="submit" class="login-btn">Redefinir senha</button>

        <div class="forgot-password">
            <a href="index.php">Voltar ao login</a>
        </div>
    </form>
</div>
