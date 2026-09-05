# Plano de Correção das Lacunas Prioritárias

Atualizado em 05/09/2026.

Este documento organiza as frentes de Testes, LGPD, Banco, Documentos, Perfil/foto, Debug, Relatórios e Documentação.

## Matriz de Prioridade

| Lacuna | Risco | Ação nesta rodada | Próxima fase |
| --- | --- | --- | --- |
| Testes | Regressões invisíveis antes de produção | Suítes rápidas, integração e HTTP verdes; relatórios e documentos privados cobertos | SMTP sandbox, carga e backup real externo |
| LGPD | Tratamento de dados pessoais e sensíveis sem governança formal | Plano LGPD documentado | Inventário real, aviso de privacidade, retenção e atendimento ao titular |
| Banco | Divergência de nomes de tabelas em Linux | Diagnóstico `check_table_case.php` e plano de normalização | Migração controlada de nomes e constraints |
| Documentos | Dados sensíveis expostos por armazenamento público | Novos arquivos em área privada, URL direta bloqueada e entrega autenticada | Exclusão, versionamento e retenção aprovados |
| Perfil/foto | Foto não persistia no servidor | Upload validado, CSRF e coluna `foto_perfil` | Remoção de foto antiga e política de imagem |
| Debug | Logs com dados sensíveis em produção | `APP_DEBUG`, `debugLog`, `debugFileLog` e console gate | Revisão completa de logs e mascaramento por campo |
| Relatórios | RF10/RF19 eram parciais | Central com quatro relatórios, filtros, indicadores, CSV e impressão/PDF | Aceite institucional e novos formatos só se necessários |
| Documentação | Conhecimento espalhado | Estado atual, implantação, DER, UML, README e rastreabilidade atualizados | Manter docs como requisito de PR |

## O Que Ficou Fora Desta Rodada

- Migração completa de nomes de tabelas: exige backup, janela de manutenção e teste com base real.
- Parecer jurídico LGPD: o documento técnico apoia conformidade, mas não substitui validação jurídica.
- XLSX/PDF gerado no servidor: só implementar se o aceite exigir além do CSV e impressão/PDF atuais.
- Gestão completa de documentos: exclusão, versionamento e retenção ainda precisam de regra operacional.
