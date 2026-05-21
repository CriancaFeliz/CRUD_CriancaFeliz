# 🔧 Corrigir Erro: Column not found em Ficha_Socioeconomico

## ❌ Problema
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'f.bolsa_familia' in 'field list'
```

A tabela `Ficha_Socioeconomico` está faltando várias colunas que a aplicação espera.

---

## ✅ Solução

### **PASSO 1: Executar a Migração SQL Completa**

Você tem 3 opções:

#### **Opção 1A: Via phpMyAdmin (Recomendado para XAMPP)**

1. Abra phpMyAdmin: `http://localhost/phpmyadmin`
2. Selecione o banco `criancafeliz` no painel esquerdo
3. Clique na aba **SQL**
4. **Abra este arquivo em um editor de texto:**
   - `database/migration_ficha_socioeconomico_completo.sql`
5. **Copie TODOS os comandos ALTER TABLE** e cole no phpMyAdmin
6. Clique em **Executar**

**Atenção**: Se receber "Column already exists" para alguma coluna, é normal! Significa que ela já foi adicionada. Ignore o erro e continue.

#### **Opção 1B: Via Arquivo SQL Direto**

Se você tem acesso ao terminal:

```bash
mysql -u root -p criancafeliz < database/migration_ficha_socioeconomico_completo.sql
```

#### **Opção 1C: Copiar e Colar (Mais Simples)**

Copie e cole **um comando por vez** no phpMyAdmin SQL:

```sql
ALTER TABLE Ficha_Socioeconomico ADD COLUMN bolsa_familia TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_brasil TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN bpc TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_emergencial TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN seguro_desemprego TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN aposentadoria TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN agua TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN esgoto TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN energia TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN moradia VARCHAR(100);
ALTER TABLE Ficha_Socioeconomico ADD COLUMN cond_residencia VARCHAR(100);
ALTER TABLE Ficha_Socioeconomico ADD COLUMN nr_comodos INT DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN nr_veiculos INT DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN entrevistado VARCHAR(255);
ALTER TABLE Ficha_Socioeconomico ADD COLUMN observacoes TEXT;
```

---

## ✔️ Verificação

Após executar, verifique se as colunas foram criadas:

```sql
SELECT COLUMN_NAME, COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'criancafeliz' 
AND TABLE_NAME = 'Ficha_Socioeconomico' 
ORDER BY COLUMN_NAME;
```

Você deverá ver as seguintes colunas:
- ✓ aposentadoria
- ✓ auxilio_brasil
- ✓ auxilio_emergencial
- ✓ bpc
- ✓ bolsa_familia
- ✓ cond_residencia
- ✓ energia
- ✓ entrevistado
- ✓ esgoto
- ✓ moradia
- ✓ nr_comodos
- ✓ nr_veiculos
- ✓ observacoes
- ✓ seguro_desemprego
- ✓ agua

---

## 📋 Todas as Colunas que Faltam

| Coluna | Tipo | Descrição | Status |
|--------|------|-----------|--------|
| bolsa_familia | TINYINT(1) | Flag: Recebe Bolsa Família | ❌ Faltando |
| auxilio_brasil | TINYINT(1) | Flag: Recebe Auxílio Brasil | ❌ Faltando |
| bpc | TINYINT(1) | Flag: Recebe BPC | ❌ Faltando |
| auxilio_emergencial | TINYINT(1) | Flag: Recebe Auxílio Emergencial | ❌ Faltando |
| seguro_desemprego | TINYINT(1) | Flag: Recebe Seguro Desemprego | ❌ Faltando |
| aposentadoria | TINYINT(1) | Flag: Recebe Aposentadoria | ❌ Faltando |
| agua | TINYINT(1) | Tem acesso a água | ❌ Faltando |
| esgoto | TINYINT(1) | Tem acesso a esgoto | ❌ Faltando |
| energia | TINYINT(1) | Tem acesso a energia elétrica | ❌ Faltando |
| moradia | VARCHAR(100) | Tipo de moradia (Casa, Apartamento, etc) | ❌ Faltando |
| cond_residencia | VARCHAR(100) | Condição da moradia | ❌ Faltando |
| nr_comodos | INT | Número de cômodos | ❌ Faltando |
| nr_veiculos | INT | Número de veículos | ❌ Faltando |
| entrevistado | VARCHAR(255) | Nome do entrevistado | ❌ Faltando |
| observacoes | TEXT | Observações gerais | ❌ Faltando |

---

## 🆘 Troubleshooting

### Erro: "Column already exists"
✓ Normal! Significa que a coluna já foi adicionada.
- Ignore este erro e continue com o próximo comando.

### Erro: "Access Denied for user"
- Você não tem permissão para ALTER TABLE
- Peça ao administrador do banco para executar os comandos

### Erro: "Unknown table 'Ficha_Socioeconomico'"
1. Verifique se está no banco `criancafeliz` correto
2. Execute: `SHOW TABLES;` para listar tabelas disponíveis
3. Verifique se o nome é exatamente `Ficha_Socioeconomico` (case-sensitive)

### A página ainda mostra erro
1. Limpe o cache do navegador: **Ctrl+Shift+Delete** (Chrome)
2. Refresque a página: **Ctrl+F5** ou **Cmd+Shift+R** (Mac)
3. Verifique os logs: `php_errors.log`
4. Confirme as colunas foram criadas com a query de verificação acima

---

## 🚀 Próximos Passos

1. **Execute a migração SQL** (copie/cole no phpMyAdmin)
2. **Verifique** com a query acima que todas as colunas existem
3. **Refresque** o navegador: `http://localhost/CriancaFeliz/socioeconomico_list.php`
4. **Teste** visitando a lista de fichas socioeconômicas

Se ainda receber erro, verifique:
- Que executou ALL os comandos ALTER TABLE
- Que o banco está correto (`criancafeliz`)
- Os logs em `php_errors.log` para mais detalhes

---

## 📝 Notas

- O aplicativo foi atualizado para ser **resiliente a colunas faltantes**
- Se mesmo depois da migração faltarem colunas, o app usará valores padrão (0 ou vazio)
- Mas é recomendado executar TODOS os comandos para garantir compatibilidade completa

---

## ✨ Arquivo de Migração

Se preferir, está pronto em: `database/migration_ficha_socioeconomico_completo.sql`

Este arquivo contém:
- Verificação inicial de colunas
- Todos os 15 comandos ALTER TABLE
- Verificação final de colunas criadas
- Contagem de fichas na tabela
