<?php
// Script para corrigir usuários com senhas corretas

$users = [
    [
        "id" => "admin001",
        "name" => "Administrador",
        "email" => "admin@criancafeliz.org",
        "password" => password_hash('admin123', PASSWORD_DEFAULT),
        "role" => "admin",
        "status" => "active",
        "created_at" => "2025-10-01 18:03:00",
        "updated_at" => date('Y-m-d H:i:s')
    ],
    [
        "id" => "psi001",
        "name" => "Dr. Maria Silva",
        "email" => "psicologa@criancafeliz.org",
        "password" => password_hash('admin123', PASSWORD_DEFAULT),
        "role" => "psicologo",
        "status" => "active",
        "created_at" => "2025-10-01 18:03:00",
        "updated_at" => date('Y-m-d H:i:s')
    ],
    [
        "id" => "func001",
        "name" => "João Santos",
        "email" => "funcionario@criancafeliz.org",
        "password" => password_hash('admin123', PASSWORD_DEFAULT),
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
    echo "✅ Usuários corrigidos com sucesso!<br><br>";
    
    echo "<strong>Credenciais:</strong><br>";
    foreach ($users as $user) {
        echo "📧 {$user['email']} | 🔑 admin123 | 👤 {$user['role']}<br>";
        
        // Testar se a senha funciona
        $testResult = password_verify('admin123', $user['password']);
        echo "🔍 Teste de senha: " . ($testResult ? '✅ OK' : '❌ ERRO') . "<br><br>";
    }
} else {
    echo "❌ Erro ao salvar usuários!";
}
?>
