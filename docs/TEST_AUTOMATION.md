# Automacao de Testes

Atualizado em 2026-06-01.

Este documento descreve a automacao criada para validar o banco e a aplicacao em um ambiente descartavel, sem usar o banco real da maquina.

## Objetivo

A suite automatizada agora cobre tres camadas:

| Camada | Comando | O que valida |
| --- | --- | --- |
| Unitarios PHP | `php tests/run.php` | Helpers, senha, sanitizacao, datas e CSV |
| Integracao com banco | `php tests/run_integration.php` dentro do container `app` | Schema, dados iniciais, usuarios, acolhimento, socioeconomico, frequencia, desligamento, documentos e psicologia |
| Smoke HTTP | `php tests/run_http_smoke.php` dentro do container `app` | Login, CSRF, sessao, redirecionamento, paginas criticas, permissoes por perfil e uploads multipart |
| Backup/restore | `tests/backup_restore_check.sh` dentro do container `db` | Dump logico, restore em schema temporario e validacao minima dos dados |

## Rodar Tudo no Windows

```powershell
.\tests\run_all.ps1
```

Se o Windows bloquear scripts por Execution Policy, use bypass apenas para este processo:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tests\run_all.ps1
```

Para manter os containers vivos depois do teste:

```powershell
.\tests\run_all.ps1 -KeepContainers
```

O ambiente de teste usa `docker-compose.test.yml`, sobe a aplicacao em:

```text
http://localhost:8090/
```

O banco usado e `criancafeliz_test`. O volume e descartavel e o runner remove tudo no final, salvo quando `-KeepContainers` for usado.

## Rodar Tudo no Linux/macOS/CI

```bash
./tests/run_all.sh
```

Para manter os containers:

```bash
KEEP_CONTAINERS=1 ./tests/run_all.sh
```

## Rodar Por Partes

Subir stack de teste:

```bash
docker compose -f docker-compose.test.yml up -d --build
```

Rodar integracao:

```bash
docker compose -f docker-compose.test.yml exec -T app php tests/run_integration.php
```

Rodar smoke HTTP:

```bash
docker compose -f docker-compose.test.yml exec -T app php tests/run_http_smoke.php
```

Rodar check de backup/restauracao:

```bash
docker compose -f docker-compose.test.yml exec -T db sh -lc "tr -d '\r' < /backup_restore_check.sh | sh"
```

Derrubar e apagar banco de teste:

```bash
docker compose -f docker-compose.test.yml down -v --remove-orphans
```

## O Que a Suite Cobre Agora

- Conexao PDO/MySQL dentro do container.
- Existencia das tabelas e da view `atendidos_com_alerta`.
- Usuario admin inicial e senha documentada.
- CRUD/autenticacao de usuario pelo model.
- Criacao e busca de acolhimento por CPF.
- Criacao socioeconomica com familia, despesas e log por trigger.
- Registro de faltas, alerta e desligamento automatico.
- Anexo de documento no prontuario pelo model.
- Ciclo de nota psicologica por CPF: criar, buscar, atualizar e excluir.
- Smoke HTTP de login, CSRF, sessao e paginas criticas.
- Smoke HTTP de permissoes por perfil: admin acessa usuarios e nao acessa psicologia; psicologo acessa psicologia e nao acessa usuarios/cadastro; funcionario nao acessa rotas restritas.
- Smoke HTTP de upload multipart da foto de perfil, com CSRF, MIME, arquivo gravado e persistencia em `Usuario.foto_perfil`.
- Smoke HTTP de upload multipart de documento do prontuario, com CSRF, MIME, registro em `documento` e arquivo em `uploads/documents/`.
- Guarda de permissao direta em exclusao socioeconomica por HTTP, antes da validacao CSRF, para evitar tentativa de mutacao por perfil sem `delete_records`.
- Backup e restauracao logica do banco de teste, validando que o dump sobe em um schema temporario separado.

## O Que Ainda Nao Da Para Garantir So Com Esta Suite

| Bloqueio | Como resolver |
| --- | --- |
| Docker daemon parado na maquina local | Abrir o Docker Desktop e esperar ficar `Running`. Depois rodar `docker ps` e `.\tests\run_all.ps1`. |
| PowerShell bloqueia `.ps1` | Rodar `powershell -NoProfile -ExecutionPolicy Bypass -File .\tests\run_all.ps1`. Isso vale so para o processo atual. |
| PHP local sem `pdo_mysql` | Usar a suite via Docker, que ja instala `pdo_mysql`, ou habilitar `extension=pdo_mysql` no `php.ini` do PHP local e reiniciar o terminal/servidor. |
| Cliente `mysql` fora do PATH | Instalar MySQL Client/MySQL Shell ou usar o container: `docker compose -f docker-compose.test.yml exec db mysql -uroot -proot criancafeliz_test`. |
| Teste em Linux com `lower_case_table_names=0` | Subir um MySQL Linux separado com nomes de tabela sensiveis a caixa e rodar `php tools/diagnostics/check_table_case.php`. A normalizacao completa exige migration planejada e backup. |
| SMTP real | Definir provedor, host, porta, usuario, senha, remetente e politica de envio. Depois implementar variaveis de ambiente e teste de recuperacao de senha contra sandbox do provedor. |
| Validacao contra dados reais | Criar dump anonimizado ou ambiente de homologacao. Nunca rodar a suite de integracao contra producao sem backup, janela combinada e base isolada. |
| LGPD/documentos | Definir responsaveis internos, bases legais, retencao, descarte e quem pode anexar/remover documentos. A automacao testa o mecanismo, nao substitui decisao operacional. |
| Relatorios oficiais PDF/Excel | Escolher modelos oficiais e stack de exportacao. A suite atual valida CSV e smoke de telas, mas nao compara layout final de PDF. |
| Backup real fora do Docker | Definir storage externo, criptografia, retencao, usuario MySQL de backup e teste periodico de restore em homologacao. |

## CI

O workflow `.github/workflows/ci.yml` agora executa:

1. lint PHP;
2. testes unitarios;
3. validacao dos arquivos Docker Compose;
4. stack Docker de teste;
5. testes de integracao com banco;
6. smoke tests HTTP;
7. teste de backup/restauracao;
8. coleta de logs em falha;
9. limpeza do ambiente.

## Cuidados

- `docker-compose.test.yml` e apenas para teste automatizado.
- O runner remove o volume `db_test_data` ao final.
- Nao aponte `DB_HOST`, `DB_NAME`, `DB_USER` ou `DB_PASS` da suite para producao.
- Para depurar, use `-KeepContainers` e depois entre no container `app` ou `db`.
