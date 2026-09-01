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
        
        try {
            $this->validateCSRF();
            
            $email = $this->getParam('email', '');
            $password = $this->getParam('password', '');
            
            $user = $this->authService->login($email, $password);
            
            // Login bem-sucedido
            redirect('dashboard.php');
            
        } catch (Exception $e) {
            
            // Armazenar erro e dados do formulário na sessão
            $_SESSION['login_errors'] = [$e->getMessage()];
            $_SESSION['form_data'] = ['email' => $email ?? ''];
            
            redirect('index.php');
        }
    }
    
    /**
     * Processa logout
     */
    public function logout() {
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
        
        $this->render('auth/forgot', $data);
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
            
            // Simular envio de email (implementar SMTP conforme memória)
            $this->sendPasswordResetEmail($email);
            
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
        
        $this->render('auth/reset', $data);
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
                throw new Exception('Senha deve ter pelo menos 6 caracteres');
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
                throw new Exception('Nova senha deve ter pelo menos 6 caracteres');
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
        // Gerar token
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1 hora
        
        // Salvar token (implementar conforme memória)
        $this->saveResetToken($email, $token, $expiry);
        
        // Montar URL de reset
        $resetUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . $token;
        
        // Enviar email (implementar SMTP conforme memória)
        $this->sendEmail($email, 'Recuperação de Senha - Criança Feliz', $resetUrl);
    }
    
    /**
     * Salva token de reset
     */
    private function saveResetToken($email, $token, $expiry) {
        $tokensFile = DATA_PATH . '/reset_tokens.json';
        
        if (!file_exists($tokensFile)) {
            file_put_contents($tokensFile, json_encode([]));
        }
        
        $tokens = json_decode(file_get_contents($tokensFile), true) ?: [];
        
        $tokens[$token] = [
            'email' => $email,
            'expiry' => $expiry,
            'used' => false
        ];
        
        file_put_contents($tokensFile, json_encode($tokens, JSON_PRETTY_PRINT));
    }
    
    /**
     * Valida token de reset
     */
    private function isValidResetToken($token) {
        $tokensFile = DATA_PATH . '/reset_tokens.json';
        
        if (!file_exists($tokensFile)) {
            return false;
        }
        
        $tokens = json_decode(file_get_contents($tokensFile), true) ?: [];
        
        if (!isset($tokens[$token])) {
            return false;
        }
        
        $tokenData = $tokens[$token];
        
        if ($tokenData['used'] || $tokenData['expiry'] < time()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Atualiza senha por token
     */
    private function updatePasswordByToken($token, $password) {
        $tokensFile = DATA_PATH . '/reset_tokens.json';
        $tokens = json_decode(file_get_contents($tokensFile), true) ?: [];
        
        if (!isset($tokens[$token])) {
            throw new Exception('Token inválido');
        }
        
        $tokenData = $tokens[$token];
        $email = $tokenData['email'];
        
        // Atualizar senha do usuário
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if ($user) {
            $userModel->updateUser($user['id'], ['password' => $password]);
        }
        
        // Marcar token como usado
        $tokens[$token]['used'] = true;
        file_put_contents($tokensFile, json_encode($tokens, JSON_PRETTY_PRINT));
    }
    
    /**
     * Envia email (implementar SMTP conforme memória)
     */
    private function sendEmail($to, $subject, $resetUrl) {
        // Implementar conforme sistema SMTP da memória
        // Por enquanto, apenas log
        error_log("Email enviado para $to: $subject - URL: $resetUrl");
    }
}
