document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const forgotPasswordLink = document.getElementById('forgotPassword');
    const loginBtn = document.querySelector('.login-btn');

    // Verificar se estamos na página de login
    if (!emailInput || !passwordInput || !loginBtn) {
        return; // Sair se não for a página de login
    }

    // Validação em tempo real dos campos
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validatePassword(password) {
        return password.length >= 6;
    }

    function updateButtonState() {
        const isEmailValid = validateEmail(emailInput.value);
        const isPasswordValid = validatePassword(passwordInput.value);
        
        if (isEmailValid && isPasswordValid) {
            loginBtn.style.opacity = '1';
            loginBtn.disabled = false;
        } else {
            loginBtn.style.opacity = '0.7';
            loginBtn.disabled = true;
        }
    }

    // Event listeners para validação em tempo real
    emailInput.addEventListener('input', function() {
        const isValid = validateEmail(this.value);
        if (this.value.length > 0) {
            if (isValid) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        } else {
            this.style.borderColor = '#e0e0e0';
        }
        updateButtonState();
    });

    passwordInput.addEventListener('input', function() {
        const isValid = validatePassword(this.value);
        if (this.value.length > 0) {
            if (isValid) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        } else {
            this.style.borderColor = '#e0e0e0';
        }
        updateButtonState();
    });

    // Submissão do formulário
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // Validação final
        if (!validateEmail(email)) {
            showMessage('Por favor, insira um email válido.', 'error');
            emailInput.focus();
            return;
        }

        if (!validatePassword(password)) {
            showMessage('A senha deve ter pelo menos 6 caracteres.', 'error');
            passwordInput.focus();
            return;
        }

        // Mostrar animação simples de loading
        showSimpleLoadingAnimation();
        
        // Submeter formulário após animação
        setTimeout(() => {
            this.submit();
        }, 2000);
        });
    }

    // Link "Esqueceu a senha?"
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            // se for link para página, deixar navegar
        });
    }

    // Função para mostrar mensagens
    function showMessage(message, type) {
        // Remove mensagem anterior se existir
        const existingMessage = document.querySelector('.message');
        if (existingMessage) {
            existingMessage.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        
        // Estilos da mensagem
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
            word-wrap: break-word;
        `;

        if (type === 'error') {
            messageDiv.style.backgroundColor = '#e74c3c';
        } else if (type === 'success') {
            messageDiv.style.backgroundColor = '#27ae60';
        } else if (type === 'info') {
            messageDiv.style.backgroundColor = '#3498db';
        }

        document.body.appendChild(messageDiv);

        // Remove a mensagem após 4 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    messageDiv.remove();
                }, 300);
            }
        }, 4000);
    }

    // Adicionar animações CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Estado inicial do botão
    updateButtonState();

    // Efeito de foco suave nos inputs (apenas na página de login)
    const loginInputs = loginForm.querySelectorAll('input');
    loginInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Função para animação simples de loading
    function showSimpleLoadingAnimation() {
        // Criar overlay
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            font-family: 'Poppins', sans-serif;
        `;

        // Criar spinner
        const spinner = document.createElement('div');
        spinner.style.cssText = `
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #6fb64f;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        `;

        // Texto de loading
        const loadingText = document.createElement('div');
        loadingText.textContent = 'Entrando no sistema...';
        loadingText.style.cssText = `
            font-size: 18px;
            font-weight: 500;
            text-align: center;
            animation: pulse 1.5s ease-in-out infinite;
        `;

        // Adicionar elementos
        overlay.appendChild(spinner);
        overlay.appendChild(loadingText);
        document.body.appendChild(overlay);

        // Remover overlay após 2 segundos
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.remove();
            }
        }, 2000);
    }
    
    // Adicionar animações CSS para o loading simples
    const loadingStyle = document.createElement('style');
    loadingStyle.textContent = `
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }
    `;
    document.head.appendChild(loadingStyle);
});
