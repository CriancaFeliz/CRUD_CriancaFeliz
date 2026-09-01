<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do Banco de Dados - Criança Feliz</title>
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
            max-width: 800px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2em;
        }
        
        .step {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .step h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
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
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .progress {
            margin: 20px 0;
        }
        
        .progress-bar {
            background: #e9ecef;
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Instalação do Banco de Dados</h1>
        
        <?php
        // Configurações
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'criancafeliz';
        
        $steps = [];
        $currentStep = 0;
        $totalSteps = 5;
        
        try {
            // PASSO 1: Conectar ao MySQL
            $currentStep++;
            echo "<div class='step'>";
            echo "<h3>Passo $currentStep/$totalSteps: Conectando ao MySQL...</h3>";
            
            $pdo = new PDO("mysql:host=$host", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            echo "<p>✅ Conexão estabelecida com sucesso!</p>";
            echo "</div>";
            
            // PASSO 2: Criar banco de dados
            $currentStep++;
            echo "<div class='step'>";
            echo "<h3>Passo $currentStep/$totalSteps: Criando banco de dados...</h3>";
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE $dbname");
            
            echo "<p>✅ Banco de dados '$dbname' criado/selecionado!</p>";
            echo "</div>";
            
            // PASSO 3: Ler arquivo SQL
            $currentStep++;
            echo "<div class='step'>";
            echo "<h3>Passo $currentStep/$totalSteps: Carregando script SQL...</h3>";
            
            $sqlFile = __DIR__ . '/database/migration.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("Arquivo migration.sql não encontrado!");
            }
            
            $sql = file_get_contents($sqlFile);
            
            echo "<p>✅ Script SQL carregado (" . number_format(strlen($sql)) . " caracteres)</p>";
            echo "</div>";
            
            // PASSO 4: Executar SQL
            $currentStep++;
            echo "<div class='step'>";
            echo "<h3>Passo $currentStep/$totalSteps: Executando comandos SQL...</h3>";
            
            $pdo->exec($sql);
            
            echo "<p>✅ Todos os comandos SQL executados com sucesso!</p>";
            echo "</div>";
            
            // PASSO 5: Verificar tabelas
            $currentStep++;
            echo "<div class='step success'>";
            echo "<h3>Passo $currentStep/$totalSteps: Verificando instalação...</h3>";
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p><strong>✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!</strong></p>";
            echo "<p>📊 Tabelas criadas: " . count($tables) . "</p>";
            echo "<ul style='margin-left: 20px; margin-top: 10px;'>";
            foreach ($tables as $table) {
                echo "<li>✓ $table</li>";
            }
            echo "</ul>";
            echo "</div>";
            
            // Credenciais
            echo "<div class='step warning'>";
            echo "<h3>🔑 Credenciais de Acesso</h3>";
            echo "<pre>";
            echo "Email: admin@criancafeliz.org\n";
            echo "Senha: admin123";
            echo "</pre>";
            echo "<p style='margin-top: 10px;'><strong>⚠️ IMPORTANTE:</strong> Altere a senha padrão após o primeiro login!</p>";
            echo "</div>";
            
            // Progresso
            echo "<div class='progress'>";
            echo "<div class='progress-bar'>";
            echo "<div class='progress-fill' style='width: 100%'>100% Completo</div>";
            echo "</div>";
            echo "</div>";
            
            // Botões
            echo "<div style='text-align: center;'>";
            echo "<a href='index.php' class='btn btn-success'>🚀 Acessar o Sistema</a>";
            echo "<a href='database/test_connection.php' class='btn' style='margin-left: 10px;'>🔍 Testar Conexão</a>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='step error'>";
            echo "<h3>❌ Erro na Instalação</h3>";
            echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<h4>🔧 Possíveis Soluções:</h4>";
            echo "<ul style='margin-left: 20px;'>";
            echo "<li>Verifique se o XAMPP está rodando (MySQL)</li>";
            echo "<li>Verifique se a porta 3306 está livre</li>";
            echo "<li>Verifique as credenciais (usuário: root, senha: vazia)</li>";
            echo "<li>Tente reiniciar o MySQL no XAMPP</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<div style='text-align: center;'>";
            echo "<a href='javascript:location.reload()' class='btn'>🔄 Tentar Novamente</a>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='step error'>";
            echo "<h3>❌ Erro</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
