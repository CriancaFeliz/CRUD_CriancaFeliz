# LGPD e Governança de Dados

Atualizado em 2026-06-01.

Este documento é um plano técnico-operacional para apoiar conformidade. Ele não substitui revisão jurídica.

Fontes oficiais usadas:

- [Lei nº 13.709/2018 - LGPD, Planalto](https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm)
- [Guia ANPD de segurança para agentes de tratamento de pequeno porte](https://www.gov.br/anpd/pt-br/centrais-de-conteudo/materiais-educativos-e-publicacoes/guia-orientativo-sobre-seguranca-da-informacao-para-agentes-de-tratamento-de-pequeno-porte)

## Dados Tratados Pelo Sistema

| Categoria | Exemplos | Observação |
| --- | --- | --- |
| Identificação | nome, CPF, RG, nascimento, foto | Dados pessoais de atendidos e usuários |
| Responsáveis | nome, parentesco, contato, endereço | Dados pessoais de familiares/responsáveis |
| Socioeconômico | renda, despesas, moradia, benefícios | Pode revelar vulnerabilidade social |
| Frequência e desligamento | presenças, faltas, justificativas, motivos | Histórico operacional do atendimento |
| Psicologia | anotações e evolução psicológica | Dados sensíveis e acesso segregado |
| Documentos | identidade, comprovantes, autorizações, saúde/escola | Alto risco se exposto indevidamente |
| Auditoria | usuário, ação, IP, alterações | Necessário para segurança e prestação de contas |

## Medidas Técnicas Já Existentes ou Adicionadas

- Senhas novas com `PasswordHelper`, Argon2id quando disponível e fallback bcrypt.
- CSRF em fluxos sensíveis.
- Prepared statements via PDO.
- Headers básicos de segurança.
- Segregação de permissões por perfil.
- Logs de debug condicionados a `APP_DEBUG`.
- Upload de documentos e foto com validação de extensão/MIME.
- Bloqueio web direto a pastas sensíveis via `.htaccess`.

## Plano de Adequação

| Prioridade | Ação | Dono sugerido |
| --- | --- | --- |
| Alta | Criar inventário de dados por módulo e finalidade | Coordenação + TI |
| Alta | Definir base legal para cada tratamento | Jurídico/gestão |
| Alta | Criar aviso de privacidade para responsáveis e usuários | Jurídico/gestão |
| Alta | Definir retenção e descarte de fichas, documentos e logs | Gestão + TI |
| Alta | Nomear canal/responsável para solicitações de titulares | Gestão |
| Alta | Formalizar processo de incidente de segurança | Gestão + TI |
| Média | Mascarar CPF em telas/listagens quando possível | TI |
| Média | Revisar backups criptografados e teste de restauração | TI |
| Média | Criar tabela para reset tokens em vez de arquivo local | TI |
| Média | Revisar logs para minimizar dados pessoais | TI |

## Checklist de Produção

- `APP_DEBUG=false`.
- SMTP real configurado.
- Banco com usuário de privilégio mínimo.
- Backups criptografados e testados.
- Acesso a `tools/`, `database/`, `data/`, `var/` e `docker/` bloqueado fora do desenvolvimento.
- Acesso psicológico disponível apenas para perfil autorizado.
- Rotina definida para atender solicitações de acesso, correção e eliminação quando aplicável.
- Plano de comunicação de incidente com prazos e responsáveis.

## Pendências De Produto

- Tela de consentimentos/ciência documental, se a base legal definida exigir.
- Trilhas de auditoria mais legíveis por registro.
- Controle de exclusão/anonimização conforme retenção aprovada.
- Relatório de acesso por usuário para auditoria interna.
