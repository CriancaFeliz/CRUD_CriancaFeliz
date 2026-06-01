# Setup do Banco de Dados - Criança Feliz

Atualizado em 2026-06-01.

Este guia descreve como preparar o banco MySQL/MariaDB do Sistema Criança Feliz usando o script principal `database/SETUP_COMPLETO_FINAL.sql`.

## 1. Arquivo Recomendado

Use este arquivo para um ambiente novo:

```text
database/SETUP_COMPLETO_FINAL.sql
```

Ele cria a estrutura principal do sistema, índices, relacionamentos, triggers/procedures e dados iniciais de teste.

## 2. Pré-Requisitos

- MySQL 5.7+ ou MariaDB 10.3+.
- Banco com charset `utf8mb4`.
- Usuário com permissão para criar tabelas, índices, foreign keys, triggers e procedures.
- PHP com `pdo_mysql` habilitado para a aplicação acessar o banco.

## 3. Instalação via Terminal

Crie o banco:

```bash
mysql -u root -e "CREATE DATABASE criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Importe o setup:

```bash
mysql -u root criancafeliz < database/SETUP_COMPLETO_FINAL.sql
```

Se seu usuário MySQL tiver senha:

```bash
mysql -u root -p criancafeliz < database/SETUP_COMPLETO_FINAL.sql
```

## 4. Instalação via phpMyAdmin

1. Abra `http://localhost/phpmyadmin`.
2. Crie o banco `criancafeliz` com charset/collation `utf8mb4`.
3. Selecione o banco.
4. Abra a aba SQL ou Importar.
5. Execute o arquivo `SETUP_COMPLETO_FINAL.sql`.

## 4.1 Instalação via Docker Compose

O repositório também inclui um ambiente Docker com aplicação, MySQL e phpMyAdmin:

```bash
docker compose up --build
```

Serviços:

| Serviço | Acesso |
| --- | --- |
| Aplicação | `http://localhost:8080/` |
| phpMyAdmin | `http://localhost:8081/` |
| MySQL pelo host | `localhost:3307` |

Credenciais:

| Item | Valor |
| --- | --- |
| Banco | `criancafeliz` |
| Usuário da aplicação | `criancafeliz` |
| Senha da aplicação | `criancafeliz` |
| Usuário root | `root` |
| Senha root | `root` |

Na primeira criação do volume `db_data`, o MySQL executa `docker/mysql/01-init.sh`, importa `database/SETUP_COMPLETO_FINAL.sql` e aplica `docker/mysql/02-missing-views.sql`.

Se o volume já existir, os scripts de inicialização do MySQL não rodam novamente. Para recriar o banco do zero:

```bash
docker compose down -v
docker compose up --build
```

## 5. Dados Iniciais

Usuário administrador:

| Campo | Valor |
| --- | --- |
| Email | `admin@criancafeliz.org` |
| Senha | `admin123` |
| Perfil | `admin` |
| Status | `Ativo` |

O script também inclui responsáveis, atendidos e oficinas de exemplo.

## 6. Tabelas Principais

O setup cria tabelas para:

- agenda/notificações;
- atendidos;
- responsáveis;
- usuários;
- fichas socioeconômicas;
- família;
- despesas;
- frequência diária;
- frequência por oficina;
- oficinas;
- desligamentos;
- documentos;
- encontros;
- presenças/sessões legadas;
- logs.

As tabelas centrais usadas pela aplicação atual são:

| Tabela lógica | Uso |
| --- | --- |
| `Usuario` / `usuario` | Usuários, login e perfis. |
| `Atendido` / `atendido` | Crianças/adolescentes atendidos. |
| `Responsavel` / `responsavel` | Responsáveis vinculados a atendidos. |
| `Ficha_Socioeconomico` / `ficha_socioeconomico` | Fichas socioeconômicas. |
| `Familia` / `familia` | Membros da família da ficha. |
| `Despesas` / `despesas` | Rendas/despesas da ficha. |
| `Frequencia_Dia` / `frequencia_dia` | Frequência diária. |
| `Frequencia_Oficina` / `frequencia_oficina` | Frequência por oficina. |
| `Oficina` / `oficina` | Oficinas disponíveis. |
| `Desligamento` / `desligamento` | Desligamentos e reativações. |
| `agenda` | Notas/avisos do dashboard. |
| `log` | Auditoria. |
| `anotacao_psicologica` | Anotações da área psicológica, quando presente no schema usado. |

