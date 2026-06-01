# Rastreabilidade de Requisitos

Atualizado em 2026-06-01.

Baseado nos requisitos identificados na monografia analisada.

| Req. | Tema | Estado no projeto | Próxima ação |
| --- | --- | --- | --- |
| RF01 | Ficha de acolhimento | Implementado e coberto por integracao | Ampliar CRUD HTTP quando necessario |
| RF02 | Prontuário digital | Parcial | Consolidar documentos, histórico e busca |
| RF03 | Evolução escolar/social | Parcial | Definir módulo/campos oficiais |
| RF04 | Controle de frequencia | Implementado e coberto por integracao | Relatorios oficiais |
| RF05 | Critérios de desligamento | Implementado parcial | Validar regras com gestão |
| RF06 | Transição automática | Parcial | Testar e documentar gatilhos |
| RF07 | Perfis e niveis de acesso | Implementado parcial, com smoke HTTP por perfil | Revisar matriz de permissoes operacional |
| RF08 | Chatbot de dúvidas | Implementado básico | Definir escopo e base de conhecimento |
| RF09 | Planejamento de oficinas | Parcial | Ampliar agenda/capacidade |
| RF10 | Relatórios automáticos | Parcial | Seguir `REPORTING_ROADMAP.md` |
| RF11 | Ficha socioeconomica | Implementado e coberto por integracao | Revisao de logs e regras operacionais |
| RF12 | Notificações | Implementado básico | Integrar eventos reais |
| RF13 | Modo escuro/claro | Implementado | Teste visual |
| RF14 | Dashboard interativo | Implementado parcial | Revisar indicadores |
| RF15 | Validação de dados | Parcial | Padronizar validações server/client |
| RF16 | Sistema de busca | Parcial | Unificar busca por CPF/nome |
| RF17 | Histórico de alterações | Implementado parcial | Revisar cobertura dos triggers/logs |
| RF18 | Agendamento de atendimentos | Parcial | Definir fluxo |
| RF19 | Exportação de dados | Parcial | Expandir CSV/PDF/XLSX |
| RF20 | Sistema de alertas | Parcial | Validar regras operacionais |
| RF21 | Gestao de documentos | Parcial, com upload/listagem e smoke HTTP multipart | Adicionar exclusao, versionamento e retencao |
| RF22 | Comunicação com responsáveis | Não implementado | Definir canal e consentimentos |
| RF23 | Auditoria e logs | Implementado parcial | Mascaramento e revisão LGPD |

## Requisitos Não Funcionais Críticos

| Tema | Estado | Próxima ação |
| --- | --- | --- |
| Segurança/confidencialidade | Parcial | LGPD, permissões, backup e logs |
| Disponibilidade | Parcial | Docker, backup e restore testados |
| Performance | Não medido | Criar métricas e índices |
| Acessibilidade | Parcial | Auditoria visual e navegação por teclado |
| LGPD | Plano criado | Executar governança operacional |
| Monitoramento | Parcial | Logs estruturados e alertas |
