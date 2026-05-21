# 🔧 Corrigir Erro: Column not found 'data_acolhimento'

## ❌ Problema
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'a.data_acolhimento' in 'field list'
```

A tabela `Atendido` não possui a coluna `data_acolhimento` que a aplicação espera.

---

## ✅ Solução

### **OPÇÃO 1: Via phpMyAdmin (Recomendado para XAMPP)**

1. Abra phpMyAdmin: `http://localhost/phpmyadmin`
2. Selecione o banco `criancafeliz` no painel esquerdo
3. Clique na aba **SQL**
4. Cole este comando:

```sql
ALTER TABLE Atendido ADD COLUMN data_acolhimento DATE COMMENT 'Data when the person was first attended';
```

5. Clique em **Executar** (ou **Go** ou **Execute**)
6. Se aparecer mensagem de sucesso, a coluna foi adicionada!

### **OPÇÃO 2: Via Arquivo de Migração**

Se você preferir, existe um arquivo SQL pronto:
- Arquivo: `database/migration_add_data_acolhimento.sql`
- Basta copiar e colar os comandos no phpMyAdmin

### **OPÇÃO 3: Via Linha de Comando (MySQL/MariaDB)**

```bash
mysql -u root -p criancafeliz < database/migration_add_data_acolhimento.sql
```

---

## ✔️ Verificação

Após executar a migração, execute esta query para confirmar:

```sql
SHOW COLUMNS FROM Atendido WHERE Field = 'data_acolhimento';
```

Você deverá ver uma linha com a coluna `data_acolhimento`.

---

## 📝 Detalhes Técnicos

### O que foi corrigido:

1. **Coluna faltante**: Adicionado `data_acolhimento DATE` à tabela `Atendido`
   - Tipo: DATE
   - Padrão: NULL
   - Descrição: Data quando a pessoa foi acolhida/atendida pela primeira vez

2. **Código resiliente**: O arquivo `app/Models/SocioeconomicoDB.php` foi atualizado para:
   - Tentar usar `data_acolhimento` se existir
   - Fallback para `data_cadastro` se a coluna não existir
   - Isso evita erros durante a migração

### Como a coluna é usada:

- **Inserção**: Quando você submete uma nova ficha, a aplicação tenta salvar `data_acolhimento`
- **Listagem**: A coluna aparece na lista de fichas (substituindo "Idade" como solicitado)
- **Visualização**: Aparece na tela de visualização da ficha completa

---

## 🚀 Próximos Passos

1. Execute a migração SQL (escolha uma opção acima)
2. Verifique que a coluna foi criada
3. Refresque a página `socioeconomico_list.php` no navegador
4. O erro deve desaparecer e você verá a coluna "Data de Acolhimento" na tabela

---

## 🆘 Troubleshooting

### Erro: "Column already exists"
- Significa que a coluna já foi adicionada com sucesso!
- Você pode ignorar este erro.

### Erro: "Access Denied for user"
- Você não tem permissão de ALTER TABLE
- Peça ao administrador do banco de dados para executar o comando

### Erro: "Unknown database 'criancafeliz'"
- Verifique se está no banco correto
- Use `SHOW DATABASES;` para listar bancos disponíveis

### A página ainda mostra erro
- Limpe o cache do navegador: **Ctrl+Shift+Del** (Chrome) ou **Ctrl+Shift+R** (Firefox)
- Verifique os logs PHP em `php_errors.log`
- Execute novamente: `SHOW COLUMNS FROM Atendido;` para confirmar a coluna existe

---

## 📊 Estrutura da Tabela Atendido (após migração)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| idatendido | INT | PK - ID único |
| nome | VARCHAR | Nome da pessoa |
| cpf | VARCHAR | CPF (sem formatação) |
| rg | VARCHAR | RG (sem formatação) |
| data_nascimento | DATE | Data de nascimento |
| **data_acolhimento** | **DATE** | **✨ NOVO: Data do primeiro acolhimento** |
| data_cadastro | DATE | Data de cadastro no sistema |
| endereco | VARCHAR | Endereço |
| numero | VARCHAR | Número |
| ... | ... | ... |

---

## 📞 Suporte

Se tiver problemas:
1. Verifique se está conectado ao banco correto
2. Confirm que o usuário MySQL tem permissão ALTER TABLE
3. Veja os logs: `php_errors.log`
4. Tente executar manualmente via phpMyAdmin
