# Documentacao tecnica completa - Sistema Crianca Feliz

Atualizado em 2026-05-20.

Este documento descreve a organizacao atual do projeto, o papel de cada pagina publica, cada controller, service, model, view e script auxiliar. Tambem registra os erros encontrados, as correcoes aplicadas nesta organizacao e as melhorias recomendadas para as proximas etapas.

## 1. Visao geral

O projeto e um sistema web em PHP para a Associacao Crianca Feliz. Ele usa uma estrutura MVC simples sem framework externo:

- arquivos PHP na raiz funcionam como pontos de entrada/roteadores publicos;
- `bootstrap.php` inicia sessao, constantes, headers de seguranca, autoload e helpers globais;
- `app/Controllers` recebe as requisicoes e decide qual view renderizar;
- `app/Services` concentra regras de negocio;
- `app/Models` acessa MySQL e, em alguns pontos legados, arquivos JSON;
- `app/Views` contem telas HTML/PHP;
- `css`, `js` e `img` guardam assets publicos.

O modo principal de persistencia e MySQL. Existem classes e arquivos JSON legados, mas a maior parte do sistema atual depende das tabelas MySQL.

## 2. Estrutura de pastas

```text
.
|-- app/
|   |-- Config/          Configuracoes de aplicacao e banco.
|   |-- Controllers/     Camada de entrada MVC.
|   |-- Helpers/         Helpers transversais.
|   |-- Models/          Persistencia MySQL/JSON.
|   |-- Services/        Regras de negocio.
|   `-- Views/           Layouts e telas.
|-- assets/
|   `-- samples/         Arquivos de amostra usados em testes manuais.
|-- css/                 Estilos globais e estilos especificos.
|-- data/                JSONs e logs operacionais legados.
|-- database/            Scripts SQL, migracoes e documentacao de banco.
|-- docs/
|   `-- archive/         Documentos antigos preservados.
|-- img/                 Imagens usadas pelas telas publicas.
|-- js/                  Scripts de interface.
|-- tests/
|   `-- manual/          Testes manuais PHP movidos da raiz.
|-- tools/
|   |-- diagnostics/     Scripts de diagnostico e debug.
|   |-- legacy/          Prototipos/arquivos antigos.
|   `-- maintenance/     Scripts administrativos e correcao pontual.
`-- var/
    `-- logs/            Logs locais nao versionaveis.
```

## 3. Inicializacao e fluxo de requisicao

1. O navegador acessa um arquivo da raiz, por exemplo `dashboard.php`.
2. O arquivo carrega `bootstrap.php`.
3. O roteador da pagina instancia um controller.
4. O controller valida autenticacao/permissao pelo `AuthService`.
5. O controller chama um service quando ha regra de negocio.
6. O service chama um model para ler/gravar dados.
7. O controller renderiza uma view diretamente ou dentro de um layout.

`bootstrap.php` define:

- `BASE_PATH`, `APP_PATH`, `DATA_PATH`, `CSS_PATH`, `JS_PATH`, `IMG_PATH`;
- headers basicos de seguranca para requisicoes nao AJAX;
- autoload simples para Config, Controllers, Models, Services e Helpers;
- helpers globais: `sanitizeInput`, `validateEmail`, `validatePassword`, `isLoggedIn`, `redirect`, `view`, `layout`, `old`;
- exibicao de erros controlada por `APP_DEBUG`.

## 4. Configuracao

O banco fica em `app/Config/Database.php`.

Padroes atuais:

- host: `localhost`
- banco: `criancafeliz`
- usuario: `root`
- senha: vazia
- charset: `utf8mb4`

Tambem podem ser usados ambientes:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_CHARSET`
- `APP_DEBUG`

Em producao, recomenda-se definir `APP_DEBUG=false`.

## 5. Paginas publicas da raiz

