# 🚀 SETUP COMPLETO - CRIANÇA FELIZ

## 📋 Descrição

Este é o script SQL único e completo para configurar 100% do banco de dados do projeto **Criança Feliz**.

**Arquivo:** `SETUP_COMPLETO_FINAL.sql`

---

## ✅ O que está incluído

### 1. Banco de Dados Base
- ✅ Todas as 16 tabelas necessárias
- ✅ Estrutura completa com colunas corretas
- ✅ Tipos de dados apropriados

### 2. Triggers de Log
- ✅ Trigger INSERT para ficha socioeconômica
- ✅ Trigger UPDATE para ficha socioeconômica (captura apenas mudanças)
- ✅ Trigger DELETE para ficha socioeconômica
- ✅ Logs em JSON com todos os campos

### 3. Índices
- ✅ Índices primários em todas as tabelas
- ✅ Índices de chave estrangeira
- ✅ Índices de performance para buscas frequentes

### 4. Foreign Keys
- ✅ Integridade referencial completa
- ✅ Cascata de deleção onde apropriado
- ✅ Relacionamentos entre tabelas

### 5. Dados Iniciais
- ✅ Usuário admin (senha: admin)
- ✅ 2 responsáveis de exemplo
- ✅ 3 atendidos de exemplo
- ✅ 6 oficinas de exemplo

---

## 🚀 Como Executar

### Passo 1: Abra phpMyAdmin
```
http://localhost/phpmyadmin
```

### Passo 2: Crie o banco de dados (se não existir)
```sql
CREATE DATABASE IF NOT EXISTS criancafeliz;
```

### Passo 3: Selecione o banco
- Clique em **criancafeliz** na esquerda

### Passo 4: Vá para SQL
- Clique na aba **SQL**

### Passo 5: Copie e Cole o Script
1. Abra o arquivo: `SETUP_COMPLETO_FINAL.sql`
2. Copie TODO o conteúdo
3. Cole na caixa de SQL do phpMyAdmin

### Passo 6: Execute
- Clique em **Executar**

---

## ⏱️ Tempo de Execução

- **Tempo esperado:** 5-10 segundos
- **Sem erros:** ✅ Sucesso!

---

## 📊 Tabelas Criadas

| Tabela | Descrição |
|--------|-----------|
| `usuario` | Usuários do sistema |
| `atendido` | Crianças atendidas |
| `responsavel` | Responsáveis pelas crianças |
| `ficha_socioeconomico` | Dados socioeconômicos |
| `familia` | Membros da família |
| `despesas` | Despesas da família |
| `frequencia_dia` | Frequência diária |
| `frequencia_oficina` | Frequência em oficinas |
| `oficina` | Oficinas disponíveis |
| `sessao` | Sessões de atendimento |
| `presenca` | Presença em sessões |
| `desligamento` | Desligamentos |
| `encontro` | Encontros registrados |
| `documento` | Documentos |
| `dias_atendimento` | Dias de atendimento |
| `agenda` | Agenda/notificações |
| `log` | Logs de alterações |

---

## 🔐 Dados de Acesso

### Usuário Admin
- **Email:** admin@criancafeliz.org
- **Senha:** admin
- **Nível:** admin

---

## 📝 Campos da Ficha Socioeconômica

O script cria a tabela `ficha_socioeconomico` com os seguintes campos:

- `idficha` - ID da ficha
- `id_atendido` - Referência ao atendido
- `nome_menor` - Nome do menor
- `entrevistado` - Nome de quem foi entrevistado
- `renda_familiar` - Renda familiar total
- `renda_per_capita` - Renda per capita
- `qtd_pessoas` - Quantidade de pessoas na casa
- `numero_comodos` - Número de cômodos
- `construcao` - Tipo de construção
- `residencia` - Tipo de residência
- `moradia` - Tipo de moradia
- `agua` - Tem água (0/1)
- `esgoto` - Tem esgoto (0/1)
- `energia` - Tem energia (0/1)
- `bolsa_familia` - Recebe Bolsa Família (0/1)
- `auxilio_brasil` - Recebe Auxílio Brasil (0/1)
- `bpc` - Recebe BPC (0/1)
- `auxilio_emergencial` - Recebe Auxílio Emergencial (0/1)
- `seguro_desemprego` - Recebe Seguro Desemprego (0/1)
- `aposentadoria` - Recebe Aposentadoria (0/1)
- `assistente_social` - Nome do assistente social
- `cadunico` - Informação CADÚnico
- `cond_residencia` - Condição da residência
- `nr_veiculos` - Número de veículos
- `observacoes` - Observações gerais

---

## 📊 Triggers de Log

### Trigger INSERT
- Registra quando uma nova ficha é criada
- Captura todos os campos em JSON

### Trigger UPDATE
- Registra quando uma ficha é alterada
- Captura apenas os campos que mudaram
- Mostra valor anterior e novo
- Descrição clara das mudanças

### Trigger DELETE
- Registra quando uma ficha é deletada
- Captura todos os dados antes da deleção

---

## 🔍 Exemplo de Log

```json
{
  "id_log": 1,
  "data_alteracao": "2025-12-09 02:30:00",
  "registro_alt": "Ficha Socioeconômica alterada - Cômodos: 3 → 4 | Renda: R$ 1800 → R$ 2300 |",
  "valor_anterior": {"numero_comodos": 3, "renda_familiar": 1800},
  "valor_atual": {"numero_comodos": 4, "renda_familiar": 2300},
  "acao": "UPDATE",
  "tabela_afetada": "ficha_socioeconomico",
  "id_usuario": 1,
  "id_registro": 6,
  "campo_alterado": "MULTIPLOS_CAMPOS",
  "ip_usuario": "127.0.0.1"
}
```

---

## ✨ Recursos Especiais

### 1. Índices de Performance
- Índices em campos frequentemente buscados
- Índices compostos para queries complexas
- Melhora significativa na velocidade

### 2. Integridade Referencial
- Foreign keys em todas as relações
- Cascata de deleção para dados relacionados
- Evita dados órfãos

### 3. Logs Completos
- Todos os campos monitorados
- Histórico completo de alterações
- Rastreabilidade total

### 4. Dados Iniciais
- Usuário admin pré-configurado
- Dados de exemplo para testes
- Oficinas padrão

---

## 🐛 Troubleshooting

### Erro: "Syntax error"
- Certifique-se de copiar TODO o arquivo
- Verifique se não há caracteres especiais

### Erro: "Table already exists"
- O banco já foi criado
- Você pode executar novamente (usa `CREATE TABLE IF NOT EXISTS`)

### Erro: "Foreign key constraint fails"
- Certifique-se de executar o script completo
- Não delete tabelas manualmente

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique o phpMyAdmin para erros
2. Consulte os logs do MySQL
3. Verifique a integridade do arquivo SQL

---

## 📦 Versão

- **Versão:** 1.0
- **Data:** Dezembro 2025
- **Status:** ✅ Pronto para Produção

---

## ✅ Checklist Pós-Setup

- [ ] Script executado sem erros
- [ ] Banco `criancafeliz` criado
- [ ] Todas as 17 tabelas presentes
- [ ] Triggers funcionando
- [ ] Usuário admin acessível
- [ ] Dados iniciais carregados
- [ ] Testes de CRUD funcionando

---

**Parabéns! Seu banco de dados está 100% configurado e pronto para uso!** 🎉
