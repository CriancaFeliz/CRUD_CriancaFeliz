<?php
/**
 * Script de Diagnóstico de Login
 * Acesse: http://localhost/CriancaFeliz/diagnostico_login.php
 */

$host = 'localhost';
$dbname = 'criancafeliz';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Diagnóstico de Login</h2>";
    
    // Buscar usuário admin
    $email = 'admin@criancafeliz.org';
    $senhaDigitada = 'admin123';
    
    $stmt = $pdo->prepare("SELECT idusuario, nome, email, Senha, nivel, status FROM Usuario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<h3>✅ Usuário encontrado no banco</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><td><strong>ID:</strong></td><td>{$user['idusuario']}</td></tr>";
        echo "<tr><td><strong>Nome:</strong></td><td>{$user['nome']}</td></tr>";
        echo "<tr><td><strong>Email:</strong></td><td>{$user['email']}</td></tr>";
        echo "<tr><td><strong>Nível:</strong></td><td>{$user['nivel']}</td></tr>";
        echo "<tr><td><strong>Status:</strong></td><td>{$user['status']}</td></tr>";
        echo "<tr><td><strong>Hash no Banco:</strong></td><td><code style='font-size:10px;'>{$user['Senha']}</code></td></tr>";
        echo "</table>";
        
        echo "<hr>";
        echo "<h3>🔐 Teste de Senha</h3>";
        
        // Testar senha
        $senhaCorreta = password_verify($senhaDigitada, $user['Senha']);
        
        if ($senhaCorreta) {
            echo "<p style='color:green; font-size:20px;'>✅ <strong>SENHA CORRETA!</strong></p>";
            echo "<p>A senha 'admin123' funciona com o hash do banco.</p>";
        } else {
            echo "<p style='color:red; font-size:20px;'>❌ <strong>SENHA INCORRETA!</strong></p>";
            echo "<p>O hash no banco NÃO corresponde à senha 'admin123'.</p>";
            
            echo "<hr>";
            echo "<h3>🔧 Solução</h3>";
            echo "<p>Execute o script de correção:</p>";
            echo "<p><a href='fix_users_mysql.php' style='background:#f0a36b; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;'>🔧 Corrigir Senhas</a></p>";
        }
        
        echo "<hr>";
        echo "<h3>📊 Informações Técnicas</h3>";
        
        // Gerar novo hash
        $novoHash = password_hash($senhaDigitada, PASSWORD_DEFAULT);
        echo "<p><strong>Hash atual no banco:</strong><br><code style='font-size:10px;'>{$user['Senha']}</code></p>";
        echo "<p><strong>Hash correto para 'admin123':</strong><br><code style='font-size:10px;'>$novoHash</code></p>";
        
        // Testar novo hash
        $testeNovoHash = password_verify($senhaDigitada, $novoHash);
        echo "<p><strong>Teste do novo hash:</strong> " . ($testeNovoHash ? '✅ OK' : '❌ ERRO') . "</p>";
        
    } else {
        echo "<h3>❌ Usuário NÃO encontrado no banco</h3>";
        echo "<p>O email <strong>$email</strong> não existe na tabela Usuario.</p>";
        echo "<p><a href='fix_users_mysql.php' style='background:#f0a36b; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;'>➕ Criar Usuários</a></p>";
    }
    
    echo "<hr>";
    echo "<h3>📋 Todos os Usuários no Banco</h3>";
    
    $stmt = $pdo->query("SELECT idusuario, nome, email, nivel, status FROM Usuario");
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($todos) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr style='background:#354047; color:white;'><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Status</th></tr>";
        
        foreach ($todos as $u) {
            echo "<tr>";
            echo "<td>{$u['idusuario']}</td>";
            echo "<td>{$u['nome']}</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td>{$u['nivel']}</td>";
            echo "<td>{$u['status']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário encontrado no banco.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro ao conectar ao banco</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
}
?>
