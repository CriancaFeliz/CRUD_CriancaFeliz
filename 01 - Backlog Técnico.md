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

- [x] Remover ou redirecionar referências legadas a `attendance.php`; o módulo atual é `faltas.php`.
- [ ] Configurar SMTP real para o fluxo de recuperação de senha. Bloqueado até definir provedor, host, porta, remetente e credenciais.
- [x] Proteger `tools/` e `database/` fora do ambiente de desenvolvimento.
- [ ] Normalizar nomes de tabelas para ambientes Linux sensíveis a maiúsculas/minúsculas.
- [ ] Executar plano LGPD: inventário, bases legais, aviso de privacidade, retenção e descarte.
- [ ] Definir política de documentos anexados: acesso, exclusão, versionamento e retenção.
- [x] Oficializar a tabela `anotacao_psicologica` no setup ou em migração validada.
- [x] Endurecer armazenamento e política de senhas com `PasswordHelper`, Argon2id quando disponível, fallback bcrypt e coluna `Senha` com `varchar(255)`.

## Concluído em 2026-06-01

- Rota legada `attendance.php` redirecionada para `faltas.php`/`desligamento.php`.
- Links do prontuário atualizados para o módulo atual de faltas/desligamento.
- `.htaccess` bloqueia acesso direto a `tools/`, `database/`, `data/`, `var/` e `docker/`.
- `anotacao_psicologica` adicionada a `SETUP_COMPLETO_FINAL.sql`, `migration.sql` e `update_schema.sql`.
- Senhas novas exigem política mínima mais forte e usam Argon2id quando disponível.
- Testes automatizados mínimos, runner e CI adicionados.
- Foto de perfil persistida em `usuario.foto_perfil`.
- Anexos de documentos adicionados ao prontuário.
- Logs detalhados de debug condicionados a `APP_DEBUG`.
- Planos de lacunas, LGPD, banco, testes, relatórios e rastreabilidade documentados.

## Prioridade Média

- [ ] Expandir testes automatizados além da suíte mínima atual.
- [ ] Revisar endpoints psicológicos ainda não implementados.
- [ ] Implementar relatórios PDF/Excel conforme roadmap.
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
