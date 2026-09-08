# Roadmap de Relatórios

Atualizado em 05/09/2026.

## Estado Atual

Foi entregue uma Central de Relatórios administrativa com filtros, indicadores,
layout padronizado, auditoria, limite de volume, CSV UTF-8 protegido contra
fórmulas e impressão/PDF pelo navegador. CPF é mascarado nas saídas.

## Relatórios Prioritários

| Prioridade | Relatório | Campos principais |
| --- | --- | --- |
| Entregue | Atendidos ativos | nome, CPF mascarado, idade, bairro, acolhimento e responsável |
| Entregue | Frequência e faltas | atendido, oficina/dia, presença, faltas e justificativas |
| Entregue | Desligamentos | motivo, data, responsável, automático/manual e retorno |
| Entregue | Socioeconômico sintético | renda, benefícios, composição familiar, moradia e bairro |
| Média | Documentos pendentes | atendido, tipo de documento ausente, data do último anexo |
| Média | Auditoria | usuário, ação, módulo, período |
| Média | Psicologia | apenas para perfil autorizado, com escopo mínimo e cuidado LGPD |

## Decisões Necessárias

- Confirmar se impressão/PDF e CSV bastam ou se XLSX/PDF gerado no servidor é obrigatório.
- Aprovar o layout oficial de prestação de contas e eventual campo de assinatura.
- Confirmar se algum perfil além do administrador pode gerar cada relatório.
- Retenção: por quanto tempo relatórios gerados ficam disponíveis.

## Implementação Sugerida

1. Validar os quatro modelos com a coordenação e os profissionais.
2. Ajustar campos e indicadores conforme o uso real.
3. Implementar novos formatos somente se forem exigidos.
4. Adicionar relatório de documentos pendentes após definir quais documentos são obrigatórios.

## Critério de Pronto

- os quatro relatórios atuais cumprem os critérios técnicos acima;
- o aceite institucional de conteúdo e layout ainda precisa ser registrado.
