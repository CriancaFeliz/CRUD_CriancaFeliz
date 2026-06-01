# Documentação Técnica - Sistema Criança Feliz

Atualizado em 2026-06-01.

Este documento descreve a organização atual do projeto `CriancaFeliz/CRUD_CriancaFeliz`, com foco em arquitetura, fluxo de requisição, rotas, módulos, banco de dados, scripts auxiliares e pontos de atenção para manutenção.

## 1. Visão Geral

O Sistema Criança Feliz é uma aplicação web em PHP para gestão de atendidos da Associação Criança Feliz. A aplicação usa uma arquitetura MVC simples, sem framework externo, com persistência principal em MySQL/MariaDB via PDO.

O projeto hoje opera com:

- `index.php` como front controller e roteador central;
- `.htaccess` para redirecionar rotas inexistentes para `index.php` no Apache;
- `var/dev-router.php` para roteamento equivalente no servidor embutido do PHP;
- controllers em `app/Controllers`;
- services em `app/Services`;
- models em `app/Models`;
- views em `app/Views`;
- assets públicos em `css`, `js` e `img`;
- SQL, migrações e diagnósticos em `database`;
- ambiente Docker local com PHP/Apache, MySQL e phpMyAdmin;
- scripts auxiliares em `tools`;
- testes manuais em `tests/manual`.

## 2. Estrutura de Pastas

```text
.
|-- app/
|   |-- Config/          Configurações de aplicação e banco.
|   |-- Controllers/     Controllers MVC.
|   |-- Helpers/         Helpers transversais.
|   |-- Models/          Persistência MySQL.
|   |-- Services/        Regras de negócio.
|   `-- Views/           Layouts e telas.
|-- assets/
|   `-- samples/         Arquivos de amostra para testes manuais.
|-- css/                 Estilos globais e específicos.
|-- data/                Arquivos runtime locais, como tokens de reset.
|-- database/            Setup, migrações e diagnóstico de banco.
|-- docker/              Scripts de inicialização do MySQL em container.
|-- docs/                Documentação técnica.
|-- img/                 Imagens públicas.
|-- js/                  Scripts de interface.
|-- tests/
|   `-- manual/          Testes manuais PHP.
|-- tools/
|   |-- diagnostics/     Scripts de diagnóstico e debug.
|   |-- legacy/          Protótipos/arquivos antigos.
|   `-- maintenance/     Scripts administrativos e correções pontuais.
|-- var/
|   |-- logs/            Logs locais não versionáveis.
|   `-- dev-router.php   Roteador para `php -S`.
|-- Dockerfile           Imagem PHP/Apache da aplicação.
|-- docker-compose.yml   Ambiente local com app, MySQL e phpMyAdmin.
|-- .htaccess            Rewrite para o front controller no Apache.
|-- index.php            Front controller e roteador central.
`-- README.md            Guia principal do repositório.
```

## 3. Inicialização

O bootstrap fica em `app/bootstrap.php` e executa:

- abertura de sessão PHP;
- definição de constantes (`BASE_PATH`, `APP_PATH`, `DATA_PATH`, `CSS_PATH`, `JS_PATH`, `IMG_PATH`);
- headers básicos de segurança em requisições não AJAX;
- configuração de erros por `APP_DEBUG`;
- autoload simples para Config, Controllers, Models, Services e Helpers;
- preparação de variáveis de log para triggers MySQL quando há usuário logado;
- helpers globais como `sanitizeInput`, `validateEmail`, `validatePassword`, `formatDateToDb`, `formatDateToBr`, `calculateAge`, `getFaixaEtaria`, `isLoggedIn`, `redirect`, `view`, `layout` e `old`;
- criação da pasta `data/` se ela não existir.

## 4. Fluxo de Requisição

