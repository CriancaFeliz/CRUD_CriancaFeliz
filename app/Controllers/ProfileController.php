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
            $userModel = new User();
            $userData = $userModel->findById($userId);
            
            if (!$userData) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Mapear campos
            $userData['id'] = $userData['id'] ?? $userData['idusuario'];
            $userData['name'] = $userData['name'] ?? $userData['nome'];
            $userData['role'] = $userData['role'] ?? $userData['nivel'];
            $userData['photo'] = $userData['foto_perfil'] ?? ($_SESSION['user_photo'] ?? '');
            
            $data = [
                'title' => 'Meu Perfil - Associação Criança Feliz',
                'userName' => $_SESSION['user_name'] ?? 'Usuário',
                'userEmail' => $_SESSION['user_email'] ?? '',
                'userRole' => $_SESSION['user_role'] ?? 'user',
                'userData' => $userData,
                'csrf_token' => $this->generateCSRF(),
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
            $this->validateCSRF();

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
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
            if ($finfo) {
                finfo_close($finfo);
            }

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];

            if (!$mimeType || !isset($allowedTypes[$mimeType])) {
                throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP');
            }
            
            // Validar tamanho (máx 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new Exception('Arquivo muito grande. Tamanho máximo: 2MB');
            }
            
            // Criar diretório de uploads se não existir
            $uploadDir = BASE_PATH . '/uploads/profiles';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $htaccess = $uploadDir . '/.htaccess';
            if (!file_exists($htaccess)) {
                @file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar)$\">\nRequire all denied\n</FilesMatch>\n");
            }
            
            // Gerar nome único para o arquivo
            $extension = $allowedTypes[$mimeType];
            $fileName = $userId . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $filePath = $uploadDir . '/' . $fileName;
            
            // Mover arquivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Erro ao salvar arquivo');
            }
            
            $publicPath = 'uploads/profiles/' . $fileName;

            $userModel = new User();
            $userModel->update($userId, [
                'foto_perfil' => $publicPath
            ]);

            $_SESSION['user_photo'] = $publicPath;
            
            $this->json(['success' => true, 'message' => 'Foto atualizada com sucesso', 'photo' => $publicPath]);
            
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
            
            if (!validatePassword($newPassword)) {
                throw new Exception(passwordValidationMessage());
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('As senhas não conferem');
            }
            
            // Carregar usuário do MySQL
            $userModel = new User();
            $user = $userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Verificar senha atual
            if (!PasswordHelper::verify($currentPassword, $user['Senha'])) {
                throw new Exception('Senha atual incorreta');
            }
            
            // Atualizar senha no banco
            $userModel->update($userId, [
                'Senha' => PasswordHelper::hash($newPassword)
            ]);
            
            $this->redirectWithSuccess('profile.php', 'Senha alterada com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError('profile.php', $e->getMessage());
        }
    }
}
