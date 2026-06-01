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

## Próxima Decisão

Antes de mercado, decidir se a autenticação continuará interna ou se será substituída/integrada a um provedor externo de identidade.