Ponto de atenção: `SETUP_COMPLETO_FINAL.sql` não cria atualmente a tabela `anotacao_psicologica`, embora a área psicológica use essa tabela. A estrutura existe nos dumps em `database/legacy_dumps/` e deve ser promovida para uma migração/setup oficial antes de usar o módulo psicológico em um banco limpo.

## 7. Auditoria

O script configura triggers de log para a ficha socioeconômica:

- `log_ficha_socioeconomico_insert`
- `log_ficha_socioeconomico_update`
- `log_ficha_socioeconomico_delete`

Antes de operações auditadas, a aplicação tenta preencher variáveis de sessão do MySQL:

- `@usuario_id`
- `@ip_usuario`

Essas variáveis são preparadas em `LogHelper` e também em algumas rotas administrativas.

## 8. Arquivos Relacionados

| Arquivo | Uso |
| --- | --- |
| `migration.sql` | Schema alinhado ao setup, útil como base de comparação. |
| `update_schema.sql` | Migração pontual para remover estruturas obsoletas e recriar triggers. |
| `migrate.php` | Executor PHP de migrações. |
| `test_connection.php` | Teste de conexão com o banco. |
| `legacy_dumps/` | Dumps antigos para consulta histórica. |
| `../docker/mysql/01-init.sh` | Script de importação usado pelo MySQL no Docker. |
| `../docker/mysql/02-missing-views.sql` | View complementar aplicada no ambiente Docker. |

## 9. Configuração da Aplicação

As credenciais padrão ficam em `app/Config/Database.php`:

| Parâmetro | Valor padrão |
| --- | --- |
| Host | `localhost` |
| Banco | `criancafeliz` |
| Usuário | `root` |
| Senha | vazia |
| Charset | `utf8mb4` |

Também é possível usar variáveis de ambiente:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=criancafeliz
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
APP_DEBUG=false
```

No Docker Compose, a aplicação usa `DB_HOST=db`, `DB_NAME=criancafeliz`, `DB_USER=criancafeliz` e `DB_PASS=criancafeliz`.

## 10. Pontos de Atenção

- Há variação de maiúsculas/minúsculas entre alguns nomes usados pelo código e pelos scripts SQL (`Usuario`/`usuario`, `Atendido`/`atendido`, etc.).
- Em Windows e em algumas configurações MySQL, isso costuma funcionar por configuração do servidor.
- Em Linux com `lower_case_table_names=0`, nomes diferentes podem quebrar consultas e triggers.
- Antes de produção, normalize os nomes ou valide a configuração do MySQL/MariaDB.
- O MySQL do Docker Compose usa `lower_case_table_names=1` para reduzir conflitos locais de caixa, mas isso não substitui a normalização do schema.
- O schema principal ainda preserva algumas tabelas legadas, como `sessao` e `presenca`, mas o módulo atual de frequência usa `faltas.php`, `Frequencia_Dia` e `Frequencia_Oficina`.
- A tabela `anotacao_psicologica` precisa ser criada a partir de uma migração validada quando o ambiente depender da área psicológica.

## 11. Troubleshooting

Erro: `A extensao pdo_mysql nao esta habilitada neste PHP.`

- Habilite `pdo_mysql` no `php.ini`.
- Reinicie Apache/PHP-FPM/servidor local.
- Rode `php -m` e confirme que `pdo_mysql` aparece.

Erro: `Table already exists`

- O banco já possui tabelas.
- Use um banco limpo para setup completo ou revise o schema antes de reimportar.

Erro: `Foreign key constraint fails`

- Importe o arquivo completo.
- Evite executar trechos isolados fora da ordem.
- Confirme que o engine é InnoDB.

Erro com nomes de tabelas em Linux

- Verifique `lower_case_table_names`.
- Padronize nomes do schema de acordo com as consultas do código.

## 12. Checklist Pós-Setup

- [ ] Banco `criancafeliz` criado.
- [ ] `SETUP_COMPLETO_FINAL.sql` importado sem erros.
- [ ] Usuário `admin@criancafeliz.org` consegue fazer login com `admin123`.
- [ ] `pdo_mysql` habilitado no PHP usado pelo servidor web.
- [ ] `APP_DEBUG=false` configurado em produção.
- [ ] Rotas protegidas redirecionam para login quando não há sessão.
- [ ] Dashboard abre após login.
- [ ] Módulos de acolhimento, socioeconômico, faltas, usuários e logs abrem no ambiente local.
