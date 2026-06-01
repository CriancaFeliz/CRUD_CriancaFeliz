# Rascunho de Governanca de Documentos

Atualizado em 2026-06-01.

Este documento organiza a politica minima para documentos anexados ao prontuario. Ele e um rascunho tecnico-operacional e precisa de aprovacao da gestao/juridico antes de virar regra oficial.

## Estado Atual do Sistema

- Documentos sao anexados em `prontuarios.php?action=upload_document`.
- Upload exige login, CSRF, permissao `edit_records`, extensao permitida e MIME valido.
- Arquivos ficam em `uploads/documents/`.
- Abertura de documentos acontece por rota autenticada.
- A suite HTTP testa upload multipart e registro na tabela `documento`.

## Classes de Documento

| Tipo no sistema | Exemplos | Risco |
| --- | --- | --- |
| `identidade` | RG, CPF, certidao | Alto |
| `comprovante_residencia` | Conta de luz/agua, declaracao | Medio/alto |
| `escola` | Declaracoes, boletins, relatorios escolares | Medio/alto |
| `saude` | Laudos, encaminhamentos, receitas | Alto, pode conter dado sensivel |
| `autorizacao` | Termos assinados e consentimentos | Alto |
| `outros` | Materiais diversos | Classificar antes de aceitar em producao |

## Regras Tecnicas Recomendadas

1. Manter upload apenas para perfis com necessidade operacional clara.
2. Registrar em log quem anexou, quando anexou e em qual prontuario.
3. Criar fluxo de remocao com justificativa e trilha de auditoria, sem apagar fisicamente antes da regra de retencao.
4. Definir versao quando um documento for substituido.
5. Bloquear listagem/download para perfis sem finalidade de atendimento.
6. Incluir `uploads/documents/` na rotina de backup criptografado.
7. Revisar nomes de arquivo para nunca expor CPF, nome completo ou tipo sensivel no caminho publico.

## Decisoes Que Faltam

| Decisao | Por que falta | Precisa resolver |
| --- | --- | --- |
| Quem pode anexar | Hoje a regra tecnica usa `edit_records`, mas a politica pode ser mais restrita | Aprovar matriz por perfil e tipo de documento |
| Quem pode remover | Remocao pode afetar auditoria e comprovacao institucional | Definir papeis, justificativa obrigatoria e aprovacao |
| Prazo de retencao | Depende de obrigacao legal, contrato, finalidade e politica interna | Definir prazo por tipo de documento |
| Descarte/anonimizacao | Dados pessoais nao devem ser mantidos sem finalidade | Definir procedimento e responsavel |
| Versionamento | Documento substituido pode precisar ser preservado | Definir se substitui, arquiva ou cria nova versao |
| Termo/aviso de privacidade | Responsaveis precisam saber finalidade e tratamento | Redigir e aprovar aviso/termo |

## Proxima Implementacao Tecnica Possivel

Quando a matriz acima for aprovada, implementar:

- coluna de `uploaded_by` em `documento`;
- coluna de `deleted_at`/`deleted_by`/`delete_reason` para exclusao logica;
- tela de gerenciamento de documentos;
- testes HTTP para remocao, bloqueio por perfil e auditoria.
