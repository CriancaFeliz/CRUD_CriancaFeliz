# Estado Atual do Sistema

Atualizado em 05/09/2026. Este é o documento técnico de referência da versão
em desenvolvimento na branch `codex/reconstrucao-release-agosto`.

## Objetivo

O sistema centraliza o atendimento da Associação Criança Feliz: acolhimento,
prontuário, situação socioeconômica, frequência, oficinas, desligamentos,
registros psicológicos, relatórios, usuários e auditoria.

A monografia de 2025 é a fonte dos objetivos acadêmicos. O código, o schema
`database/SETUP_COMPLETO_FINAL.sql` e este documento descrevem o que existe
de fato em 2026. Funcionalidades citadas na monografia não devem ser apresentadas
como prontas sem confirmação nesta documentação.

## Módulos disponíveis

| Área | Estado verificável |
| --- | --- |
| Autenticação | Login, logout por POST, bloqueio de tentativas, sessão segura e recuperação por token |
| Usuários | Cadastro, edição, ativação/desativação e papéis `admin`, `funcionario` e `psicologo` |
| Acolhimento | Assistente em quatro etapas, edição, listagem, busca e validações |
| Prontuário | Visão consolidada, responsável e anexos entregues por rota autenticada |
| Socioeconômico | Assistente em cinco etapas, composição familiar, benefícios, renda e moradia |
| Frequência | Chamada diária e por oficina, justificativas, histórico e alertas |
| Desligamentos | Manual, automático pelas regras existentes e reativação |
| Psicologia | Pacientes, anotações, avaliações e relatório restrito ao psicólogo |
| Relatórios | Atendidos, frequência, desligamentos e síntese socioeconômica; CSV e impressão/PDF |
| Auditoria | Consulta de logs e registro das ações centrais |
| Interface | Tema claro/escuro, responsividade, calendário, avisos e chatbot local |

## Perfis atuais

| Recurso | Administrador | Funcionário | Psicólogo |
| --- | :---: | :---: | :---: |
| Consulta geral de prontuários | Sim | Sim | Sim |
| Alterar fichas operacionais | Sim | Não | Não |
| Frequência e oficinas | Sim | Consulta operacional | Não |
| Usuários, desligamentos e logs | Sim | Não | Não |
| Relatórios administrativos | Sim | Não | Não |
| Área psicológica e suas notas | Não | Não | Sim |

A separação da psicologia é deliberada para confidencialidade. Qualquer mudança
nessa matriz depende de validação formal da ONG.

## Segurança já aplicada

- consultas PDO preparadas e emulação desativada;
- proteção CSRF nas alterações;
- senha forte com hash seguro e nenhuma conta padrão no setup;
- limitação persistente de tentativas de login e recuperação;
- mensagens de autenticação que não revelam se a conta existe;
- cookies de sessão `HttpOnly`, `SameSite=Lax` e `Secure` configurável;
- escape de saída e remoção de construções inseguras de HTML nos fluxos revisados;
- CSV protegido contra fórmulas e CPF mascarado nos relatórios;
- documentos, fotos de perfil e fotos de acolhimento novos em `var/private`, fora da área pública;
- bloqueio HTTP para código, banco, logs, ferramentas, testes e documentação;
- modo de depuração desativado por padrão no Docker.

## Validação automatizada

Última execução local aprovada:

- 12 testes rápidos, 33 asserções;
- 15 testes de integração, 94 asserções;
- 9 fluxos HTTP, 152 asserções;
- importação integral do schema em MariaDB isolado;
- inspeção visual dos quatro relatórios no navegador.

Em 05/09/2026, a validação foi repetida localmente no XAMPP com uma base
isolada `criancafeliz_test`: os mesmos testes rápidos, de integração e HTTP
foram aprovados sem alterar a base existente `criancafeliz`.

Os números mudam quando novos testes são adicionados. A fonte executável é a
pasta `tests/`.

## Pendências que dependem da ONG ou da hospedagem

- confirmar a regra oficial para alerta e desligamento por faltas;
- confirmar a matriz final de acesso entre profissionais;
- configurar HTTPS e SMTP no servidor definitivo;
- definir retenção, descarte e responsáveis pelos dados sob a LGPD;
- definir e testar a rotina automatizada de backup fora do servidor;
- decidir se SMS/e-mail para responsáveis faz parte do escopo;
- decidir se operação offline é realmente necessária;
- manter backups e dumps com dados reais fora do repositório; a pasta
  `database/legacy_dumps/` é ignorada pelo Git para evitar novo envio acidental.

## Documentos relacionados

- `docs/DEPLOYMENT.md`: instalação e implantação segura;
- `docs/ERD.md`: modelo de dados atual;
- `docs/UML.md`: arquitetura e fluxos principais;
- `docs/REQUIREMENTS_TRACEABILITY.md`: requisitos da monografia versus entrega;
- `docs/LGPD_AND_DATA_GOVERNANCE.md`: decisões operacionais de privacidade.
