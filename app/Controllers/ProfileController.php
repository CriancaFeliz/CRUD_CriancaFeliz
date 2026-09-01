<?php

/**
 * Controller para gerenciamento de perfil do usuário
 */
class ProfileController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Exibe a tela de perfil do usuário
     */
    public function index() {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                throw new Exception('Usuário não identificado');
            }
            
            // Carregar dados do usuário do MySQL
            $userModel = App::getUserModel();
            $userData = $userModel->findById($userId);
            
            if (!$userData) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Mapear campos
            $userData['id'] = $userData['id'] ?? $userData['idusuario'];
            $userData['name'] = $userData['name'] ?? $userData['nome'];
            $userData['role'] = $userData['role'] ?? $userData['nivel'];
            
            $data = [
                'title' => 'Meu Perfil - Associação Criança Feliz',
                'userName' => $_SESSION['user_name'] ?? 'Usuário',
                'userEmail' => $_SESSION['user_email'] ?? '',
                'userRole' => $_SESSION['user_role'] ?? 'user',
                'userData' => $userData,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'profile/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Atualiza a foto do perfil
     */
    public function updatePhoto() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                throw new Exception('Usuário não identificado');
            }
            
            // Verificar se foi enviado um arquivo
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Nenhuma foto foi enviada');
            }
            
            $file = $_FILES['photo'];
            
            // Validar tipo de arquivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP');
            }
            
            // Validar tamanho (máx 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new Exception('Arquivo muito grande. Tamanho máximo: 2MB');
            }
            
            // Criar diretório de uploads se não existir
            $uploadDir = ROOT_PATH . '/uploads/profiles';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Gerar nome único para o arquivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . '/' . $fileName;
            
            // Mover arquivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Erro ao salvar arquivo');
            }
            
            // Atualizar apenas a sessão (não precisa salvar no JSON)
            $_SESSION['user_photo'] = '/uploads/profiles/' . $fileName;
            
            $this->json(['success' => 'Foto atualizada com sucesso', 'photo' => '/uploads/profiles/' . $fileName]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Atualiza a senha do usuário
     */
    public function updatePassword() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('profile.php');
        }
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                throw new Exception('Usuário não identificado');
            }
            
            $currentPassword = $this->getParam('current_password', '');
            $newPassword = $this->getParam('new_password', '');
            $confirmPassword = $this->getParam('confirm_password', '');
            
            // Validações
            if (empty($currentPassword)) {
                throw new Exception('Senha atual é obrigatória');
            }
            
            if (empty($newPassword)) {
                throw new Exception('Nova senha é obrigatória');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('A nova senha deve ter no mínimo 6 caracteres');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('As senhas não conferem');
            }
            
            // Carregar usuário do MySQL
            $userModel = App::getUserModel();
            $user = $userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Verificar senha atual
            if (!password_verify($currentPassword, $user['Senha'])) {
                throw new Exception('Senha atual incorreta');
            }
            
            // Atualizar senha no banco
            $userModel->update($userId, [
                'Senha' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);
            
            $this->redirectWithSuccess('profile.php', 'Senha alterada com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError('profile.php', $e->getMessage());
        }
    }
}
