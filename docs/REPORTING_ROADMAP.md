# Roadmap de Relatórios

Atualizado em 2026-06-01.

## Estado Atual

O projeto já possui exportações CSV em módulos como acolhimento, socioeconômico e logs. O requisito de relatórios automáticos ainda precisa evoluir para relatórios oficiais em PDF/Excel com filtros e layout padronizado.

Nesta rodada de prioridade média, foi entregue a primeira camada prática:

- relatório socioeconômico com impressão/PDF pelo navegador;
- relatório psicológico com filtros, impressão/PDF pelo navegador e CSV compatível com Excel;
- exportação CSV usando helper testado para escapar aspas e quebras de linha.

## Relatórios Prioritários

| Prioridade | Relatório | Campos principais |
| --- | --- | --- |
| Alta | Atendidos ativos | nome, CPF mascarado, idade, status, data de acolhimento |
| Alta | Frequência e faltas | atendido, oficina/dia, presença, faltas justificadas/não justificadas |
| Alta | Desligamentos | motivo, data, responsável pelo registro, possibilidade de retorno |
| Alta | Socioeconômico sintético | faixa de renda, benefícios, composição familiar, bairro |
| Média | Documentos pendentes | atendido, tipo de documento ausente, data do último anexo |
| Média | Auditoria | usuário, ação, módulo, período |
| Média | Psicologia | apenas para perfil autorizado, com escopo mínimo e cuidado LGPD |

## Decisões Necessárias

- Formato: PDF, XLSX ou ambos.
- Biblioteca para fase seguinte: Composer com `dompdf`/`mpdf` para PDF real e `phpoffice/phpspreadsheet` para XLSX real. Até lá, usar impressão/PDF do navegador e CSV compatível com Excel.
- Layout oficial: cabeçalho, logo, assinatura, filtros aplicados e data de geração.
- Controle de acesso: quais perfis podem gerar cada relatório.
- Retenção: por quanto tempo relatórios gerados ficam disponíveis.

## Implementação Sugerida

1. Criar `ReportController`.
2. Criar service por relatório.
3. Reutilizar filtros dos módulos existentes.
4. Adicionar auditoria de geração.
5. Criar testes de integração para filtros e permissões.

## Critério de Pronto

- Relatório não expõe dados além do necessário.
- Geração registra usuário, filtros e data.
- Arquivo gerado abre corretamente.
- Exportação respeita o perfil do usuário.
