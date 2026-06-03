---
tipo: auditoria
status: ativo
area: desenvolvimento
tags:
  - projeto/crianca-feliz
  - seguranca
  - senhas
---

# Auditoria de Segurança

## Revisão de 2026-06-01

### Senhas

Estado anterior:

- Uso correto de `password_hash()` e `password_verify()`.
- `PASSWORD_DEFAULT` sem política centralizada.
- Coluna `Senha` com `varchar(100)`.
- Política mínima de 6 caracteres.
- Credencial inicial fraca em scripts e documentação.

Mudanças aplicadas:

- Criado `PasswordHelper`.
- Novas senhas usam Argon2id quando disponível.
- Fallback para bcrypt com custo 12.
- Coluna `Senha` ampliada para `varchar(255)`.
- Política mínima elevada para 12 caracteres.
- Senhas padrão/comuns bloqueadas em novas definições de senha.
- Usuário inicial documentado com senha local mais forte.
- Tokens de recuperação salvos como hash SHA-256 do token.

### Prioridades Altas

- [x] Legado `attendance.php` redirecionado.
- [x] `tools/`, `database/`, `data/`, `var/` e `docker/` bloqueados no Apache.
- [x] `anotacao_psicologica` oficializada no setup/migração.
- [x] Senhas endurecidas para padrão mais adequado a produto.
- [ ] SMTP real depende de provedor e credenciais.
- [ ] Normalização completa de nomes de tabelas exige migração própria.

## Revisao de 2026-06-03

### Logs Administrativos

Mudancas aplicadas:

- As telas de logs passaram a renderizar pelo layout principal, mantendo a verificacao de acesso admin no `LogController`.
- Valores de campos sensiveis como senha, password, token, secret, segredo, API key, cookie, session, CSRF, credencial e hash agora sao mascarados em lista, busca, detalhe, JSON bruto exibido e CSV.
- Exportacoes CSV de logs neutralizam valores iniciados por `=`, `+`, `-` ou `@` para reduzir risco de formula injection ao abrir em planilhas.
- Titulos enviados ao layout principal pelo modulo de logs agora sao escapados com `htmlspecialchars`, fechando risco de XSS via parametros refletidos na topbar.
- Paginacao de logs e APIs JSON foi normalizada para inteiros positivos, com limite de `per_page` em 200.
- Foi corrigida a chamada interna de `setFlash()` no controller de logs e o filtro legado `by_action` passou a ler `acao`.

Testes executados:

- `php tests/run.php`: 10 testes, 27 assercoes.
- Lint PHP completo do repositorio: aprovado.
- `tests/run_all.ps1`: unitarios e lint aprovados; etapa Docker bloqueada porque o Docker Desktop nao estava ativo.

Limites da validacao local:

- O PHP local em `C:\Program Files\PHP\current\php.exe` nao possui `pdo_mysql`, entao login/rotas com banco nao puderam ser validadas pelo servidor embutido nesta maquina.
- A validacao completa com banco deve ser repetida com Docker Desktop ativo ou PHP com `pdo_mysql` habilitado.

## Próxima Decisão

Antes de mercado, decidir se a autenticação continuará interna ou se será substituída/integrada a um provedor externo de identidade.
