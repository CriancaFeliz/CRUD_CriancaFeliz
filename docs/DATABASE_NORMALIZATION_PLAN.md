# Plano de Normalização do Banco

Atualizado em 2026-06-01.

## Problema

O código usa nomes como `Atendido`, `Usuario`, `Ficha_Socioeconomico`, `Frequencia_Dia` e `Desligamento`, enquanto partes do SQL aparecem em minúsculas. No Docker local, o MySQL sobe com `lower_case_table_names=1`, o que mascara a divergência. Em Linux com `lower_case_table_names=0`, isso pode quebrar models, triggers e migrations.

## Ações Nesta Rodada

- Criado `tools/diagnostics/check_table_case.php`.
- Documentado o risco no README e no plano de lacunas.
- Adicionada coluna `foto_perfil` nos scripts oficiais.

## Como Diagnosticar

Com o banco configurado:

```bash
php tools/diagnostics/check_table_case.php
```

Resultado esperado:

```text
OK: nomes de tabelas esperados foram encontrados com a caixa exata.
```

## Estratégia Recomendada

1. Escolher padrão único. Recomendação: minúsculas com snake_case para tabelas e colunas novas.
2. Criar migration idempotente para renomear tabelas ou criar views de compatibilidade temporárias.
3. Atualizar models para o padrão escolhido.
4. Atualizar triggers de auditoria.
5. Rodar testes de integração em MySQL Linux com `lower_case_table_names=0`.
6. Remover compatibilidade temporária quando o ambiente estiver estável.

## Ordem Sugerida de Migração

| Etapa | Tabelas |
| --- | --- |
| 1 | `Usuario`, `Atendido`, `Responsavel` |
| 2 | `Ficha_Socioeconomico`, `Familia`, `Despesas` |
| 3 | `Frequencia_Dia`, `Frequencia_Oficina`, `Oficina`, `Desligamento` |
| 4 | `documento`, `anotacao_psicologica`, `log` |

## Risco

Não aplicar a migração completa nesta rodada foi intencional: renomear tabelas sem base real, backup e teste de restauração pode causar perda de disponibilidade.
