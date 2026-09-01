# Migração: Adicionar Colunas de Benefícios ao Banco de Dados

## 📋 Descrição
Este arquivo contém instruções para adicionar as colunas de benefícios sociais à tabela `Ficha_Socioeconomico` se elas ainda não existirem em seu banco de dados.

## ⚠️ Importante
- **Se você receber um erro dizendo que uma coluna já existe**, é seguro ignorar — significa que ela já foi adicionada.
- **Se o seu usuário MySQL não tiver permissão para executar ALTER TABLE**, peça ao administrador do banco de dados para executar os comandos.
- **Faça um backup do seu banco antes de executar qualquer migração.**

## 🔧 Como Executar

### Opção 1: Via phpMyAdmin (Recomendado para XAMPP)
1. Abra phpMyAdmin no navegador: `http://localhost/phpmyadmin`
2. Selecione o banco de dados `criancafeliz` no painel esquerdo
3. Clique na aba **SQL** no topo
4. Copie e cole **APENAS** um dos comandos abaixo de cada vez:

```sql
ALTER TABLE Ficha_Socioeconomico ADD COLUMN bolsa_familia TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_brasil TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN bpc TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_emergencial TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN seguro_desemprego TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN aposentadoria TINYINT(1) DEFAULT 0;
```

5. Clique em **Executar** (ou **Go** ou **Execute**)
6. Repita para cada comando até que todos tenham sido executados

### Opção 2: Via Linha de Comando (MySQL/MariaDB)
Se você tem acesso ao terminal/cmd do seu servidor:

```bash
mysql -u root -p criancafeliz < database/migration_beneficios_columns.sql
```

Ou conecte ao MySQL interativamente:

```bash
mysql -u root -p
```

Então execute os comandos SQL um a um.

### Opção 3: Usar o Script de Migração PHP (Automático)
Se a aplicação detectar colunas faltantes, ela tentará criá-las automaticamente quando você submeter uma nova ficha socioeconômica. Verificar `app/Models/SocioeconomicoDB.php` na função `createFicha()`.

## ✅ Verificação
Para verificar se as colunas foram criadas com sucesso, execute em phpMyAdmin:

```sql
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'criancafeliz' 
AND TABLE_NAME = 'Ficha_Socioeconomico' 
AND COLUMN_NAME IN ('bolsa_familia', 'auxilio_brasil', 'bpc', 'auxilio_emergencial', 'seguro_desemprego', 'aposentadoria')
ORDER BY COLUMN_NAME;
```

Você deverá ver 6 linhas com as colunas criadas.

## 🆘 Troubleshooting

### Erro: "Column already exists"
Significa que a coluna já foi adicionada. Pule esse comando e continue com o próximo.

### Erro: "Access Denied for user"
Você não tem permissão para alterar a tabela. Peça ao administrador do banco de dados para executar os comandos.

### Erro: "Unknown table 'Ficha_Socioeconomico'"
Verifique se:
1. Você está no banco correto (`criancafeliz`)
2. A tabela realmente existe (execute `SHOW TABLES;`)
3. O nome da tabela está correto (case-sensitive em alguns sistemas)

### A aplicação ainda mostra "Nenhum benefício informado"
- Confirme que as colunas foram criadas (use a SQL de verificação acima)
- Tente submeter uma nova ficha com alguns benefícios marcados
- Verifique os logs PHP em `php_errors.log` ou `error_log` para mensagens de erro

## 📝 Notas Técnicas

### Estrutura das Colunas
- **Tipo**: `TINYINT(1)` — tipo pequeno para booleano (0 ou 1)
- **Padrão**: `DEFAULT 0` — benefício não ativo por padrão
- **Espaço**: Apenas 1 byte por coluna, não afeta performance

### Campos Adicionados
1. `bolsa_familia` — Bolsa Família
2. `auxilio_brasil` — Auxílio Brasil
3. `bpc` — Benefício de Prestação Continuada
4. `auxilio_emergencial` — Auxílio Emergencial
5. `seguro_desemprego` — Seguro Desemprego
6. `aposentadoria` — Aposentadoria

### Como a Aplicação Usa
- Quando você submete a ficha socioeconômica via formulário, marca quais benefícios a família possui
- A aplicação insere `1` para benefício marcado, `0` para não marcado
- Na listagem e visualização, a aplicação lê essas flags e exibe os nomes dos benefícios

## ✨ Próximos Passos
1. Execute a migração (siga uma das opções acima)
2. Volte para `socioeconomico_list.php` e refresque a página
3. Submeta uma nova ficha socioeconômica marcando alguns benefícios
4. Verifique se os benefícios aparecem na listagem e na tela de visualização
