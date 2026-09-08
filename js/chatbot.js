/**
 * Assistente Criança Feliz — Central de Ajuda Interativa em Grid
 * Sistema de Suporte da Associação Criança Feliz
 */

class ChatBot {
    constructor() {
        this.isOpen = false;
        this.isTyping = false;
        const userRole = window.userRole || 'funcionario';
        
        const baseTopics = [
            { id: 'como-usar', icon: 'fa-compass', title: 'Como usar o sistema?', desc: 'Navegação e painel', roles: ['administrador', 'funcionario', 'psicologo'] },
            { id: 'fichas', icon: 'fa-folder-open', title: 'Fichas e Cadastros', desc: 'Acolhimento e social', roles: ['administrador', 'funcionario'] },
            { id: 'faltas', icon: 'fa-calendar-check', title: 'Faltas e Chamada', desc: 'Frequência e alertas', roles: ['administrador', 'funcionario'] },
            { id: 'oficinas', icon: 'fa-cogs', title: 'Gerenciar Oficinas', desc: 'Turmas e horários', roles: ['administrador', 'funcionario'] },
            { id: 'calendario', icon: 'fa-calendar-alt', title: 'Anotações e Avisos', desc: 'Calendário e eventos', roles: ['administrador', 'funcionario', 'psicologo'] },
            { id: 'desligamento', icon: 'fa-user-clock', title: 'Desligamentos', desc: 'Regras de 18 anos', roles: ['administrador', 'funcionario'] },
            { id: 'relatorios', icon: 'fa-chart-pie', title: 'Relatórios', desc: 'Gráficos e métricas', roles: ['administrador', 'funcionario'] },
            { id: 'perfil', icon: 'fa-user-circle', title: 'Meu Perfil e Senha', desc: 'Foto e segurança', roles: ['administrador', 'funcionario', 'psicologo'] },
            { id: 'psicologia', icon: 'fa-brain', title: 'Área Psicológica', desc: 'Prontuários e sigilo', roles: ['psicologo'] },
            { id: 'logs', icon: 'fa-history', title: 'Auditoria e Logs', desc: 'Histórico de ações', roles: ['administrador'] }
        ];

        this.helpTopics = baseTopics.filter(t => t.roles.includes(userRole));
        this.knowledgeBase = this.initKnowledgeBase();
        this.init();
    }

