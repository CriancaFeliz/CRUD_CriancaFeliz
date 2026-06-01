# Plano de Testes

Atualizado em 2026-06-01.

## Como Rodar

```bash
php tests/run.php
```

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

## CI

O workflow `.github/workflows/ci.yml` executa:

- checkout;
- PHP 8.2 com `pdo_mysql` e `fileinfo`;
- lint de arquivos PHP;
- `php tests/run.php`;
- `docker compose config --quiet`.

## Próxima Cobertura

| Prioridade | Fluxo | Tipo |
| --- | --- | --- |
| Alta | Autenticação e permissões por perfil | Integração |
| Alta | CRUD de acolhimento | Integração com banco |
| Alta | Ficha socioeconômica com família/despesas | Integração com banco |
| Alta | Upload de foto/documentos | Integração + filesystem |
| Alta | Prontuário e busca por CPF/nome | Integração |
| Média | Logs e exportação CSV | Integração |
| Média | Faltas/desligamento automático | Integração |
| Média | Área psicológica | Integração com permissões |

## Critério Para Mercado

Antes de produção, a meta mínima é:

- CI verde em todo PR.
- Cobertura dos fluxos críticos com banco Docker.
- Teste de backup/restauração documentado.
- Smoke test manual em ambiente limpo.
