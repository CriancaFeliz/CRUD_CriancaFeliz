# Documento de Modernização Estética do Frontend (CSS/Bootstrap)

Este documento descreve detalhadamente as melhorias implementadas na arquitetura de estilos e no design visual do **Sistema Criança Feliz**, substituindo o uso de propriedades rígidas inline por um **Design System unificado** baseado em variáveis CSS nativas, estética glassmorphic de alta fidelidade e transições dinâmicas de tema.

---

## 1. Propósito da Modernização

Antes deste upgrade, o sistema exibia estilos inline rígidos (atributos `style="..."` aplicados diretamente às tags HTML) e injeções de estilo dinâmicas controladas via JavaScript (`js/theme-toggle.js`). Essa abordagem causava:
1. **Inconsistência visual** e dificuldades na manutenção do código.
2. **Bugs de contraste no Modo Escuro**, como textos e inputs com fundo branco residual ou preto-sobre-preto.
3. **Efeito visual indesejado (flash)**, no qual a página carregava clara e depois mudava para escura após a execução do script.

**O que foi feito:**
- Refatoramos todo o design para utilizar **Variáveis CSS** puras aplicadas sob o atributo `data-theme`.
- Removemos 100% da injeção de estilo inline por Javascript. Agora, o JavaScript apenas altera o atributo de tema na tag raiz (`html`/`body`), garantindo a renderização instantânea do design apropriado pelo navegador.
- Padronizamos as views principais (`dashboard/index.php`, `prontuarios/index.php`, `acolhimento/index.php` e layout `main.php`) com classes CSS semânticas e reutilizáveis.

---

## 2. Design System & Tokens de Estilo (Variáveis CSS)

Centralizamos todos os tokens visuais na folha de estilos principal ([style.css](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/css/style.css)). 

| Token / Variável CSS | Tema Claro (Light) | Tema Escuro (Dark) | Aplicação |
| :--- | :--- | :--- | :--- |
| `--bg-app` | Gradiente Quente Pôr do Sol (`#ffd8be` → `#3e6475`) | Gradiente Escuro Petróleo (`#090d16` → `#0e1e24`) | Fundo do aplicativo geral |
| `--bg-primary` | `#ffffff` | `#15222d` | Fundo principal da página |
| `--bg-secondary` | `#f8f9fa` | `#1a2c3a` | Fundo secundário (tabelas zebradas, etc.) |
| `--bg-tertiary` | `#edf1f3` | `#0c141c` | Fundo terciário de áreas auxiliares |
| `--text-primary` | `#1e293b` | `#f8fafc` | Títulos e textos de alta importância |
| `--text-secondary`| `#475569` | `#cbd5e1` | Parágrafos e textos auxiliares |
| `--text-muted` | `#94a3b8` | `#64748b` | Subtítulos ou informações secundárias |
| `--border-color` | `rgba(240, 163, 107, 0.25)` | `rgba(240, 163, 107, 0.15)` | Linhas de divisão e bordas finas |
| `--input-bg` | `rgba(255, 255, 255, 0.9)` | `rgba(15, 26, 35, 0.7)` | Fundo de inputs |
| `--card-bg` | `rgba(255, 255, 255, 0.85)` | `rgba(26, 44, 58, 0.75)` | Fundo de cards glassmorphic |
| `--sidebar-bg` | `rgba(14, 42, 51, 0.85)` | `rgba(12, 20, 28, 0.85)` | Fundo do menu lateral |
| `--content-bg` | `rgba(237, 241, 243, 0.82)` | `rgba(21, 34, 45, 0.8)` | Fundo do painel de conteúdo |
| `--primary-orange` | `#ff7a00` | `#ff9124` | Botões primários, destaque, links de marca |
| `--primary-green` | `#6fb64f` | `#4fb673` | Indicadores de sucesso, botão de login, hoje |

---

## 3. Alterações Realizadas nos Arquivos

### A. Layout Geral & Transição de Tema
- **[main.php](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/app/Views/layouts/main.php)**: 
  - Removido o bloco `<style>` interno redundante.
  - Substituição de estilos de título inline por `.topbar-title` e do link do perfil por `.user-profile-link`.
  - Substituição do avatar e fallback do avatar por `.avatar` e `.avatar-placeholder` com suporte integrado a `object-fit: cover` diretamente no CSS.
- **[theme-toggle.js](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/js/theme-toggle.js)**:
  - Deletada a função antiga `applyDashboardStyles` que forçava estilos inline via DOM.
  - O script foi modificado para atuar estritamente na manipulação de `data-theme` na tag raiz `html` e no localStorage.

