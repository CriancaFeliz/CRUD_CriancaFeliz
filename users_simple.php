<?php
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Verificar se é admin
if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash_error'] = 'Acesso negado. Apenas administradores podem gerenciar usuários.';
    header('Location: dashboard.php');
    exit();
}

// Carregar dados dos usuários
$usersFile = 'data/users.json';
$users = [];

if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
}

// Processar ações
$action = $_GET['action'] ?? 'index';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Criar novo usuário
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['flash_error'] = 'Todos os campos são obrigatórios';
    } else {
        // Verificar se email já existe
        $emailExists = false;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $emailExists = true;
                break;
            }
        }
        
        if ($emailExists) {
            $_SESSION['flash_error'] = 'Email já está em uso';
        } else {
            // Criar usuário
            $newUser = [
                'id' => uniqid('user_'),
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $users[] = $newUser;
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            
            $_SESSION['flash_success'] = 'Usuário criado com sucesso!';
            header('Location: users_simple.php');
            exit();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Sistema Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #121a1f; margin: 0; padding: 20px; font-family: 'Poppins', sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; background: #edf1f3; border-radius: 16px; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { background: #ff7a00; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; text-decoration: none; display: inline-block; }
        .btn.secondary { background: #6c757d; }
        .btn.success { background: #28a745; }
        .table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .flash { padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .flash.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 2px solid #f0a36b; border-radius: 8px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: #fff; margin: 50px auto; padding: 20px; border-radius: 12px; max-width: 500px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gerenciar Usuários</h1>
            <div>
                <a href="dashboard.php" class="btn secondary">← Voltar</a>
                <button onclick="openModal()" class="btn success">👤 Novo Usuário</button>
            </div>
        </div>
        
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="flash success"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="flash error"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível de Acesso</th>
                    <th>Status</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php
                            $roleNames = [
                                'admin' => 'Administrador',
                                'psicologo' => 'Psicólogo',
                                'funcionario' => 'Funcionário'
                            ];
                            echo $roleNames[$user['role']] ?? 'Desconhecido';
                            ?>
                        </td>
                        <td><?php echo $user['status'] === 'active' ? 'Ativo' : 'Inativo'; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal para criar usuário -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <h3>Novo Usuário</h3>
            <form method="post">
                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Senha *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Nível de Acesso *</label>
                    <select name="role" required>
                        <option value="">Selecione</option>
                        <option value="admin">Administrador</option>
                        <option value="psicologo">Psicólogo - Assistente Social</option>
                        <option value="funcionario">Funcionário</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" class="btn secondary">Cancelar</button>
                    <button type="submit" class="btn">Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('userModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
