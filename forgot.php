<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu sua senha? - Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #ffffff; margin: 0; padding: 0; }
        .container { width: 100vw; height: 100vh; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; }
        .forgot-container { display:flex; background:#fff; border-radius:0; box-shadow:none; overflow:hidden; width:100%; height:100%; max-width:1400px; max-height:800px; }
        .forgot-left { flex:1; position:relative; }
        .forgot-left img { width:100%; height:100%; object-fit:cover; }
        .forgot-left::after { content:""; position:absolute; top:-10%; left:-60px; width:160px; height:120%; background:#0e6f9d; border-radius:0 60% 60% 0 / 0 50% 50% 0; box-shadow:2px 0 0 2px #0e6f9d; }
        .forgot-right { flex:1; padding:60px 50px; display:flex; flex-direction:column; justify-content:center; gap:20px; }
        .logo-img { width:180px; height:auto; }
        .title { font-size:34px; font-weight:800; text-align:left; margin-top:10px; }
        .subtitle { color:#333; }
        .input-group input { border-color:#f0a36b; }
        .btn-primary { background:#6fb64f; color:#fff; border:none; padding:14px 22px; border-radius:28px; font-weight:700; cursor:pointer; }
        .btn-link { color:#f0a36b; text-decoration:none; text-align:center; display:inline-block; margin-top:10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-container">
            <div class="forgot-left">
                <img src="img/84ee2f859c98cde210228f9cf472d03b4932ff8c.jpg" alt="bg">
            </div>
            <div class="forgot-right">
                <div style="text-align:center"><img src="img/logo.png" class="logo-img" alt="logo"></div>
                <h1 class="title">ESQUECEU SUA SENHA?</h1>
                <p class="subtitle">Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>
                <form method="post" action="#" onsubmit="event.preventDefault(); alert('Enviaremos um link se o e-mail existir.'); window.location='index.php';">
                    <div class="input-group">
                        <input type="email" placeholder="Digite seu email" required>
                    </div>
                    <button class="btn-primary" type="submit">Recuperar senha</button>
                </form>
                <a class="btn-link" href="index.php">Cancelar</a>
            </div>
        </div>
    </div>
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
    <!-- Modo Escuro -->
    <script src="js/theme-toggle.js"></script>
</body>
</html>


