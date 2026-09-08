# 🚀 Instalação Rápida - Sistema de Logs

## ⚡ Passo a Passo

### 1️⃣ Criar Triggers no Banco de Dados

#### Opção A: Via phpMyAdmin (Recomendado)

1. Abra `http://localhost/phpmyadmin`
2. Selecione banco de dados `criancafeliz`
3. Clique na aba **"SQL"**
4. Abra o arquivo: `database/migration_logs_completo.sql`
5. Copie TODO o conteúdo
6. Cole na caixa de SQL do phpMyAdmin
7. Clique em **"Executar"** (botão azul)
8. ✅ Pronto! Triggers criadas

#### Opção B: Via Linha de Comando

```bash
# Abra o terminal/CMD na pasta do projeto
cd c:\xampp\htdocs\CriancaFeliz

# Execute o comando
mysql -u root -p criancafeliz < database/migration_logs_completo.sql

# Digite a senha do MySQL (padrão: vazio, só pressione Enter)
```

#### Opção C: Verificar se Triggers Foram Criadas

No phpMyAdmin:
1. Vá para banco `criancafeliz`
2. Clique em **"Acionadores"** (ou "Triggers")
3. Você deve ver triggers como:
   - `log_atendido_insert`
   - `log_atendido_update`
   - `log_atendido_delete`
   - `log_ficha_acolhimento_insert`
   - ... (e mais)

Se não vir, execute novamente o SQL.

### 2️⃣ Verificar Estrutura da Tabela `log`

No phpMyAdmin:
1. Vá para banco `criancafeliz`
2. Clique em tabela `log`
3. Clique em **"Estrutura"**
4. Verifique se existem as colunas:
   - ✅ `id_log`
   - ✅ `data_alteracao`
   - ✅ `registro_alt`
   - ✅ `valor_anterior`
   - ✅ `valor_atual`
   - ✅ `acao`
   - ✅ `tabela_afetada`
   - ✅ `id_usuario`
   - ✅ `id_registro` (nova)
   - ✅ `campo_alterado` (nova)
   - ✅ `ip_usuario` (nova)

Se faltar alguma coluna, execute este SQL:

```sql
ALTER TABLE `log` 
ADD COLUMN IF NOT EXISTS `id_registro` INT(11) DEFAULT NULL AFTER `id_usuario`,
ADD COLUMN IF NOT EXISTS `campo_alterado` VARCHAR(100) DEFAULT NULL AFTER `id_registro`,
ADD COLUMN IF NOT EXISTS `ip_usuario` VARCHAR(45) DEFAULT NULL AFTER `campo_alterado`;
```

### 3️⃣ Acessar o Sistema de Logs

1. Faça login como **administrador**
   - Email: `admin@criancafeliz.org`
   - Senha: `admin123`

2. Acesse a URL:
   ```
   http://localhost/CriancaFeliz/logs.php
   ```

3. Você deve ver:
   - 📊 Dashboard com estatísticas
   - 📋 Tabela com últimos logs
   - 🔍 Botão de filtros avançados
   - 📥 Botão de exportação CSV

### 4️⃣ Testar o Sistema

Para verificar se está funcionando:

1. Crie um novo atendido em `Prontuários`
2. Volte para `logs.php`
3. Você deve ver um novo log com:
   - Ação: ➕ Criar
   - Tabela: `atendido`
   - Descrição: Nome do atendido criado

Se não aparecer:
- Atualize a página (F5)
- Verifique se é administrador
- Verifique se triggers foram criadas

## 📂 Arquivos Criados/Modificados

### Novos Arquivos

```
app/Models/LogDB.php                    ← Model para gerenciar logs
app/Controllers/LogController.php       ← Controller principal
app/Views/logs/index.php                ← Dashboard de logs
app/Views/logs/show.php                 ← Detalhes de um log
logs.php                                ← Página principal (wrapper)
database/migration_logs_completo.sql    ← SQL com triggers
SISTEMA_LOGS_README.md                  ← Documentação completa
INSTALACAO_SISTEMA_LOGS.md              ← Este arquivo
```

### Arquivos Modificados

Nenhum arquivo existente foi modificado. O sistema é totalmente independente.

## 🔧 Configuração Avançada

### Capturar IP do Usuário

Para registrar o IP de quem fez a alteração, adicione ao seu arquivo de login:

```php
// Em index.php ou seu arquivo de autenticação
$_SESSION['user_id'] = $user['idusuario'];
$_SESSION['user_name'] = $user['nome'];

// Adicione esta linha para capturar IP
$_SERVER['REMOTE_ADDR']; // Já é capturado automaticamente
```

### Limpar Logs Antigos

Para remover logs com mais de 90 dias:

```
POST http://localhost/CriancaFeliz/logs.php?action=delete_old
```

Ou via código:

```php
$logModel = new LogDB();
$logModel->deleteOldLogs(90); // Remove logs com mais de 90 dias
```

## 🎯 Próximos Passos

1. ✅ Instalação concluída
2. 📖 Leia `SISTEMA_LOGS_README.md` para usar o sistema
3. 🔍 Explore os filtros e buscas
4. 📊 Analise as estatísticas
5. 📥 Exporte logs em CSV

## ❓ Dúvidas Frequentes

### P: Onde vejo os logs?
**R:** Acesse `http://localhost/CriancaFeliz/logs.php` (apenas como admin)

### P: Quem pode acessar os logs?
**R:** Apenas usuários com `nivel = 'admin'` na tabela `usuario`

### P: Os logs são deletados automaticamente?
**R:** Não. Você pode limpar manualmente logs com mais de 90 dias.

### P: Posso exportar os logs?
**R:** Sim! Clique em "📥 Exportar CSV" para baixar em formato Excel.

### P: Como rastrear alterações de um atendido específico?
**R:** Use o filtro "Buscar" com o ID ou nome do atendido.

### P: Posso ver quem fez cada alteração?
**R:** Sim! Cada log registra o usuário que fez a ação.

## 🐛 Troubleshooting

### Erro: "Acesso negado"
- Verifique se você é administrador
- Verifique se `nivel = 'admin'` na tabela `usuario`

### Erro: "Tabela log não encontrada"
- Verifique se a tabela `log` existe no banco
- Execute o SQL de criação novamente

### Logs não aparecem
- Verifique se triggers foram criadas: `SHOW TRIGGERS;`
- Atualize a página (F5)
- Crie um novo registro para testar

### Performance lenta
- Limpe logs antigos
- Verifique índices: `SHOW INDEX FROM log;`

## 📞 Suporte

Para problemas:
1. Verifique este arquivo
2. Leia `SISTEMA_LOGS_README.md`
3. Verifique error_log do PHP
4. Verifique console do navegador (F12)

---

**Status**: ✅ Pronto para produção  
**Versão**: 1.0  
**Data**: Dezembro 2025