### B. Elementos CSS Globais e Vidro Fosco (Glassmorphic)
- **[style.css](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/css/style.css)**:
  - Criação da classe `.card-glass` (aplica `backdrop-filter: blur(12px)` e fundos translúcidos).
  - Animações micro-interativas nos cards: ao passar o mouse (`:hover`), o card se eleva levemente com `transform: translateY(-2px)` e ganha uma sombra com brilho alaranjado suave.
  - Criação de classes para Badges (`.badge-crianca`, `.badge-adolescente`, `.badge-adulto`) e Status (`.status-ativo`, `.status-inativo`) com transparências e cores harmoniosas para ambos os temas.
  - Padronização de botões modernos (`.btn` e `.btn.secondary`) com gradientes degradê dinâmicos e elevação responsiva.
  - Criação de tabelas modernas de visualização em vidro fosco (`.table-glass`).
  - Criação de classes de suporte para estado vazio (`.empty-state-container`, `.empty-state-icon`, `.empty-state-title`, `.empty-state-text`).
  - Correção agressiva para elementos com cores brancas residuais ou inputs em modo escuro (correção de bugs de legibilidade em campos focados).
- **[acolhimento-form.css](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/css/acolhimento-form.css)**:
  - Refatorado para herdar diretamente as variáveis de cores globais de marca do arquivo `style.css`.

### C. Refatoração de Views
- **[dashboard/index.php](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/app/Views/dashboard/index.php)**:
  - Removidos estilos inline dos cards de estatísticas (substituídos por `.stat-card-glass` e `.note-card-glass`).
  - Ajustados os estilos dinâmicos de geração do calendário escolar para injetar classes CSS nativas (como `.calendar-day`, `.today`, `.has-notes`) em vez de propriedades inline de cor.
- **[prontuarios/index.php](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/app/Views/prontuarios/index.php)**:
  - Removido inline styling do card de busca rápida e das tabelas de resultados.
  - Os botões de ação na tabela foram convertidos para classes CSS padronizadas (`.btn-icon`, `.view-btn`, `.edit-btn`, `.delete-btn`) e as larguras de coluna e centralização foram atribuídas à classe `.actions-cell`.
- **[acolhimento/index.php](file:///c:/Users/mateu/Documents/Meu%20Segundo%20C%C3%A9rebro/Projetos/CriancaFeliz-MELHORADO/app/Views/acolhimento/index.php)**:
  - Removidos os blocos de estilos em tempo de execução baseados em PHP.
  - Atualizadas as funções auxiliares JavaScript (`formatStatus()` e `formatCategoria()`) para injetarem classes CSS seguras em vez de tags HTML com propriedades inline de fundo e cor.
  - Refatoração dos botões e mensagens de tabela para utilizarem a classe centralizadora `.actions-cell`.

---

## 4. Guia de Desenvolvimento Futuro (Visual Coeso)

Para manter a consistência estética do sistema Criança Feliz nas próximas implementações, siga estas diretrizes:

### Evite Estilos Inline
**🚫 NÃO FAÇA:**
```html
<div style="background: #fff; padding: 20px; border-radius: 12px; color: #333;">...</div>
```

**✅ PREFIRA:**
```html
<div class="card-glass">...</div>
```

### Elementos de Formulário
Sempre utilize a classe padrão `.form-control` nos inputs e `.form-label-bold` nos labels. O CSS global se encarregará de renderizá-los com o contraste ideal dependendo do tema atual (claro/escuro).

### Criação de Badges/Status
Use a seguinte estrutura semântica para Badges:
```html
<span class="badge badge-crianca">Criança</span>
<span class="badge badge-adolescente">Adolescente</span>
```

Use a seguinte estrutura para Status de atividade:
```html
<span class="status status-ativo">Ativo</span>
<span class="status status-inativo">Inativo</span>
```

### Botões Reutilizáveis
- Botão principal (laranja degradê): `<a href="..." class="btn">Texto</a>`
- Botão secundário (cinza escuro): `<a href="..." class="btn secondary">Texto</a>`
- Botões de ícones em tabelas (tamanho compacto de 32px):
  - Visualizar: `<a href="..." class="btn-icon view-btn"><i class="fas fa-eye"></i></a>`
  - Editar: `<a href="..." class="btn-icon edit-btn"><i class="fas fa-edit"></i></a>`
  - Excluir: `<button type="submit" class="btn-icon delete-btn"><i class="fas fa-trash"></i></button>`
