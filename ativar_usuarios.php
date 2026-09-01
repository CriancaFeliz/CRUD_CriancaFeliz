<?php
/**
 * Script DIRETO para ativar usuários
 * Acesse: http://localhost/CriancaFeliz/ativar_usuarios.php
 */

$host = 'localhost';
$dbname = 'criancafeliz';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Ativando TODOS os Usuários</h2>";
    
    // ATIVAR TODOS OS USUÁRIOS
    $stmt = $pdo->exec("UPDATE Usuario SET status = 'Ativo'");
    
    echo "<p style='color:green; font-size:18px;'>✅ <strong>$stmt usuário(s) ativado(s)!</strong></p>";
    
    echo "<hr>";
    echo "<h3>📋 Status Atual dos Usuários:</h3>";
    
    $stmt = $pdo->query("SELECT idusuario, nome, email, nivel, status FROM Usuario");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width:100%;'>";
    echo "<tr style='background:#354047; color:white;'>";
    echo "<th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Status</th>";
    echo "</tr>";
    
    foreach ($usuarios as $user) {
        $statusColor = ($user['status'] === 'Ativo') ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$user['idusuario']}</td>";
        echo "<td>{$user['nome']}</td>";
        echo "<td><strong>{$user['email']}</strong></td>";
        echo "<td>{$user['nivel']}</td>";
        echo "<td style='color:$statusColor; font-weight:bold;'>{$user['status']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>✅ PRONTO!</h3>";
    echo "<p>Todos os usuários estão <strong style='color:green;'>ATIVOS</strong> agora!</p>";
    
    echo "<p><a href='index.php' style='background:#f0a36b; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-size:18px;'>🔐 Fazer Login</a></p>";
    
    echo "<hr>";
    echo "<h3>🔑 Credenciais:</h3>";
    echo "<ul style='font-size:16px;'>";
    echo "<li>📧 <strong>admin@criancafeliz.org</strong> | 🔑 <strong>admin123</strong></li>";
    echo "<li>📧 <strong>psicologa@criancafeliz.org</strong> | 🔑 <strong>admin123</strong></li>";
    echo "<li>📧 <strong>funcionario@criancafeliz.org</strong> | 🔑 <strong>admin123</strong></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
