<?php
/**
 * Verificador de Colunas - Ficha Socioeconomico
 * 
 * Este script verifica quais colunas faltam na tabela Ficha_Socioeconomico
 * e gera os comandos SQL necessários para criá-las.
 * 
 * Acesso: http://localhost/CriancaFeliz/check_ficha_columns.php
 */

session_start();
require_once 'bootstrap.php';

$missingColumns = [];
$existingColumns = [];
$sqlCommands = [];

try {
    // Conectar ao banco
    $db = Database::getConnection();
    
    // Listar colunas existentes
    $stmt = $db->query("SHOW COLUMNS FROM Ficha_Socioeconomico");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existingColumnNames = array_column($columns, 'Field');
    
    // Definir colunas esperadas
    $expectedColumns = [
        'bolsa_familia' => 'TINYINT(1) DEFAULT 0',
        'auxilio_brasil' => 'TINYINT(1) DEFAULT 0',
        'bpc' => 'TINYINT(1) DEFAULT 0',
        'auxilio_emergencial' => 'TINYINT(1) DEFAULT 0',
        'seguro_desemprego' => 'TINYINT(1) DEFAULT 0',
        'aposentadoria' => 'TINYINT(1) DEFAULT 0',
        'agua' => 'TINYINT(1) DEFAULT 0',
        'esgoto' => 'TINYINT(1) DEFAULT 0',
        'energia' => 'TINYINT(1) DEFAULT 0',
        'moradia' => 'VARCHAR(100)',
        'cond_residencia' => 'VARCHAR(100)',
        'nr_comodos' => 'INT DEFAULT 0',
        'nr_veiculos' => 'INT DEFAULT 0',
        'entrevistado' => 'VARCHAR(255)',
        'observacoes' => 'TEXT'
    ];
    
    // Comparar
    foreach ($expectedColumns as $col => $type) {
        if (in_array($col, $existingColumnNames)) {
            $existingColumns[] = $col;
        } else {
            $missingColumns[] = $col;
            $sqlCommands[] = "ALTER TABLE Ficha_Socioeconomico ADD COLUMN $col $type;";
        }
    }
    
    $connectionOk = true;
    $connectionError = null;
} catch (Exception $e) {
    $connectionOk = false;
    $connectionError = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Colunas - Ficha Socioeconomico</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Poppins, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .header h1 { margin: 0 0 10px 0; font-size: 28px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .success { background: #e8f6ea; border-left: 4px solid #6fb64f; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .success h3 { color: #6fb64f; margin-bottom: 10px; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .error h3 { color: #dc3545; margin-bottom: 10px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .warning h3 { color: #856404; margin-bottom: 10px; }
        .info { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .info h3 { color: #1976D2; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; }
        .table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .table tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; margin: 15px 0; }
        .button { display: inline-block; padding: 10px 20px; background: #6fb64f; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .button:hover { background: #5a9a3f; }
        .stat { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #667eea; }
        .stat-label { color: #6c757d; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificador de Colunas</h1>
            <p>Verifica quais colunas faltam na tabela Ficha_Socioeconomico</p>
        </div>

        <div class="card">
            <?php if (!$connectionOk): ?>
                <div class="error">
                    <h3>❌ Erro de Conexão</h3>
                    <p>Não foi possível conectar ao banco de dados:</p>
                    <pre><?php echo htmlspecialchars($connectionError); ?></pre>
                    <p>Verifique a configuração do banco em <code>app/Config/Database.php</code></p>
                </div>
            <?php else: ?>
                <div class="stat">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($existingColumns); ?></div>
                        <div class="stat-label">Colunas Existentes</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($missingColumns); ?></div>
                        <div class="stat-label">Colunas Faltando</div>
                    </div>
                </div>

                <?php if (empty($missingColumns)): ?>
                    <div class="success">
                        <h3>✅ Tudo Perfeito!</h3>
                        <p>Todas as colunas esperadas existem na tabela <code>Ficha_Socioeconomico</code>.</p>
                        <p>Você pode usar a aplicação normalmente.</p>
                    </div>
                <?php else: ?>
                    <div class="warning">
                        <h3>⚠️ Colunas Faltando</h3>
                        <p>As seguintes colunas precisam ser adicionadas:</p>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Coluna</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($missingColumns as $col): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($col); ?></code></td>
                                        <td><span class="badge badge-danger">Faltando</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="info">
                        <h3>✨ Comandos SQL para Criar as Colunas</h3>
                        <p>Copie e cole esses comandos no phpMyAdmin SQL ou execute via linha de comando:</p>
                        <pre><?php echo htmlspecialchars(implode("\n", $sqlCommands)); ?></pre>
                        <p style="margin-top: 15px; color: #666;">
                            <strong>Ou use o arquivo:</strong> <code>database/migration_ficha_socioeconomico_completo.sql</code>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="info">
                    <h3>📋 Colunas Existentes</h3>
                    <p>Essas colunas já estão presentes na tabela:</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Coluna</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($existingColumns as $col): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($col); ?></code></td>
                                    <td><span class="badge badge-success">Existente</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="info">
                    <h3>🚀 Próximos Passos</h3>
                    <ol style="margin-left: 20px; line-height: 1.8;">
                        <li>Copie os comandos SQL acima</li>
                        <li>Abra phpMyAdmin: <code>http://localhost/phpmyadmin</code></li>
                        <li>Selecione o banco <code>criancafeliz</code></li>
                        <li>Clique na aba <strong>SQL</strong></li>
                        <li>Cole os comandos e clique em <strong>Executar</strong></li>
                        <li>Refresque esta página para confirmar</li>
                    </ol>
                </div>

                <p style="margin-top: 20px; text-align: center; color: #6c757d;">
                    <a href="check_ficha_columns.php" class="button">🔄 Recarregar</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
