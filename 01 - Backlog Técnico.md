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

- [x] Criar automação de testes com banco Docker, integração e smoke HTTP.
- [x] Remover ou redirecionar referências legadas a `attendance.php`; o módulo atual é `faltas.php`.
- [ ] Configurar SMTP real para o fluxo de recuperação de senha. Bloqueado até definir provedor, host, porta, remetente e credenciais.
- [x] Proteger `tools/` e `database/` fora do ambiente de desenvolvimento.
- [ ] Normalizar nomes de tabelas para ambientes Linux sensíveis a maiúsculas/minúsculas.
- [ ] Executar plano LGPD: inventário, bases legais, aviso de privacidade, retenção e descarte.
- [ ] Definir política de documentos anexados: acesso, exclusão, versionamento e retenção. Rascunho técnico criado; falta aprovação operacional/jurídica.
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
- `docker-compose.test.yml`, `tests/run_all.ps1`, `tests/run_integration.php` e `tests/run_http_smoke.php` adicionados para validar banco e aplicação em ambiente descartável.
- Corrigido `Acolhimento::findByCpf()` para desbloquear fluxos psicológicos por CPF.
- Smoke HTTP ampliado para permissoes por perfil (`admin`, `psicologo`, `funcionario`) e uploads multipart de foto de perfil/documentos do prontuario.
- `SocioeconomicoController::update()` e `SocioeconomicoController::delete()` agora exigem `edit_records`/`delete_records` antes de alterar dados.
- Backup/restauracao do banco de teste automatizado no Docker e documentado em [[docs/BACKUP_RESTORE_RUNBOOK]].
- Rascunho de governanca de documentos criado em [[docs/DOCUMENT_GOVERNANCE_DRAFT]].
- Insumos de SMTP real organizados em [[docs/SMTP_SETUP]].

## Prioridade Média

- [x] Expandir testes automatizados além da suíte mínima atual.
- [x] Revisar endpoints psicológicos ainda não implementados.
- [x] Implementar primeira camada de relatórios PDF/Excel conforme roadmap: impressão/PDF pelo navegador e CSV compatível com Excel.
- [ ] Reduzir o tamanho do roteador em `index.php`.
- [ ] Alinhar `SETUP_COMPLETO_FINAL.sql`, `migration.sql` e `update_schema.sql`.
- [x] Decidir estratégia para tabelas legadas preservadas no schema.
- [x] Migrar reset tokens de `data/reset_tokens.json` para tabela própria com expiração.

## Concluído em Prioridade Média em 2026-06-01

- Reset tokens migrados para `password_reset_tokens`.
- Endpoints psicológicos `saveAssessment`, `search` e `report` implementados.
- Relatório psicológico com filtros, exportação CSV compatível com Excel e impressão/PDF pelo navegador.
- Relatório socioeconômico ganhou botão de impressão/PDF pelo navegador e CSV compatível com Excel.
- Estratégia de tabelas legadas documentada em [[docs/LEGACY_TABLE_STRATEGY]].
- Teste automatizado de exportação CSV adicionado.
- Check de backup/restauracao adicionado aos runners e ao CI.

## Prioridade Baixa

- [x] Comprimir imagens grandes em `img/`.
- [ ] Padronizar idioma de nomes internos.
- [ ] Avaliar Composer/autoload PSR-4 se o projeto crescer.

## Concluído em Prioridade Baixa em 2026-06-01

- Imagem de login otimizada de ~9 MB para ~250 KB mantendo o caminho existente.

## Revisão

- Última organização: 2026-06-01
- Nota central: [[00 - Painel do Projeto Criança Feliz]]
