# Runbook de Backup e Restauracao

Atualizado em 2026-06-01.

Este runbook descreve a rotina tecnica minima para testar backup e restauracao do MySQL/MariaDB do sistema. Ele nao substitui a politica operacional de retencao, criptografia e armazenamento externo.

## O Que Ja Esta Automatizado

A suite Docker de teste valida que um dump logico consegue ser restaurado em outro banco temporario.

Comando principal:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tests\run_all.ps1
```

Etapa executada pelo runner:

```bash
docker compose -f docker-compose.test.yml exec -T db sh -lc "tr -d '\r' < /backup_restore_check.sh | sh"
```

O check faz:

1. `mysqldump` do banco `criancafeliz_test`.
2. criacao do banco temporario `criancafeliz_restore_test`.
3. restore do dump nesse banco temporario.
4. validacao de tabelas, usuario admin inicial e registros de atendidos.
5. exclusao do banco temporario e do dump em `/tmp`.

## Rotina Recomendada Para Homologacao/Producao

1. Gerar dump com transacao, triggers e rotinas:

```bash
mysqldump --single-transaction --routines --triggers --events \
  -h HOST -P 3306 -u USUARIO -p NOME_DO_BANCO > criancafeliz_YYYY-MM-DD.sql
```

2. Compactar e criptografar o arquivo antes de sair do servidor:

```bash
7z a -t7z -mhe=on -p"SENHA_FORTE" criancafeliz_YYYY-MM-DD.sql.7z criancafeliz_YYYY-MM-DD.sql
```

3. Armazenar fora do servidor principal.

4. Restaurar em ambiente separado, nunca por cima de producao sem janela aprovada:

```bash
mysql -h HOST_RESTORE -P 3306 -u USUARIO -p -e "CREATE DATABASE criancafeliz_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h HOST_RESTORE -P 3306 -u USUARIO -p criancafeliz_restore < criancafeliz_YYYY-MM-DD.sql
```

5. Validar o restore:

```sql
SELECT COUNT(*) FROM Usuario;
SELECT COUNT(*) FROM Atendido;
SELECT COUNT(*) FROM documento;
SELECT COUNT(*) FROM anotacao_psicologica;
```

6. Registrar data, responsavel, origem do dump, destino do restore e resultado.

## Frequencia Sugerida

| Rotina | Frequencia inicial |
| --- | --- |
| Backup automatico | Diario |
| Copia externa/off-site | Diario ou semanal |
| Teste de restauracao | Mensal e antes de deploy grande |
| Revisao de permissao do usuario de backup | Trimestral |
| Revisao de retencao | Semestral |

## Insumos Que Ainda Faltam

| Item | Por que falta | Precisa resolver |
| --- | --- | --- |
| Destino externo do backup | O repo nao sabe onde guardar dados reais | Definir storage, conta, caminho e responsavel |
| Criptografia | Dados pessoais e sensiveis nao podem ficar em dump aberto | Definir ferramenta, senha/chave e guarda segura |
| Retencao | Depende de regra da instituicao e LGPD | Definir prazos por tipo de dado |
| Usuario MySQL de backup | Precisa existir no banco real com privilegio minimo | Criar usuario com permissao de leitura/dump |
| Janela de restore | Restore pode parar operacao se feito no lugar errado | Definir ambiente de homologacao e plano de rollback |