| Arquivo | URL esperada | Controller/metodo | View principal | Funcao | Permissao |
|---|---|---|---|---|---|
| `index.php` | `/index.php` | `AuthController::showLogin` ou `processLogin` | `auth/login` | Login do sistema | Publica |
| `logout.php` | `/logout.php` | `AuthController::logout` | N/A | Encerra sessao | Logado |
| `dashboard.php` | `/dashboard.php` | `DashboardController::index` | `dashboard/index` | Painel inicial, calendario, alertas e indicadores | Logado |
| `dashboard.php?action=getCalendarNotes` | mesma | `DashboardController::getCalendarNotes` | JSON | Lista anotacoes do calendario | Logado |
| `dashboard.php?action=saveCalendarNote` | mesma | `DashboardController::saveCalendarNote` | JSON | Salva anotacao/aviso do calendario | Logado |
| `dashboard.php?action=deleteCalendarNote` | mesma | `DashboardController::deleteCalendarNote` | JSON | Remove anotacao/aviso | Logado |
| `prontuarios.php` | `/prontuarios.php` | `ProntuarioController::index` | `prontuarios/index` | Hub de prontuarios e acesso a fichas | Logado |
| `acolhimento_list.php` | `/acolhimento_list.php` | `AcolhimentoController::index/delete` | `acolhimento/index` | Lista fichas de acolhimento | Logado; excluir exige permissao |
| `acolhimento_form.php` | `/acolhimento_form.php` | `AcolhimentoController::create/store` | `acolhimento/create` | Cria/edita ficha de acolhimento | `create_records` |
| `acolhimento_view.php` | `/acolhimento_view.php?id=...` | `AcolhimentoController::show` | `acolhimento/show` | Visualiza ficha de acolhimento | Logado |
| `acolhimento_search.php` | `/acolhimento_search.php?q=...` | `AcolhimentoController::search` | JSON | Busca AJAX de acolhimento | Logado |
| `socioeconomico_list.php` | `/socioeconomico_list.php` | `SocioeconomicoController::index/delete` | `socioeconomico/index` | Lista fichas socioeconomicas | Logado; excluir exige POST/CSRF |
| `socioeconomico_form.php` | `/socioeconomico_form.php` | `SocioeconomicoController::create/store` | `socioeconomico/create_multistep` | Cria/edita ficha socioeconomica | `create_records` |
| `socioeconomico_view.php` | `/socioeconomico_view.php?id=...` | `SocioeconomicoController::show` | `socioeconomico/show` | Visualiza ficha socioeconomica | Logado |
| `faltas.php` | `/faltas.php` | `FaltasController` | `faltas/*` | Controle de faltas por dia/oficina | Logado; configuracoes exigem admin |
| `desligamento.php` | `/desligamento.php` | `DesligamentoController` | `desligamento/*` | Desligamento manual/automatico e reativacao | Admin |
| `psychology.php` | `/psychology.php` | `PsychologyController` | `psychology/*` | Area psicologica e anotacoes | Psicologo |
| `edit_annotation.php` | `/edit_annotation.php` | Script procedural | HTML proprio | Edicao direta de anotacao psicologica | Psicologo |
| `users.php` | `/users.php` | `UserController` | `users/*` | CRUD de usuarios | Admin |
| `profile.php` | `/profile.php` | `ProfileController` | `profile/index` | Perfil, foto e senha | Logado |
| `logs.php` | `/logs.php` | `LogController` | `logs/*` | Auditoria e logs do sistema | Admin |
| `forgot.php` | `/forgot.php` | `AuthController::showForgotPassword` ou `processForgotPassword` | `auth/forgot` | Solicita recuperacao de senha e gera token local | Publica |
| `reset_password.php` | `/reset_password.php?token=...` | `AuthController::showResetPassword` ou `processResetPassword` | `auth/reset` | Redefine senha usando token de recuperacao | Publica |

## 6. Controllers

### `BaseController`

Classe base usada pelos demais controllers. Responsavel por:

- renderizar views simples e views com layout;
- adicionar dados globais: usuario atual, estado de login e `old_input`;
- redirecionar com flash success/error;
- responder JSON;
- ler dados `POST` e `GET` sanitizados;
- validar CSRF;
- gerar CSRF;
- tratar excecoes;
- identificar AJAX;
- processar upload simples.

Ponto importante: `getPostData()` preserva campos JSON (`familia_json`, `despesas_json`, `despesas`, `familia`) para evitar quebra de `json_decode`.

### `AuthController`

Controla login, logout e recuperacao de senha.

- `showLogin`: mostra tela de login.
- `processLogin`: valida CSRF e credenciais.
- `logout`: encerra sessao.
- `showForgotPassword`, `processForgotPassword`, `showResetPassword`, `processResetPassword`: fluxo MVC com views `auth/forgot` e `auth/reset`; gera tokens locais em `data/reset_tokens.json`, mas o envio SMTP real ainda deve ser configurado para producao.
- `changePassword`: troca senha do usuario logado.

