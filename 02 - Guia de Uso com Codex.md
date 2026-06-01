---
tipo: guia
status: ativo
area: desenvolvimento
tags:
  - projeto/crianca-feliz
  - codex
  - obsidian
---

# Guia de Uso com Codex

Use o Codex como parceiro de manutenção do projeto: primeiro para entender o estado atual, depois para editar com escopo pequeno, validar e registrar o resultado.

## Prompts Bons

```text
Leia o projeto e me diga quais arquivos controlam o fluxo de acolhimento, sem editar nada.
```

```text
Implemente a próxima tarefa do Backlog Técnico, explique o plano antes de editar e rode as validações possíveis.
```

```text
Atualize a documentação depois desta mudança e crie um commit com uma mensagem clara.
```

```text
Revise este diff como code review, priorizando bugs, regressões e testes faltando.
```

## Regras de Trabalho

- Antes de mexer em código, pedir ou permitir que o Codex leia os arquivos relacionados.
- Para tarefas de documentação, manter links entre este painel, README e documentos em `docs/`.
- Para tarefas de banco, conferir [[database/README_SETUP]] e os scripts SQL antes de alterar código.
- Para tarefas de interface, conferir [[docs/STYLING_UPGRADE]] antes de criar novos estilos.
- Depois de mudanças relevantes, pedir validação com `git diff --check`, lint PHP e, quando possível, Docker Compose.

## Fluxo Recomendado

1. Escolher uma tarefa em [[01 - Backlog Técnico]].
2. Pedir diagnóstico sem edição.
3. Aprovar o plano.
4. Deixar o Codex implementar.
5. Rodar validações.
6. Atualizar documentação.
7. Commitar mudanças relacionadas juntas.
