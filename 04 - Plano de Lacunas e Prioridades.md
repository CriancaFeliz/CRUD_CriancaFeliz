---
tipo: plano
status: ativo
area: desenvolvimento
tags:
  - projeto/crianca-feliz
  - prioridades
  - lgpd
  - testes
---

# Plano de Lacunas e Prioridades

Atualizado em 2026-06-01.

Esta nota consolida as lacunas analisadas em Testes, LGPD, Banco, Documentos, Perfil/foto, Debug, Relatórios e Documentação. A ordem abaixo separa o que foi corrigido nesta rodada do que precisa virar próxima fase.

## Entregue nesta rodada

- Testes: criada suíte automatizada mínima em `tests/automated/`, runner `tests/run.php` e workflow CI em `.github/workflows/ci.yml`.
- LGPD: criado plano de governança e segurança em [[docs/LGPD_AND_DATA_GOVERNANCE]].
- Banco: adicionada coluna `foto_perfil`, criado diagnóstico de caixa de tabelas e plano de normalização em [[docs/DATABASE_NORMALIZATION_PLAN]].
- Documentos: prontuário passou a aceitar anexos controlados por admin, com validação de extensão/MIME, armazenamento em `uploads/documents/` e abertura via rota autenticada.
- Perfil/foto: foto de perfil saiu do `sessionStorage` e passou a persistir no banco via `foto_perfil`.
- Debug: logs detalhados e sensíveis passam a obedecer `APP_DEBUG`; console log do front também é silenciado quando `APP_DEBUG=false`.
- Relatórios: criado roadmap específico em [[docs/REPORTING_ROADMAP]].
- Documentação: README, documentação técnica, manutenção/testes e rastreabilidade foram atualizados.

## Prioridade Alta

- Rodar a CI em todo push/PR e aumentar cobertura dos fluxos críticos: login, permissões, acolhimento, socioeconômico, prontuário, documentos e logs.
- Executar o plano LGPD: inventário de dados, bases legais, termo/aviso de privacidade, rotina de atendimento ao titular, retenção e descarte.
- Normalizar nomes de tabelas antes de produção Linux com `lower_case_table_names=0`.
- Revisar política de documentos: quem pode anexar, visualizar, remover e por quanto tempo manter cada tipo.
- Definir stack de relatórios PDF/Excel e modelos oficiais para direção/assistência social.

## Prioridade Média

- Migrar reset tokens de `data/reset_tokens.json` para tabela própria com expiração e auditoria.
- Configurar SMTP real.
- Implementar endpoints psicológicos pendentes: `saveAssessment`, `search` e `report`.
- Criar testes de integração com banco Docker.
- Criar rotina de backup/restauração documentada e testada.

## Próxima Revisão

1. Validar esta branch em Docker.
2. Confirmar se o banco de produção/homologação será Linux com tabela sensível a caixa.
3. Definir responsáveis internos por LGPD e documentos.
4. Escolher formato de relatórios oficiais.
5. Expandir testes automatizados dos fluxos com banco.
