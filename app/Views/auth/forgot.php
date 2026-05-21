<div class="login-form">
    <h2 class="login-title">RECUPERAR SENHA</h2>

    <?php if (!empty($messages['success'])): ?>
        <div class="success-message"><?php echo htmlspecialchars($messages['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($messages['error'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($messages['error']); ?></div>
    <?php endif; ?>

    <p style="margin: 0 0 20px; color: #555; text-align: center;">
        Informe o email cadastrado para receber o link de redefinicao.
    </p>

    <form action="forgot.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="input-group">
            <input type="email" id="email" name="email" placeholder="Digite seu email" autocomplete="email" required>
        </div>

        <button type="submit" class="login-btn">Enviar link</button>

        <div class="forgot-password">
            <a href="index.php">Voltar ao login</a>
        </div>
    </form>
</div>
