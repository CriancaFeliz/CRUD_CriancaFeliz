<p align="center">
  <img src="img/logo.png" alt="Associação Criança Feliz" width="180">
</p>

<h1 align="center">Sistema Criança Feliz</h1>

<p align="center">
  Sistema web em PHP para gerenciamento de atendidos da Associação Criança Feliz.<br>
  O projeto centraliza acolhimento, fichas socioeconômicas, prontuários,<br>
  frequência, desligamentos, área psicológica, usuários, perfil e auditoria.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/MySQL%2FMariaDB-PDO-4479A1?logo=mysql&logoColor=white" alt="MySQL/MariaDB">
  <img src="https://img.shields.io/badge/Arquitetura-MVC%20sem%20framework-f0a36b" alt="MVC sem framework">
  <img src="https://img.shields.io/badge/Licença-MIT-green" alt="MIT License">
</p>

---

## Índice

- [Funcionalidades](#funcionalidades)
- [Tecnologias](#tecnologias)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Execução local](#execução-local)
- [Configuração](#configuração)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Rotas principais](#rotas-principais)
- [Perfis de acesso](#perfis-de-acesso)
- [Documentação](#documentação)
- [Observações importantes](#observações-importantes)
- [Contribuição](#contribuição)
- [Licença](#licença)

---

## Funcionalidades

| Módulo | Descrição |
| --- | --- |
| Dashboard | Painel inicial com indicadores, calendário, notas e alertas operacionais. |
| Prontuários | Consulta consolidada dos dados dos atendidos por CPF/nome. |
| Acolhimento | Cadastro, edição, visualização, busca, paginação e exportação CSV. |
| Socioeconômico | Formulário multi-etapas, família, despesas, cálculo de renda e relatórios. |
| Controle de faltas | Frequência diária e por oficina, histórico e alertas. |
| Desligamento | Desligamento manual, processamento automático por faltas e reativação. |
| Área psicológica | Lista de pacientes, prontuário psicológico e anotações por paciente. |
| Usuários | CRUD de usuários com papéis e ativação/desativação. |
| Logs | Auditoria com filtros, detalhe de alterações, APIs JSON e exportação CSV. |
| Perfil | Foto de perfil, dados do usuário e troca de senha. |
| Recuperação de senha | Geração de token local para redefinição; integração SMTP ainda deve ser configurada. |
| Interface | Tema claro/escuro, layout responsivo e chatbot integrado. |

---

## Tecnologias

- Back-end: PHP 7.4+ em arquitetura MVC simples, sem framework externo.
- Banco de dados: MySQL/MariaDB via PDO.
- Front-end: HTML5, CSS3 responsivo e JavaScript vanilla.
- Segurança: sessões PHP, CSRF tokens, prepared statements, headers básicos de segurança e senhas com `password_hash`.
- Roteamento: front controller em `index.php`, com `.htaccess` para Apache e `var/dev-router.php` para o servidor embutido do PHP.

---

## Pré-requisitos

- PHP 7.4 ou superior, compatível com PHP 8.x.
- Extensão PHP `pdo_mysql` habilitada.
- MySQL 5.7+ ou MariaDB 10.3+.
- Apache com `mod_rewrite` habilitado, ou servidor embutido do PHP para desenvolvimento.
- Cliente MySQL ou phpMyAdmin para importar o banco.
- Opcional: Docker Desktop com Docker Compose, caso queira subir aplicação, MySQL e phpMyAdmin em containers.

---

## Instalação

```bash
git clone https://github.com/CriancaFeliz/CRUD_CriancaFeliz.git
cd CRUD_CriancaFeliz
```

Crie o banco:

```bash
mysql -u root -e "CREATE DATABASE criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Importe o schema completo:

```bash
mysql -u root criancafeliz < database/SETUP_COMPLETO_FINAL.sql
```

Usuário inicial criado pelo setup:

| Campo | Valor |
| --- | --- |
| Email | `admin@criancafeliz.org` |
| Senha | `admin123` |
| Perfil | `admin` |

> Em algumas instalações MySQL/Linux, nomes de tabela são sensíveis a maiúsculas e minúsculas. O código atual usa nomes como `Atendido`, `Usuario` e `Ficha_Socioeconomico`, enquanto alguns scripts SQL também preservam nomes em minúsculas. Se o ambiente tiver `lower_case_table_names=0`, valide a importação e os nomes das tabelas antes de usar em produção.

---

## Execução local

### Apache, XAMPP, WAMP ou Laragon

1. Coloque o projeto dentro do webroot, por exemplo `C:\xampp\htdocs\CRUD_CriancaFeliz`.
2. Habilite `mod_rewrite`.
3. Mantenha o arquivo `.htaccess` na raiz.
4. Acesse:

```text
http://localhost/CRUD_CriancaFeliz/
```

### Servidor embutido do PHP

Para desenvolvimento, use o roteador local:

```bash
php -S localhost:8000 var/dev-router.php
```

Depois acesse:

```text
http://localhost:8000/
```

### Docker Compose

Para subir um ambiente local isolado com Apache/PHP, MySQL e phpMyAdmin:

```bash
docker compose up --build
```

Acesse:

| Serviço | URL / conexão |
| --- | --- |
| Aplicação | `http://localhost:8080/` |
| phpMyAdmin | `http://localhost:8081/` |
| MySQL pelo host | `localhost:3307` |

Credenciais do MySQL no Docker:

| Usuário | Senha | Uso |
| --- | --- | --- |
| `root` | `root` | administração/phpMyAdmin |
| `criancafeliz` | `criancafeliz` | aplicação |

Na primeira subida, o container do MySQL importa `database/SETUP_COMPLETO_FINAL.sql` via `docker/mysql/01-init.sh` e depois aplica `docker/mysql/02-missing-views.sql`. O volume `db_data` preserva o banco entre execuções; se precisar recriar o banco do zero, pare e remova o volume:

```bash
docker compose down -v
docker compose up --build
```

Para parar sem apagar o banco:

```bash
docker compose down
```

---

## Configuração

As credenciais padrão ficam em `app/Config/Database.php`:

| Parâmetro | Padrão |
| --- | --- |
| Host | `localhost` |
| Banco | `criancafeliz` |
| Usuário | `root` |
| Senha | vazia |
| Charset | `utf8mb4` |

Também é possível configurar por variáveis de ambiente:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=criancafeliz
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
APP_DEBUG=false
```

No Docker Compose, essas variáveis já são definidas no serviço `app`; o host do banco dentro da rede Docker é `db`.

Em produção, defina `APP_DEBUG=false` para ocultar detalhes de erro.

---

## Estrutura do projeto

```text
CRUD_CriancaFeliz/
├── app/
│   ├── Config/          # Configurações de aplicação e banco
│   ├── Controllers/     # Controllers MVC
│   ├── Helpers/         # Helpers transversais
│   ├── Models/          # Models MySQL
│   ├── Services/        # Regras de negócio
│   └── Views/           # Layouts e telas
├── assets/samples/      # Arquivos de amostra para testes manuais
├── css/                 # Estilos globais
├── data/                # Arquivos runtime locais não versionados
├── database/            # Setup, migrações e diagnósticos de banco
├── docker/              # Scripts de inicialização do MySQL em container
├── docs/                # Documentação técnica e histórico do projeto
├── img/                 # Imagens públicas
├── js/                  # Scripts de interface
├── tests/manual/        # Testes manuais
├── tools/               # Diagnósticos, manutenção e legado
├── var/                 # Roteador de dev e logs locais
├── Dockerfile           # Imagem PHP/Apache da aplicação
├── docker-compose.yml   # Ambiente local com app, MySQL e phpMyAdmin
├── .htaccess            # Rewrite para o front controller
└── index.php            # Front controller e roteador central
```

---

## Rotas principais

O sistema aceita rotas amigáveis e equivalentes com `.php` por compatibilidade. As principais são:

| Rota | Descrição |
| --- | --- |
| `/` ou `/index.php` | Login. |
| `/forgot.php` | Solicitação de recuperação de senha. |
| `/reset_password.php?token=...` | Redefinição de senha. |
| `/dashboard.php` | Dashboard e calendário. |
| `/prontuarios.php` | Consulta consolidada de prontuários. |
| `/acolhimento_list.php` | Lista de fichas de acolhimento. |
| `/acolhimento_form.php` | Cadastro/edição de acolhimento. |
| `/acolhimento_view.php?id=...` | Visualização de acolhimento. |
| `/socioeconomico_list.php` | Lista de fichas socioeconômicas. |
| `/socioeconomico_form.php` | Cadastro/edição socioeconômica. |
| `/socioeconomico_view.php?id=...` | Visualização socioeconômica. |
| `/faltas.php` | Frequência diária. |
| `/faltas.php?action=oficina` | Frequência por oficina. |
| `/faltas.php?action=historico&id=...` | Histórico de frequência. |
| `/faltas.php?action=alertas` | Alertas de faltas. |
| `/desligamento.php` | Lista e gestão de desligamentos. |
| `/psychology.php` | Dashboard da área psicológica. |
| `/psychology.php?action=patients` | Lista de pacientes da psicologia. |
| `/users.php` | Gestão de usuários. |
| `/logs.php` | Auditoria do sistema. |
| `/profile.php` | Perfil do usuário. |
| `/logout.php` | Encerrar sessão. |

---

## Perfis de acesso

| Perfil | Acesso principal |
| --- | --- |
| `admin` | Administração do sistema, usuários, logs, fichas, frequência e desligamentos. Por regra atual, não acessa a área psicológica. |
| `funcionario` | Consulta geral de registros e módulos operacionais liberados por autenticação. |
| `psicologo` | Área psicológica, pacientes e anotações psicológicas. |

---

## Documentação

- [docs/PROJECT_DOCUMENTATION.md](docs/PROJECT_DOCUMENTATION.md): arquitetura, rotas, controllers, services, models, banco e pendências.
- [docs/MAINTENANCE_AND_TESTING.md](docs/MAINTENANCE_AND_TESTING.md): scripts auxiliares, comandos de validação e checklist operacional.
- [database/README_SETUP.md](database/README_SETUP.md): guia específico do setup do banco.
- [docs/STYLING_UPGRADE.md](docs/STYLING_UPGRADE.md): padrões visuais e guia de CSS.
- [docs/RELACAO_GERAL_DE_ALTERACOES.md](docs/RELACAO_GERAL_DE_ALTERACOES.md): histórico do ciclo de modernização.
- [docs/archive/](docs/archive/): documentos antigos preservados para referência.
- [00 - Painel do Projeto Criança Feliz.md](00%20-%20Painel%20do%20Projeto%20Crian%C3%A7a%20Feliz.md): painel Obsidian para navegação do projeto.

---

## Observações importantes

- Os scripts em `tools/maintenance/` podem alterar dados. Use apenas com backup e preferencialmente fora de produção.
- O fluxo de recuperação de senha gera tokens em `data/reset_tokens.json` e registra a URL no log do PHP. Para produção, implemente envio SMTP real.
- A pasta `data/` guarda dados locais/runtime e não deve ser usada como fonte principal de persistência.
- O módulo atual de frequência é `faltas.php`. Ainda há referências legadas a `attendance.php` em alguns pontos do código; elas devem ser removidas ou redirecionadas em uma próxima correção.
- A área psicológica usa a tabela `anotacao_psicologica`; em bancos novos, confirme se essa tabela foi criada a partir de uma migração validada ou dos dumps legados.
- A imagem de login em `img/84ee2f859c98cde210228f9cf472d03b4932ff8c.jpg` é grande e pode ser comprimida para reduzir o tempo de clone/carregamento.

---

## Contribuição

1. Faça um fork do projeto.
2. Crie uma branch para sua alteração: `git checkout -b feature/minha-feature`.
3. Faça commits pequenos e descritivos.
4. Envie sua branch: `git push origin feature/minha-feature`.
5. Abra um Pull Request.

---

## Licença

Este projeto está sob a licença MIT. Consulte [LICENSE](LICENSE) para mais detalhes.

---

<p align="center">
  Feito com carinho para a <strong>Associação Criança Feliz</strong>
</p>
