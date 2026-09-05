# DER - Modelo de Dados Atual

Fonte: `database/SETUP_COMPLETO_FINAL.sql`, validado por importação em
MariaDB em 05/09/2026. O diagrama mostra as entidades centrais; campos descritivos
foram resumidos para manter a leitura.

```mermaid
erDiagram
    RESPONSAVEL ||--o{ ATENDIDO : acompanha
    ATENDIDO ||--o| FICHA_SOCIOECONOMICO : possui
    FICHA_SOCIOECONOMICO ||--o{ FAMILIA : registra
    FICHA_SOCIOECONOMICO ||--o{ DESPESAS : consolida
    ATENDIDO ||--o{ DOCUMENTO : anexa
    ATENDIDO ||--o{ ENCONTRO : evolui
    USUARIO ||--o{ ENCONTRO : registra
    ATENDIDO ||--o{ FREQUENCIA_DIA : recebe
    USUARIO ||--o{ FREQUENCIA_DIA : registra
    ATENDIDO ||--o{ FREQUENCIA_OFICINA : recebe
    OFICINA ||--o{ FREQUENCIA_OFICINA : organiza
    USUARIO ||--o{ FREQUENCIA_OFICINA : registra
    ATENDIDO ||--o| DESLIGAMENTO : possui
    USUARIO ||--o{ DESLIGAMENTO : executa
    ATENDIDO ||--o{ ANOTACAO_PSICOLOGICA : possui
    USUARIO ||--o{ ANOTACAO_PSICOLOGICA : escreve
    USUARIO ||--o{ LOG : origina
    SESSAO ||--o{ PRESENCA : agrupa
    ATENDIDO ||--o{ PRESENCA : recebe
    USUARIO ||--o{ PRESENCA : registra

    USUARIO {
        int idusuario PK
        varchar nome
        varchar email
        varchar Senha
        varchar nivel
        varchar status
    }
    RESPONSAVEL {
        int idresponsavel PK
        varchar nome
        varchar cpf
        varchar telefone
        varchar parentesco
    }
    ATENDIDO {
        int idatendido PK
        int id_responsavel FK
        varchar nome
        date data_nascimento
        varchar cpf
        varchar status
        int faixa_etaria
    }
    FICHA_SOCIOECONOMICO {
        int idficha PK
        int id_atendido FK
        decimal renda_familiar
        decimal renda_per_capita
        int qtd_pessoas
        varchar residencia
    }
    FAMILIA {
        int id_familia PK
        int id_ficha FK
        varchar nome
        varchar parentesco
        decimal renda
    }
    DESPESAS {
        int id_despesa PK
        int id_ficha FK
        varchar tipo_renda
        decimal valor_renda
        decimal valor_despesa
    }
    DOCUMENTO {
        int iddocumento PK
        int IDatendido FK
        varchar tipo
        varchar arquivo
        datetime data_upload
    }
    ENCONTRO {
        int id_encontro PK
        int id_atendido FK
        int ID_usuario FK
        date Dataencontro
        varchar evolucao
    }
    OFICINA {
        int id_oficina PK
        varchar nome
        varchar dia_semana
        time horario_inicio
        boolean ativo
    }
    FREQUENCIA_DIA {
        int id_frequencia_dia PK
        int id_atendido FK
        int registrado_por FK
        date data
        enum status
    }
    FREQUENCIA_OFICINA {
        int id_frequencia PK
        int id_atendido FK
        int id_oficina FK
        int registrado_por FK
        date data
        enum status
    }
    DESLIGAMENTO {
        int id_desligamento PK
        int id_atendido FK
        int desligado_por FK
        varchar motivo
        enum tipo_motivo
        date data_desligamento
        boolean automatico
    }
    ANOTACAO_PSICOLOGICA {
        int id_anotacao PK
        int id_atendido FK
        int id_psicologo FK
        datetime data_anotacao
        enum tipo
        text conteudo
    }
    LOG {
        int id_log PK
        int id_usuario FK
        datetime data_alteracao
        varchar acao
        varchar tabela_afetada
        longtext dados_completos
    }
    SESSAO {
        int id_sessao PK
        int criado_por FK
        date data_sessao
    }
    PRESENCA {
        int id_presenca PK
        int id_sessao FK
        int id_atendido FK
        int registrado_por FK
        enum status
    }
```

## Tabelas de infraestrutura

- `agenda`: avisos e notificações do dashboard;
- `password_reset_tokens`: hashes temporários de recuperação de senha;
- `auth_rate_limits`: limitação de tentativas de autenticação;
- `dias_atendimento`: apoio ao calendário;
- `atendidos_com_alerta`: view derivada de faltas.

`sessao` e `presenca` são estruturas legadas mantidas por compatibilidade.
Os fluxos atuais usam principalmente `frequencia_dia` e
`frequencia_oficina`.
