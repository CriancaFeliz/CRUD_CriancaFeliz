# Origem da base de continuidade

Esta branch foi criada para consolidar uma única base confiável para a continuidade do projeto Criança Feliz.

## Composição

1. Base completa do commit `4c1ed7a`, anteriormente compartilhado pelas branches `codex/leonardo`, `codex/mateus` e `codex/pedro`.
2. Arquivos do pacote `criancafeliz-release` enviado à coordenação em agosto de 2026, aplicados sobre a base completa.
3. Schema consolidado em `database/SETUP_COMPLETO_FINAL.sql`, definido como fonte única para instalações novas.

## Regras de continuidade

- A branch `main` e a tag `v1.0` não devem ser mescladas em bloco nesta base.
- Dumps com dados pessoais, arquivos `.env`, senhas e backups de produção não devem ser versionados.
- A importação do setup completo deve ocorrer somente em banco novo; bases existentes exigem plano de migração próprio, backup e teste em cópia anonimizada.
- Código de diagnóstico, manutenção e testes não deve ser publicado junto com o pacote de produção.
