<p align="center">
  <img src="img/logo.png" alt="Associação Criança Feliz" width="180">
</p>

<h1 align="center">Sistema Criança Feliz</h1>

<p align="center">
  Sistema web para gerenciamento de atendidos da Associação Criança Feliz.<br>
  Controle de prontuários, fichas de acolhimento, fichas socioeconômicas,<br>
  frequência, desligamentos, área psicológica, usuários e auditoria.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Licença-MIT-green" alt="MIT License">
</p>

---

## 📋 Índice

- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Rotas Principais](#-rotas-principais)
- [Perfis de Acesso](#-perfis-de-acesso)
- [Screenshots](#-screenshots)
- [Contribuição](#-contribuição)
- [Licença](#-licença)

---

## ✨ Funcionalidades

| Módulo | Descrição |
|--------|-----------|
| **Dashboard** | Painel inicial com calendário interativo, alertas, indicadores e anotações |
| **Prontuários** | Hub de consulta consolidada de dados dos atendidos |
| **Fichas de Acolhimento** | Cadastro, edição, busca, visualização e exportação CSV |
| **Fichas Socioeconômicas** | Formulário multi-etapas com cálculo de renda e relatórios |
| **Controle de Faltas** | Frequência por dia e por oficina, histórico e alertas automáticos |
| **Desligamento** | Desligamento manual, automático por faltas e reativação |
| **Área Psicológica** | Anotações, avaliações e acompanhamento por paciente |
| **Gestão de Usuários** | CRUD completo com ativação/desativação e perfis de acesso |
| **Auditoria (Logs)** | Registro de todas as ações com busca, filtros e exportação |
| **Perfil** | Foto de perfil, troca de senha e dados pessoais |
| **Recuperação de Senha** | Fluxo com geração de token (requer SMTP para produção) |
| **Tema Escuro** | Alternância entre modo claro e escuro |
| **Chatbot** | Assistente integrado na interface |

---

## 🛠 Tecnologias

- **Back-end:** PHP 7.4+ (MVC sem framework externo)
- **Banco de dados:** MySQL / MariaDB com PDO
- **Front-end:** HTML5, CSS3 (responsivo), JavaScript vanilla
- **Fonte:** Google Fonts (Poppins)
- **Segurança:** CSRF tokens, prepared statements, headers de segurança, bcrypt para senhas

---

## 📦 Pré-requisitos

- **PHP** 7.4 ou superior (compatível com 8.x)
- **MySQL** 5.7+ ou **MariaDB** 10.3+
- **Servidor web:** Apache (XAMPP, WAMP, Laragon) ou equivalente
- **Extensão** `pdo_mysql` habilitada

---

## 🚀 Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/CriancaFeliz.git

# 2. Mova para o webroot do seu servidor local
#    Exemplo XAMPP: C:\xampp\htdocs\CriancaFeliz

# 3. Crie o banco de dados
mysql -u root -e "CREATE DATABASE criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Importe o schema
mysql -u root criancafeliz < database/SETUP_COMPLETO_FINAL.sql

# 5. Acesse no navegador
#    http://localhost/CriancaFeliz/index.php
```

> **Alternativa:** use `criancafeliz.sql` (dump principal) se preferir um schema diferente do setup completo.

---

## ⚙ Configuração

### Banco de dados

A configuração padrão (ideal para desenvolvimento local) está em `app/Config/Database.php`:

| Parâmetro | Padrão |
|-----------|--------|
| Host | `localhost` |
| Banco | `criancafeliz` |
| Usuário | `root` |
| Senha | *(vazia)* |
| Charset | `utf8mb4` |

### Variáveis de ambiente

Para ambientes de produção ou customizados, defina variáveis de ambiente:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=criancafeliz
DB_USER=seu_usuario
DB_PASS=sua_senha
DB_CHARSET=utf8mb4
APP_DEBUG=false
```

> ⚠ **Produção:** sempre defina `APP_DEBUG=false` para ocultar erros detalhados.

---

## 📁 Estrutura do Projeto

```
CriancaFeliz/
├── app/
│   ├── Config/          # Configurações (App, Database)
│   ├── Controllers/     # Controllers MVC (14 controllers)
│   ├── Helpers/         # Helpers transversais (LogHelper)
│   ├── Models/          # Models MySQL e JSON legado (16 models)
│   ├── Services/        # Regras de negócio (6 services)
│   └── Views/           # Templates HTML/PHP
│       ├── layouts/     #   ├── auth.php (login)
│       │                #   └── main.php (sistema)
│       ├── acolhimento/ # Fichas de acolhimento
│       ├── attendance/  # Frequência (legado)
│       ├── auth/        # Login, esqueci senha, reset
│       ├── dashboard/   # Painel principal
│       ├── desligamento/# Desligamentos
│       ├── faltas/      # Frequência (novo)
│       ├── logs/        # Auditoria
│       ├── profile/     # Perfil do usuário
│       ├── prontuarios/ # Prontuários
│       ├── psychology/  # Área psicológica
│       ├── socioeconomico/ # Fichas socioeconômicas
│       └── users/       # Gestão de usuários
├── css/                 # Estilos globais
├── js/                  # Scripts de interface
├── img/                 # Imagens públicas (logo, fundo)
├── database/            # SQL: schema, migrações, triggers, fixes
├── data/                # Dados locais/runtime (não versionados)
├── docs/                # Documentação técnica
├── tools/               # Scripts de diagnóstico e manutenção
├── tests/               # Testes manuais
├── assets/samples/      # Arquivos de amostra para testes
├── var/logs/            # Logs locais (não versionados)
├── bootstrap.php        # Inicialização do sistema
├── index.php            # Ponto de entrada (login)
└── ...                  # Demais rotas públicas
```

---

## 🗺 Rotas Principais

| Rota | Descrição |
|------|-----------|
| `index.php` | Login |
| `dashboard.php` | Painel inicial |
| `prontuarios.php` | Hub de prontuários |
| `acolhimento_list.php` | Lista de fichas de acolhimento |
| `acolhimento_form.php` | Cadastro/edição de acolhimento |
| `acolhimento_view.php?id=` | Visualização de ficha |
| `socioeconomico_list.php` | Lista de fichas socioeconômicas |
| `socioeconomico_form.php` | Cadastro/edição socioeconômico |
| `socioeconomico_view.php?id=` | Visualização socioeconômica |
| `faltas.php` | Controle de faltas |
| `desligamento.php` | Desligamentos |
| `psychology.php` | Área psicológica |
| `users.php` | Gestão de usuários |
| `logs.php` | Auditoria do sistema |
| `profile.php` | Perfil do usuário |
| `forgot.php` | Recuperação de senha |
| `reset_password.php?token=` | Redefinição de senha |
| `logout.php` | Encerrar sessão |

---

## 👥 Perfis de Acesso

| Perfil | Permissões |
|--------|------------|
| **Administrador** | Acesso total: usuários, logs, desligamentos, configurações e todos os módulos |
| **Funcionário** | Prontuários, fichas de acolhimento e socioeconômicas, frequência |
| **Psicólogo** | Área psicológica exclusiva, anotações e acompanhamento de pacientes |

---

## 📸 Screenshots

> *Para adicionar screenshots, coloque as imagens na pasta `docs/screenshots/` e atualize os links abaixo.*

<!--
<p align="center">
  <img src="docs/screenshots/login.png" alt="Tela de Login" width="400">
  <img src="docs/screenshots/dashboard.png" alt="Dashboard" width="400">
</p>
-->

---

## 📖 Documentação

A documentação técnica completa está na pasta `docs/`:

- **[PROJECT_DOCUMENTATION.md](docs/PROJECT_DOCUMENTATION.md)** — Arquitetura, controllers, services, models, erros corrigidos e melhorias recomendadas
- **[MAINTENANCE_AND_TESTING.md](docs/MAINTENANCE_AND_TESTING.md)** — Scripts de diagnóstico, manutenção e testes manuais
- **[docs/archive/](docs/archive/)** — Documentos históricos preservados para referência

---

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/minha-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona minha feature'`)
4. Push para a branch (`git push origin feature/minha-feature`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## ⚠ Observações Importantes

- Os scripts em `tools/maintenance/` podem alterar dados. **Use apenas com backup** e preferencialmente fora de produção.
- A recuperação de senha gera tokens locais em `data/reset_tokens.json`. Para produção, configure um servidor SMTP real.
- Os dados da pasta `data/` não são versionados (contêm dados de runtime). O sistema cria os arquivos automaticamente quando necessário.
- A imagem de login (`img/84ee2f...jpg`) tem ~9 MB. Considere comprimi-la para melhorar o tempo de clone do repositório.

---

<p align="center">
  Feito com ❤ para a <strong>Associação Criança Feliz</strong>
</p>
