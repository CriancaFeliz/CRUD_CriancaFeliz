# Rastreabilidade de Requisitos

Atualizado em 05/09/2026 com base na monografia de 2025 e em testes do código atual.

Baseado nos requisitos identificados na monografia analisada.

| Req. | Tema | Estado no projeto | Próxima ação |
| --- | --- | --- | --- |
| RF01 | Ficha de acolhimento | Implementado; assistente em quatro etapas e fluxo HTTP testado | Validar campos finais com a ONG |
| RF02 | Prontuário digital | Implementado para dados centrais e anexos privados | Definir retenção e versionamento |
| RF03 | Evolução escolar/social | Parcial; há evolução psicológica, mas não um fluxo social/escolar completo | Definir formulário oficial |
| RF04 | Controle de frequência | Implementado para dia e oficina, com integração testada | Validar regras operacionais |
| RF05 | Critérios de desligamento | Implementado manual e automático por faltas | Confirmar limiar e maioridade com a ONG |
| RF06 | Transição automática | Implementado por cálculo dinâmico de idade, sem trocar o prontuário | Padronizar limites etários |
| RF07 | Perfis e níveis de acesso | Implementado e testado para admin, funcionário e psicólogo | Aprovação formal da matriz |
| RF08 | Chatbot de dúvidas | Implementado como ajuda local de opções fixas | Atualizar textos após aceite |
| RF09 | Planejamento de oficinas | Implementado para cadastro, ativação e frequência | Validar planejamento mensal desejado |
| RF10 | Relatórios automáticos | Implementado: atendidos, frequência, desligamentos e socioeconômico | Validar modelos de prestação de contas |
| RF11 | Ficha socioeconômica | Implementado em cinco etapas, com renda e composição familiar | Validar campos com serviço social |
| RF12 | Notificações | Implementado para feedback da interface | Uniformizar telas remanescentes |
| RF13 | Modo escuro/claro | Implementado com preferência local | Completar auditoria visual |
| RF14 | Dashboard interativo | Implementado com calendário, alertas, estatísticas e notas | Confirmar indicadores úteis |
| RF15 | Validação de dados | Implementado nos fluxos centrais; cobertura ainda não total | Revisar campos legados |
| RF16 | Sistema de busca | Implementado por nome/CPF nos módulos centrais | Avaliar RG conforme uso real |
| RF17 | Histórico de alterações | Parcial; logs e triggers existem, mas nem toda entidade tem diff completo | Ampliar cobertura |
| RF18 | Agendamento de atendimentos | Parcial; calendário possui notas/avisos, não agenda clínica completa | Definir fluxo e responsáveis |
| RF19 | Exportação de dados | Parcial; relatórios oferecem CSV e impressão/PDF | Definir exportação de prontuário |
| RF20 | Sistema de alertas | Parcial; faltas e maioridade cobertas | Aniversários e vencimentos dependem de regra |
| RF21 | Gestão de documentos | Implementado para upload, lista e visualização autenticada | Definir exclusão e retenção |
| RF22 | Comunicação com responsáveis | Não implementado | Definir canal, consentimento e custo |
| RF23 | Auditoria e logs | Implementado nos fluxos principais | Revisão LGPD e retenção |

## Requisitos Não Funcionais Críticos

| Tema | Estado | Próxima ação |
| --- | --- | --- |
| Segurança/confidencialidade | Parcial forte | Validar infraestrutura, criptografia em repouso e matriz com a ONG |
| Disponibilidade e backup | Parcial | Agendar backup externo e testar restauração no servidor definitivo |
| Performance | Não homologado | Medir com volume e concorrência representativos |
| Responsividade | Implementado nas telas centrais | Testar aparelhos reais |
| Acessibilidade | Parcial | Auditoria WCAG, teclado e leitor de tela |
| LGPD | Controles técnicos e plano existentes | Aprovar política, bases legais, retenção e responsáveis |
| Monitoramento | Parcial | Centralizar logs e alertas da hospedagem |
| Offline/cache | Não implementado | Confirmar se continua no escopo; evitar dados sensíveis no navegador |
