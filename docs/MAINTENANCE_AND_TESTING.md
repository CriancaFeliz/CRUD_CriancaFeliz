# Manutencao, diagnosticos e testes manuais

Este arquivo registra para onde foram movidos os scripts que antes estavam na raiz do projeto.

## Diagnosticos

Pasta: `tools/diagnostics/`

Use estes arquivos apenas em ambiente local ou homologacao:

- `check_ficha_columns.php`: verifica colunas esperadas da ficha.
- `debug_buttons.php`: diagnostica botoes/acoes da area psicologica.
- `debug_edit_socio.php`: diagnostica edicao socioeconomica.
- `debug_renda_calculation.php`: diagnostica calculo de renda.
- `debug_renda_list.php`: lista dados de renda para conferencia.
- `debug_socio_batch.php`: diagnostico em lote de fichas socioeconomicas.
- `debug_socio_ficha.php`: diagnostico individual de ficha socioeconomica.
- `diagnostico_login.php`: verifica usuarios e senhas no banco.

## Manutencao

Pasta: `tools/maintenance/`

Use com muito cuidado, pois estes scripts alteram dados:

- `ativar_usuarios.php`: ativa usuarios.
- `corrigir_renda_marina.php`: correcao pontual de renda.
- `fix_renda_marina.php`: diagnostico/correcao pontual de renda.
- `fix_users.php`: correcao de usuarios em fluxo legado.
- `fix_users_mysql.php`: corrige/cria usuarios no MySQL.
- `generate_password.php`: gera hash de senha.
- `install_database.php`: instalador visual do banco.
- `limpar_sessao.php`: limpa sessao.

Recomendacao: em producao, mova `tools/` para fora do webroot ou proteja com autenticacao do servidor.

## Testes manuais

Pasta: `tests/manual/`

- `test_psychology.php`
- `test_psychology_edit_delete.php`
- `test_socioeconomico_submit.php`
- `test_users.php`

Eles ainda nao sao testes automatizados. Dependem de banco local, sessao e dados de exemplo.

## Validacoes executadas nesta revisao

- `php -l` em todos os arquivos PHP: OK em 125 arquivos.
- Auditoria de views referenciadas por controllers: todas as views existem.
- Smoke test com `php -S`:
  - `index.php`: HTTP 200.
  - `forgot.php`: HTTP 200.
  - `reset_password.php?token=invalido`: HTTP 302 para `forgot.php`.
  - rotas protegidas sem sessao (`dashboard.php`, `psychology.php`, `acolhimento_list.php`, `socioeconomico_list.php`, `profile.php`): HTTP 302 para login.
  - `POST index.php` com CSRF valido e credenciais de teste: HTTP 302, sem fatal.

Observacao de ambiente: o PHP CLI testado possui `PDO`, mas nao possui `pdo_mysql`. O projeto agora falha com mensagem clara nesse caso, mas para login real e banco MySQL a extensao `pdo_mysql` precisa estar habilitada.

## Legado

Pasta: `tools/legacy/`

- `users_simple.php`: prototipo/CRUD simplificado antigo de usuarios.

## Arquivos de amostra e logs

- `assets/samples/dog-para-teste.jpg`: imagem de amostra que antes ficava na raiz.
- `var/logs/debug.log`: log antigo movido da raiz.

## Checklist antes de usar scripts

1. Confirmar backup do banco.
2. Confirmar ambiente local/homologacao.
3. Conferir se o script usa as credenciais corretas.
4. Executar uma vez e registrar resultado.
5. Remover ou bloquear acesso depois do uso.
