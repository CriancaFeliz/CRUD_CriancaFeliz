<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema Criança Feliz'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Lado esquerdo com a imagem -->
            <div class="image-section">
                <img src="img/84ee2f859c98cde210228f9cf472d03b4932ff8c.jpg" alt="Crianças felizes" class="children-image">
            </div>
            
            <!-- Lado direito com o formulário -->
            <div class="form-section">
                <div class="logo-section">
                    <img src="img/logo.png" alt="Associação Criança Feliz" class="logo-img">
                </div>
                
                <!-- Conteúdo da página -->
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script src="js/chatbot.js"></script>
    <script src="js/theme-toggle.js"></script>
    
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
