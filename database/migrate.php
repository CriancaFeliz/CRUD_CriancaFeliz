<?php
/**
 * Script de Migração do Banco de Dados
 * Execute este arquivo para criar/atualizar o banco
 */

// Configurações do banco
$host = 'localhost';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

echo "🚀 INICIANDO MIGRAÇÃO DO BANCO DE DADOS\n";
echo "==========================================\n\n";

try {
    // Conectar ao MySQL (sem selecionar banco)
    $dsn = "mysql:host=$host;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conectado ao MySQL\n";
    
    // Ler arquivo de migração
    $sqlFile = __DIR__ . '/migration.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("❌ Arquivo migration.sql não encontrado!");
    }
    
    $sql = file_get_contents($sqlFile);
    
    echo "📄 Arquivo de migração carregado\n";
    echo "📊 Executando comandos SQL...\n\n";
    
    // Executar SQL
    $pdo->exec($sql);
    
    echo "\n✅ MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "==========================================\n";
    echo "📦 Banco de dados: criancafeliz\n";
    echo "👤 Usuário padrão: admin@criancafeliz.org\n";
    echo "🔑 Senha padrão: admin123\n";
    echo "==========================================\n\n";
    
    // Verificar tabelas criadas
    $pdo->exec("USE criancafeliz");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tabelas criadas (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "   ✓ $table\n";
    }
    
    echo "\n🎉 Banco de dados pronto para uso!\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