1. O navegador acessa uma URL como `/dashboard.php`.
2. No Apache, `.htaccess` envia arquivos inexistentes para `index.php`. No servidor embutido, `var/dev-router.php` faz o mesmo.
3. `index.php` remove query string, calcula a rota relativa e decide se a rota é pública ou protegida.
4. Rotas públicas carregam `AuthController` para login, recuperação ou reset de senha.
5. Rotas protegidas exigem sessão via `isLoggedIn()`.
6. O roteador instancia o controller correspondente.
7. O controller valida permissões, chama services/models e renderiza uma view ou JSON.

## 5. Configuração

O banco é configurado em `app/Config/Database.php`.

Padrões locais:

| Parâmetro | Valor |
| --- | --- |
| Host | `localhost` |
| Banco | `criancafeliz` |
| Usuário | `root` |
| Senha | vazia |
| Charset | `utf8mb4` |

Variáveis de ambiente suportadas:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_CHARSET`
- `APP_DEBUG`

`APP_DEBUG` vem ativo por padrão quando a variável não existe. Em produção, use `APP_DEBUG=false`.

O projeto exige a extensão `pdo_mysql`. Quando ela não está habilitada, a aplicação lança uma mensagem explícita.

No Docker Compose, essas variáveis são definidas no serviço `app` com `DB_HOST=db`, `DB_NAME=criancafeliz`, `DB_USER=criancafeliz` e `DB_PASS=criancafeliz`.

## 6. Execução Local

Com Apache/XAMPP/Laragon:

1. Coloque a pasta no webroot.
2. Habilite `mod_rewrite`.
3. Mantenha `.htaccess` na raiz.
4. Acesse a pasta pelo navegador.

Com servidor embutido do PHP:

```bash
php -S localhost:8000 var/dev-router.php
```

Com Docker Compose:

```bash
docker compose up --build
```

Serviços expostos:

| Serviço | Acesso |
| --- | --- |
| Aplicação | `http://localhost:8080/` |
| phpMyAdmin | `http://localhost:8081/` |
| MySQL pelo host | `localhost:3307` |

Na primeira inicialização, o MySQL executa os scripts em `docker/mysql/` e importa `database/SETUP_COMPLETO_FINAL.sql`. O volume `db_data` mantém o banco entre execuções; use `docker compose down -v` apenas quando quiser recriar o banco do zero.

## 7. Rotas Públicas

| Rota | Controller/método | Função |
| --- | --- | --- |
| `/`, `/index.php`, `/login`, `/login.php` | `AuthController::showLogin` ou `processLogin` | Login. |
| `/forgot.php` | `AuthController::showForgotPassword` ou `processForgotPassword` | Gera token de recuperação. |
| `/reset_password.php?token=...` | `AuthController::showResetPassword` ou `processResetPassword` | Redefine senha usando token. |

Observação: o envio SMTP real ainda não está implementado. O método atual registra a URL de recuperação no log do PHP e salva hashes de tokens em `data/reset_tokens.json`.

## 8. Rotas Protegidas

