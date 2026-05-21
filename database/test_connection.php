<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão - Banco de Dados</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            margin-top: 20px;
        }
        
        .info h3 {
            margin-bottom: 10px;
        }
        
        .info ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info li {
            padding: 5px 0;
        }
        
        .info li::before {
            content: "✓ ";
            color: #0c5460;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Teste de Conexão com Banco de Dados</h1>
        
        <?php
        // Incluir configuração
        require_once __DIR__ . '/../app/Config/Database.php';
        
        try {
            // Tentar conectar
            $pdo = Database::getConnection();
            
            echo '<div class="status success">';
            echo '<h2>✅ Conexão Estabelecida com Sucesso!</h2>';
            echo '<p>O banco de dados está funcionando corretamente.</p>';
            echo '</div>';
            
            // Verificar tabelas
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo '<div class="info">';
                echo '<h3>📋 Tabelas Encontradas (' . count($tables) . '):</h3>';
                echo '<ul>';
                foreach ($tables as $table) {
                    echo "<li>$table</li>";
                }
                echo '</ul>';
                echo '</div>';
                
                // Verificar usuário admin
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM Usuario");
                $result = $stmt->fetch();
                
                echo '<div class="info">';
                echo '<h3>👥 Usuários Cadastrados:</h3>';
                echo '<p>' . $result['total'] . ' usuário(s) encontrado(s)</p>';
                echo '</div>';
                
            } else {
                echo '<div class="status error">';
                echo '<h2>⚠️ Banco Vazio</h2>';
                echo '<p>O banco existe mas não possui tabelas.</p>';
                echo '<p><strong>Solução:</strong> Execute a migração.</p>';
                echo '</div>';
            }
            
            // Informações da conexão
            echo '<div class="info">';
            echo '<h3>ℹ️ Informações da Conexão:</h3>';
            echo '<pre>';
            echo "Host: localhost\n";
            echo "Banco: criancafeliz\n";
            echo "Charset: utf8mb4\n";
            echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
            echo "Versão: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            echo '</pre>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="status error">';
            echo '<h2>❌ Erro na Conexão</h2>';
            echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<h3>🔧 Possíveis Soluções:</h3>';
            echo '<ul>';
            echo '<li>Verifique se o XAMPP está rodando (MySQL)</li>';
            echo '<li>Verifique as credenciais em app/Config/Database.php</li>';
            echo '<li>Execute a migração: <a href="migrate.php">migrate.php</a></li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="migrate.php" class="btn">🚀 Executar Migração</a>
            <a href="../index.php" class="btn" style="background: #6fb64f;">🏠 Ir para o Sistema</a>
        </div>
    </div>
</body>
</html>
