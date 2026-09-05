# Origem da base de continuidade

Esta branch foi criada para consolidar uma única base confiável para a continuidade do projeto Criança Feliz.

## Composição

1. Base completa do commit `4c1ed7a`, anteriormente compartilhado pelas branches `codex/leonardo`, `codex/mateus` e `codex/pedro`.
2. Arquivos do pacote `criancafeliz-release` enviado à coordenação em agosto de 2026, aplicados sobre a base completa.
3. Migração `20260825_upgrade_legacy_production.sql`, preservada em `database/migrations/` para revisão e testes antes de qualquer uso em produção.

## Regras de continuidade

- A branch `main` e a tag `v1.0` não devem ser mescladas em bloco nesta base.
- Dumps com dados pessoais, arquivos `.env`, senhas e backups de produção não devem ser versionados.
- A migração de produção só pode ser executada após backup e teste em uma cópia anonimizada do banco.
- Código de diagnóstico, manutenção e testes não deve ser publicado junto com o pacote de produção.