| Rota | Controller/método | Função |
| --- | --- | --- |
| `/logout.php` | `AuthController::logout` | Encerra sessão. |
| `/dashboard.php` | `DashboardController::index` | Dashboard. |
| `/dashboard.php?action=getCalendarNotes` | `DashboardController::getCalendarNotes` | Lista notas do calendário em JSON. |
| `/dashboard.php?action=saveCalendarNote` | `DashboardController::saveCalendarNote` | Salva nota do calendário. |
| `/dashboard.php?action=deleteCalendarNote` | `DashboardController::deleteCalendarNote` | Remove nota do calendário. |
| `/prontuarios.php` | `ProntuarioController::index` | Busca/consulta de prontuários. |
| `/acolhimento_list.php` | `AcolhimentoController::index` | Lista fichas de acolhimento. |
| `/acolhimento_list.php?action=export` | `AcolhimentoController::export` | Exporta acolhimento em CSV. |
| `/acolhimento_list.php?action=stats` | `AcolhimentoController::stats` | Estatísticas de acolhimento. |
| `/acolhimento_form.php` | `AcolhimentoController::create` ou `store` | Cria/edita acolhimento. |
| `/acolhimento_search.php?q=...` | `AcolhimentoController::search` | Busca AJAX. |
| `/acolhimento_view.php?id=...` | `AcolhimentoController::show` | Visualiza ficha. |
| `/socioeconomico_list.php` | `SocioeconomicoController::index` | Lista fichas socioeconômicas. |
| `/socioeconomico_list.php?action=export` | `SocioeconomicoController::export` | Exporta CSV. |
| `/socioeconomico_list.php?action=stats` | `SocioeconomicoController::stats` | Estatísticas. |
| `/socioeconomico_list.php?action=report` | `SocioeconomicoController::report` | Relatório. |
| `/socioeconomico_form.php` | `SocioeconomicoController::create` ou `store` | Cria/edita ficha. |
| `/socioeconomico_view.php?id=...` | `SocioeconomicoController::show` | Visualiza ficha. |
| `/faltas.php` | `FaltasController::index` | Frequência diária. |
| `/faltas.php?action=oficina` | `FaltasController::oficina` | Frequência por oficina. |
| `/faltas.php?action=historico&id=...` | `FaltasController::historico` | Histórico do atendido. |
| `/faltas.php?action=alertas` | `FaltasController::alertas` | Alertas de faltas. |
| `/faltas.php?action=salvarDia` | `FaltasController::salvarDia` | Endpoint AJAX. |
| `/faltas.php?action=salvarOficina` | `FaltasController::salvarOficina` | Endpoint AJAX. |
| `/faltas.php?action=gerenciarOficinas` | `FaltasController::gerenciarOficinas` | Administração de oficinas. |
| `/desligamento.php` | `DesligamentoController::index` | Lista desligamentos. |
| `/desligamento.php?action=novo&id=...` | `DesligamentoController::novo` | Novo desligamento. |
| `/desligamento.php?action=salvar` | `DesligamentoController::salvar` | Salva desligamento. |
| `/desligamento.php?action=reativar` | `DesligamentoController::reativar` | Reativa atendido. |
| `/desligamento.php?action=automatico` | `DesligamentoController::automatico` | Processa desligamentos automáticos. |
| `/psychology.php` | `PsychologyController::index` | Dashboard psicológico. |
| `/psychology.php?action=patients` | `PsychologyController::patients` | Lista pacientes. |
| `/psychology.php?action=patient&cpf=...` | `PsychologyController::patient` | Detalhe do paciente. |
| `/psychology.php?action=save_note` | `PsychologyController::saveNote` | Salva anotação. |
| `/psychology.php?action=get_note&id=...` | `PsychologyController::getNote` | Busca anotação. |
| `/psychology.php?action=update_note` | `PsychologyController::updateNote` | Atualiza anotação. |
| `/psychology.php?action=delete_note&id=...` | `PsychologyController::deleteNote` | Remove anotação. |
| `/users.php` | `UserController::index` | Lista usuários. |
| `/users.php?action=create` | `UserController::create` ou `store` | Cria usuário. |
| `/users.php?action=edit&id=...` | `UserController::edit` ou `update` | Edita usuário. |
| `/users.php?action=delete&id=...` | `UserController::delete` | Exclui usuário. |
| `/users.php?action=toggle_status&id=...` | `UserController::toggleStatus` | Ativa/desativa usuário. |
| `/logs.php` | `LogController::index` | Auditoria. |
| `/logs.php?action=search` | `LogController::search` | Busca avançada. |
| `/logs.php?action=show&id=...` | `LogController::show` | Detalhe de log. |
| `/logs.php?action=export` | `LogController::export` | Exporta CSV. |
| `/profile.php` | `ProfileController::index` | Perfil. |
| `/profile.php?action=updatePhoto` | `ProfileController::updatePhoto` | Atualiza foto. |
| `/profile.php?action=updatePassword` | `ProfileController::updatePassword` | Atualiza senha. |

