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
            $storedPhoto = $userData['foto_perfil'] ?? ($_SESSION['user_photo'] ?? '');
            $userData['photo'] = $storedPhoto !== ''
                ? 'profile.php?action=photo&id=' . (int)$userId
                : '';
            
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
            
            $uploadDir = BASE_PATH . '/var/private/profiles';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0750, true) && !is_dir($uploadDir)) {
                    throw new Exception('Não foi possível preparar a área segura de fotos');
                }
            }
            
            // Gerar nome único para o arquivo
            $extension = $allowedTypes[$mimeType];
            $fileName = $userId . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $filePath = $uploadDir . '/' . $fileName;
            
            // Mover arquivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Erro ao salvar arquivo');
            }
            
            $privatePath = 'var/private/profiles/' . $fileName;

            $userModel = new User();
            $currentUser = $userModel->findById($userId);
            $previousPath = (string)($currentUser['foto_perfil'] ?? '');

            try {
                $userModel->update($userId, [
                    'foto_perfil' => $privatePath
                ]);
            } catch (Throwable $exception) {
                @unlink($filePath);
                throw $exception;
            }

            $_SESSION['user_photo'] = $privatePath;
            $this->removeManagedPhoto($previousPath, $privatePath);

            $photoUrl = 'profile.php?action=photo&id=' . (int)$userId;
            
            $this->json(['success' => true, 'message' => 'Foto atualizada com sucesso', 'photo' => $photoUrl]);
            
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function viewPhoto() {
        $this->requireAuth();

        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $requestedUserId = (int)($_GET['id'] ?? $currentUserId);
        if ($requestedUserId <= 0
            || ($requestedUserId !== $currentUserId && !$this->authService->hasPermission('manage_users'))) {
            http_response_code(403);
            exit;
        }

        try {
            $user = (new User())->findById($requestedUserId);
            $filePath = $this->resolveManagedPhoto((string)($user['foto_perfil'] ?? ''));
            if (!$filePath) {
                http_response_code(404);
                exit;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $filePath) : 'application/octet-stream';
            if ($finfo) {
                finfo_close($finfo);
            }

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: inline; filename="profile-image"');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, no-store, max-age=0');
            header('Pragma: no-cache');
            readfile($filePath);
            exit;
        } catch (Throwable $exception) {
            reportException($exception, 'ProfileController::viewPhoto');
            http_response_code(404);
            exit;
        }
    }

    private function resolveManagedPhoto($relativePath) {
        $relativePath = ltrim((string)$relativePath, '/\\');
        if ($relativePath === '') {
            return null;
        }

        $filePath = realpath(BASE_PATH . '/' . $relativePath);
        if (!$filePath || !is_file($filePath)) {
            return null;
        }

        $allowedDirectories = [
            realpath(BASE_PATH . '/var/private/profiles'),
            realpath(BASE_PATH . '/uploads/profiles')
        ];

        foreach (array_filter($allowedDirectories) as $allowedDirectory) {
            if (strpos($filePath, $allowedDirectory . DIRECTORY_SEPARATOR) === 0) {
                return $filePath;
            }
        }

        return null;
    }

    private function removeManagedPhoto($relativePath, $exceptPath = '') {
        if ($relativePath === '' || $relativePath === $exceptPath) {
            return;
        }

        $filePath = $this->resolveManagedPhoto($relativePath);
        if ($filePath) {
            @unlink($filePath);
        }
    }
    
    /**
     * Atualiza a senha do usuário
     */
    public function updatePassword() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            redirect('profile.php');
        }
        
        try {
            $this->validateCSRF();
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
