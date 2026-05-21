# 📊 Sistema de Logs Inteligente - Criança Feliz

## 🎯 Visão Geral

Sistema completo e inteligente de logs que registra **TODAS** as alterações no banco de dados em tempo real. Apenas **administradores** têm acesso.

## ✨ Funcionalidades Principais

### 1. **Registro Automático de Alterações**
- ✅ Criação de registros (INSERT)
- ✅ Edição de registros (UPDATE)
- ✅ Deleção de registros (DELETE)
- ✅ Captura automática via triggers MySQL

### 2. **Informações Capturadas**
- 📅 Data e hora exata da alteração
- 👤 Usuário que realizou a ação
- 🌐 IP do usuário
- 📝 Descrição detalhada do registro alterado
- 🔄 Valor anterior e valor atual
- 🏷️ Campo específico que foi alterado
- 🗂️ Tabela afetada

### 3. **Tabelas Monitoradas**
- `atendido` - Dados dos atendidos
- `ficha_acolhimento` - Fichas de acolhimento
- `ficha_socioeconomico` - Fichas socioeconômicas
- `anotacao_psicologica` - Anotações psicológicas
- `frequencia_dia` - Frequência diária
- `desligamento` - Desligamentos
- `usuario` - Gerenciamento de usuários

### 4. **Filtros e Buscas**
- 🔍 Busca por tabela afetada
- 🔍 Busca por tipo de ação (INSERT/UPDATE/DELETE)
- 🔍 Busca por usuário
- 🔍 Busca por período (data inicial e final)
- 🔍 Busca por texto (descrição, valores)
- 🔍 Combinação de múltiplos filtros

### 5. **Visualizações**
- 📊 Dashboard com estatísticas gerais
- 📈 Gráficos de ações por tipo
- 👥 Usuários mais ativos
- 📅 Atividade dos últimos 7 dias
- 🔎 Detalhes completos de cada log

### 6. **Exportação**
- 📥 Exportar logs em CSV
- 📥 Exportar com filtros aplicados
- 📥 Compatível com Excel/Google Sheets

### 7. **Gerenciamento**
- 🗑️ Limpeza de logs antigos (>90 dias)
- 📊 Estatísticas em tempo real
- 🔐 Acesso restrito a administradores

## 🚀 Instalação

### Passo 1: Aplicar Triggers MySQL

Execute o arquivo SQL para criar os triggers:

```bash
mysql -u root -p criancafeliz < database/migration_logs_completo.sql
```

Ou via phpMyAdmin:
1. Abra phpMyAdmin
2. Selecione banco `criancafeliz`
3. Vá para "SQL"
4. Cole o conteúdo de `database/migration_logs_completo.sql`
5. Clique em "Executar"

### Passo 2: Verificar Estrutura da Tabela

A tabela `log` já existe, mas será atualizada com novas colunas:

```sql
ALTER TABLE `log` 
ADD COLUMN IF NOT EXISTS `id_registro` INT(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `campo_alterado` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `ip_usuario` VARCHAR(45) DEFAULT NULL;
```

### Passo 3: Acessar o Sistema

1. Faça login como **administrador**
2. Acesse: `http://localhost/CriancaFeliz/logs.php`
3. Ou clique no menu lateral (ícone 📊)

## 📖 Como Usar

### Dashboard Principal

```
http://localhost/CriancaFeliz/logs.php
```

Mostra:
- Estatísticas gerais (total de logs, criações, edições, deleções)
- Lista de últimos logs com paginação
- Botões de filtro e exportação

### Filtros Avançados

Clique em **"🔍 Filtros Avançados"** para:

1. **Filtrar por Tabela**
   - Selecione qual tabela deseja analisar
   - Exemplo: `ficha_acolhimento`

2. **Filtrar por Ação**
   - Criar (INSERT)
   - Editar (UPDATE)
   - Deletar (DELETE)

3. **Filtrar por Usuário**
   - Veja quem fez cada alteração
   - Identifique padrões de uso

