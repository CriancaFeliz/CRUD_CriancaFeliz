# Plano de Testes

Atualizado em 2026-06-01.

## Como Rodar

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

Ou, em Linux/macOS/CI:

```bash
./tests/run_all.sh
```

Detalhes da automacao: [docs/TEST_AUTOMATION.md](TEST_AUTOMATION.md).

Lint PHP completo:

```powershell
Get-ChildItem -Recurse -Filter *.php |
  Where-Object { $_.FullName -notmatch '\\database\\legacy_dumps\\' } |
  ForEach-Object { php -l $_.FullName }
```

Validação Docker:

```bash
docker compose config --quiet
docker compose up --build
```

## Testes Automatizados Atuais

| Arquivo | Escopo |
| --- | --- |
| `tests/automated/PasswordHelperTest.php` | Política, hash, verificação e exceção de senha fraca |
| `tests/automated/BootstrapHelperTest.php` | Sanitização, email e formatação de datas |
| `tests/automated/ReportExportHelperTest.php` | Escape CSV para exportações compatíveis com planilhas |

## Testes de Integracao e Smoke

| Arquivo | Escopo |
| --- | --- |
| `tests/run_integration.php` | Runner de integracao com MySQL no container |
| `tests/integration/DatabaseSchemaTest.php` | Conexao, tabelas/view criticas e admin inicial |
| `tests/integration/UserIntegrationTest.php` | CRUD e autenticacao de usuario |
| `tests/integration/CoreFlowsIntegrationTest.php` | Acolhimento, socioeconomico, faltas, desligamento, documentos e psicologia |
| `tests/run_http_smoke.php` | Login, CSRF, sessao, paginas criticas, permissoes por perfil e uploads multipart via HTTP |
| `tests/backup_restore_check.sh` | Dump e restore do banco de teste em schema temporario |

## CI

O workflow `.github/workflows/ci.yml` executa:

- checkout;
- PHP 8.2 com `pdo_mysql` e `fileinfo`;
- lint de arquivos PHP;
- `php tests/run.php`;
- `docker compose config --quiet`;
- `docker compose -f docker-compose.test.yml config --quiet`;
- ambiente Docker de teste;
- testes de integracao com banco;
- smoke tests HTTP;
- teste de backup/restauracao;
- logs e limpeza do ambiente em caso de falha.

## Proxima Cobertura

| Prioridade | Fluxo | Tipo |
| --- | --- | --- |
| Alta | Recuperacao de senha com SMTP real ou sandbox do provedor | Integracao + provedor externo |
| Alta | Backup e restauracao do banco real em ambiente de homologacao | Operacional + banco |
| Alta | Normalizacao de nomes de tabelas em Linux com `lower_case_table_names=0` | Migracao + teste de restauracao |
| Alta | Politica LGPD de documentos: remocao, retencao, versionamento e auditoria | Regra operacional + testes |
| Media | Relatorios PDF/XLSX oficiais com comparacao de layout/arquivo | Integracao + artefato |
| Media | Logs com mascaramento por campo sensivel | Integracao + seguranca |

## Critério Para Mercado

Antes de produção, a meta mínima é:

- CI verde em todo PR.
- Cobertura dos fluxos críticos com banco Docker.
- Teste de backup/restauração documentado.
- Smoke test manual em ambiente limpo.
