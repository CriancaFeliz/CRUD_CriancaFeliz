<?php
/**
 * Script DIRETO para ativar usuÃ¡rios
 * Acesse: http://localhost/CriancaFeliz/tools/maintenance/ativar_usuarios.php
 */

$host = 'localhost';
$dbname = 'criancafeliz';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>ðŸ”§ Ativando TODOS os UsuÃ¡rios</h2>";
    
    // ATIVAR TODOS OS USUÃRIOS
    $stmt = $pdo->exec("UPDATE Usuario SET status = 'Ativo'");
    
    echo "<p style='color:green; font-size:18px;'>âœ… <strong>$stmt usuÃ¡rio(s) ativado(s)!</strong></p>";
    
    echo "<hr>";
    echo "<h3>ðŸ“‹ Status Atual dos UsuÃ¡rios:</h3>";
    
    $stmt = $pdo->query("SELECT idusuario, nome, email, nivel, status FROM Usuario");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width:100%;'>";
    echo "<tr style='background:#354047; color:white;'>";
    echo "<th>ID</th><th>Nome</th><th>Email</th><th>NÃ­vel</th><th>Status</th>";
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
    echo "<h3>âœ… PRONTO!</h3>";
    echo "<p>Todos os usuÃ¡rios estÃ£o <strong style='color:green;'>ATIVOS</strong> agora!</p>";
    
    echo "<p><a href='index.php' style='background:#f0a36b; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-size:18px;'>ðŸ” Fazer Login</a></p>";
    
    echo "<hr>";
    echo "<h3>ðŸ”‘ Credenciais:</h3>";
    echo "<ul style='font-size:16px;'>";
    echo "<li>ðŸ“§ <strong>admin@criancafeliz.org</strong> | ðŸ”‘ <strong>admin123</strong></li>";
    echo "<li>ðŸ“§ <strong>psicologa@criancafeliz.org</strong> | ðŸ”‘ <strong>admin123</strong></li>";
    echo "<li>ðŸ“§ <strong>funcionario@criancafeliz.org</strong> | ðŸ”‘ <strong>admin123</strong></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2>âŒ Erro</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>


