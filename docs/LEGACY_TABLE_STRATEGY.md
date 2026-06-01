# Estratégia Para Tabelas Legadas

Atualizado em 2026-06-01.

## Decisão

Tabelas e dumps legados devem permanecer apenas como referência histórica ou compatibilidade temporária. O desenvolvimento novo deve apontar para os models atuais e para as tabelas oficialmente documentadas no setup principal.

## Regras

- Não criar novos fluxos usando tabelas legadas sem registrar decisão técnica.
- Não misturar nomes antigos e novos no mesmo módulo.
- Manter dumps antigos em `database/legacy_dumps/`.
- Antes de remover uma tabela, validar se não há referência em `app/`, `database/`, `docker/` ou `tools/`.
- Se uma tabela legada precisar continuar existindo por compatibilidade, documentar quem usa e quando será removida.

## Candidatas a Revisão

| Item | Ação |
| --- | --- |
| `presenca`, `sessao`, `frequencia` | Já removidas por `update_schema.sql` quando existirem |
| Variações de caixa de `Atendido`/`atendido` | Resolver no plano de normalização do banco |
| Variações de caixa de `Usuario`/`usuario` | Resolver no plano de normalização do banco |
| Views ou aliases temporários | Usar apenas durante migração controlada |

## Critério de Pronto

- `tools/diagnostics/check_table_case.php` executa sem divergência no ambiente alvo.
- Testes de integração rodam em MySQL Linux com `lower_case_table_names=0`.
- Documentação do schema aponta para uma única fonte de verdade.
