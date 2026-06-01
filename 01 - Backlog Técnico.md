---
tipo: backlog
status: ativo
area: desenvolvimento
tags:
  - projeto/crianca-feliz
  - backlog
  - manutencao
---

# Backlog Técnico

Fonte inicial: pendências descritas em [[docs/PROJECT_DOCUMENTATION]], [[docs/RELACAO_GERAL_DE_ALTERACOES]], [[docs/MAINTENANCE_AND_TESTING]] e [[database/README_SETUP]].

## Prioridade Alta

- [ ] Remover ou redirecionar referências legadas a `attendance.php`; o módulo atual é `faltas.php`.
- [ ] Configurar SMTP real para o fluxo de recuperação de senha.
- [ ] Proteger `tools/` e `database/` fora do ambiente de desenvolvimento.
- [ ] Normalizar nomes de tabelas para ambientes Linux sensíveis a maiúsculas/minúsculas.
- [ ] Oficializar a tabela `anotacao_psicologica` no setup ou em migração validada.

## Prioridade Média

- [ ] Criar testes automatizados além dos testes manuais.
- [ ] Revisar endpoints psicológicos ainda não implementados.
- [ ] Reduzir o tamanho do roteador em `index.php`.
- [ ] Alinhar `SETUP_COMPLETO_FINAL.sql`, `migration.sql` e `update_schema.sql`.
- [ ] Decidir estratégia para tabelas legadas preservadas no schema.

## Prioridade Baixa

- [ ] Comprimir imagens grandes em `img/`.
- [ ] Padronizar idioma de nomes internos.
- [ ] Avaliar Composer/autoload PSR-4 se o projeto crescer.

## Revisão

- Última organização: 2026-06-01
- Nota central: [[00 - Painel do Projeto Criança Feliz]]