4. **Filtrar por Período**
   - Data início e data fim
   - Análise histórica

5. **Buscar por Texto**
   - Nome, CPF, descrição
   - Busca em múltiplos campos

### Visualizar Detalhes

Clique em **"Ver"** em qualquer log para:
- Ver informações completas
- Comparar valor anterior vs. atual
- Visualizar dados em JSON
- Rastrear histórico do registro

### Exportar Logs

Clique em **"📥 Exportar CSV"** para:
- Baixar logs em formato CSV
- Abrir em Excel/Google Sheets
- Compartilhar com equipe

## 🔧 Configuração

### Definir Variáveis de Sessão

Para capturar IP e navegador do usuário, adicione ao seu código de login:

```php
// Em seu arquivo de login (index.php ou auth)
$_SESSION['user_id'] = $user['idusuario'];
$_SESSION['user_name'] = $user['nome'];

// Opcional: Capturar IP
$_SERVER['REMOTE_ADDR']; // IP do usuário
```

### Registrar Logs Manualmente

Para ações que não são capturadas por triggers:

```php
$logModel = new LogDB();
$logModel->logAction(
    'UPDATE',                    // ação
    'tabela_customizada',        // tabela
    'Descrição da alteração',    // descrição
    'valor_anterior',            // valor anterior
    'valor_novo',                // valor novo
    123,                         // id_registro (opcional)
    $_SESSION['user_id'],        // usuario_id
    $_SERVER['REMOTE_ADDR']      // ip_usuario
);
```

## 📊 Estrutura de Dados

### Tabela `log`

```sql
CREATE TABLE `log` (
  `id_log` int(11) PRIMARY KEY AUTO_INCREMENT,
  `data_alteracao` datetime DEFAULT CURRENT_TIMESTAMP,
  `registro_alt` varchar(255),
  `valor_anterior` longtext,
  `valor_atual` longtext,
  `acao` varchar(50),           -- INSERT, UPDATE, DELETE
  `tabela_afetada` varchar(100),
  `id_usuario` int(11),         -- FK para usuario
  `id_registro` int(11),        -- ID do registro alterado
  `campo_alterado` varchar(100),
  `ip_usuario` varchar(45),
  `navegador` varchar(255),
  
  KEY `idx_data_acao` (`data_alteracao`, `acao`),
  KEY `idx_tabela_acao` (`tabela_afetada`, `acao`),
  KEY `idx_usuario_data` (`id_usuario`, `data_alteracao`)
);
```

## 🎯 Casos de Uso

### 1. Auditoria de Segurança
```
Filtro: Ação = DELETE, Data = Últimos 7 dias
Resultado: Ver quem deletou registros e quando
```

### 2. Rastrear Alterações de um Atendido
```
Filtro: ID Registro = 123
Resultado: Histórico completo de todas as alterações
```

### 3. Atividade de um Usuário
```
Filtro: Usuário = João Silva, Data = Mês atual
Resultado: Tudo que João fez no sistema
```

### 4. Problemas em Fichas Socioeconômicas
```
Filtro: Tabela = ficha_socioeconomico, Ação = UPDATE
Resultado: Todas as edições de fichas
```

### 5. Relatório Executivo
```
Exportar: CSV com últimos 30 dias
Resultado: Arquivo para análise em Excel
```

## 🔐 Segurança

### Acesso Restrito
- ✅ Apenas administradores podem acessar
- ✅ Verificação em `LogController::requireAdmin()`
- ✅ Validação em `logs.php`

### Proteção de Dados
- ✅ SQL Injection prevenido (prepared statements)
- ✅ XSS prevenido (htmlspecialchars)
- ✅ CSRF tokens (se implementado)

### Privacidade
- ✅ Logs não são deletados automaticamente
- ✅ Apenas admin pode limpar logs antigos
- ✅ Mínimo de 30 dias antes de limpeza

## 📈 Performance

