# Manutenção, Diagnósticos e Testes Manuais

Atualizado em 2026-06-01.

Este documento reúne os scripts auxiliares do projeto, os cuidados antes de executá-los e um checklist de validação local.

## 1. Visão Geral

O sistema usa `index.php` como front controller. A raiz deve permanecer enxuta: rotas reais, assets públicos, documentação, scripts SQL e ferramentas auxiliares ficam em pastas próprias.

Scripts em `tools/` e `database/` podem revelar informações ou alterar dados. Em produção, mantenha essas pastas fora do webroot ou bloqueie o acesso via configuração do servidor.

Para desenvolvimento local, o projeto pode rodar diretamente no PHP ou pelo Docker Compose incluído no repositório.

## 2. Diagnósticos

Pasta: `tools/diagnostics/`

Use apenas em ambiente local ou homologação.

| Script | Função |
| --- | --- |
| `check_ficha_columns.php` | Verifica colunas esperadas da ficha socioeconômica. |
| `check_table_case.php` | Verifica divergências de caixa em nomes de tabelas usadas pelo código. |
| `debug_buttons.php` | Diagnostica botões/ações da área psicológica. |
| `debug_edit_socio.php` | Diagnostica edição socioeconômica. |
| `debug_renda_calculation.php` | Diagnostica cálculo de renda. |
| `debug_renda_list.php` | Lista dados de renda para conferência. |
| `debug_socio_batch.php` | Diagnóstico em lote de fichas socioeconômicas. |
| `debug_socio_ficha.php` | Diagnóstico individual de ficha socioeconômica. |
| `diagnostico_login.php` | Verifica usuário admin e hash de senha no banco. |

## 3. Manutenção

Pasta: `tools/maintenance/`

Use com backup do banco e preferencialmente fora de produção.

| Script | Função |
| --- | --- |
| `ativar_usuarios.php` | Ativa usuários e exibe credenciais de teste. |
| `corrigir_renda_marina.php` | Correção pontual de renda. |
| `fix_renda_marina.php` | Diagnóstico/correção pontual de renda. |
| `fix_users.php` | Correção de usuários em fluxo legado. |
| `fix_users_mysql.php` | Corrige/cria usuários no MySQL com senha padrão. |
| `generate_password.php` | Gera hash de senha. |
| `install_database.php` | Instalador visual do banco. |
| `limpar_sessao.php` | Limpa sessão local. |
| `migrate_reset_tokens.php` | Migra tokens válidos de `data/reset_tokens.json` para `password_reset_tokens`. |

Senha padrão usada pelos scripts de correção de usuários: `AlterarEstaSenha!2026`, ou o valor da variável `INITIAL_ADMIN_PASSWORD`.

## 4. Banco de Dados

Pasta: `database/`

| Arquivo | Função |
| --- | --- |
| `SETUP_COMPLETO_FINAL.sql` | Setup completo recomendado para ambiente novo. |
| `migration.sql` | Schema alinhado ao setup, útil como referência ou migração base. |
| `update_schema.sql` | Ajustes pontuais de schema e triggers. |
| `migrate.php` | Executor PHP de migrações. |
| `test_connection.php` | Diagnóstico de conexão com o banco. |
| `legacy_dumps/` | Dumps antigos preservados para consulta. |

Ponto de atenção: o código ainda usa nomes de tabelas com variação de caixa (`Atendido`, `atendido`, `Usuario`, `usuario`, etc.). Em servidores Linux com `lower_case_table_names=0`, valide o schema antes de produção.

## 5. Testes Automatizados

Pasta: `tests/automated/`

Execute:

```bash
php tests/run.php
```

Suite completa com Docker, banco descartavel e smoke HTTP:

```powershell
.\tests\run_all.ps1
```