## 9. Controllers

### `BaseController`

Fornece renderização de views/layouts, redirecionamentos com flash, JSON, leitura de dados `GET`/`POST`, CSRF, upload simples, detecção AJAX e tratamento de exceções.

### `AuthController`

Controla login, logout, recuperação de senha, reset por token e troca de senha do usuário logado.

### `DashboardController`

Monta estatísticas, alertas e calendário. Usa a tabela `agenda` para notas/avisos do calendário.

### `ProntuarioController`

Centraliza consulta de atendidos e dados consolidados para prontuários.

### `AcolhimentoController`

Gerencia listagem, criação, edição, visualização, exclusão segura por POST/CSRF, busca, exportação e estatísticas de fichas de acolhimento.

### `SocioeconomicoController`

Gerencia formulário multi-etapas, cálculo de renda, família, despesas, visualização, exclusão, exportação, estatísticas e relatório socioeconômico.

### `FaltasController`

Controla frequência diária e por oficina, histórico, alertas e configuração de oficinas. A configuração de oficinas exige permissão administrativa.

### `DesligamentoController`

Controla desligamento manual, reativação e processamento automático de desligamentos por faltas. As ações de alteração exigem permissão administrativa.

### `PsychologyController`

Controla a área psicológica, incluindo pacientes e anotações. Os métodos `saveAssessment`, `search` e `report` ainda retornam erro de método não implementado.

### `UserController`

CRUD de usuários, ativação/desativação e proteção contra autodesativação/autoexclusão.

### `ProfileController`

Perfil do usuário, foto local e troca de senha.

### `LogController`

Auditoria do sistema, filtros, detalhe, exportação, APIs JSON e limpeza de logs antigos. O construtor restringe acesso ao perfil `admin`.

## 10. Services

| Service | Responsabilidade |
| --- | --- |
| `AuthService` | Login, sessão, autorização, permissões, registro, perfil e senha. |
| `UserService` | Criação, edição, exclusão, status e estatísticas de usuários. |
| `AcolhimentoService` | Validação, CRUD, busca, CSV e logs JSON de acolhimento. |
| `SocioeconomicoService` | Validação, CRUD, cálculo de renda, relatórios e CSV. |
| `PsychologyService` | Pacientes, anotações psicológicas e estatísticas da área. |

## 11. Models

| Model | Tabela/função principal |
| --- | --- |
| `BaseModel` | CRUD genérico via PDO. |
| `User` | `Usuario`, autenticação e usuários. |
| `Acolhimento` | `Atendido` e `Responsavel`. |
| `Socioeconomico` | `Ficha_Socioeconomico`, `Familia`, `Despesas` e `Atendido`. |
| `FrequenciaDia` | `Frequencia_Dia`. |
| `FrequenciaOficina` | `Frequencia_Oficina`. |
| `Oficina` | `Oficina`. |
| `Desligamento` | `Desligamento` e status de `Atendido`. |
| `PsychologyNote` | `anotacao_psicologica`. |
| `Log` | `log`. |

## 12. Views

| Pasta | Conteúdo |
| --- | --- |
| `layouts` | Layout autenticado e layout de autenticação. |
| `auth` | Login, esqueci senha e reset. |
| `dashboard` | Dashboard principal. |
| `prontuarios` | Busca e detalhe consolidado. |
| `acolhimento` | Lista, formulário e visualização. |
| `socioeconomico` | Lista, formulário multi-etapas, visualização e relatório. |
| `faltas` | Frequência diária, oficina, histórico, alertas e oficinas. |
| `desligamento` | Lista, seleção e novo desligamento. |
| `psychology` | Dashboard, pacientes e detalhe/anotações. |
| `users` | Lista, criação e edição de usuários. |
| `profile` | Perfil. |
| `logs` | Auditoria, busca, histórico e detalhe. |

