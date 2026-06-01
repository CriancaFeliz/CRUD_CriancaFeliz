---
tipo: projeto
status: ativo
area: desenvolvimento
tags:
  - projeto/crianca-feliz
  - php
  - mysql
  - obsidian
---

# Painel do Projeto Criança Feliz

Esta é a nota central para navegar o projeto no Obsidian. Ela não substitui a documentação técnica do repositório; ela organiza os pontos de entrada para pensar, revisar e evoluir o sistema com mais clareza.

## Visão Rápida

- Status: ativo
- Stack: PHP, MySQL/MariaDB, JavaScript, CSS, Docker
- Arquitetura: MVC simples sem framework externo
- Última frente: hardening de prioridades altas em 2026-06-01
- Frente atual: automacao de testes verde com banco Docker, smoke HTTP, permissoes por perfil, uploads multipart e documentacao de bloqueios operacionais
- Entrada técnica: [[README|README]]
- Documentação completa: [[docs/PROJECT_DOCUMENTATION|Documentação técnica]]
- Plano de lacunas: [[04 - Plano de Lacunas e Prioridades]]
- Pendencias externas: [[05 - Pendências Externas e Insumos]]
- Backlog: [[01 - Backlog Técnico]]
- Auditoria de segurança: [[03 - Auditoria de Segurança]]
- Uso com Codex: [[02 - Guia de Uso com Codex]]

## Mapa de Documentação

- [[README|README]]: entrada principal do repositório.
- [[docs/PROJECT_DOCUMENTATION|Documentação técnica]]: arquitetura, rotas, controllers, models, services e pendências.
- [[docs/GAP_REMEDIATION_PLAN|Plano de lacunas]]: matriz de prioridades e entregas por lacuna.
- [[docs/LGPD_AND_DATA_GOVERNANCE|LGPD e governança]]: plano técnico-operacional de dados.
- [[docs/TEST_PLAN|Plano de testes]]: testes automatizados, CI e próximos fluxos.
- [[docs/TEST_AUTOMATION|Automação de testes]]: comandos, stack Docker de teste e como resolver bloqueios.
- [[docs/BACKUP_RESTORE_RUNBOOK|Backup e restore]]: rotina tecnica e insumos para producao.
- [[docs/DOCUMENT_GOVERNANCE_DRAFT|Governanca de documentos]]: rascunho de politica de documentos anexados.
- [[docs/SMTP_SETUP|SMTP]]: dados necessarios para envio real de recuperacao de senha.
- [[docs/DATABASE_NORMALIZATION_PLAN|Plano do banco]]: normalização de nomes de tabelas.
- [[docs/LEGACY_TABLE_STRATEGY|Tabelas legadas]]: decisão para tabelas antigas e compatibilidade.
- [[docs/REPORTING_ROADMAP|Roadmap de relatórios]]: relatórios PDF/Excel e decisões pendentes.
- [[docs/REQUIREMENTS_TRACEABILITY|Rastreabilidade]]: requisitos da monografia versus estado atual.
- [[docs/MAINTENANCE_AND_TESTING|Manutenção e testes]]: scripts auxiliares, validação local e checklist operacional.
- [[database/README_SETUP|Setup do banco]]: instruções de MySQL/MariaDB e Docker.
- [[docs/STYLING_UPGRADE|Padrões visuais]]: design system, CSS e tema claro/escuro.
- [[docs/RELACAO_GERAL_DE_ALTERACOES|Relação geral de alterações]]: histórico de reorganização e modernização.

## Áreas do Sistema

- Autenticação e recuperação de senha
- Dashboard
- Prontuários
- Acolhimento
- Ficha socioeconômica
- Controle de faltas
- Desligamento
- Área psicológica
- Usuários
- Logs e auditoria
- Perfil

## Comandos Úteis

```bash
docker compose up --build
```

```bash
php -S localhost:8000 var/dev-router.php
```

```powershell
.\tests\run_all.ps1
```

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Próximas Revisões

- Revisar [[01 - Backlog Técnico]] antes de abrir novas frentes.
- Manter decisões técnicas em notas curtas quando uma mudança alterar arquitetura, banco ou deploy.
- Atualizar este painel sempre que um documento virar a fonte principal de algum assunto.