### `DashboardController`

Monta o painel inicial:

- estatisticas de acolhimento e socioeconomico;
- alertas de fichas incompletas/vencidas e faltas;
- anotacoes/avisos do calendario;
- APIs AJAX de calendario.

Foi adicionada normalizacao de estatisticas para aceitar retorno de models JSON e MySQL.

### `ProntuarioController`

Lista prontuarios e exibe detalhes por CPF. Atua como entrada para visualizar dados consolidados de atendidos.

### `AcolhimentoController`

Gerencia fichas de acolhimento:

- listagem paginada;
- cadastro/edicao em formulario;
- visualizacao;
- exclusao com permissao;
- busca AJAX;
- exportacao CSV;
- estatisticas.

Usa `AcolhimentoService`.

### `SocioeconomicoController`

Gerencia fichas socioeconomicas:

- listagem paginada;
- cadastro/edicao multi-etapas;
- visualizacao;
- exclusao com POST e CSRF;
- busca;
- exportacao CSV;
- estatisticas;
- relatorio.

Usa `SocioeconomicoService`.

### `FaltasController`

Modulo principal de frequencia:

- `index`: presenca/falta por dia;
- `oficina`: presenca/falta por oficina;
- `salvarDia`: endpoint AJAX;
- `salvarOficina`: endpoint AJAX;
- `historico`: historico do atendido;
- `alertas`: lista atendidos com faltas relevantes;
- `gerenciarOficinas`, `salvarOficinaConfig`, `toggleOficina`: administracao de oficinas.

Usa `FrequenciaDia`, `FrequenciaOficina`, `Oficina`, `Desligamento`.

### `DesligamentoController`

Gerencia desligamentos:

- lista desligamentos;
- escolhe/cria novo desligamento;
- salva desligamento;
- reativa atendido;
- processa desligamentos automaticos.

Restrito a admin.

### `PsychologyController`

Area psicologica:

- dashboard da area;
- lista pacientes;
- abre paciente por CPF;
- salva, busca, atualiza e exclui anotacoes;
- possui metodos ainda nao implementados para avaliacao, busca e relatorio.

Restrito ao papel `psicologo`.

### `UserController`

CRUD de usuarios:

- lista usuarios;
- cria;
- edita;
- exclui;
- ativa/desativa.

Protege autoexclusao/autodesativacao comparando ids como string para evitar falhas por tipo.

### `ProfileController`

Mostra perfil, atualiza foto e atualiza senha.

### `LogController`

Lista, busca, detalha e exporta logs/auditoria.

## 7. Services

### `AuthService`

Autenticacao e autorizacao.

- Faz login pelo model `User`;
- cria sessao;
- encerra sessao;
- retorna usuario atual;
- valida permissoes por perfil;
- protege rotas com `requireAuth` e `requirePermission`;
- altera senha.

Correcao aplicada: admin agora realmente tem acesso amplo, exceto area psicologica. Antes o comentario dizia isso, mas a implementacao bloqueava permissoes nao listadas, como relatorios/frequencia.

### `UserService`

Orquestra criacao, edicao, exclusao, status e estatisticas de usuarios. Mapeia campos de banco (`idusuario`, `nome`, `nivel`) para o formato usado pelas views (`id`, `name`, `role`).

### `AcolhimentoService`

Valida CPF, datas, CEP, telefone; cria/edita/exclui fichas; busca; exporta CSV; gera logs JSON de acoes.

### `SocioeconomicoService`

Valida dados socioeconomicos; cria/edita/exclui fichas; busca; calcula renda/situacao; gera relatorios e CSV; registra logs JSON.

### `PsychologyService`

Busca pacientes, carrega anotacoes psicologicas, salva/edita/exclui anotacoes, calcula estatisticas da area psicologica.

Correcao aplicada: substituido `str_contains` por `strpos` para manter compatibilidade com PHP 7.4+.

## 8. Models

### Bases

- `BaseModel`: CRUD generico em MySQL via PDO (antigo `BaseModelDB`).

### Usuarios

- `User`: model MySQL da tabela `Usuario`. Autentica por email/senha, cria usuario padrao, lista usuarios sem senha e mapeia campos para o formato das telas.

### Acolhimento