    init() {
        // 1. Ocultar totalmente para Administrador
        if (window.userRole === 'admin' || window.userRole === 'administrador') {
            return;
        }

        // 2. Respeitar configuração do usuário
        const isEnabled = localStorage.getItem('chatbot_enabled') !== 'false';
        if (!isEnabled) {
            return;
        }

        if (document.getElementById('chatbot')) {
            return;
        }

        this.createChatbotHTML();
        this.bindEvents();
        this.showWelcomeAndGrid();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div class="chatbot-container" id="chatbot">
                <button class="chatbot-toggle" id="chatbot-toggle" type="button" aria-label="Abrir Central de Ajuda" title="Central de Ajuda Criança Feliz">
                    <i class="fas fa-comment-dots" id="chatbot-toggle-icon"></i>
                    <span class="chatbot-status-indicator" title="Assistente Online"></span>
                </button>

                <div class="chatbot-window" id="chatbot-window" role="dialog" aria-labelledby="chatbot-title">
                    <div class="chatbot-header">
                        <div class="chatbot-brand">
                            <img src="img/logo.png" class="chatbot-logo" alt="Logo Criança Feliz">
                            <div class="chatbot-header-text">
                                <h3 id="chatbot-title">Central de Ajuda Criança Feliz</h3>
                                <span class="chatbot-status">
                                    <span class="chatbot-status-dot"></span> Online &bull; Suporte do Sistema
                                </span>
                            </div>
                        </div>
                        <div class="chatbot-header-actions">
                            <button type="button" class="chatbot-header-btn" id="chatbot-reset" title="Reiniciar opções" aria-label="Reiniciar opções">
                                <i class="fas fa-rotate-left"></i>
                            </button>
                            <button type="button" class="chatbot-header-btn" id="chatbot-close" title="Fechar" aria-label="Fechar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="chatbot-content" id="chatbot-content">
                        <!-- Conteúdo, grid e mensagens aparecem aqui -->
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    bindEvents() {
        const toggle = document.getElementById('chatbot-toggle');
        const close = document.getElementById('chatbot-close');
        const reset = document.getElementById('chatbot-reset');
        const content = document.getElementById('chatbot-content');

        if (toggle) {
            toggle.addEventListener('click', () => this.toggleChat());
        }

        if (close) {
            close.addEventListener('click', () => this.closeChat());
        }

        if (reset) {
            reset.addEventListener('click', () => this.resetConversation());
        }

        if (content) {
            content.addEventListener('click', (e) => {
                const card = e.target.closest('.chatbot-grid-card');
                if (card) {
                    const topicId = card.getAttribute('data-topic');
                    if (topicId) {
                        this.handleTopicSelection(topicId);
                    }
                    return;
                }

                const backBtn = e.target.closest('.chatbot-back-btn');
                if (backBtn) {
                    this.returnToGrid();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeChat();
            }
        });
    }

    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    openChat() {
        const windowElem = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        const icon = document.getElementById('chatbot-toggle-icon');

        this.isOpen = true;
        if (windowElem) windowElem.classList.add('active');
        if (toggle) toggle.classList.add('active');
        if (icon) icon.className = 'fas fa-times';

        setTimeout(() => {
            const content = document.getElementById('chatbot-content');
            if (content) {
                const userMessages = content.querySelectorAll('.chatbot-message.user');
                if (userMessages.length === 0) {
                    content.scrollTop = 0;
                } else {
                    this.scrollToBottom();
                }
            }
        }, 50);
    }

    closeChat() {
        const windowElem = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        const icon = document.getElementById('chatbot-toggle-icon');

        this.isOpen = false;
        if (windowElem) windowElem.classList.remove('active');
        if (toggle) toggle.classList.remove('active');
        if (icon) icon.className = 'fas fa-comment-dots';
    }

    showWelcomeAndGrid() {
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        // Mensagem de boas-vindas
        const welcomeDiv = document.createElement('div');
        welcomeDiv.className = 'chatbot-message bot';
        welcomeDiv.innerHTML = `
            <strong>Olá! Seja bem-vindo(a) ao Assistente Virtual.</strong>
            <div class="chatbot-spacing"></div>
            Selecione uma das opções no painel abaixo para obter orientações e atalhos rápidos do sistema:
        `;
        content.appendChild(welcomeDiv);

        // Renderiza a lista de opções iniciando no topo
        this.renderOptionsGrid(true);
    }

    renderOptionsGrid(scrollToTop = false) {
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        // Remove grids anteriores caso existam
        const oldGrids = content.querySelectorAll('.chatbot-grid-container');
        oldGrids.forEach(g => g.remove());

        const gridContainer = document.createElement('div');
        gridContainer.className = 'chatbot-grid-container';

        const gridHTML = `
            <div class="chatbot-grid-title">
                <i class="fas fa-th-list"></i> Opções de Ajuda
            </div>
            <div class="chatbot-help-grid">
                ${this.helpTopics.map(topic => `
                    <button type="button" class="chatbot-grid-card" data-topic="${topic.id}">
                        <div class="chatbot-card-icon-wrapper">
                            <i class="fas ${topic.icon}"></i>
                        </div>
                        <div class="chatbot-card-content">
                            <div class="chatbot-card-title">${topic.title}</div>
                            <div class="chatbot-card-desc">${topic.desc}</div>
                        </div>
                        <i class="fas fa-chevron-right chatbot-card-arrow"></i>
                    </button>
                `).join('')}
            </div>
        `;

        gridContainer.innerHTML = gridHTML;
        content.appendChild(gridContainer);
        
        if (scrollToTop) {
            this.scrollToTop();
        } else {
            this.scrollToBottom();
        }
    }

    handleTopicSelection(topicId) {
        if (this.isTyping) return;

        const topic = this.helpTopics.find(t => t.id === topicId);
        if (!topic) return;

        // 1. Adiciona a escolha do usuário
        this.addUserMessage(topic.title, topic.icon);

        // 2. Remove o grid atual para focar na resposta
        const content = document.getElementById('chatbot-content');
        const grid = content.querySelector('.chatbot-grid-container');
        if (grid) grid.remove();

        // 3. Simula digitação e responde
        this.showTyping();

        setTimeout(() => {
            this.hideTyping();
            const answer = this.knowledgeBase[topicId];

            if (answer) {
                this.addBotMessage(answer.response, answer.actions || []);
            } else {
                this.addBotMessage('Informações para este tópico não foram encontradas.');
            }

            // 4. Adiciona botão ergonômico para voltar ao grid
            this.showBackToGridButton();
        }, 450);
    }

    addUserMessage(title, icon) {
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        const msgDiv = document.createElement('div');
        msgDiv.className = 'chatbot-message user';
        msgDiv.innerHTML = `<i class="fas ${icon || 'fa-check'}"></i> <span>${title}</span>`;

        content.appendChild(msgDiv);
        this.scrollToBottom();
    }

    addBotMessage(text, actions = []) {
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        const msgDiv = document.createElement('div');
        msgDiv.className = 'chatbot-message bot';
        msgDiv.innerHTML = this.formatBotResponse(text);

        if (Array.isArray(actions) && actions.length > 0) {
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'chatbot-actions-group';

            actions.forEach(action => {
                const btn = document.createElement('a');
                btn.className = 'chatbot-action-link';
                btn.href = action.url;
                btn.innerHTML = `<i class="fas ${action.icon || 'fa-arrow-right'}"></i> <span>${action.label}</span>`;
                actionsDiv.appendChild(btn);
            });

            msgDiv.appendChild(actionsDiv);
        }

        content.appendChild(msgDiv);
        this.scrollToBottom();
    }

    showBackToGridButton() {
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        // Remove botões de voltar anteriores
        const oldBacks = content.querySelectorAll('.chatbot-back-wrapper');
        oldBacks.forEach(b => b.remove());

        const wrapper = document.createElement('div');
        wrapper.className = 'chatbot-back-wrapper';
        wrapper.innerHTML = `
            <button type="button" class="chatbot-back-btn" onclick="window.cfChatbot.returnToGrid()">
                <i class="fas fa-th-list"></i> Ver todas as opções de ajuda
            </button>
        `;

        content.appendChild(wrapper);
        this.scrollToBottom();
    }

    returnToGrid() {
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        // Remove o botão de voltar
        const oldBacks = content.querySelectorAll('.chatbot-back-wrapper');
        oldBacks.forEach(b => b.remove());

        // Renderiza a lista de opções
        this.renderOptionsGrid(false);
    }

    resetConversation() {
        const content = document.getElementById('chatbot-content');
        if (content) {
            content.innerHTML = '';
        }
        this.showWelcomeAndGrid();
        this.scrollToTop();
    }

    showTyping() {
        this.isTyping = true;
        const content = document.getElementById('chatbot-content');
        if (!content) return;

        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-typing';
        typingDiv.id = 'chatbot-typing-indicator';
        typingDiv.innerHTML = `
            <div class="chatbot-typing-dot"></div>
            <div class="chatbot-typing-dot"></div>
            <div class="chatbot-typing-dot"></div>
        `;

        content.appendChild(typingDiv);
        this.scrollToBottom();
    }

    hideTyping() {
        this.isTyping = false;
        const indicator = document.getElementById('chatbot-typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    scrollToTop() {
        const content = document.getElementById('chatbot-content');
        if (content) {
            content.scrollTop = 0;
        }
    }

    scrollToBottom() {
        const content = document.getElementById('chatbot-content');
        if (content) {
            content.scrollTop = content.scrollHeight;
        }
    }

    formatBotResponse(text) {
        const div = document.createElement('div');
        div.textContent = text;
        let escaped = div.innerHTML;

        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        escaped = escaped.replace(/(?:^|\n)\s*•\s*(.*?)(?=\n|$)/g, '<div class="chatbot-bullet"><i class="fas fa-check"></i> <span>$1</span></div>');
        escaped = escaped.replace(/\n\n/g, '<div class="chatbot-spacing"></div>');
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    initKnowledgeBase() {
        return {
            'como-usar': {
                response: 
                    'O **Sistema Criança Feliz** reúne a gestão completa da instituição:\n\n' +
                    '• **Menu Lateral**: Acesso rápido a Início, Prontuários, Faltas, Oficinas, Relatórios e Perfil.\n' +
                    '• **Dashboard Principal**: Acompanhe o calendário, alertas prioritários e contagem de fichas ativas.\n' +
                    '• **Níveis de Acesso**: Recursos protegidos para Administradores, Funcionários e Psicólogos.\n' +
                    '• **Tema Escuro/Claro**: Alternável no botão da barra superior.',
                actions: [
                    { label: 'Ir para o Dashboard', url: 'dashboard.php', icon: 'fa-home' }
                ]
            },

            'fichas': {
                response:
                    'O sistema gerencia dois tipos de cadastros principais:\n\n' +
                    '• **Ficha de Acolhimento**: Cadastro civil, dados da criança, responsável legal e composição familiar.\n' +
                    '• **Ficha Socioeconômica**: Avaliação das condições sociais, renda per capita e moradia.\n\n' +
                    'Todas as fichas podem ser consultadas, filtradas e editadas pelo menu de Prontuários.',
                actions: [
                    { label: 'Acessar Prontuários', url: 'prontuarios.php', icon: 'fa-folder-open' },
                    { label: 'Nova Ficha Socioeconômica', url: 'socioeconomico.php?action=create', icon: 'fa-plus' }
                ]
            },

            'faltas': {
                response:
                    'O módulo de **Frequência e Faltas** controla a assiduidade dos atendidos:\n\n' +
                    '• **Frequência por Dia**: Lançamento da presença ou ausência na data selecionada.\n' +
                    '• **Frequência por Oficina**: Chamada específica de turmas e atividades socioeducativas.\n' +
                    '• **Alertas de Faltas**: Atendidos com 3 ou mais faltas não justificadas disparam alerta prioritário no painel.',
                actions: [
                    { label: 'Frequência Diária', url: 'faltas.php', icon: 'fa-calendar-day' },
                    { label: 'Faltas por Oficina', url: 'faltas.php?action=oficina', icon: 'fa-chalkboard-teacher' },
                    { label: 'Alertas de Faltas', url: 'faltas.php?action=alertas', icon: 'fa-exclamation-triangle' }
                ]
            },

            'oficinas': {
                response:
                    'Na área de **Gerenciar Oficinas**, administradores podem:\n\n' +
                    '• Cadastrar novas oficinas, horários e dias da semana.\n' +
                    '• Matricular atendidos e organizar as turmas.\n' +
                    '• Controlar vagas e acompanhar o preenchimento das atividades.',
                actions: [
                    { label: 'Gerenciar Oficinas', url: 'faltas.php?action=gerenciarOficinas', icon: 'fa-cogs' }
                ]
            },

            'calendario': {
                response:
                    'O calendário do Dashboard organiza o cronograma da associação:\n\n' +
                    '• **Anotações (Laranja)**: Reuniões internas, lembretes operacionais e atendimentos.\n' +
                    '• **Avisos (Verde)**: Passeios, eventos gerais e avisos para toda a equipe.\n' +
                    '• **Como criar**: Clique sobre o dia desejado no calendário e preencha o formulário.\n' +
                    '• **Como excluir**: Clique no botão de lixeira do cartão e confirme no modal com a logo da ONG.',
                actions: [
                    { label: 'Ver Calendário', url: 'dashboard.php', icon: 'fa-calendar-alt' }
                ]
            },

            'desligamento': {
                response:
                    'O painel de **Desligamentos** faz a gestão de saídas da ONG:\n\n' +
                    '• **Maioridade (18 anos)**: Notificação automática no Dashboard para transição de atendidos que atingiram a idade limite.\n' +
                    '• **Limite de Faltas**: Lista atendidos com excesso de faltas não justificadas para parecer social.\n' +
                    '• O histórico completo dos atendimentos é preservado.',
                actions: [
                    { label: 'Painel de Desligamentos', url: 'desligamento.php', icon: 'fa-user-times' }
                ]
            },

            'relatorios': {
                response:
                    'A área de **Relatórios** consolida os dados institucionais:\n\n' +
                    '• Totalizadores de atendidos ativos e inativos.\n' +
                    '• Assiduidade e taxa de presença por oficina e período.\n' +
                    '• Distribuição socioeconômica das famílias atendidas.',
                actions: [
                    { label: 'Visualizar Relatórios', url: 'reports.php', icon: 'fa-chart-pie' }
                ]
            },

            'perfil': {
                response:
                    'Na página de **Perfil**, você tem controle da sua conta:\n\n' +
                    '• Atualização de foto de perfil institucional.\n' +
                    '• Alteração de senha com validação de segurança.\n' +
                    '• Consulta de perfil de acesso e e-mail cadastrado.',
                actions: [
                    { label: 'Acessar Meu Perfil', url: 'profile.php', icon: 'fa-user-circle' }
                ]
            },

            'psicologia': {
                response:
                    'A **Área Psicológica** é um módulo protegido para profissionais de psicologia:\n\n' +
                    '• Registro de prontuários clínicos e evoluções de atendimento.\n' +
                    '• Histórico psicológico sigiloso com acesso restrito.\n' +
                    '• Gráficos e indicadores psicossociais da instituição.',
                actions: [
                    { label: 'Área Psicológica', url: 'psychology.php', icon: 'fa-brain' }
                ]
            },

            'logs': {
                response:
                    'O **Sistema de Logs** audita todas as movimentações da plataforma:\n\n' +
                    '• Registro de autenticações (logins e logouts).\n' +
                    '• Rastreabilidade de inclusões, alterações e exclusões.\n' +
                    '• Comparação detalhada de dados para fins de segurança e auditoria.',
                actions: [
                    { label: 'Sistema de Logs', url: 'logs.php', icon: 'fa-history' }
                ]
            }
        };
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.cfChatbot = new ChatBot();
});