## 13. JavaScript e CSS

JavaScript:

- `js/script.js`: scripts globais.
- `js/chatbot.js`: assistente integrado.
- `js/theme-toggle.js`: alternância de tema.
- `js/notifications.js`: notificações visuais.
- `js/acolhimento-form.js`: comportamento do formulário de acolhimento.
- `js/acolhimento-multistep.js`: fluxo multi-etapas de acolhimento.
- `js/socioeconomico-multistep.js`: fluxo multi-etapas socioeconômico.

CSS:

- `css/style.css`: layout principal, login, formulários, tabelas, tema claro/escuro, responsividade e classes reutilizáveis.
- `css/acolhimento-form.css`: ajustes específicos do formulário de acolhimento.

## 14. Banco de Dados

Arquivos principais:

- `database/SETUP_COMPLETO_FINAL.sql`: setup completo com tabelas, índices, foreign keys, triggers/procedures e dados iniciais.
- `database/migration.sql`: schema alinhado ao setup, útil como referência de estrutura.
- `database/update_schema.sql`: migração pontual para remover estruturas obsoletas e recriar triggers.
- `database/migrate.php`: executor PHP de migrações.
- `database/test_connection.php`: diagnóstico de conexão.
- `database/legacy_dumps/`: dumps antigos preservados.
- `docker/mysql/01-init.sh`: importação do setup completo no container MySQL.
- `docker/mysql/02-missing-views.sql`: view complementar aplicada após o setup no Docker.

Tabelas relevantes:

- `usuario` / `Usuario`
- `atendido` / `Atendido`
- `responsavel` / `Responsavel`
- `ficha_socioeconomico` / `Ficha_Socioeconomico`
- `familia` / `Familia`
- `despesas` / `Despesas`
- `frequencia_dia` / `Frequencia_Dia`
- `frequencia_oficina` / `Frequencia_Oficina`
- `oficina` / `Oficina`
- `desligamento` / `Desligamento`
- `agenda`
- `log`
- `anotacao_psicologica`

Ponto de atenção: há mistura de caixa alta/baixa entre scripts e código. Em ambientes Linux com `lower_case_table_names=0`, isso pode causar falhas. Antes de produção, normalize os nomes ou valide a configuração do MySQL/MariaDB.

O setup atual cria `anotacao_psicologica` com chaves estrangeiras para `atendido` e `usuario`. Em bancos existentes, `database/update_schema.sql` oficializa a tabela.

No ambiente Docker, o serviço MySQL é iniciado com `lower_case_table_names=1` para reduzir problemas locais causados por variação de maiúsculas/minúsculas. Isso não elimina a pendência de normalizar o schema antes de produção Linux.

## 15. Scripts Auxiliares

Diagnósticos em `tools/diagnostics/`:

- `check_ficha_columns.php`
- `debug_buttons.php`
- `debug_edit_socio.php`
- `debug_renda_calculation.php`
- `debug_renda_list.php`
- `debug_socio_batch.php`
- `debug_socio_ficha.php`
- `diagnostico_login.php`
- `check_table_case.php`

Manutenção em `tools/maintenance/`:

- `ativar_usuarios.php`
- `corrigir_renda_marina.php`
- `fix_renda_marina.php`
- `fix_users.php`
- `fix_users_mysql.php`
- `generate_password.php`
- `install_database.php`
- `limpar_sessao.php`

Legado:

- `tools/legacy/users_simple.php`

Testes:

- `tests/run.php`
- `tests/automated/PasswordHelperTest.php`
- `tests/automated/BootstrapHelperTest.php`
- `tests/manual/test_psychology.php`
- `tests/manual/test_psychology_edit_delete.php`
- `tests/manual/test_socioeconomico_submit.php`
- `tests/manual/test_users.php`

## 16. Segurança e Permissões

Perfis atuais em `AuthService`:

