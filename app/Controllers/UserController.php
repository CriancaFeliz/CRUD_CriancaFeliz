<?php

/**
 * Controller para gerenciamento de usuários
 */
class UserController extends BaseController {
    private $userService;
    
    public function __construct() {
        parent::__construct();
        $this->userService = new UserService();
    }
    
    /**
     * Lista usuários (apenas admin)
     */
    public function index() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        try {
            $users = $this->userService->getAllUsers();
            
            $data = [
                'title' => 'Gerenciar Usuários',
                'pageTitle' => 'Gerenciamento de Usuários',
                'users' => $users,
                'currentUser' => $this->authService->getCurrentUser(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'users/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe formulário de criação de usuário
     */
    public function create() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        $data = [
            'title' => 'Novo Usuário',
            'pageTitle' => 'Cadastrar Novo Usuário',
            'currentUser' => $this->authService->getCurrentUser(),
            'csrf_token' => $this->generateCSRF(),
            'messages' => $this->getFlashMessages()
        ];
        
        $this->renderWithLayout('main', 'users/create', $data);
    }
    
    /**
     * Processa criação de usuário
     */
    public function store() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            redirect('users.php');
        }
        
        try {
            $this->validateCSRF();
            $data = $this->getPostData();
            
            // Validações
            if (empty($data['name'])) {
                throw new Exception('Nome é obrigatório');
            }
            
            if (empty($data['email'])) {
                throw new Exception('Email é obrigatório');
            }
            
            if (!validateEmail($data['email'])) {
                throw new Exception('Email inválido');
            }
            
            if (empty($data['password'])) {
                throw new Exception('Senha é obrigatória');
            }
            
            if (!validatePassword($data['password'])) {
                throw new Exception('Senha deve ter pelo menos 6 caracteres');
            }
            
            if (empty($data['role'])) {
                throw new Exception('Nível de acesso é obrigatório');
            }
            
            if (!in_array($data['role'], ['admin', 'psicologo', 'funcionario'])) {
                throw new Exception('Nível de acesso inválido');
            }
            
            $userId = $this->userService->createUser($data);
            
            $this->redirectWithSuccess('users.php', 'Usuário criado com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError('users.php?action=create', $e->getMessage());
        }
    }
    
    /**
     * Exibe formulário de edição de usuário
     */
    public function edit($id) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        try {
            $user = $this->userService->getUser($id);
            
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            $data = [
                'title' => 'Editar Usuário',
                'pageTitle' => 'Editar Usuário',
                'user' => $user,
                'currentUser' => $this->authService->getCurrentUser(),
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'users/edit', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Processa atualização de usuário
     */
    public function update($id) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            redirect('users.php');
        }
        
        try {
            $this->validateCSRF();
            $data = $this->getPostData();
            
            // Validações
            if (empty($data['name'])) {
                throw new Exception('Nome é obrigatório');
            }
            
            if (empty($data['email'])) {
                throw new Exception('Email é obrigatório');
            }
            
            if (!validateEmail($data['email'])) {
                throw new Exception('Email inválido');
            }
            
            if (empty($data['role'])) {
                throw new Exception('Nível de acesso é obrigatório');
            }
            
            if (!in_array($data['role'], ['admin', 'psicologo', 'funcionario'])) {
                throw new Exception('Nível de acesso inválido');
            }
            
            // Se senha foi fornecida, validar
            if (!empty($data['password'])) {
                if (!validatePassword($data['password'])) {
                    throw new Exception('Senha deve ter pelo menos 6 caracteres');
                }
            } else {
                // Remover senha vazia para não alterar
                unset($data['password']);
            }
            
            $this->userService->updateUser($id, $data);
            
            $this->redirectWithSuccess('users.php', 'Usuário atualizado com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError("users.php?action=edit&id=$id", $e->getMessage());
        }
    }
    
    /**
     * Exclui usuário
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $currentUser = $this->authService->getCurrentUser();
            
            // Não permitir que admin exclua a si mesmo
            if ($currentUser['id'] === $id) {
                throw new Exception('Não é possível excluir seu próprio usuário');
            }
            
            $this->userService->deleteUser($id);
            
            $this->json(['success' => true, 'message' => 'Usuário excluído com sucesso']);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Altera status do usuário (ativar/desativar)
     */
    public function toggleStatus($id) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $currentUser = $this->authService->getCurrentUser();
            
            // Não permitir que admin desative a si mesmo
            if ($currentUser['id'] === $id) {
                throw new Exception('Não é possível alterar o status do seu próprio usuário');
            }
            
            $result = $this->userService->toggleUserStatus($id);
            
            $this->json([
                'success' => true, 
                'message' => 'Status alterado com sucesso',
                'status' => $result['status']
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
