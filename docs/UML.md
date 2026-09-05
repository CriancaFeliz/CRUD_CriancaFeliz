# Arquitetura e Fluxos

## Componentes

```mermaid
flowchart LR
    U[Usuário autenticado] --> W[Apache ou servidor PHP]
    W --> F[index.php - Front Controller]
    F --> C[Controllers]
    C --> A[AuthService e permissões]
    C --> S[Services - regras de negócio]
    S --> M[Models - PDO]
    M --> D[(MySQL ou MariaDB)]
    C --> V[Views PHP]
    V --> U
    C --> P[(var/private/documents)]
    C --> L[(log de auditoria)]
```

O projeto usa MVC sem framework. `index.php` resolve as rotas; controllers
validam autenticação, permissão e CSRF; services concentram regras; models acessam
o banco por consultas preparadas; views produzem a interface.

## Cadastro de acolhimento

```mermaid
sequenceDiagram
    actor Profissional
    participant Tela as Assistente de acolhimento
    participant Controller as AcolhimentoController
    participant Service as AcolhimentoService
    participant DB as Banco
    Profissional->>Tela: preenche quatro etapas
    Tela->>Controller: POST único com CSRF
    Controller->>Controller: valida e normaliza campos
    Controller->>Service: solicita criação ou edição
    Service->>DB: grava responsável e atendido em transação
    DB-->>Service: identificadores persistidos
    Service-->>Controller: resultado
    Controller-->>Profissional: redireciona com confirmação
```

## Documento de prontuário

```mermaid
sequenceDiagram
    actor Profissional
    participant Controller as ProntuarioController
    participant Private as Área privada
    participant DB as Banco
    Profissional->>Controller: envia arquivo com CSRF
    Controller->>Controller: valida permissão, extensão, MIME e tamanho
    Controller->>Private: grava nome aleatório
    Controller->>DB: registra caminho privado
    Profissional->>Controller: solicita documento autenticado
    Controller->>DB: localiza metadados
    Controller->>Controller: confere caminho permitido
    Controller->>Private: lê arquivo
    Controller-->>Profissional: entrega com nosniff
```

## Relatórios

```mermaid
sequenceDiagram
    actor Admin
    participant Controller as ReportController
    participant Service as ReportService
    participant DB as Banco
    Admin->>Controller: escolhe tipo e filtros
    Controller->>Controller: exige view_reports
    Controller->>Service: filtros normalizados
    Service->>DB: consulta parametrizada e limitada
    DB-->>Service: resultados
    Service-->>Controller: colunas, linhas e indicadores
    alt visualização
        Controller-->>Admin: HTML para tela ou impressão
    else exportação
        Controller-->>Admin: CSV UTF-8 com CPF mascarado
    end
```
