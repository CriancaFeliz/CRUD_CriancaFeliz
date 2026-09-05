# Implantação Segura

Este roteiro serve para um ambiente novo. A atualização de um banco legado usa
`database/migrations/20260825_upgrade_legacy_production.sql` e deve ser
ensaiada primeiro em uma cópia do banco.

## 1. Requisitos

- PHP 7.4 ou superior com `pdo_mysql`, `mbstring` e `fileinfo`;
- Apache com `mod_rewrite` ou configuração equivalente;
- MySQL 5.7+ ou MariaDB 10.3+;
- HTTPS obrigatório para uso real;
- diretório de backup fora da raiz pública.

## 2. Banco novo

Crie um banco `utf8mb4` e importe:

```text
database/SETUP_COMPLETO_FINAL.sql
```

O setup não cria usuários nem dados de crianças. Ele inclui apenas oficinas
iniciais de apoio. Nunca importe os dumps de `database/legacy_dumps` em um
ambiente público.

## 3. Configuração

Copie `.env.example` para `.env` e defina, no mínimo:

```env
APP_ENV=production
APP_DEBUG=false
APP_BASE_URL=https://endereco-real.example
APP_KEY=segredo-aleatorio-com-pelo-menos-32-caracteres
SESSION_COOKIE_SECURE=true

DB_HOST=servidor-do-banco
DB_PORT=3306
DB_NAME=criancafeliz
DB_USER=usuario_exclusivo
DB_PASS=senha-forte-e-exclusiva
DB_CHARSET=utf8mb4
```

O usuário do banco deve ter apenas os privilégios necessários sobre esse banco.
Não use `root` na aplicação.

## 4. Primeiro administrador

Defina temporariamente no ambiente:

```env
INITIAL_ADMIN_NAME=Administrador responsável
INITIAL_ADMIN_EMAIL=email-institucional@example.org
INITIAL_ADMIN_PASSWORD=senha-longa-unica-e-forte
```

Execute em linha de comando:

```bash
php tools/maintenance/create_admin.php
```

Remova imediatamente as três variáveis `INITIAL_ADMIN_*`. Não há senha
padrão ou conta de demonstração.

## 5. Permissões de diretório

O processo do PHP precisa escrever somente em:

- `var/logs`;
- `var/private/documents`;
- `uploads/profiles`;
- `data` enquanto os recursos locais ainda forem usados.

As demais pastas devem ficar somente para leitura. Confirme que
`var/private`, `database`, `tools`, `tests`,
`docs` e `.env` não respondem pela web.

## 6. Migração de versão existente

1. coloque o sistema em manutenção;
2. faça backup do banco e dos anexos;
3. restaure ambos em um ambiente de homologação;
4. execute o script de upgrade legado;
5. rode a suíte de integração e os fluxos HTTP;
6. confira contagens de usuários, atendidos, fichas, frequência e documentos;
7. só então repita a operação no servidor real.

O código mantém leitura de `uploads/documents` para anexos antigos. Novos
arquivos são gravados em `var/private/documents`. A migração física dos
antigos pode ser feita depois, com inventário e backup.

## 7. Backup e restauração

Inclua no backup:

- banco completo com triggers, procedures e views;
- `var/private/documents`;
- anexos legados em `uploads/documents`, se existirem;
- fotos em `uploads/profiles`;
- configuração segura guardada separadamente.

Criptografe o backup, limite o acesso e mantenha uma cópia fora do servidor.
Teste a restauração periodicamente; um arquivo de backup nunca testado não é uma
garantia de recuperação.

## 8. Aceite antes da publicação

- HTTPS válido e redirecionamento de HTTP;
- `APP_DEBUG=false`;
- login e logout;
- matriz de perfis confirmada;
- documentos inacessíveis por URL direta;
- geração dos quatro relatórios;
- backup e restauração testados;
- SMTP configurado ou recuperação de senha formalmente desabilitada;
- política de retenção/LGPD aprovada;
- dumps históricos removidos do repositório que será publicado.
