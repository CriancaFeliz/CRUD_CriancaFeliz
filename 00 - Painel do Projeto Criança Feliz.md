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
- Entrada técnica: [[README|README]]
- Documentação completa: [[docs/PROJECT_DOCUMENTATION|Documentação técnica]]
- Backlog: [[01 - Backlog Técnico]]
- Auditoria de segurança: [[03 - Auditoria de Segurança]]
- Uso com Codex: [[02 - Guia de Uso com Codex]]

## Mapa de Documentação

- [[README|README]]: entrada principal do repositório.
- [[docs/PROJECT_DOCUMENTATION|Documentação técnica]]: arquitetura, rotas, controllers, models, services e pendências.
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
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Próximas Revisões

- Revisar [[01 - Backlog Técnico]] antes de abrir novas frentes.
- Manter decisões técnicas em notas curtas quando uma mudança alterar arquitetura, banco ou deploy.
- Atualizar este painel sempre que um documento virar a fonte principal de algum assunto.