- `Acolhimento`: model MySQL principal para fichas de acolhimento, tabela `Atendido` e `Responsavel` (antigo `AcolhimentoDB`).
  * `data_acolhimento` agora usa a coluna correta antes de cair para `data_cadastro`;
  * estatisticas agora retornam `ativas` e `inativas`, chaves esperadas pelo dashboard.

### Socioeconomico

- `Socioeconomico`: model MySQL principal para `Ficha_Socioeconomico`, `Familia`, `Despesas` e `Atendido` (antigo `SocioeconomicoDB`).
  * `getStatistics()` agora retorna `ativas` e `inativas`.

### Frequencia e oficinas

- `FrequenciaDia`: frequencia por dia (antigo `FrequenciaDiaDB`).
- `FrequenciaOficina`: frequencia por oficina (antigo `FrequenciaOficinaDB`).
- `Oficina`: cadastro e status de oficinas (antigo `OficinaDB`).

### Desligamento

- `Desligamento`: MySQL atual, controla desligamento, reativacao e estatisticas (antigo `DesligamentoDB`).

### Psicologia

- `PsychologyNote`: anotacoes psicologicas no banco.

### Logs

- `Log`: leitura e manipulacao de auditoria (antigo `LogDB`).

## 9. Views

### Layouts

- `layouts/auth.php`: layout do login com imagem e logo.
- `layouts/main.php`: layout principal logado, menu lateral, topbar, flash messages e scripts globais.

### Autenticacao

- `auth/login.php`: formulario de login com CSRF.

### Dashboard

- `dashboard/index.php`: calendario interativo, alertas, cards de estatisticas, anotacoes e avisos. Usa fetch para salvar/listar/remover notas.

### Prontuarios

- `prontuarios/index.php`: entrada para prontuarios.
- `prontuarios/show.php`: detalhe consolidado por atendido.

### Acolhimento

- `acolhimento/index.php`: lista, busca AJAX, paginacao e acoes.
- `acolhimento/create.php`: formulario principal de criacao/edicao.
- `acolhimento/create_old.php`: versao antiga preservada; candidata a arquivamento futuro.
- `acolhimento/show.php`: visualizacao da ficha.

### Socioeconomico

- `socioeconomico/index.php`: lista, filtros, paginacao, acoes seguras.
- `socioeconomico/create_multistep.php`: formulario multi-etapas usado atualmente.
- `socioeconomico/create.php`: formulario antigo/simplificado.
- `socioeconomico/show.php`: visualizacao.

### Faltas moderno

- `faltas/dia.php`: lancamento diario.
- `faltas/oficina.php`: lancamento por oficina.
- `faltas/historico.php`: historico de frequencia.
- `faltas/alertas.php`: alertas de faltas.
- `faltas/gerenciar_oficinas.php`: CRUD/status de oficinas.

### Desligamento

- `desligamento/index.php`: lista.
- `desligamento/selecionar.php`: selecao de atendido.
- `desligamento/novo.php`: formulario.

### Psicologia

- `psychology/index.php`: dashboard da area.
- `psychology/patients.php`: lista de pacientes.
- `psychology/patient.php`: detalhe do paciente e anotacoes.

### Usuarios

- `users/index.php`: lista e acoes de usuario.
- `users/create.php`: cadastro.
- `users/edit.php`: edicao.

### Perfil

- `profile/index.php`: dados do usuario, foto local e troca de senha.

### Logs

- `logs/index.php`: listagem.
- `logs/search.php`: busca.
- `logs/show.php`: detalhe.

## 10. JavaScript e CSS

### JavaScript

- `js/script.js`: scripts globais do sistema.
- `js/chatbot.js`: chatbot/interface auxiliar carregada nos layouts.
- `js/theme-toggle.js`: modo escuro.
- `js/notifications.js`: notificacoes visuais.
- `js/acolhimento-form.js`: validacoes e comportamento do formulario de acolhimento.
- `js/acolhimento-multistep.js`: fluxo multi-etapas de acolhimento.
- `js/socioeconomico-multistep.js`: fluxo multi-etapas, familia, despesas e campos dinamicos socioeconomicos.

### CSS

- `css/style.css`: estilos globais, login, layout principal, formularios, tema escuro e responsividade.
- `css/acolhimento-form.css`: ajustes especificos do formulario de acolhimento.

## 11. Banco de dados

Arquivos principais:

