# 📓 Atividades Práticas — Projeto Criança Feliz

**Objetivo:** Analisar a estrutura de dados, arquitetura e organização do código-fonte do projeto, realizando a documentação visual (DER e Diagrama de Classes) e a higienização estrutural do repositório.

---

## 📍 Tarefa 1: Modelagem de Dados e Diagrama Entidade-Relacionamento (DER)

### 🌯 Objetivo
Analisar os scripts SQL na pasta [`database/`](../database/) (com foco no arquivo principal [`database/SETUP_COMPLETO_FINAL.sql`](../database/SETUP_COMPLETO_FINAL.sql)) e construir o **DER Completo** do sistema.

### 🎓 O que deve ser feito:
1. **Mapear todas as entidades/tabelas** do banco de dados (ex.: `usuario`, `atendido`, `responsavel`, `ficha_socioeconomico`, `frequencia_dia`, `oficina`, `sessao`, `presenca`, `log`, etc.).
2. **Definir Atributos e Chaves**:
   - Identificar Chaves Primárias (PK) e Chaves Estrangeiras (FK)ternas.
   - Identificar tipos de dados e restrições (`NOT NULLa`, `UNIQUE`, etc.).
3. **Mapear Cardinalidades**:
   - Estabelecer as relações entre tabelas com as cardinalidades corretas (ex.: `1:1`, `1:N`, `N:N`).
   - Identificar regras de integridade referencial (`CASCADE`, `SET NULL`, etc.).
4. **Ferramentas sugeridas para entrega**:
   - Draw.io, MySQL Workbench, DBDesigner, Lucidchart, Mermaid ou PlantUML.

---

## 📍 Tarefa 2: Diagrama de Classes (UML) da Camada de Aplicação

### 🌯 Objetivo
Analisar a arquitetura MPC na pasta [`app/`](../app/), com ênfase na pasta [`app/Controllers/`](../app/Controllers/), e gerar o **Diagrama de Classes UML**.

### 🎓 O que deve ser feito:
1. **Mapear os Controllers**:
   - Identificar a hierarquia de herança (ex.: classes que herdam de `BaseController`).
   - Listar atributos e métodos de cada Controller, com suas respectivas visibilidades (`+ público`, `- privado`, `# protegido`).
2. **Mapear Associações e Dependências**:
   - Representar a interação dos Controllers com a camada de **Models** ([`app/Models/`](../app/Models/)) e **Services** ([`app/Services/`](../app/Services/)).
   - Indicar injeção de dependências e helpers utilizados (ex.: `LogHelper`, `Database`).
3. **Ferramentas sugeridas para entrega**:
   - Astah, Draw.io, PlantUML, Mermaid ou Lucidchart.

---

## 📍 Tarefa 3: Higienização e Reorganização de Arquivos Soltos (Limpeza Arquitetural)

### 🌯 Objetivo
Identificar, classificar e mover arquivos que estão soltos na raiz do projeto e que **não fazem parte do fluxo oficial do sistema MVC**.

### 🔍 Contexto e Problema:
Na raiz do projeto existem diversos scripts temporários, rotinas de correção pontual e arquivos de teste criados durante o desenvolvimento. Deixar esses scripts na raiz pública representa um risco de segurança (exposição de rotinas administrativas sem autenticação) e prejudica a manutenibilidade do código.

> **Exemplo Real:**
> O arquivo `corrigir_renda_marina.php` executa um `UPDATE` fixo e pontual para um único registro (`WHERE idficha = 4`). Esse tipo de script de correção manual (hotfix / patch) não pertence à raiz do sitee não deve estar disponível no ambiente de produção.

### 🎓 O que deve ser feito:
1. **Auditar todos os arquivos na raiz** de `hospedagem/`.
2. **Criar pastas apropriadas** para segregação de responsabilidades. Sugestões de estrutura:
   - `tools/maintenance/` ou `scripts/correcoes/`: Para scripts de manutenção pontual (ex.: `corrigir_renda_marina.php`, `fix_renda_marina.php`, `fix_users.php`, `ativar_usuarios.php`, `generate_password.php`, `install_database.php`).
   - `tools/diagnostics/` ou `debug/`: Para scripts de diagnóstico e inspeção (ex.: `check_ficha_columns.php`, `debug_*.php`, `diagnostico_login.php`).
   - `tests/manual/` ou `tests/`: Para scripts de testes manuais (ex.: `test_*.php`).
   - `legacy/` ou `archive/`: Para arquivos obsoletos, backups ou recursos temporários (ex.: `dog para usar de teste.jpg`, dumps duplicados).
3. **Garantir que a raiz mantenha apenas os pontos de entrada legítimos da aplicação** (ex.: `index.php`, `bootstrap.php`, as views/páginas oficiais de rota ou `.htaccess`).
4. **Registrar um resumo**: Criar uma breve tabela ou lista justificando para onde cada arquivo foi movido e por quê.

---

## 🎐 Entregáveis para a Próxima Aula:

1. 📜 **Documento DER**: Imagem/PDF ou código Mermaid/PlantUML do Diagrama Entidade-Relacionamento.
2. � **Documento Diagrama de Classes**: Imagem/PDF ou código UML das classes mapeadas.
3. 🔂 **Relatório de Reorganização**: Lista dos arquivos movidos/removidos da raiz, com a respectiva justificativa técnica e nova localização.
