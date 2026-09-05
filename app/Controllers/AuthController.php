<?php

/**
 * Controller para autenticação
 */
class AuthController extends BaseController {
    
    /**
     * Exibe página de login
     */
    public function showLogin() {
        // Se já está logado, redirecionar para dashboard
        if ($this->authService->isLoggedIn()) {
            redirect('dashboard.php');
        }
        
        $data = [
            'title' => 'Login - Associação Criança Feliz',
            'errors' => $_SESSION['login_errors'] ?? [],
            'formData' => $_SESSION['form_data'] ?? [],
            'csrf_token' => $this->generateCSRF()
        ];
        
        // Limpar dados da sessão
        unset($_SESSION['login_errors']);
        unset($_SESSION['form_data']);
        
        $this->renderWithLayout('auth', 'auth/login', $data);
    }
    
    /**
     * Processa login
     */
    public function processLogin() {
        if (!$this->isPost()) {
            redirect('index.php');
        }

        $email = '';
        $rateLimiter = null;
        $recordFailedAttempt = false;

        try {
            $this->validateCSRF();

            $email = strtolower(trim((string) $this->getParam('email', '')));
            $password = $this->getParam('password', '');

            $rateLimiter = new RateLimitService();
            $maxAttempts = $this->configInt('LOGIN_MAX_ATTEMPTS', 5, 2, 20);
            $windowSeconds = $this->configInt('LOGIN_WINDOW_SECONDS', 900, 60, 86400);
            $clientIp = getClientIp();

            if (
                !$rateLimiter->isAllowed('login_email', $email ?: 'empty', $maxAttempts, $windowSeconds)
                || !$rateLimiter->isAllowed('login_ip', $clientIp, $maxAttempts * 3, $windowSeconds)
            ) {
                throw new Exception('Muitas tentativas de acesso. Aguarde alguns minutos e tente novamente.');
            }

            $recordFailedAttempt = true;
            $user = $this->authService->login($email, $password);

            $rateLimiter->clear('login_email', $email);

            // Login bem-sucedido
            redirect('dashboard.php');

        } catch (Throwable $e) {
            if ($recordFailedAttempt && $rateLimiter instanceof RateLimitService) {
                try {
                    $maxAttempts = $maxAttempts ?? 5;
                    $windowSeconds = $windowSeconds ?? 900;
                    $blockSeconds = $this->configInt('LOGIN_BLOCK_SECONDS', 900, 60, 86400);
                    $rateLimiter->hit('login_email', $email ?: 'empty', $maxAttempts, $windowSeconds, $blockSeconds);
                    $rateLimiter->hit('login_ip', $clientIp ?? getClientIp(), $maxAttempts * 3, $windowSeconds, $blockSeconds);
                } catch (Throwable $rateLimitError) {
                    reportException($rateLimitError, 'login-rate-limit');
                }
            }

            // Armazenar erro e dados do formulário na sessão
            $_SESSION['login_errors'] = [$this->safeLoginError($e)];
            $_SESSION['form_data'] = ['email' => $email];

            redirect('index.php');
        }
    }
    
    /**
     * Processa logout
     */
    public function logout() {
        if (!$this->isPost()) {
            $this->redirectWithError('dashboard.php', 'Solicitação de saída inválida.', false);
        }

        $this->validateCSRF();
        $this->authService->logout();
        redirect('index.php');
    }
    
    /**
     * Exibe página de esqueceu senha
     */
    public function showForgotPassword() {
        if ($this->authService->isLoggedIn()) {
            redirect('dashboard.php');
        }
        
        $data = [
            'title' => 'Esqueceu a Senha - Associação Criança Feliz',
            'csrf_token' => $this->generateCSRF(),
            'messages' => $this->getFlashMessages()
        ];
        
        $this->renderWithLayout('auth', 'auth/forgot', $data);
    }
    
