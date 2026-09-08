---
tipo: pendencias
status: ativo
area: desenvolvimento
tags:
  - projeto/crianca-feliz
  - pendencias
  - bloqueios
---

# Pendencias Externas e Insumos

Atualizado em 2026-06-01.

Esta nota lista apenas o que ainda falta, por que falta e o que precisa ser resolvido fora do codigo antes da proxima implementacao segura.

| Pendencia | Por que falta | O que precisa resolver |
| --- | --- | --- |
| SMTP real para recuperacao de senha | O repo nao tem provedor, credenciais, remetente oficial nem sandbox de email | Escolher provedor, host, porta, TLS, usuario/API key, remetente, texto do email e conta de teste |
| Normalizacao de nomes de tabelas em Linux | Renomear tabelas pode quebrar producao se feito sem backup, janela e teste de restore | Definir padrao de nomes, gerar dump anonimizado, testar em MySQL Linux `lower_case_table_names=0`, aprovar migration e rollback |
| LGPD operacional | O documento tecnico nao substitui decisao juridica/gestao | Definir inventario, bases legais, aviso de privacidade, canal do titular, retencao e descarte |
| Politica oficial de documentos | Ja existe rascunho tecnico, mas acesso/remocao/retencao dependem de regra institucional | Aprovar quem pode anexar, ver, remover, versionar e por quanto tempo manter cada tipo de documento |
| Backup real fora do Docker | O teste automatizado prova restore tecnico no banco de teste, mas nao define storage real | Definir destino externo, criptografia, senha/chave, retencao, usuario MySQL de backup e rotina mensal de restore em homologacao |
| Relatorios PDF/XLSX oficiais | A aplicacao tem CSV e impressao/PDF do navegador, mas nao ha modelo oficial aprovado | Definir layouts, campos, filtros, assinatura/cabecalho e biblioteca para gerar PDF/XLSX |
| Dados reais de homologacao | Testes nao devem usar producao diretamente | Criar dump anonimizado ou base de homologacao com dados representativos |
| Refatoracao do roteador `index.php` | E possivel, mas mexe em muitas rotas e deve ser feito depois da suite estabilizada | Criar matriz de rotas, refatorar por etapas e manter smoke HTTP cobrindo regressao |
| Padronizacao de idioma interno | Renomear classes/tabelas/campos tem alto risco de quebra | Escolher convencao e aplicar so junto de migration/refactor planejado |
| Composer/autoload PSR-4 | O projeto ainda funciona com autoload simples; mudanca estrutural agora nao e urgente | Avaliar quando houver crescimento de dependencias ou inclusao de biblioteca SMTP/PDF |

## Ja Deixado Pronto Para Desbloquear

- [[docs/SMTP_SETUP]]: lista os dados necessarios para SMTP.
- [[docs/DATABASE_NORMALIZATION_PLAN]]: plano de normalizacao de nomes de tabelas.
- [[docs/DOCUMENT_GOVERNANCE_DRAFT]]: rascunho da politica de documentos.
- [[docs/BACKUP_RESTORE_RUNBOOK]]: rotina de backup/restauracao e checklist operacional.
- [[docs/REPORTING_ROADMAP]]: caminho para PDF/XLSX oficiais.
