# Plano de Correção das Lacunas Prioritárias

Atualizado em 2026-06-01.

Este documento organiza as frentes de Testes, LGPD, Banco, Documentos, Perfil/foto, Debug, Relatórios e Documentação.

## Matriz de Prioridade

| Lacuna | Risco | Ação nesta rodada | Próxima fase |
| --- | --- | --- | --- |
| Testes | Regressoes invisiveis antes de producao | Runner PHP, CI, integracao MySQL Docker, smoke HTTP, permissoes por perfil, uploads multipart e backup/restore no Docker | SMTP sandbox, backup real externo e testes de relatorios oficiais |
| LGPD | Tratamento de dados pessoais e sensíveis sem governança formal | Plano LGPD documentado | Inventário real, aviso de privacidade, retenção e atendimento ao titular |
| Banco | Divergência de nomes de tabelas em Linux | Diagnóstico `check_table_case.php` e plano de normalização | Migração controlada de nomes e constraints |
| Documentos | RF21 estava apenas no schema, sem fluxo real | Upload/listagem no prontuario, abertura via rota autenticada e rascunho de governanca | Exclusao, versionamento, classificacao e retencao aprovados |
| Perfil/foto | Foto não persistia no servidor | Upload validado, CSRF e coluna `foto_perfil` | Remoção de foto antiga e política de imagem |
| Debug | Logs com dados sensíveis em produção | `APP_DEBUG`, `debugLog`, `debugFileLog` e console gate | Revisão completa de logs e mascaramento por campo |
| Relatórios | RF10/RF19 ainda parciais | Roadmap, impressão/PDF pelo navegador e CSV compatível com Excel | PDF/XLSX reais com biblioteca dedicada |
| Documentação | Conhecimento espalhado | Novos docs, README e Obsidian atualizados | Manter docs como requisito de PR |

## O Que Ficou Fora Desta Rodada

- Migração completa de nomes de tabelas: exige backup, janela de manutenção e teste com base real.
- Parecer jurídico LGPD: o documento técnico apoia conformidade, mas não substitui validação jurídica.
- Relatórios PDF/Excel: exige definir layout oficial, campos e biblioteca.
- Gestão completa de documentos: exclusão, versionamento e retenção ainda precisam de regra operacional.