Se o PowerShell bloquear scripts:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tests\run_all.ps1
```

No Linux/macOS/CI:

```bash
./tests/run_all.sh
```

O runner atual cobre helpers de senha, sanitizacao, email, datas e escape CSV de relatorios. A automacao Docker cobre integracao com MySQL, dados iniciais, usuarios, acolhimento, socioeconomico, frequencia, desligamento, documentos, psicologia, permissoes por perfil, upload multipart de foto/documento, smoke HTTP e backup/restauracao do banco de teste.

Veja também `docs/TEST_PLAN.md` e `docs/TEST_AUTOMATION.md`.

## 6. Testes Manuais

Pasta: `tests/manual/`

| Script | Escopo |
| --- | --- |
| `test_psychology.php` | Fluxo básico da área psicológica. |
| `test_psychology_edit_delete.php` | Edição e exclusão de anotações psicológicas. |
| `test_socioeconomico_submit.php` | Envio de ficha socioeconômica. |
| `test_users.php` | Operações de usuários. |

Esses arquivos ainda não são testes automatizados. Eles dependem de banco local, sessão e dados de exemplo.

## 7. Checklist Antes de Rodar Scripts

1. Confirmar backup do banco.
2. Confirmar que o ambiente é local ou homologação.
3. Conferir credenciais em `app/Config/Database.php` ou variáveis `DB_*`.
4. Confirmar que `pdo_mysql` está habilitado.
5. Executar o script uma vez e registrar resultado.
6. Remover, bloquear ou tirar `tools/` do webroot após o uso.

## 8. Validação Local Recomendada

Verificar PHP:

```bash
php -v
php -m
```

Confirmar lint dos arquivos PHP:

```bash
php -l index.php
```

Rodar testes automatizados:

```bash
php tests/run.php
```

Para validar todos os PHPs no PowerShell:

```powershell
Get-ChildItem -Recurse -Filter *.php |
  ForEach-Object { php -l $_.FullName }
```

Subir servidor local:

```bash
php -S localhost:8000 var/dev-router.php
```

Ou subir o ambiente completo com Docker Compose:

```bash
docker compose up --build
```

No Docker, valide:

| Serviço | Resultado esperado |
| --- | --- |
| `http://localhost:8080/` | Tela de login. |
| `http://localhost:8081/` | phpMyAdmin acessível. |
| `localhost:3307` | MySQL acessível pelo host. |

Para recriar o banco inicial do Docker do zero:

```bash
docker compose down -v
docker compose up --build
```

Smoke test manual no navegador:

| URL | Resultado esperado |
| --- | --- |
| `http://localhost:8000/` | Tela de login. |
| `http://localhost:8000/forgot.php` | Tela de recuperação de senha. |
| `http://localhost:8000/reset_password.php?token=invalido` | Redireciona para recuperação com erro. |
| `http://localhost:8000/dashboard.php` sem sessão | Redireciona para login. |
| `http://localhost:8000/faltas.php` sem sessão | Redireciona para login. |

Login inicial após importar `SETUP_COMPLETO_FINAL.sql`:

- email: `admin@criancafeliz.org`
- senha: `AlterarEstaSenha!2026`

## 9. Fluxo Mínimo Pós-Setup

1. Login com usuário admin.
2. Abrir dashboard.
3. Abrir prontuários.
4. Cadastrar e visualizar uma ficha de acolhimento.
5. Cadastrar e visualizar uma ficha socioeconômica.
6. Registrar frequência diária em `faltas.php`.
7. Abrir alertas e histórico de faltas.
8. Gerenciar usuários.
9. Abrir logs.
10. Validar perfil e troca de senha.

## 10. Pendências Conhecidas

- Normalizar nomes de tabelas para ambientes sensíveis a maiúsculas/minúsculas.
- Configurar SMTP real para recuperação de senha com provedor e credenciais de produção.
- Executar plano LGPD e política de retenção de documentos.
- Definir destino real, criptografia, retencao e rotina operacional de backup fora do Docker.
- Criar comparacao automatizada para relatorios PDF/XLSX oficiais quando os modelos forem definidos.
