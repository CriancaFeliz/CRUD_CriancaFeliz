<?php
// Script para corrigir usuÃ¡rios com senhas corretas

$defaultPassword = getenv('INITIAL_ADMIN_PASSWORD') ?: 'AlterarEstaSenha!2026';

$users = [
    [
        "id" => "admin001",
        "name" => "Administrador",
        "email" => "admin@criancafeliz.org",
        "password" => password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        "role" => "admin",
        "status" => "active",
        "created_at" => "2025-10-01 18:03:00",
        "updated_at" => date('Y-m-d H:i:s')
    ],
    [
        "id" => "psi001",
        "name" => "Dr. Maria Silva",
        "email" => "psicologa@criancafeliz.org",
        "password" => password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        "role" => "psicologo",
        "status" => "active",
        "created_at" => "2025-10-01 18:03:00",
        "updated_at" => date('Y-m-d H:i:s')
    ],
    [
        "id" => "func001",
        "name" => "JoÃ£o Santos",
        "email" => "funcionario@criancafeliz.org",
        "password" => password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        "role" => "funcionario",
        "status" => "active",
        "created_at" => "2025-10-01 18:03:00",
        "updated_at" => date('Y-m-d H:i:s')
    ]
];

// Salvar no arquivo
$usersFile = 'data/users.json';
$result = file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result !== false) {
    echo "âœ… UsuÃ¡rios corrigidos com sucesso!<br><br>";
    
    echo "<strong>Credenciais:</strong><br>";
    foreach ($users as $user) {
        echo "ðŸ“§ {$user['email']} | ðŸ”‘ {$defaultPassword} | ðŸ‘¤ {$user['role']}<br>";
        
        // Testar se a senha funciona
        $testResult = password_verify($defaultPassword, $user['password']);
        echo "ðŸ” Teste de senha: " . ($testResult ? 'âœ… OK' : 'âŒ ERRO') . "<br><br>";
    }
} else {
    echo "âŒ Erro ao salvar usuÃ¡rios!";
}
?>