- `admin`: acesso administrativo amplo, exceto permissões da área psicológica.
- `psicologo`: acesso à área psicológica e anotações.
- `funcionario`: acesso básico de consulta geral.

Rotas sensíveis:

- usuários e configuração de oficinas exigem perfil administrativo;
- logs exigem perfil `admin` diretamente no `LogController`;
- área psicológica exige permissões psicológicas;
- exclusões de acolhimento e socioeconômico devem ocorrer por POST com CSRF.
- novas senhas passam por `PasswordHelper`, com mínimo de 12 caracteres, bloqueio de senhas padrão/comuns, Argon2id quando disponível e fallback bcrypt com custo 12.
- tokens de recuperação são gravados como SHA-256 do token, não como token puro.
- upload de foto e documentos exige CSRF, validação de extensão/MIME e grava em `uploads/`.
- logs detalhados de debug devem ficar condicionados a `APP_DEBUG=true`.

## 17. Pontos de Atenção Atuais

Prioridade alta:

- Configurar envio SMTP real no fluxo de recuperação de senha.
- Executar plano LGPD e retenção/descarte de documentos.
- Normalizar nomes de tabelas para evitar problemas em Linux.

Concluídos nesta revisão:

- Redirecionar o legado `attendance.php` para as rotas atuais de `faltas.php` e `desligamento.php`.
- Promover `anotacao_psicologica` para o setup/migração oficial.
- Bloquear acesso web direto a `tools/`, `database/`, `data/`, `var/` e `docker/` no Apache.
- Endurecer política e armazenamento de senhas para Argon2id quando disponível, com fallback bcrypt.
- Criar testes automatizados mínimos e CI.
- Persistir foto de perfil no banco.
- Incluir anexos de documentos no prontuário.
- Criar documentação das lacunas, LGPD, banco, relatórios e rastreabilidade.

Prioridade média:

- Expandir testes automatizados para integração com banco.
- Revisar endpoints psicológicos não implementados (`saveAssessment`, `search`, `report`).
- Migrar tokens de reset de senha para banco ou serviço dedicado se o fluxo for usado em produção.
- Criar um roteador mais declarativo para reduzir o tamanho de `index.php`.

Prioridade baixa:

- Padronizar idioma de nomes internos.
- Adotar Composer/autoload PSR-4 se o projeto crescer.
- Comprimir imagens grandes em `img/`.

## 18. Como Validar Localmente

1. Conferir PHP:

```bash
php -v
php -m
```

2. Confirmar que `pdo_mysql` aparece na lista de módulos.

3. Criar e importar o banco:

```bash
mysql -u root -e "CREATE DATABASE criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root criancafeliz < database/SETUP_COMPLETO_FINAL.sql
```

4. Subir servidor local:

```bash
php -S localhost:8000 var/dev-router.php
```

5. Acessar `http://localhost:8000/`.

6. Login inicial:

- email: `admin@criancafeliz.org`
- senha: `AlterarEstaSenha!2026`

7. Fluxo mínimo recomendado:

- login;
- dashboard;
- prontuários;
- listar/cadastrar acolhimento;
- listar/cadastrar socioeconômico;
- registrar faltas;
- abrir perfil;
- como admin, gerenciar usuários e logs;
- como psicólogo, acessar área psicológica.

Validação equivalente com Docker:

```bash
docker compose up --build
```

Depois acesse `http://localhost:8080/` e `http://localhost:8081/`. Para reiniciar o banco inicial em container:

```bash
docker compose down -v
docker compose up --build
```

## 19. Validações Desta Atualização

Nesta revisão de documentação foram checados:

- estado do git e remoto do repositório;
- lista real de arquivos versionados;
- front controller `index.php`;
- `.htaccess` e `var/dev-router.php`;
- controllers, models e services existentes;
- usuário inicial e senha do setup SQL;
- documentação principal, setup do banco, manutenção/testes e relação de alterações.
- arquivos Docker e fluxo de inicialização em containers.
