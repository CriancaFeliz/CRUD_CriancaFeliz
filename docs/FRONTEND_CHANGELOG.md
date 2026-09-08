# Frontend Changelog — Sistema Criança Feliz

## Informações Gerais
- **Data:** 05/09/2026
- **Branch de Trabalho:** `codex/reconstrucao-release-agosto`
- **Ambiente Local:** Apache / PHP 8.2 / MySQL (`http://localhost/CriancaFelizFinal/`)
- **Objetivo:** Primeira etapa de melhoria visual e responsividade do sistema. Padronização da identidade visual oficial (Verde `#52a435` / `#6fb64f` como cor primária e Laranja `#ff7a00` como cor de destaque), aprimoramento de layout e eliminação de duplo scroll no mobile/tablet, criação de classes reutilizáveis para tabelas, botões, modais, formulários e cartões glassmorphic, e redução drástica de estilos inline mantendo a integridade total do backend e das permissões.

---

## Arquivos Modificados

1. [css/style.css](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/css/style.css)
   - **Variáveis de Design System:**
     - Consolidou o Verde (`--primary-green: #52a435;`, `--gradient-green: linear-gradient(...)`) como cor principal da ONG.
     - Consolidou o Laranja (`--primary-orange: #ff7a00;`, `--gradient-orange: linear-gradient(...)`) como cor de destaque e ações ativas.
     - Substituiu o fundo rígido com alto contraste por um gradiente suave e elegante (`--bg-app`) no tema claro e um dark mode sofisticado e harmonioso no tema escuro (`[data-theme="dark"]`).
   - **App Shell & Responsividade:**
     - Corrigiu a estrutura do container `.app` e `.content` para telas móveis e tablets (`@media (max-width: 860px)` e no encerramento do arquivo).
     - Eliminou conflitos de altura e duplo scrollbar em dispositivos móveis, utilizando flexbox com rolagem nativa suave (`-webkit-overflow-scrolling: touch`).
     - Sidebar móvel reestruturada como barra de navegação ergonômica horizontal com ícones touch-friendly (área de toque de 40px+).
   - **Componentes Globais Padronizados:**
     - Criação da classe `.table-responsive` para que tabelas tenham rolagem horizontal contida sem quebrar a largura da viewport em celulares.
     - Refinamento de `.table-glass` com cabeçalhos elegantes, bordas suaves e efeito hover sutil nas linhas.
     - Padronização de botões: `.btn`, `.btn-primary`, `.btn-success`, `.btn-secondary`, `.btn-danger`, `.btn-warning` e `.btn-icon` (com dimensões 34x34px para ações de tabela).
     - Componentes de formulário: `.form-control`, `.form-select`, foco acessível com `:focus-visible` e anel de foco destacado.
     - Classes de filtros e estatísticas: `.filtros-container`, `.filtros-row`, `.stats-row`, `.stat-card` e `.stat-card-glass`.
     - Animação suave para modais com `@keyframes modalFade` e `@keyframes modalScale`.

2. [app/Views/dashboard/index.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/dashboard/index.php)
   - Substituição de mais de 40 linhas de estilos inline hardcoded nos modais de anotação (`#typeModal` e `#noteModal`) pelas classes reutilizáveis `.modal`, `.modal-content`, `.modal-header`, `.modal-close`, `.modal-choice-btn.orange`, `.modal-choice-btn.green` e botões padronizados.
   - Preservação integral de toda a lógica JavaScript do calendário e das anotações.

3. [app/Views/users/index.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/users/index.php)
   - Remoção de centenas de caracteres de CSS inline repetitivos.
   - Implementação de wrapper com `.table-responsive` e tabela com `.table-glass`.
   - Substituição dos botões com emojis soltos (✏️, ⏸️, 🗑️) por botões padronizados com FontAwesome (`.btn-icon.edit-btn`, `.btn-icon.toggle-btn`, `.btn-icon.delete-btn`).
   - Badges de nível de acesso e status harmonizados com o design system e compatíveis com modo escuro.

4. [app/Views/profile/index.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/profile/index.php)
   - Limpeza de estilos inline repetidos nos blocos de foto e alteração de senha.
   - Aplicação de `.card-glass`, `.form-group`, `.form-control`, `.btn.primary`, `.btn.success` e `.btn.secondary`.
   - Manutenção idêntica de todos os IDs (`photoInput`, `profilePhoto`, `photoForm`), tokens CSRF e scripts de upload/atualização.

5. [app/Views/psychology/index.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/psychology/index.php)
   - Substituição de fundos brancos fixos por `.card-glass` e `.stat-card-glass`.
   - Grid de gráficos e faixas etárias adaptada para dispositivos móveis (transição fluida para 1 coluna no celular).
   - Cores de texto e bordas migradas para variáveis CSS do tema claro/escuro.

6. [app/Views/faltas/dia.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/faltas/dia.php)
   - Remoção do bloco `<style>` redundante de 115 linhas.
   - Tabela de frequência envelopada em `.table-responsive` com `.table-glass`.
   - Inputs e selects integrados às classes `.form-control` e `.form-select`.

7. [app/Views/faltas/oficina.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/faltas/oficina.php)
   - Remoção do bloco `<style>` redundante de 110 linhas.
   - Tabela de oficinas atualizada com `.table-responsive` e `.table-glass`.
   - Cartão de informações da oficina estilizado com visual glassmorphic moderno.

8. [app/Views/faltas/alertas.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/faltas/alertas.php)
   - Remoção de CSS inline e bloco `<style>`.
   - Aplicação de `.card-glass`, `.badge-danger`, `.badge-warning` e botões oficiais.

9. [app/Views/faltas/gerenciar_oficinas.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/faltas/gerenciar_oficinas.php)
   - Remoção de bloco `<style>` com mais de 140 linhas duplicadas.
   - Cards de oficinas migrados para `.card-glass` responsivos com `.status.ativo` / `.status.inativo`.
   - Modal de cadastro/edição de oficina adaptado para as classes globais `.modal` e `.modal-content`.

