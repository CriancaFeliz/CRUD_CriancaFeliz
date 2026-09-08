# Setup do Banco de Dados - Criança Feliz

Atualizado em 2026-09-08.

Este guia descreve como preparar o banco MySQL/MariaDB do Sistema Criança Feliz usando o script principal `database/schema_completo.sql`.

## 1. Arquivo Oficial

Use este arquivo para criação, documentação e setup em ambientes novos:

```text
database/schema_completo.sql
```

Ele cria a estrutura principal limpa (16 tabelas ativas, índices, relacionamentos de integridade referencial, triggers de auditoria, view de alertas e oficinas iniciais). Não cria senhas padrão inseguras nem dados fictícios de atendidos.

## 2. Pré-Requisitos

- MySQL 5.7+ ou MariaDB 10.3+.
- Banco com charset `utf8mb4` e collation `utf8mb4_unicode_ci` ou `utf8mb4_general_ci`.
- Usuário com permissão para criar tabelas, índices, foreign keys, triggers e views.
- PHP 8.0+ com extensão `pdo_mysql` habilitada.

## 3. Instalação via Terminal

Crie o banco:

```bash
mysql -u root -e "CREATE DATABASE criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Importe o setup:

```bash
mysql -u root criancafeliz < database/schema_completo.sql
```

Se seu usuário MySQL possuir senha:

```bash
mysql -u root -p criancafeliz < database/schema_completo.sql
```

## 4. Instalação via phpMyAdmin

1. Acesse o phpMyAdmin do seu servidor/hospedagem.
2. Crie ou selecione o banco de dados `criancafeliz_db` (ou `criancafeliz`).
3. Abra a aba **Importar** (ou **SQL**).
4. Carregue e execute o arquivo `schema_completo.sql`.

## 4.1 Instalação via Docker Compose

O repositório inclui ambiente Docker pronto para desenvolvimento e testes:

```bash
docker compose up --build
```

Serviços disponibilizados:

| Serviço | Acesso |
| --- | --- |
| Aplicação | `http://localhost:8080/` |
| phpMyAdmin | `http://localhost:8081/` |
| MySQL pelo host | `localhost:3307` |

Credenciais padrão do Docker de desenvolvimento:

| Item | Valor |
| --- | --- |
| Banco | `criancafeliz` |
| Usuário da aplicação | `criancafeliz` |
| Senha da aplicação | `criancafeliz_dev` |
| Usuário root | `root` |
| Senha root | `root_dev` |

Na primeira criação do volume `db_data`, o MySQL executa `docker/mysql/01-init.sh` e importa automaticamente `database/schema_completo.sql`.

Para recriar o banco do zero no Docker:

```bash
docker compose down -v
docker compose up --build
```

## 5. Criação do Primeiro Administrador

Após configurar as variáveis de ambiente no `.env`, crie o primeiro usuário administrador com senha segura via linha de comando:

```bash
php tools/maintenance/create_admin.php
```

## 6. Tabelas Ativas e Views

O schema foi saneado e contém apenas as 16 tabelas e 1 view ativas consumidas pela aplicação:

| Tabela / View | Tipo | Descrição e Uso |
| --- | --- | --- |
| `usuario` | Tabela | Usuários, login, níveis de acesso e perfis. |
| `atendido` | Tabela | Crianças e adolescentes acolhidos. |
| `responsavel` | Tabela | Responsáveis legais vinculados aos atendidos. |
| `ficha_socioeconomico` | Tabela | Fichas socioeconômicas e dados habitacionais. |
| `familia` | Tabela | Composição familiar vinculada à ficha socioeconômica. |
| `despesas` | Tabela | Rendas e despesas da ficha socioeconômica. |
| `frequencia_dia` | Tabela | Registro de frequência diária geral (P/F/J). |
| `frequencia_oficina` | Tabela | Registro de frequência por oficina (P/F/J). |
| `oficina` | Tabela | Oficinas e atividades socioeducativas. |
| `desligamento` | Tabela | Histórico de desligamentos e reativações. |
| `documento` | Tabela | Documentos e anexos vinculados aos atendidos. |
| `anotacao_psicologica` | Tabela | Prontuários e anotações do atendimento psicológico. |
| `password_reset_tokens` | Tabela | Tokens seguros para recuperação de senha com expiração. |
| `auth_rate_limits` | Tabela | Controle de taxa e proteção contra ataques de força bruta no login. |
| `agenda` | Tabela | Notificações e recados do painel. |
| `log` | Tabela | Auditoria de alterações do sistema. |
| `atendidos_com_alerta` | View | Visão para detecção e alerta de faltas consecutivas/críticas. |

## 7. Auditoria e Triggers

O script configura triggers de auditoria para a ficha socioeconômica:

- `log_ficha_socioeconomico_insert`
- `log_ficha_socioeconomico_update`
- `log_ficha_socioeconomico_delete`

Antes de operações auditadas, a aplicação alimenta as variáveis de contexto da sessão MySQL `@usuario_id` e `@ip_usuario` para rastreabilidade completa.

## 8. Configuração da Aplicação (`.env`)

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=criancafeliz
DB_USER=usuario_da_aplicacao
DB_PASS=senha_forte
DB_CHARSET=utf8mb4
APP_ENV=production
APP_DEBUG=false
```

## 9. Checklist Pós-Setup

- [ ] Banco `criancafeliz` criado com charset `utf8mb4`.
- [ ] `schema_completo.sql` importado com sucesso.
- [ ] Primeiro administrador criado por `php tools/maintenance/create_admin.php`.
- [ ] `APP_DEBUG=false` configurado para produção.
- [ ] Rotas protegidas funcionando e autenticação operacional.
- [ ] Frequências, Acolhimento, Socioeconômico, Psicologia e Relatórios validados.