- `criancafeliz.sql` e `banco.sql`: dumps/schemas principais preservados.
- `database/SETUP_COMPLETO_FINAL.sql`: setup completo.
- `database/migration_*.sql`: migracoes incrementais.
- `database/fix_*.sql`: correcoes pontuais.
- `database/triggers_*.sql`: triggers de auditoria/logs.
- `database/migrate.php`: executor de migracoes.
- `database/test_connection.php`: diagnostico de conexao.

Tabelas importantes inferidas pelo codigo:

- `Usuario`
- `Atendido`
- `Responsavel`
- `Ficha_Socioeconomico`
- `Familia`
- `Despesas`
- `Desligamento`
- `Frequencia_Dia`
- `Frequencia_Oficina`
- `Oficina`
- `frequencia`
- `anotacao_psicologica`

## 12. Scripts auxiliares

Os scripts auxiliares foram movidos para reduzir risco de confusao na raiz.

### Diagnosticos

- `tools/diagnostics/check_ficha_columns.php`
- `tools/diagnostics/debug_buttons.php`
- `tools/diagnostics/debug_edit_socio.php`
- `tools/diagnostics/debug_renda_calculation.php`
- `tools/diagnostics/debug_renda_list.php`
- `tools/diagnostics/debug_socio_batch.php`
- `tools/diagnostics/debug_socio_ficha.php`
- `tools/diagnostics/diagnostico_login.php`

### Manutencao

- `tools/maintenance/ativar_usuarios.php`
- `tools/maintenance/corrigir_renda_marina.php`
- `tools/maintenance/fix_renda_marina.php`
- `tools/maintenance/fix_users.php`
- `tools/maintenance/fix_users_mysql.php`
- `tools/maintenance/generate_password.php`
- `tools/maintenance/install_database.php`
- `tools/maintenance/limpar_sessao.php`

### Testes manuais

- `tests/manual/test_psychology.php`
- `tests/manual/test_psychology_edit_delete.php`
- `tests/manual/test_socioeconomico_submit.php`
- `tests/manual/test_users.php`

### Legado

- `tools/legacy/users_simple.php`

## 13. Erros encontrados e corrigidos

1. Dashboard esperava `ativas` e `inativas`, mas os models MySQL retornavam apenas `total`, `porCategoria` e `porStatus`.
   - Corrigido com normalizacao no controller e retorno consistente nos DB models.

2. Admin era bloqueado em permissoes nao listadas, apesar do comentario dizer que admin tinha acesso total exceto psicologia.
   - Corrigido em `AuthService::hasPermission()`.

3. Troca de senha buscava `$user['password']`, mas a coluna MySQL e `Senha`.
   - Corrigido para aceitar `Senha` e fallback `password`.

4. Exclusao em acolhimento/socioeconomico tinha atalhos inseguros ou simulacao de POST.
   - Corrigido para aceitar apenas formulario POST com CSRF.

5. Variavel MySQL `@ip_usuario` era definida com aspas duplicadas/risco de SQL incorreto.
   - Corrigido em `LogHelper` e `logs.php` com prepared statement.

6. `Socioeconomico::searchByName()` chamava `$this->db`, atributo inexistente.
   - Corrigido para chamar `searchAdvanced()`.

7. `PsychologyService` usava `str_contains`, exigindo PHP 8, embora o projeto documente PHP 7.4+.
   - Corrigido para `strpos`.

8. Consultas de frequencia procuravam tabela `usuario`, enquanto o schema usa `Usuario`.
   - Corrigido para `Usuario`.

9. `AcolhimentoDB::getFicha()` exibia `data_cadastro` como `data_acolhimento`.
   - Corrigido para preferir `data_acolhimento`.

10. Comparacoes de usuario atual com id de rota podiam falhar por tipo (`int` versus `string`).
    - Corrigido em protecoes de usuario.

11. Endpoints da area psicologica tinham validacao CSRF inconsistente em edicao/exclusao de anotacoes.
    - Corrigido para exigir autenticacao, permissao, POST e token CSRF.

12. Documentos, debug e scripts de manutencao estavam misturados com rotas publicas.
    - Reorganizado em `docs`, `tools`, `tests`, `assets` e `var`.

13. Configuracao de banco era apenas hardcoded.
    - Adicionado suporte a variaveis de ambiente.

14. `PDO::MYSQL_ATTR_INIT_COMMAND` causava fatal quando o PHP tinha PDO, mas nao tinha `pdo_mysql`.
    - Corrigido com validacao explicita da extensao e uso da constante apenas quando ela existe.