### Índices Criados
```sql
CREATE INDEX idx_data_acao ON log (data_alteracao, acao);
CREATE INDEX idx_tabela_acao ON log (tabela_afetada, acao);
CREATE INDEX idx_usuario_data ON log (id_usuario, data_alteracao);
```

### Paginação
- 50 logs por página (configurável)
- Carregamento rápido mesmo com milhares de registros

### Limpeza Automática
- Remover logs com mais de 90 dias
- Comando: `POST /logs.php?action=delete_old`

## 🐛 Troubleshooting

### Logs não aparecem
1. Verifique se triggers foram criadas: `SHOW TRIGGERS;`
2. Verifique se a tabela `log` existe
3. Verifique permissões do usuário MySQL

### Erro de acesso
1. Verifique se você é administrador
2. Verifique se `nivel = 'admin'` na tabela `usuario`

### Performance lenta
1. Verifique índices: `SHOW INDEX FROM log;`
2. Limpe logs antigos
3. Aumente `per_page` em paginação

## 📝 Exemplos de Logs

### Criar Atendido
```
Ação: ➕ Criar
Tabela: atendido
Descrição: Novo atendido criado: Ana Beatriz Silva
Valor Anterior: (vazio)
Valor Atual: Nome: Ana Beatriz Silva | CPF: 111.222.333-44 | Data Nascimento: 2012-05-14 | Status: Ativo
```

### Editar Ficha Socioeconômica
```
Ação: ✏️ Editar
Tabela: ficha_socioeconomico
Descrição: Ficha Socioeconômica alterada (ID: 4)
Valor Anterior: Renda: R$ 100000.00 | Pessoas: 5
Valor Atual: Renda: R$ 200000.00 | Pessoas: 4
```

### Deletar Anotação Psicológica
```
Ação: 🗑️ Deletar
Tabela: anotacao_psicologica
Descrição: Anotação Psicológica deletada (ID: 6)
Valor Anterior: Tipo: Observação | Título: edição
Valor Atual: (vazio)
```

## 🎓 Documentação Técnica

### Triggers MySQL

Cada tabela monitorada tem 3 triggers:
- `log_[tabela]_insert` - Registra criações
- `log_[tabela]_update` - Registra edições
- `log_[tabela]_delete` - Registra deleções

### Model LogDB

Métodos disponíveis:
- `getAllLogs($page, $perPage)` - Todos os logs
- `getLogsByTable($table, $page, $perPage)` - Por tabela
- `getLogsByAction($action, $page, $perPage)` - Por ação
- `getLogsByUser($userId, $page, $perPage)` - Por usuário
- `getLogsByRegistroId($id, $page, $perPage)` - Por ID de registro
- `getLogsByDateRange($start, $end, $page, $perPage)` - Por período
- `searchAdvanced($filters, $page, $perPage)` - Busca avançada
- `getStatistics()` - Estatísticas gerais
- `exportToCSV($filters)` - Exportar em CSV
- `deleteOldLogs($days)` - Limpar logs antigos
- `logAction(...)` - Registrar manualmente

### Controller LogController

Ações disponíveis:
- `index()` - Dashboard principal
- `byTable()` - Filtrar por tabela
- `byAction()` - Filtrar por ação
- `byUser()` - Filtrar por usuário
- `historicoRegistro()` - Histórico de um registro
- `search()` - Busca avançada
- `show()` - Detalhes de um log
- `export()` - Exportar CSV
- `deleteOld()` - Limpar logs antigos
- `apiGetLogs()` - API JSON
- `apiSearch()` - API de busca
- `apiStats()` - API de estatísticas

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique este README
2. Analise os logs em `logs.php`
3. Verifique o console do navegador (F12)
4. Verifique error_log do PHP

## 📄 Licença

Sistema Criança Feliz - Todos os direitos reservados.

---

**Versão**: 1.0  
**Última atualização**: Dezembro 2025  
**Status**: ✅ Produção