10. [app/Views/desligamento/index.php](file:///c:/Users/leona/Documents/ChatGPT/Criança%20Feliz/sistema/app/Views/desligamento/index.php)
    - Remoção de 107 linhas de regras CSS inline que quebravam o tema escuro.
    - Aplicação de `.stats-row`, `.stat-card-glass`, `.table-responsive` e `.table-glass`.

---

## O Que Mudou Visualmente
- **Identidade e Cores:** A interface agora transmite acolhimento e profissionalismo, com o Verde como tom mestre e o Laranja energizando botões de ação e itens ativos da navegação.
- **Tabelas Responsivas:** Nenhuma tabela do sistema extrapola a tela ou quebra em celulares; agora todas possuem scroll horizontal suave e cabeçalhos com boa hierarquia.
- **Modo Escuro Coeso:** As telas de usuários, perfil, área psicológica, faltas e desligamento não apresentam mais "caixas brancas ofuscantes" quando o modo escuro está ativado.
- **Micro-interações:** Botões e cartões reagem com elevação suave (`transform: translateY(-2px)`) e sombras dinâmicas no hover, além de anel de foco acessível para navegação via teclado (`:focus-visible`).
- **Navegação Móvel Confortável:** A sidebar no mobile fica fixa na parte inferior da tela, sem sobreposição nem corte de conteúdo.

---

## Telas e Perfis Testados

Foram realizados testes automatizados e de renderização HTTP nas três contas do sistema com a senha `TesteCF!2026`:

1. **Administrador (`admin@criancafeliz.test`):**
   - `dashboard.php` — [200 OK] Cards, calendário e modais de anotação.
   - `users.php` — [200 OK] Tabela responsiva com botões FontAwesome.
   - `profile.php` — [200 OK] Interface de perfil, foto e troca de senha.
   - `faltas.php` — [200 OK] Frequência diária com botões de rádio e filtros.
   - `faltas.php?action=oficina` — [200 OK] Frequência por oficina.
   - `faltas.php?action=alertas` — [200 OK] Alertas de faltas e cartões de atenção.
   - `faltas.php?action=gerenciarOficinas` — [200 OK] Cards e modal de oficinas.
   - `desligamento.php` — [200 OK] Indicadores numéricos e tabela de desligamentos.
   - `prontuarios.php` — [200 OK] Ações rápidas e estatísticas.
   - `reports.php` — [200 OK] Filtros e visualização de relatórios.
   - `logs.php` — [200 OK] Auditoria e paginação de logs.

2. **Funcionário (`funcionario@criancafeliz.test`):**
   - `dashboard.php` — [200 OK] Menus operacionais.
   - `prontuarios.php` — [200 OK] Acesso apenas para consulta conforme permissões.
   - `faltas.php` — [200 OK] Frequência diária.
   - `profile.php` — [200 OK] Perfil pessoal.

3. **Psicóloga (`psicologa@criancafeliz.test`):**
   - `dashboard.php` — [200 OK] Área médica e anotações.
   - `psychology.php` — [200 OK] Área psicológica confidencial com gráficos e estatísticas responsivas.
   - `profile.php` — [200 OK] Perfil pessoal.

---

## Diagnóstico Técnico: Upload de Fotos (Não alterado nesta fase)

Conforme solicitado, o upload de fotos não foi modificado nesta primeira etapa, mas foi submetido a uma inspeção minuciosa para diagnóstico:

### Diagnóstico do Comportamento Atual:
1. **Fluxo do Endpoint:**
   - A requisição é disparada via JavaScript em `profile/index.php` via `fetch('profile.php?action=updatePhoto', ...)`.
   - O controlador `ProfileController::updatePhoto()` grava o arquivo em `BASE_PATH . '/var/private/profiles/' . $fileName`.
   - No banco de dados, o campo `foto_perfil` da tabela `usuario` recebe a string relativa `'var/private/profiles/' . $fileName`.
2. **Causas Raiz de Potenciais Falhas no Windows / XAMPP:**
   - **Permissão de Criação de Diretório:** A chamada `mkdir($uploadDir, 0750, true)` no PHP em ambiente Windows pode falhar silenciosamente ou ser bloqueada pelas ACLs do Windows dependendo de como o Apache foi instalado, lançando a exceção `"Não foi possível preparar a área segura de fotos"`.
   - **Separadores de Diretório no Windows:** O código concatena com barra normal `'/'`, enquanto no Windows o caminho absoluto pode requerer `DIRECTORY_SEPARATOR` para resolução consistente pelo `realpath()` dentro de `resolveManagedPhoto()`.
   - **Sessão vs. Banco no Login:** No `AuthService::createSession()`, o valor salvo na sessão é `$_SESSION['user_photo'] = $user['foto_perfil'] ?? '';`. Se o usuário alterar a foto em uma aba, a sessão atualiza, mas a topbar em outros layouts pode não refletir sem refresh ou se a variável de sessão não for recarregada.
   - **Stream de Imagem e Buffer:** O método `ProfileController::viewPhoto()` utiliza `ob_end_clean()` em loop para limpar o buffer antes do `readfile()`. Se houver qualquer espaço em branco ou notice em arquivos incluídos antes dos cabeçalhos `header('Content-Type: ...')`, os bytes da imagem podem ser corrompidos.

---

## Correção: Calendário do Dashboard e Erro em Vermelho

- **Sintoma Relatado:** Ao acessar `dashboard.php`, surgia um banner vermelho: *"Não foi possível processar a solicitação. Código: c8f73dd6d40e"* e os números dos dias do calendário não apareciam (apenas as siglas "D S T Q Q S S").
- **Causas Raiz Identificadas:**
  1. **Renderização Condicional Falha no JS:** O método `generateCalendar()` só era chamado após o retorno bem-sucedido de `await fetch('dashboard.php?action=getCalendarNotes&month=...')`. Se houvesse qualquer lentidão, erro ou resposta inesperada, `generateCalendar()` nunca rodava, deixando o grid sem os elementos `.calendar-day`. Além disso, o mês no HTML estava estático como "Setembro, 2025".
  2. **Ciclo Vicioso no Tratamento de Erros AJAX:** O `fetch` nativo não enviava o cabeçalho `X-Requested-With: XMLHttpRequest`. Quando ocorria qualquer exceção em segundo plano no endpoint de anotações, o roteador `index.php` tratava a requisição como uma navegação comum de página, gravando a mensagem de erro em `$_SESSION['flash_error'] = 'Não foi possível processar a solicitação. Código: ' . $errorId` e retornando um fragmento HTML `<h1>...`. Esse HTML quebrava o `response.json()` no frontend, e na próxima recarga da página a mensagem vermelha era impressa na tela.
  3. **Concorrência e getenv no Windows:** No Apache `mpm_winnt` (multi-thread), chamadas simultâneas a `putenv()` e `getenv()` podiam retornar vazio para `DB_HOST` em requisições paralelas, lançando exceções silenciosas de conexão.
- **Soluções Aplicadas:**
  1. **`app/Views/dashboard/index.php`:**
     - Inicialização imediata de `generateCalendar()` no carregamento do DOM (os dias 1 a 30/31 sempre são desenhados imediatamente).
     - Rótulo do mês inicial gerado dinamicamente no PHP (`Setembro, 2026`).
     - Chamadas `fetch()` atualizadas com headers `'X-Requested-With': 'XMLHttpRequest'` e `'Accept': 'application/json'`.
     - Bloco `finally` em `loadNotes()` para assegurar que mesmo em caso de erro no servidor o calendário e a lista de anotações permaneçam renderizados.
     - `changeMonth()` agora redesenha o calendário imediatamente antes de buscar as anotações do novo mês.
  2. **`index.php`:**
     - Detecção aprimorada de requisições AJAX/JSON (`$isAjax`) no bloco de exceção global, abrangendo cabeçalhos e ações específicas de API (`getCalendarNotes`, etc.). Requisições de API com erro agora respondem em JSON 500 sem poluir a sessão do usuário com `flash_error`.
  3. **`app/Config/Database.php` e `app/bootstrap.php`:**
     - Leitura de variáveis de ambiente com prioridade em `$_ENV` e `$_SERVER` antes do fallback para `getenv()`, prevenindo condições de corrida de concorrência no Windows.

---

## Redesign do Menu Lateral, Dashboard e Modal de Confirmação

- **Solicitações do Usuário:**
  1. Menu lateral com fundo branco, moderno, elegante e profissional.
  2. Imagem da logo aumentada e com maior destaque.
  3. Remoção visual da barra de rolagem (scrollbar track) mantendo a rolagem da página fluida e normal.
  4. Remoção de todos os emojis, substituindo-os por ícones modernos FontAwesome.
  5. Tira lateral dos cards de "Avisos" alterada de laranja para verde (`#52a435`).
  6. Modal moderno de confirmação ao excluir anotações/avisos, contendo o logotipo oficial da ONG Criança Feliz.

- **Implementações Realizadas:**
  1. **Menu Lateral Moderno com Fundo Branco (`css/style.css`):**
     - `.sidebar` atualizada com fundo branco `#ffffff !important`, bordas arredondadas de 24px e sombra sutil `0 10px 30px rgba(0,0,0,0.06)`.
     - Ícones inativos em cinza chumbo suave (`#64748b`), hover com suave realce verde (`rgba(82, 164, 53, 0.12)`) e ícone verde `#52a435`.
     - Ícone ativo em formato pill com gradiente laranja vibrante e ícone branco com sombra sutil.
     - Grade `.app` ajustada para largura ideal da barra lateral (96px).
  2. **Tamanho da Logo Aumentado (`css/style.css`):**
     - `.sidebar .logo` dimensionada para `72px !important` (antes 46px), proporcionando maior legibilidade e presença institucional.
  3. **Scrollbar Oculta com Rolagem Funcional (`css/style.css`):**
     - Aplicado `scrollbar-width: none` e `::-webkit-scrollbar { display: none; }` no container `.content` e `body`. Rolagem com mouse, touch e teclado 100% preservada sem a barra cinza lateral.
  4. **Substituição de Emojis por Ícones Profissionais (`app/Views/dashboard/index.php` e `app/Controllers/DashboardController.php`):**
     - Removidos todos os emojis do card "Alertas Prioritários" e backend (`⚠️`, `🎂`, `📅`, `✅`, `❌`).
     - Alertas agora utilizam ícones do FontAwesome: `fa-check-circle`, `fa-exclamation-triangle`, `fa-user-clock`, `fa-file-alt`, `fa-calendar-alt` e `fa-times-circle`.
  5. **Tira Verde nos Avisos (`css/style.css` e `app/Views/dashboard/index.php`):**
     - Estilização de `#avisosList .note-card-glass` e `.note-card-glass.aviso` com `border-left: 8px solid #52a435 !important`.
     - Cartões de anotação comum mantêm a faixa laranja e avisos utilizam a faixa verde.
  6. **Modal Moderno de Confirmação com Logo (`app/Views/dashboard/index.php` e `css/style.css`):**
     - Substituído o `confirm()` nativo do navegador por modal moderno `#deleteConfirmModal`.
     - Incorpora o logo oficial da ONG (`img/logo.png`), efeito glassmorphic com `backdrop-filter: blur(6px)`, animação suave de entrada (`modalPopIn`), e botões ergonômicos "Cancelar" e "Sim, Excluir" com ícone `fa-trash-alt`.
  7. **Redimensionamento dos Ícones da Sidebar e Equilíbrio da Logo (`css/style.css`):**
     - A coluna do menu lateral foi ajustada para `88px` de largura com padding ergonômico de `18px 10px`.
     - O logotipo oficial foi ampliado para `68px` de largura (aumento de mais de 35%), tornando todo o texto "ASSOCIAÇÃO CRIANÇA FELIZ" e as ilustrações perfeitamente visíveis e destacados.
     - Os botões de navegação foram refinados para `44x44px` (ícones com `16px`) com bordas arredondadas de `12px` e `gap: 5px`.
  8. **Espaçamento e Gaps em Anotações e Avisos (`css/style.css`):**
     - Aplicado `gap: 24px` e `margin-top: 24px` na seção `.dashboard-notes-grid`, criando um respiro harmonioso entre a coluna de Anotações do Calendário e a coluna de Avisos, bem como em relação ao grid superior.
     - Aplicado `display: flex; flex-direction: column; gap: 12px; margin-top: 14px;` dentro de `#notesList` e `#avisosList`, garantindo que os cards individuais de anotação e aviso não fiquem colados uns nos outros nem no título da seção.

---

## Modernização Integral do Chatbot — Assistente Criança Feliz

- **Problemas do Chatbot Anterior:**
  - Ausência de campo de digitação (usuário só podia clicar em botões pré-programados).
  - Uso excessivo de emojis soltos e texto cru de markdown exibido como asteriscos na tela.
  - Botão flutuante estático e janela sem alinhamento com o design system da ONG.
  - Respostas sem links nem ações diretas para as telas correspondentes.

- **Melhorias e Inovações Implementadas (`js/chatbot.js` e `css/style.css`):**
  1. **Botão Flutuante Sofisticado (FAB):**
     - Gradiente verde oficial (`#52a435`), sombra dinâmica com elevação no hover (`transform: scale(1.08)`), ícone `fa-comment-dots` que alterna limpamente para `fa-times` ao abrir (sem duplicidade ou sobreposição).
     - Indicador de status pulsante verde vivo (*pulse animation*).
  2. **Cabeçalho Institucional:**
     - Logotipo oficial da Associação Criança Feliz (`img/logo.png`), título nítido, indicador "Online • Suporte do Sistema", botão de reinício/limpeza (`fa-rotate-left`) e botão de fechar (`fa-times`).
  3. **Interface em Lista Vertical (Cards 100% de largura, um embaixo do outro):**
     - Substituída a grade em 2 colunas por uma lista vertical elegante e espaçosa (`display: flex; flex-direction: column; gap: 8px;`).
     - Cards em largura total com espaço amplo para título completo e descrição sem cortes ou truncamentos.
     - Ícone temático institucional à esquerda (38x38px com raio de 10px em verde suave) e seta indicativa de navegação à direita (`fa-chevron-right`) com animação sutil no hover.
     - Efeito de elevação (*lift effect*) e realce da borda em `#52a435` ao passar o mouse.
  4. **Scroll Suave 100% Invisível & Ancoragem Inicial no Topo:**
     - Barra de rolagem cinza oculta visualmente tanto no Chrome/Safari (`::-webkit-scrollbar { display: none; }`) quanto no Firefox (`scrollbar-width: none;`), permitindo rolagem fluida e livre com o scroll do mouse ou toque no celular sem qualquer poluição estética.
     - **Correção da rolagem inicial:** Ao abrir a janela do assistente, a visão inicia fixada no topo (`scrollTop = 0`), garantindo que o usuário visualize a mensagem de boas-vindas completa antes da listagem de opções.
  5. **Fluxo de Navegação & Botão de Retorno:**
     - Ao clicar em qualquer opção, a resposta completa é exibida com marcadores estilizados e botões de atalho direto para a funcionalidade correspondente.
     - Botão ergonômico *"Ver todas as opções de ajuda"* (`.chatbot-back-btn`) permite retornar à lista principal a qualquer momento com 1 clique.
  6. **Design System & Modo Escuro:**
     - Compatibilidade completa com tema claro e escuro (`[data-theme="dark"]`).
     - Totalmente responsivo para dispositivos móveis (`@media (max-width: 768px)`).

---

## 1.9 Ajustes de Menu e Topbar — Configurações e Tema

- **Afastamento do Botão de Configurações (`app/Views/layouts/main.php` e `css/style.css`):**
  - O ícone de configurações pessoais/perfil (`fa-cog`) recebeu a classe `.nav-icon-profile`.
  - Aplicado espaçamento isolado (`margin-top: 24px !important;`) com microdivisor horizontal sutil (`::before` de 22px em `#e2e8f0`), demarcando a seção como uma área à parte do fluxo operacional do menu.
  - Em dispositivos móveis (`@media (max-width: 768px)`), a margem superior e o divisor são desativados automaticamente, mantendo a barra inferior compacta e alinhada.

- **Remoção do Botão de Troca de Tema da Barra Superior (`js/theme-toggle.js` e `css/style.css`):**
  - Desativada a injeção do botão `.dashboard-theme-toggle` / `.theme-toggle` no topo da página.
  - Ocultação global via CSS (`display: none !important`).
  - A alternância e preferência de tema agora ficam reservadas para a tela de configurações pessoais de perfil do usuário (`profile.php`).

---

## 2.0 Redesign de Perfil, Seletor de Tema e Identificação de Cargos

- **Identificação Clara e Institucional de Cargos (`app/Views/profile/index.php`):**
  - Adicionado badge de identificação de papel profissional no cartão principal de perfil:
    * **Administrador do Sistema** (`fa-shield-alt`, badge em tom laranja/dourado institucional).
    * **Psicólogo(a) Institucional** (`fa-brain`, badge em tom azul especializado).
    * **Educador / Funcionário** (`fa-id-badge`, badge em tom verde oficial).
  - Cada cargo exibe uma descrição detalhada de seu escopo e nível de permissões no sistema (acesso a relatórios, prontuários sigilosos ou chamadas de oficinas).
  - Exibição de indicador de status ativo (*pulse animation*) e identificador de usuário (`ID #`).

- **Seletor de Aparência do Sistema — Modo Claro e Escuro (`app/Views/profile/index.php`, `js/theme-toggle.js` e `css/style.css`):**
  - Novo card dedicado **"Aparência e Tema do Sistema"** com cards visuais interativos para:
    * **Tema Claro** (`fa-sun` com miniatura e resumo).
    * **Tema Escuro** (`fa-moon` com miniatura e resumo).
  - A troca é instantânea ao clique, com indicação visual do tema ativo ("Ativo" com checkmark).
  - Persistência dupla via `localStorage` e `document.cookie` (`SameSite=Lax`, expiração de 1 ano), garantindo que a preferência seja mantida mesmo antes da autenticação.
  - Script no `<head>` de `layouts/main.php` e `layouts/auth.php` aplicando o tema imediatamente antes do primeiro frame de renderização, eliminando qualquer cintilação branca (*FOUC*).

- **Compatibilidade Total da Tela de Login com Modo Escuro (`app/Views/layouts/auth.php` e `css/style.css`):**
  - A tela de autenticação (`/login.php` ou `/index.php`) agora adapta automaticamente todas as suas superfícies ao modo escuro:
    * Fundo escuro profundo `#0b131a` e `#0f1922`.
    * Formulário em tom `#14222e` com bordas sutis.
    * Imagem institucional com contraste e brilho calibrados para o modo noturno.
    * Campos de entrada de e-mail e senha com contraste alto, bordas laranja elegantes e texto claro.

- **Segurança e Alteração de Senha Mais Moderna (`app/Views/profile/index.php`):**
  - Campos de senha atual, nova senha e confirmação com botão de alternância de visibilidade (*olhinho* `fa-eye` / `fa-eye-slash`).
  - Layout em colunas responsivas, orientação de tamanho mínimo (12 caracteres) e botão de salvar com ícone de escudo.

- **Reformulação Completa e Padronização do Modo Escuro em Todo o Sistema (`css/style.css`):**
  - **Eliminação de Cards Brancos e Incoerências Visuais:**
    * Removido o conflito onde seletores globais tardios forçavam `background: #ffffff !important` e texto `#15191c !important` em todos os cards mesmo no tema escuro.
    * Todas as superfícies (`.card`, `.card-glass`, `.stat-card`, `.stat-card-glass`, `.note-card`, `.note-card-glass`, `.chart-card`, `.ficha-card`, `.alerta-card`, `.action-card`, `.profile-card`, `.log-stat-card`) agora possuem fundo noturno refinado (`#111d23`), bordas translúcidas sutis (`rgba(255, 255, 255, 0.08)`) e sombras suaves.
  - **Correção Crítica de Legibilidade e Contraste ("Não dava para ver nada"):**
    * Títulos de seção ("Anotações do Calendário", "Avisos", "Ações Rápidas", "Resultados da Busca", cabeçalhos de tabela) agora possuem contraste absoluto em branco nítido (`#f8fafc !important`), eliminando os textos quase pretos sobre fundo escuro.
    * Rótulos de estatísticas (`.stat-label`), descrições de ações (`.action-desc`), datas e textos auxiliares agora utilizam slate legível (`#cbd5e1 !important`).
    * Os textos e conteúdos das anotações/avisos (`.note-text`) agora possuem cor clara e legível (`#f1f5f9 !important`).
  - **Calendário Totalmente Integrado ao Modo Escuro:**
    * As células dos dias do calendário (`.calendar-day`) deixaram de ser blocos brancos ofuscantes: agora utilizam fundo noturno suave (`#182a32`), números em branco (`#f8fafc`) e hover verde elegante.
    * Dias com anotação (`has-anotacao`), dias com aviso (`has-aviso`) e dia atual (`today`) mantêm suas cores temáticas destacadas (laranja e verde) com números em branco puro.
    * Botões de navegação de mês (`‹` e `›`) em laranja institucional vibrante.
  - **Ações Rápidas e Prontuários (`prontuarios.php`):**
    * Cards de ação rápida (`.action-card`) agora possuem fundo `#111d23`, bordas coloridas sutis por categoria, título em destaque e descrição legível.
  - **Tabelas, Formulários e Modais no Modo Escuro:**
    * Todas as tabelas do sistema (`users.php`, `logs.php`, `acolhimento_list.php`, etc.) contam com cabeçalhos `#182a32`, linhas alternadas em `#111d23` e texto contrastante (`#cbd5e1`).
    * Todos os campos de texto, caixas de seleção e textareas receberam fundo `#0e171c`, texto claro e foco laranja com glow.
    * O modal moderno de confirmação de exclusão e modais de anotação adaptados com fundo noturno escuro `#111d23` e tipografia legível.

- **Correção Geral e Definitiva do Modo Escuro em Todo o Site (`css/style.css`, `css/reports.css` e Views):**
  - **Fim das Tabelas Brancas e Hover Ofuscante:**
    * O seletor duplicado de modo claro que forçava `background: #ffffff !important` e hover `#f9fbfb !important` nas tabelas foi completamente isolado sob `html:not([data-theme="dark"])`.
    * No modo escuro (`[data-theme="dark"]` e `body.dark-theme`), as tabelas (`acolhimento_list.php`, `socioeconomico_list.php`, `users.php`, `logs.php`, `faltas.php`, `desligamento.php`, `reports.php`, `psychology`) agora possuem fundo noturno profundo `#111d23`, bordas suaves em `rgba(255, 255, 255, 0.08)` e cabeçalhos em `#182a32`.
    * As linhas (`tr`, `tbody tr`, `td`) possuem cor `#111d23` com texto claro `#e2e8f0`.
    * O hover nas linhas (`tr:hover td`, `tbody tr:hover td`) agora utiliza um tom noturno suave e confortável (`#192c36 !important`) com texto em branco nítido (`#ffffff !important`), eliminando completamente o clarão ofuscante anterior.
  - **Badges de Categoria e Status no Modo Escuro:**
    * Adolescente: fundo suave translúcido `rgba(245, 158, 11, 0.18)` com texto âmbar `#fbbf24` e borda de 1px.
    * Criança: fundo suave `rgba(56, 189, 248, 0.18)` com texto ciano/azul `#38bdf8` e borda de 1px.
    * Adulto: fundo suave `rgba(168, 85, 247, 0.18)` com texto roxo `#c084fc` e borda de 1px.
    * Status Ativo: fundo verde noturno `rgba(82, 164, 53, 0.18)` com texto `#4ade80` e borda de 1px.
    * Status Inativo: fundo vermelho noturno `rgba(239, 68, 68, 0.18)` com texto `#f87171` e borda de 1px.
  - **Botões de Ação na Tabela:**
    * Ícones de ação (`.view-btn`, `.edit-btn`, `.delete-btn`, `.toggle-btn`) agora utilizam fundo escuro `#182a32`, bordas refinadas e cores dedicadas (azul céu para visualização, âmbar para edição, vermelho claro para exclusão, verde para alternância de status), com efeitos de iluminação (glow) correspondentes ao passar o mouse.
  - **Filtros de Busca e Ações Superiores:**
    * Os cartões de busca (`.search-filters`) receberam fundo `#111d23`, labels claras em `#f8fafc` e campos de entrada escuros `#0e171c`.
    * O botão `← Voltar` foi padronizado em `#182a32` com borda e texto claro, e o botão `+ Cadastrar` no verde institucional `#52a435` com hover iluminado.
  - **Modernização de Páginas Legadas e Eliminação de Estilos Inline:**
    * `app/Views/socioeconomico/index.php`: Removidos todos os `style="background:#fff..."` legados e substituídos pelas classes globais `.card-glass`, `.table-glass` e `.btn-icon`.
    * `app/Views/users/create.php` e `app/Views/users/edit.php`: Removidos estilos inline forçados, campos convertidos para `.form-control` / `.form-select`, substituídos emojis por ícones FontAwesome e adicionado suporte integral ao tema escuro.
    * `css/reports.css`: Adicionado suporte nativo e completo ao modo escuro para a Central de Relatórios (`reports.php`), cobrindo o cabeçalho hero, filtros, resumo, tabelas e rodapé.
    * `css/style.css`: Criado um escudo universal de sobrescrita no final da folha de estilos para garantir que quaisquer resquícios de `style="background:#fff..."` em componentes antigos sejam renderizados em tons noturnos de alta legibilidade no modo escuro.

### Correção de Dimensões e Visibilidade da Coluna de Ações nas Tabelas
- **Problema Solucionado:** A coluna "AÇÕES" estava sendo cortada no canto direito das tabelas (o título aparecia como "AÇÕE" e o terceiro botão, de exclusão, ficava inacessível fora da área visível) devido ao contêiner com `overflow: hidden !important` e células com largura excessiva.
- **Ajustes Realizados:**
  - **Responsividade e Rolagem Horizontal:** Substituído `overflow-hidden` por `.table-responsive` nos contêineres de tabela em `acolhimento`, `socioeconomico`, `logs` e `psychology`. O contêiner agora possui `overflow-x: auto !important; overflow-y: hidden !important;`, impedindo qualquer corte indesejado de colunas.
  - **Scrollbar Personalizada Noturna:** Criada barra de rolagem horizontal ultra fina (6px), com trilho escuro (`#0d171c`) e indicador sutil (`#253944`) com destaque ao passar o mouse (`#ef7417`), garantindo elegância estética sem barras cinzas brutas nativas do SO.
  - **Largura Garantida da Coluna de Ações:** A classe `.actions-cell` agora possui largura fixa calibrada (`width: 155px !important; min-width: 155px !important; max-width: 175px !important;`) com alinhamento centralizado, acomodando perfeitamente todos os 3 botões (Visualizar, Editar e Excluir) em uma única linha sem quebra e sem corte.
  - **Otimização de Espaçamento:** Reduzido o padding horizontal das células das tabelas para `12px 14px`, e aplicada largura máxima com elipse suave (`text-overflow: ellipsis`) para nomes muito extensos em telas com resoluções menores (como laptops 1366x768).

### Sistema Universal de Pré-Visualização de Fotos e Imagens (Previewzinho)
- **Implementação Realizada:**
  - **Ficha de Acolhimento — Foto 3x4 (`acolhimento_form.php`):**
    * Substituído o input de arquivo básico por um card moderno com moldura 3x4 proporcional (`120x160px`).
    * **No Cadastro:** Apresenta placeholder com ícone de câmera, texto e status; ao escolher a foto, exibe pré-visualização instantânea via `FileReader`, badge verde "Nova Foto", nome do arquivo, tamanho em KB e botão "Desfazer".
    * **Na Edição:** Carrega automaticamente a **Foto Atual** já salva com badge azul "Foto Atual"; ao selecionar um novo arquivo, atualiza o preview em tempo real e oferece o botão "Desfazer" para reverter e manter a foto cadastrada sem perder nada.
    * Suporta arrastar e soltar imagens diretamente na moldura 3x4 com feedback visual dinâmico.
  - **Ficha de Acolhimento — Carimbo/Assinatura (`acolhimento_form.php`):**
    * Implementada moldura panorâmica (`220x110px`) com `object-fit: contain` para garantir a legibilidade de assinaturas ou carimbos profissionalmente digitalizados.
    * Funciona de forma idêntica tanto no cadastro quanto na edição, com rota autenticada `acolhimento_view.php?action=carimbo&id=...` para servir a imagem de forma segura.
    * Exibição do carimbo/assinatura adicionada à tela de visualização do acolhimento (`acolhimento_view.php`).
  - **Foto de Perfil do Usuário (`profile.php`):**
    * Adicionada renderização instantânea da foto via `FileReader` no momento em que o arquivo é selecionado na câmera, antes do upload assíncrono terminar, com rollback automático caso ocorra erro.
  - **Anexos de Documentos em Prontuários (`prontuarios_view.php`):**
    * Adicionado card dinâmico de pré-visualização para novos anexos: se for imagem (`JPG`, `PNG`, `WEBP`), exibe thumbnail e detalhes; se for `PDF` ou `DOC`, exibe ícone estilizado colorido e tamanho antes de clicar em "Anexar".
  - **Design System Noturno & Diurno:**
    * Componentes totalmente integrados às variáveis globais e compatíveis tanto com o tema claro quanto com o tema escuro (`css/acolhimento-form.css` e `css/style.css`).

### [05/09/2026 - Segurança Reforçada: Validação e Restrição Estrita de Nome Completo e Encaminhado Por]
- **Campos Protegidos:**
  - `nome_completo` (Nome Completo)
  - `encaminha_por` (Encaminhado por)
- **Regra de Segurança Implementada:**
  - Proibição absoluta de números (`0-9`), emojis, símbolos, scripts e caracteres especiais (`< > @ # $ % ! ? * + = / \ " '` etc.).
  - Aceitação estrita e segura apenas de letras (incluindo acentuação da língua portuguesa como `á, à, â, ã, é, ê, í, ó, ô, õ, ú, ü, ç`) e espaços.
- **Camada Frontend (Tempo Real):**
  - **Bloqueio no evento `beforeinput`:** Intercepta antes da digitação ou composição ser inserida na tela, impedindo que números, emojis ou símbolos apareçam no campo.
  - **Sanitização no evento `input` e `paste`:** Limpa instantaneamente qualquer conteúdo colado ou inserido via preenchimento automático mantendo unicamente letras e espaços.
  - **Validação HTML5:** Adicionados atributos `pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$"` em `nome_completo` e `pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]*$"` em `encaminha_por`, com `title` informativo de segurança e avisos visuais de apoio (`field-hint`).
- **Camada Backend (Serviço e Controlador):**
  - **`AcolhimentoService::validateFichaData`:** Validação rigorosa com `preg_match('/[0-9]/', ...)` e `preg_match('/^[\p{L}\s]+$/u', ...)`. Lança exceções amigáveis e claras (`O nome completo não pode conter números`, `O nome completo deve conter apenas letras e espaços (sem números, emojis ou caracteres especiais)`).
  - **`AcolhimentoController::store`:** Mapeamento de erros direto para `$_SESSION['field_errors']` e `$_SESSION['flash_error']`, exibindo banner no topo e mensagem de erro em vermelho logo abaixo do respectivo campo com retenção dos dados preenchidos (`old_input`).
- **Testes Automatizados:**
  - Suíte de testes unitários `tests/automated/NameSecurityValidationTest.php` cobrindo todas as variações (números, emojis, tags `<script>`, símbolos `@ #`, acentos em português, campo opcional vazio).
  - Teste de integração HTTP no `tests/run_http_smoke.php` (`testNameAndEncaminhadoSecurityValidation`).

### [07/09/2026 - Reformulação Geral dos Alertas Prioritários, Modal de Aniversariantes e Sistema de Descarte por Usuário]
- **Visão Geral:**
  - Reestruturação completa do subsistema de **Alertas Prioritários** no dashboard principal (`dashboard.php`), correção de inconsistências de regras de negócio, introdução de modal amplo e padronizado para visualização dos aniversariantes do mês com link rápido para prontuário, sistema de descarte individual com isolamento exclusivo por usuário logado (`localStorage`) e botão de restauração global.

- **1. Backend (`app/Controllers/DashboardController.php`):**
  - **Correção de Regras e Consultas:**
    * Eliminado o bug com a variável indefinida `$idadeLimite` que quebrava silenciosamente a checagem de maioridade (18 anos).
    * Otimizadas as consultas para agregação direta via PDO em vez de paginação manual com `listFichas(1, 100)`.
    * Separação granular do controle de faltas: **Crítico (>= 3 faltas)** com risco iminente de desligamento e **Atenção (2 faltas)** para busca ativa preventiva.
    * Inclusão de checagens para acolhidos sem Ficha Socioeconômica, dados cadastrais incompletos e revisões semestrais pendentes (> 6 meses).
  - **Identificadores Estáveis de Alerta:**
    * Cada alerta retornado pelo método `getAlertas()` possui um `id` consistente (`faltas_criticas`, `faltas_risco`, `maioridade`, `sem_socioeconomico`, `dados_incompletos`, `revisao_socioeconomica`, `sessoes_psicologicas`, `aniversariantes_mes`).
  - **Endpoint de Aniversariantes do Mês (`getAniversariantesDoMes()`):**
    * Busca todos os acolhidos com status `Ativo` que fazem aniversário no mês atual (`MONTH(data_nascimento) = MONTH(CURDATE())`).
    * Calcula a idade exata a ser completada (`idade_completando`), dia formatado em 2 dígitos (`dia_formatado`), flag indicando se faz aniversário hoje (`is_hoje`), foto/iniciais e rota direta para o prontuário (`prontuarios.php?id=X`).
    * Vincula a ação especial `'action' => 'open_birthday_modal'` ao alerta de aniversário.
    * Envia `$aniversariantesDetalhes` e `$userId` diretamente para a view.

- **2. Frontend View (`app/Views/dashboard/index.php`):**
  - **Modal de Aniversariantes do Mês (`#birthdayModal`):**
    * Substituição da caixa estreita anterior por um modal amplo e imersivo com layout horizontal flexível (`max-width: 920px`).
    * Desvinculada a classe restritiva `.modal-confirm-dialog` que impunha `max-width: 400px`, garantindo largura total sem esmagamento de conteúdo.
    * Cada card de aniversariante (`.birthday-person-card`) exibe: badge de calendário com dia e mês abreviado, avatar ou iniciais em círculo, nome completo em linha única sem quebras feias, idade que completará com data de nascimento, e botão de ação direta para o Prontuário (`.btn-birthday-prontuario`).
    * Cabeçalho executivo com logotipo oficial, título e contagem de acolhidos sem poluição de emojis.
    * Botão de fechamento estilizado em vermelho vibrante (`#d73535`) para clara identificação visual da ação de cancelamento/saída.
  - **Botão de Abertura "Ver lista" no Alerta:**
    * Componente `.pill-open-btn` estilizado como cápsula em vermelho vibrante (`#d73535`) com texto branco em alto contraste, ícone de usuários e chevron integrado `Ver lista ›`.
  - **Sistema de Descarte Individual por Usuário (Dismiss):**
    * Cada alerta na lista possui um botão discreto de fechar (`.pill-dismiss-btn` com `×`).
    * O clique chama `dismissAlert(event, alertId)` isolado com `stopPropagation()`.
    * Armazenamento escopado no `localStorage` sob a chave `cf_dismissed_alerts_user_${currentUserId}`, garantindo que dispensas feitas por um usuário **nunca** afetem a visão de outros usuários logados.
    * Saída com micro-animação lateral suave (`@keyframes pillDismiss`).
  - **Botão de Restauração Global ("Restaurar (N)"):**
    * Botão dinâmico `#btnRestoreAlerts` no cabeçalho do card de alertas, exibindo a quantidade de itens ocultados.
    * Ao clicar, restaura todos os alertas dispensados instantaneamente com animação de reentrada (`@keyframes pillRestore`).
    * Se todos os alertas forem ocultados, a tela exibe a faixa compacta `.alerts-dismissed-box` com mensagem informativa e botão inline de restauração.

- **3. Folhas de Estilo e Design System (`css/style.css`):**
  - Definição das classes `.alerts-header-flex`, `.btn-restore-alerts`, `.alerts-pills-stack`, `.alert-item-wrapper`, `.pill-open-btn`, `.alerts-dismissed-box`, `.btn-restore-inline`.
  - Estilização do modal: `.modal-birthday-dialog` (largura máxima de 920px, min-width de 780px no desktop, cantos arredondados de 20px, sombra profunda e backdrop blur).
  - Cards internos: `.birthday-person-card`, `.birthday-date-badge`, `.birthday-avatar-col`, `.birthday-avatar-initials`, `.birthday-info-col`, `.birthday-person-name`, `.birthday-person-sub`, `.btn-birthday-prontuario`, `.btn-birthday-close`.
  - Cores vermelhas padronizadas (`#d73535` / `#b91c1c`) para os botões de ação e fechamento com sombras sutis e estados hover com elevação.
  - Suporte completo a ambos os temas: Light Theme e Dark Theme (`[data-theme="dark"]` e `body.dark-theme`).

- **4. Roteamento Direto do Prontuário Individual nos Aniversariantes:**
  - **Problema corrigido:** Ao clicar no botão "Prontuário" dentro do modal de aniversariantes, o link direcionava para `prontuarios.php?id=X`. Como o roteador do `index.php` e o `ProntuarioController` aceitavam unicamente o parâmetro `cpf` na rota de detalhe (`action=show`), a requisição caía na listagem geral de todos os prontuários em vez de abrir a ficha individual do aniversariante correspondente.
  - **Backend (`DashboardController.php`):**
    * Adicionada a coluna `a.cpf` à consulta SQL de aniversariantes do mês em `getAniversariantesDoMes()`.
    * Geração do link direto individual (`link_prontuario`): `prontuarios.php?action=show&cpf={cpf}&id={id}` (com fallback seguro apenas com `id` caso o atendido ainda não possua CPF cadastrado).
  - **Roteador Principal (`index.php`):**
    * Atualização do bloco `case 'prontuarios'` para suportar abertura de prontuário tanto por CPF quanto por ID (`$prontuarioController->show($cpf, $id)`).
    * Aceita requisições tanto explícitas (`action=show&id=...`) quanto diretas (`prontuarios.php?id=...` ou `prontuarios.php?cpf=...`).
  - **Controlador (`ProntuarioController.php`):**
    * Assinatura de `public function show($cpf = null, $id = null)` flexibilizada.
    * Busca direta no serviço de acolhimento (`getFicha($id)`) e socioeconômico (`getFicha($id)`) quando o ID é fornecido.
    * Busca por CPF normalizado mantida para retrocompatibilidade total com as telas de busca.
    * Correlacionamento automático: se uma ficha for encontrada pelo CPF mas a outra não, o controlador busca a ficha complementar pelo ID vinculado ao atendido.
    * Prevenção de avisos de chave indefinida (`acao_sugerida`, níveis de alerta dinâmicos).
  - **Testes e Validação:**
    * Teste unitário e end-to-end em `tests/run_http_smoke.php` (`testProntuarioAccessibleByIdAndCpf`): acesso com CPF e ID, acesso apenas por ID e acesso direto com `prontuarios.php?id=...` validados com sucesso (HTTP 200 e dados do atendido renderizados).
    * Suíte de testes `tests/run.php` executada: **9 testes aprovados, 0 falhas**.

- **5. Testes e Validação:**
  - Sintaxe PHP validada sem erros (`php -l DashboardController.php` e `index.php`).
  - Suíte de testes unitários automatizados executada via `tests/run.php` com **9 aprovados, 0 falhas** (100% de sucesso).
  - Verificação de renderização completa da view confirmando que a limitação de 400px foi eliminada e que todos os botões e seletores funcionam de acordo com as especificações.

---

## Próximos Passos Sugeridos
1. No dashboard, abra o modal de **Aniversariantes de Setembro** e clique no botão **Prontuário** de qualquer um dos atendidos (ex: *Tiago dos Campos* ou *Chiquinho do Vale*): o sistema abrirá diretamente o prontuário individual daquele atendido.
2. No formulário de acolhimento (`acolhimento_form.php`), tente digitar ou colar números, emojis ou caracteres especiais nos campos **Nome Completo** e **Encaminhado por**: veja que eles são bloqueados instantaneamente em tempo real.
3. Digite nomes acentuados válidos (ex: *João da Conceição Silva*, *Vara da Infância e Juventude*) e confirme que funcionam com total perfeição.

---

## Validação técnica — 08/09/2026

- Corrigido o fechamento de permissão na visualização socioeconômica, que causava erro de sintaxe PHP.
- A negativa de acesso à área psicológica e a outras rotas protegidas agora redireciona ao dashboard com mensagem, em vez de responder erro HTTP 500.
- `SETUP_COMPLETO_FINAL.sql` foi consolidado como fonte única do schema para instalações novas. Referências aos scripts legados removidos foram atualizadas na documentação e no instalador auxiliar.
- Repostas as quatro oficinas iniciais previstas no setup no banco local de testes.
- Validações executadas neste ambiente:
  - sintaxe de 119 arquivos PHP: sem erros;
  - testes unitários: 9 aprovados;
  - testes de integração: 15 testes e 94 asserções aprovados;
  - smoke test HTTP: 11 testes e 172 asserções aprovados.
- Após os testes, os registros fictícios devem ser removidos do banco local; somente a configuração inicial e as contas de teste solicitadas devem permanecer.