    /**
     * Processa solicitação de recuperação de senha
     */
    public function processForgotPassword() {
        if (!$this->isPost()) {
            redirect('forgot.php');
        }
        
        try {
            $this->validateCSRF();
            
            $email = $this->getParam('email', '');
            
            if (empty($email) || !validateEmail($email)) {
                throw new Exception('Email válido é obrigatório');
            }
            $rateLimiter = new RateLimitService();
            $windowSeconds = $this->configInt('PASSWORD_RESET_WINDOW_SECONDS', 3600, 300, 86400);
            $emailMax = $this->configInt('PASSWORD_RESET_EMAIL_MAX_ATTEMPTS', 3, 1, 20);
            $ipMax = $this->configInt('PASSWORD_RESET_IP_MAX_ATTEMPTS', 10, 2, 100);
            $clientIp = getClientIp();

            $emailAllowed = $rateLimiter->isAllowed('password_reset_email', strtolower($email), $emailMax, $windowSeconds);
            $ipAllowed = $rateLimiter->isAllowed('password_reset_ip', $clientIp, $ipMax, $windowSeconds);

            if ($emailAllowed && $ipAllowed) {
                $rateLimiter->hit('password_reset_email', strtolower($email), $emailMax, $windowSeconds, $windowSeconds);
                $rateLimiter->hit('password_reset_ip', $clientIp, $ipMax, $windowSeconds, $windowSeconds);
                $this->sendPasswordResetEmail($email);
            }
            
            $this->redirectWithSuccess('forgot.php', 'Se o email existir no sistema, você receberá instruções para redefinir sua senha.');
            
        } catch (Exception $e) {
            $this->redirectWithError('forgot.php', $e->getMessage());
        }
    }
    
    /**
     * Exibe página de redefinir senha
     */
    public function showResetPassword() {
        $token = $this->getParam('token', '');
        
        if (empty($token)) {
            $this->redirectWithError('forgot.php', 'Token inválido');
        }
        
        // Validar token (implementar conforme memória)
        if (!$this->isValidResetToken($token)) {
            $this->redirectWithError('forgot.php', 'Token inválido ou expirado');
        }
        
        $data = [
            'title' => 'Redefinir Senha - Associação Criança Feliz',
            'token' => $token,
            'csrf_token' => $this->generateCSRF(),
            'messages' => $this->getFlashMessages()
        ];
        
        $this->renderWithLayout('auth', 'auth/reset', $data);
    }
    
