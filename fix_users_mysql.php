<?php
/**
 * Script para corrigir senhas dos usuários no MySQL
 * Acesse: http://localhost/CriancaFeliz/fix_users_mysql.php
 */

// Configuração do banco
$host = 'localhost';
$dbname = 'criancafeliz';
$username = 'root';
$password = '';

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Corrigindo Senhas dos Usuários</h2>";
    
    // Senha padrão: admin123
    $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    echo "<p><strong>Hash gerado:</strong> <code>$senhaHash</code></p>";
    echo "<p><strong>Testando hash:</strong> " . (password_verify('admin123', $senhaHash) ? '✅ OK' : '❌ ERRO') . "</p>";
    
    echo "<hr>";
    
    // Usuários para criar/atualizar
    $usuarios = [
        [
            'nome' => 'Administrador',
            'email' => 'admin@criancafeliz.org',
            'nivel' => 'admin',
            'status' => 'Ativo'
        ],
        [
            'nome' => 'Dr. Maria Silva',
            'email' => 'psicologa@criancafeliz.org',
            'nivel' => 'Psicólogo',
            'status' => 'Ativo'
        ],
        [
            'nome' => 'João Santos',
            'email' => 'funcionario@criancafeliz.org',
            'nivel' => 'Funcionário',
            'status' => 'Ativo'
        ]
    ];
    
    foreach ($usuarios as $usuario) {
        // Verificar se usuário existe
        $stmt = $pdo->prepare("SELECT idusuario, email FROM Usuario WHERE email = ?");
        $stmt->execute([$usuario['email']]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Atualizar senha E STATUS
            $stmt = $pdo->prepare("UPDATE Usuario SET Senha = ?, nome = ?, nivel = ?, status = 'Ativo' WHERE email = ?");
            $stmt->execute([
                $senhaHash,
                $usuario['nome'],
                $usuario['nivel'],
                $usuario['email']
            ]);
            
            echo "✅ <strong>{$usuario['email']}</strong> - Senha e status atualizados!<br>";
        } else {
            // Criar usuário
            $stmt = $pdo->prepare("INSERT INTO Usuario (nome, email, Senha, nivel, status) VALUES (?, ?, ?, ?, 'Ativo')");
            $stmt->execute([
                $usuario['nome'],
                $usuario['email'],
                $senhaHash,
                $usuario['nivel']
            ]);
            
            echo "✅ <strong>{$usuario['email']}</strong> - Usuário criado!<br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>📋 Credenciais de Acesso:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background:#f0a36b; color:white;'><th>Email</th><th>Senha</th><th>Nível</th></tr>";
    
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td><strong>{$usuario['email']}</strong></td>";
        echo "<td><code>admin123</code></td>";
        echo "<td>{$usuario['nivel']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🔍 Verificação no Banco:</h3>";
    
    // Listar todos os usuários
    $stmt = $pdo->query("SELECT idusuario, nome, email, nivel, status FROM Usuario");
    $todosUsuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background:#354047; color:white;'><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Status</th></tr>";
    
    foreach ($todosUsuarios as $user) {
        echo "<tr>";
        echo "<td>{$user['idusuario']}</td>";
        echo "<td>{$user['nome']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['nivel']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>✅ PRONTO!</h3>";
    echo "<p>Agora você pode fazer login com:</p>";
    echo "<ul>";
    echo "<li>📧 <strong>admin@criancafeliz.org</strong> | 🔑 <strong>admin123</strong></li>";
    echo "<li>📧 <strong>psicologa@criancafeliz.org</strong> | 🔑 <strong>admin123</strong></li>";
    echo "<li>📧 <strong>funcionario@criancafeliz.org</strong> | 🔑 <strong>admin123</strong></li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' style='background:#f0a36b; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;'>🔐 Ir para Login</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro ao conectar ao banco de dados</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Verifique:</strong></p>";
    echo "<ul>";
    echo "<li>MySQL está rodando no XAMPP</li>";
    echo "<li>Banco 'criancafeliz' existe</li>";
    echo "<li>Credenciais estão corretas (root/sem senha)</li>";
    echo "</ul>";
}
?>
