<?php

/**
 * Service para autenticação e autorização
 */
class AuthService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Realiza login do usuário
     */
    public function login($email, $password) {
        // Validações
        if (empty($email)) {
            throw new Exception('Email é obrigatório');
        }
        
        if (!validateEmail($email)) {
            throw new Exception('Email inválido');
        }
        
        if (empty($password)) {
            throw new Exception('Senha é obrigatória');
        }
        
        if (!validatePassword($password)) {
            throw new Exception('A senha deve ter pelo menos 6 caracteres');
        }
        
        // Verificar se usuário existe
        $userExists = $this->userModel->findByEmail($email);
        
        if (!$userExists) {
            throw new Exception('Email ou senha incorretos');
        }
        
        // Verificar se está ativo
        $status = strtolower($userExists['status'] ?? 'inativo');
        if ($status !== 'ativo' && $status !== 'active') {
            throw new Exception('Usuário inativo');
        }
        
        // Tentar autenticar
        $user = $this->userModel->authenticate($email, $password);
        
        if (!$user) {
            throw new Exception('Email ou senha incorretos');
        }
        
        // Verificar status (aceita 'Ativo' ou 'active')
        $status = strtolower($user['status'] ?? '');
        if ($status !== 'ativo' && $status !== 'active') {
            throw new Exception('Usuário inativo');
        }
        
        // Criar sessão
        $this->createSession($user);
        
        return $user;
    }
    
    /**
     * Cria sessão do usuário
     */
    private function createSession($user) {
    $_SESSION['user_id'] = $user['idusuario'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['nome'];

    // Usa 'nivel' do banco como role
    $_SESSION['user_role'] = $user['nivel'] ?? 'funcionario';

    $_SESSION['login_time'] = time();
    session_regenerate_id(true);
}


    
    /**
     * Realiza logout do usuário
     */
    public function logout() {
        // Limpar todas as variáveis de sessão
        $_SESSION = [];
        
        // Destruir cookie de sessão se existir
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir sessão
        session_destroy();
    }
    
    /**
     * Verifica se usuário está logado
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Obtém usuário atual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Verifica se usuário tem permissão
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['user_role'];
        
        // Definir permissões por role
        $permissions = [
            'admin' => [
                'manage_users',
                'view_all_records',
                'create_records',
                'edit_records',
                'delete_records',
                'manage_system',
                'view_reports'
            ],
            'psicologo' => [
                'view_all_records',
                'psychological_notes',
                'view_psychological_area',
                'edit_psychological_notes',
                'add_psychological_note',
                'delete_psychological_note',
                // Removendo permissões de frequência e desligamento
                // 'view_attendance',
                // 'view_attendance_alerts',
                // 'view_attendance_reports',
                // 'manage_attendance_batch'
            ],
            'funcionario' => [
                'view_all_records'
            ]
        ];
        
        if (!isset($permissions[$role])) {
            return false;
        }
        
        // Admin tem todas as permissões exceto área psicológica
        if ($role === 'admin' && $permission === 'psychological_notes') {
            return false;
        }
        
        if ($role === 'admin' && $permission === 'view_psychological_area') {
            return false;
        }
        
        return in_array($permission, $permissions[$role]);
    }
    
    /**
     * Middleware para verificar autenticação
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            // Verificar se é AJAX antes de redirecionar
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                // Para AJAX, lançar exceção ao invés de redirecionar
                throw new Exception('Não autenticado. Faça login novamente.');
            } else {
                redirect('index.php');
            }
        }
    }
    
    /**
     * Middleware para verificar permissão
     */
    public function requirePermission($permission) {
        $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            throw new Exception('Acesso negado');
        }
    }
    
    /**
     * Registra novo usuário
     */
    public function register($data) {
        return $this->userModel->createUser($data);
    }
    
    /**
     * Atualiza perfil do usuário
     */
    public function updateProfile($id, $data) {
        // Verificar se é o próprio usuário ou admin
        if ($_SESSION['user_id'] !== $id && !$this->hasPermission('manage_users')) {
            throw new Exception('Acesso negado');
        }
        
        return $this->userModel->updateUser($id, $data);
    }
    
    /**
     * Altera senha do usuário
     */
    public function changePassword($currentPassword, $newPassword) {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        // Verificar senha atual
        if (!password_verify($currentPassword, $user['password'])) {
            throw new Exception('Senha atual incorreta');
        }
        
        // Validar nova senha
        if (!validatePassword($newPassword)) {
            throw new Exception('Nova senha deve ter pelo menos 6 caracteres');
        }
        
        // Atualizar senha
        return $this->userModel->updateUser($userId, [
            'password' => $newPassword
        ]);
    }
    
    /**
     * Verifica timeout de sessão
     */
    public function checkSessionTimeout($timeout = 3600) { // 1 hora
        if ($this->isLoggedIn()) {
            $loginTime = $_SESSION['login_time'] ?? 0;
            if (time() - $loginTime > $timeout) {
                $this->logout();
                return false;
            }
            
            // Atualizar tempo de login
            $_SESSION['login_time'] = time();
        }
        
        return true;
    }
}