    /**
     * Processa redefinição de senha
     */
    public function processResetPassword() {
        if (!$this->isPost()) {
            redirect('forgot.php');
        }
        
        try {
            $this->validateCSRF();
            
            $token = $this->getParam('token', '');
            $password = $this->getParam('password', '');
            $confirmPassword = $this->getParam('confirm_password', '');
            
            if (empty($token)) {
                throw new Exception('Token inválido');
            }
            
            if (!$this->isValidResetToken($token)) {
                throw new Exception('Token inválido ou expirado');
            }
            
            if (empty($password) || !validatePassword($password)) {
                throw new Exception(passwordValidationMessage());
            }
            
            if ($password !== $confirmPassword) {
                throw new Exception('Senhas não conferem');
            }
            
            // Atualizar senha (implementar)
            $this->updatePasswordByToken($token, $password);
            
            $this->redirectWithSuccess('index.php', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
            
        } catch (Exception $e) {
            $this->redirectWithError('reset_password.php?token=' . urlencode($token ?? ''), $e->getMessage());
        }
    }
    
    /**
     * Altera senha do usuário logado
     */
    public function changePassword() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $currentPassword = $this->getParam('current_password', '');
            $newPassword = $this->getParam('new_password', '');
            $confirmPassword = $this->getParam('confirm_password', '');
            
            if (empty($currentPassword)) {
                throw new Exception('Senha atual é obrigatória');
            }
            
            if (empty($newPassword) || !validatePassword($newPassword)) {
                throw new Exception(passwordValidationMessage());
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('Senhas não conferem');
            }
            
            $this->authService->changePassword($currentPassword, $newPassword);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => 'Senha alterada com sucesso']);
            } else {
                $this->redirectWithSuccess('dashboard.php', 'Senha alterada com sucesso');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('dashboard.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Envia email de recuperação de senha
     */
    private function sendPasswordResetEmail($email) {
        $userModel = new User();
        if (!$userModel->findByEmail($email)) {
            debugLog('Solicitacao de reset para email inexistente', ['email_hash' => hash('sha256', strtolower($email))]);
            return;
        }

        // Gerar token
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1 hora
        
        $this->saveResetToken($email, $token, $expiry);

        $baseUrl = rtrim((string) (getenv('APP_BASE_URL') ?: ''), '/');
        $appEnvironment = strtolower((string) (getenv('APP_ENV') ?: 'production'));
        $isAllowedUrl = filter_var($baseUrl, FILTER_VALIDATE_URL)
            && ($appEnvironment !== 'production' || strpos($baseUrl, 'https://') === 0);

        if (!$isAllowedUrl) {
            $this->invalidateResetToken($token);
            reportException(new RuntimeException('APP_BASE_URL ausente ou insegura.'), 'password-reset');
            return;
        }

        $resetUrl = $baseUrl . '/reset_password.php?token=' . rawurlencode($token);

        if (!$this->sendEmail($email, 'Recuperação de Senha - Criança Feliz', $resetUrl)) {
            $this->invalidateResetToken($token);
        }
    }
    
    /**
     * Salva token de reset
     */
    private function saveResetToken($email, $token, $expiry) {
        $tokenHash = hash('sha256', $token);
        $resetTokenModel = new PasswordResetToken();
        $resetTokenModel->createToken($email, $tokenHash, date('Y-m-d H:i:s', $expiry));
    }

    private function invalidateResetToken($token) {
        $tokenHash = hash('sha256', $token);
        (new PasswordResetToken())->markUsed($tokenHash);
    }
    
    /**
     * Valida token de reset
     */
    private function isValidResetToken($token) {
        $tokenHash = hash('sha256', $token);
        $resetTokenModel = new PasswordResetToken();

        return (bool) $resetTokenModel->findValidByHash($tokenHash);
    }
    
    /**
     * Atualiza senha por token
     */
    private function updatePasswordByToken($token, $password) {
        $tokenHash = hash('sha256', $token);
        $resetTokenModel = new PasswordResetToken();
        $tokenData = $resetTokenModel->findValidByHash($tokenHash);

        if (!$tokenData) {
            throw new Exception('Token inválido');
        }
        
        $email = $tokenData['email'];
        
        // Atualizar senha do usuário
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if ($user) {
            $userId = $user['idusuario'] ?? $user['id'] ?? null;
            if ($userId) {
                $userModel->updateUser($userId, ['password' => $password]);
            }
        }
        
        $resetTokenModel->markUsed($tokenHash);
    }
    
    private function sendEmail($to, $subject, $resetUrl) {
        if (!filter_var(getenv('MAIL_ENABLED'), FILTER_VALIDATE_BOOLEAN)) {
            debugLog('Envio de recuperação desabilitado por configuração.');
            return false;
        }

        $from = trim((string) (getenv('MAIL_FROM') ?: ''));
        if (!validateEmail($from) || !validateEmail($to)) {
            reportException(new RuntimeException('Configuração de email inválida.'), 'password-reset-mail');
            return false;
        }

        $message = "Recebemos uma solicitação para redefinir sua senha.\n\n"
            . "Use o link abaixo em até uma hora:\n{$resetUrl}\n\n"
            . "Se você não fez essa solicitação, ignore esta mensagem.";
        $headers = [
            'From: ' . $from,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: Criança Feliz'
        ];

        $sent = @mail($to, $subject, $message, implode("\r\n", $headers));
        if (!$sent) {
            reportException(new RuntimeException('O transporte de email recusou a mensagem.'), 'password-reset-mail');
        }

        return $sent;
    }

    private function configInt($name, $default, $minimum, $maximum) {
        $value = getenv($name);
        $value = $value === false ? $default : (int) $value;
        return max($minimum, min($maximum, $value));
    }

    private function safeLoginError(Throwable $exception) {
        $safeMessages = [
            'Token CSRF inválido',
            'Email é obrigatório',
            'Email inválido',
            'Senha é obrigatória',
            'Email ou senha incorretos',
            'Muitas tentativas de acesso. Aguarde alguns minutos e tente novamente.'
        ];

        if (in_array($exception->getMessage(), $safeMessages, true)) {
            return $exception->getMessage();
        }

        $errorId = reportException($exception, 'login');
        return 'Não foi possível entrar agora. Tente novamente. Código: ' . $errorId;
    }
}
