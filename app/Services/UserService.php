<?php

/**
 * Service para gerenciamento de usuários
 */
class UserService {
    private $userModel;
    private $logModel;
    
    public function __construct() {
        $this->userModel = new User();
        require_once APP_PATH . '/Models/Log.php';
        $this->logModel = new Log();
    }
    
    /**
     * Obtém todos os usuários
     */
    public function getAllUsers() {
        $users = $this->userModel->findAllSafe();
        
        // Mapear campos para formato esperado
        foreach ($users as &$user) {
            $user['id'] = $user['id'] ?? $user['idusuario'] ?? null;
            $user['name'] = $user['name'] ?? $user['nome'] ?? '';
            $user['email'] = $user['email'] ?? '';
            $user['role'] = $user['role'] ?? $user['nivel'] ?? 'funcionario';
            $user['status'] = $user['status'] ?? 'Ativo';
        }
        
        return $users;
    }
    
    /**
     * Obtém usuário por ID
     */
    public function getUser($id) {
        $user = $this->userModel->findById($id);
        if ($user) {
            // Mapear campos MySQL para formato esperado
            $user['id'] = $user['id'] ?? $user['idusuario'];
            $user['name'] = $user['name'] ?? $user['nome'];
            $user['role'] = $user['role'] ?? $user['nivel'];
            unset($user['Senha']); // Remover senha
        }
        return $user;
    }
    
    /**
     * Cria novo usuário
     */
    public function createUser($data) {
        // Verificar se email já existe
        if ($this->userModel->findByEmail($data['email'])) {
            throw new Exception('Email já está em uso');
        }
        
        // Preparar dados (sem created_at/updated_at pois não existem na tabela)
        $userData = [
            'name' => sanitizeInput($data['name']),
            'email' => sanitizeInput($data['email']),
            'password' => $data['password'], // Será hasheado no model
            'role' => $data['role'],
            'status' => 'Ativo' // Usar status em português conforme banco
        ];
        
        $result = $this->userModel->createUser($userData);
        if ($result) {
            $newId = $result['id'] ?? null;
            $logData = $userData;
            unset($logData['password']);
            $this->logModel->logAction('INSERT', 'usuario', "Usuário criado: {$userData['name']}", null, json_encode($logData, JSON_UNESCAPED_UNICODE), $newId);
        }
        return $result;
    }
    
    /**
     * Atualiza usuário
     */
    public function updateUser($id, $data) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        // Verificar se email já existe (exceto para o próprio usuário)
        // Mapear ID para comparação (pode ser idusuario ou id)
        $userId = $user['idusuario'] ?? $user['id'] ?? null;
        $dataUserId = is_array($id) ? ($id['idusuario'] ?? $id['id']) : $id;
        
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser) {
            $existingUserId = $existingUser['idusuario'] ?? $existingUser['id'] ?? null;
            if ($existingUserId && $existingUserId != $dataUserId) {
                throw new Exception('Email já está em uso');
            }
        }
        
        // Preparar dados (sem updated_at pois não existe na tabela)
        $userData = [
            'name' => sanitizeInput($data['name']),
            'email' => sanitizeInput($data['email']),
            'role' => $data['role']
        ];
        
        // Adicionar senha se fornecida
        if (!empty($data['password'])) {
            $userData['password'] = $data['password']; // Será hasheado no model
        }
        
        // Adicionar status se fornecido
        if (isset($data['status'])) {
            $userData['status'] = $data['status'];
        }
        
        $result = $this->userModel->updateUser($id, $userData);
        if ($result) {
            $logData = $userData;
            unset($logData['password']);
            
            $antigo = [
                'name' => $user['nome'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['nivel'] ?? '',
                'status' => $user['status'] ?? ''
            ];
            
            $this->logModel->logAction('UPDATE', 'usuario', "Usuário atualizado: {$userData['name']}", json_encode($antigo, JSON_UNESCAPED_UNICODE), json_encode($logData, JSON_UNESCAPED_UNICODE), $dataUserId);
        }
        return $result;
    }
    
    /**
     * Exclui usuário
     */
    public function deleteUser($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        $result = $this->userModel->delete($id);
        if ($result) {
            $antigo = [
                'name' => $user['nome'] ?? '',
                'email' => $user['email'] ?? ''
            ];
            $this->logModel->logAction('DELETE', 'usuario', "Usuário excluído: {$antigo['name']}", json_encode($antigo, JSON_UNESCAPED_UNICODE), null, $id);
        }
        return $result;
    }
    
    /**
     * Alterna status do usuário
     */
    public function toggleUserStatus($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            throw new Exception('Usuário não encontrado');
        }
        
        // Status no banco está em português: 'Ativo' ou 'Inativo'
        $currentStatus = $user['status'] ?? 'Ativo';
        $currentStatusLower = strtolower($currentStatus);
        
        $newStatus = ($currentStatusLower === 'ativo' || $currentStatusLower === 'active') 
            ? 'Inativo' 
            : 'Ativo';
        
        $result = $this->userModel->updateUser($id, [
            'status' => $newStatus
        ]);
        
        if ($result) {
            $this->logModel->logAction('UPDATE', 'usuario', "Status do usuário '{$user['nome']}' alterado para $newStatus", json_encode(['status' => $currentStatus], JSON_UNESCAPED_UNICODE), json_encode(['status' => $newStatus], JSON_UNESCAPED_UNICODE), $id);
        }
        
        return ['status' => $newStatus];
    }
    
    /**
     * Obtém estatísticas de usuários
     */
    public function getUserStats() {
        $users = $this->userModel->getAll();
        
        $stats = [
            'total' => count($users),
            'active' => 0,
            'inactive' => 0,
            'by_role' => [
                'admin' => 0,
                'psicologo' => 0,
                'funcionario' => 0
            ]
        ];
        
        foreach ($users as $user) {
            $status = strtolower($user['status'] ?? 'ativo');
            if ($status === 'ativo' || $status === 'active') {
                $stats['active']++;
            } else {
                $stats['inactive']++;
            }
            
            $role = $user['role'] ?? $user['nivel'] ?? 'funcionario';
            if (isset($stats['by_role'][$role])) {
                $stats['by_role'][$role]++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Obtém nome do papel/role em português
     */
    public function getRoleName($role) {
        $roles = [
            'admin' => 'Administrador',
            'psicologo' => 'Psicólogo',
            'funcionario' => 'Funcionário'
        ];
        
        return $roles[$role] ?? 'Desconhecido';
    }
    
    /**
     * Obtém descrição das permissões por role
     */
    public function getRolePermissions($role) {
        $permissions = [
            'admin' => 'Acesso total ao sistema, gerenciamento de usuários e configurações',
            'psicologo' => 'Visualização de fichas, área exclusiva de anotações psicológicas',
            'funcionario' => 'Apenas visualização de informações, sem edição'
        ];
        
        return $permissions[$role] ?? 'Sem permissões definidas';
    }
}