15. `AuthService` abria o model de usuario no construtor, fazendo paginas publicas dependerem do banco antes de qualquer acao.
    - Corrigido com carregamento sob demanda do model.

16. Rotas chamavam views inexistentes: `auth/forgot`, `auth/reset`, `acolhimento/edit`, `socioeconomico/edit`, `socioeconomico/report` e filtros especificos de logs.
    - Corrigido com novas views/rotas ou reaproveitamento das views existentes.

17. Rotas protegidas instanciavam controllers pesados antes de checar sessao.
    - Adicionada verificacao inicial de login nos pontos de entrada principais.

18. Unificação dos módulos `attendance.php` e `faltas.php`, concentrando o controle no banco de dados e removendo as lógicas JSON/legadas obsoletas.

19. Refatoração e limpeza completa dos modelos `Acolhimento`, `Socioeconomico` e `Desligamento`, removendo classes híbridas e arquivos JSON da pasta `data/`.

20. Extração de dezenas de estilos inline (`style="..."`) das views principais para o CSS centralizado, com design glassmorphic premium e suporte a variáveis de tema.

## 14. Problemas ainda existentes ou melhorias recomendadas

Prioridade alta:

- Configurar SMTP real para recuperacao de senha. As rotas e views existem, mas o envio ainda fica registrado em log ate uma integracao de email ser ligada.
- Remover ou proteger scripts de `tools/maintenance` em producao. Eles devem ficar fora do webroot ou exigir autenticacao forte.

Prioridade media:

- Padronizar nomes de tabelas e classes (`Atendido`, `Usuario`, `Ficha_Socioeconomico`) para reduzir problemas em servidores Linux.
- Remover logs de debug verbosos de `SocioeconomicoDB` ou controla-los por `APP_DEBUG`.
- Criar um roteador unico em vez de muitos roteadores pequenos na raiz.
- Criar testes automatizados reais. Os arquivos em `tests/manual` ainda dependem de execucao manual e banco local.
- Adicionar validacao completa de CPF. Hoje a validacao rejeita tamanho e sequencias repetidas, mas nao calcula digitos verificadores.

Prioridade baixa:

- Padronizar idioma de nomes internos (`create/store/index` versus `salvar/novo`).
- Separar assets publicos em uma pasta `public/` em uma versao futura.
- Adicionar Composer/autoload PSR-4 se o projeto crescer.
- Criar uma tela administrativa para rodar migracoes com seguranca, ou remover scripts web de migracao.

## 15. Convencoes sugeridas daqui para frente

- Manter paginas publicas na raiz apenas quando forem rotas reais.
- Colocar scripts temporarios em `tools/diagnostics` ou `tools/maintenance`.
- Colocar testes manuais em `tests/manual`.
- Colocar documentacao nova em `docs/`.
- Mover documentos antigos para `docs/archive/`.
- Evitar links de exclusao por GET; usar POST com CSRF.
- Evitar SQL montado com interpolacao quando houver valor externo.
- Nao deixar scripts de correcao pontual acessiveis publicamente em producao.
- Preferir `App::get...Model()` quando o codigo precisar respeitar o modo de persistencia.

## 16. Como validar localmente

1. Instalar PHP 7.4+ ou 8.x e MySQL/MariaDB.
2. Criar banco `criancafeliz`.
3. Importar `database/SETUP_COMPLETO_FINAL.sql` ou um dump principal validado.
4. Configurar variaveis de ambiente se nao usar `root` sem senha:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
5. Servir a pasta pelo Apache/XAMPP ou equivalente.
6. Acessar `index.php`.
7. Testar fluxo minimo:
   - login;
   - dashboard;
   - listar prontuarios;
   - criar/editar acolhimento;
   - criar/editar socioeconomico;
   - registrar faltas;
   - acessar perfil;
   - para admin, gerenciar usuarios e logs;
   - para psicologo, acessar area psicologica.

## 17. Observacoes de validacao desta rodada

Nao foi possivel executar `php -l` nem subir servidor local porque o executavel `php` nao esta instalado no PATH desta maquina e nao foi encontrado em caminhos comuns de XAMPP/Wamp/Laragon.

Mesmo sem PHP local, foram feitas verificacoes estaticas de referencias, organizacao de arquivos e busca por rotas/caminhos quebrados.
