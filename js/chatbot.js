// Chatbot para Sistema Criança Feliz
class ChatBot {
    constructor() {
        this.isOpen = false;
        this.currentMenu = 'main';
        this.init();
    }

    init() {
        this.createChatbotHTML();
        this.bindEvents();
        this.showWelcomeMessage();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div class="chatbot-container" id="chatbot">
                <button class="chatbot-toggle" id="chatbot-toggle">
                    💬
                </button>
                <div class="chatbot-window" id="chatbot-window">
                    <div class="chatbot-header">
                        <h3>Assistente Criança Feliz</h3>
                        <p>Como posso ajudar você?</p>
                        <button class="chatbot-close" id="chatbot-close">×</button>
                    </div>
                    <div class="chatbot-content" id="chatbot-content">
                        <!-- Mensagens aparecerão aqui -->
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    bindEvents() {
        const toggle = document.getElementById('chatbot-toggle');
        const close = document.getElementById('chatbot-close');
        
        toggle.addEventListener('click', () => this.toggleChat());
        close.addEventListener('click', () => this.closeChat());
    }

    toggleChat() {
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        
        this.isOpen = !this.isOpen;
        
        if (this.isOpen) {
            window.classList.add('active');
            toggle.classList.add('active');
            toggle.innerHTML = '×';
        } else {
            window.classList.remove('active');
            toggle.classList.remove('active');
            toggle.innerHTML = '💬';
        }
    }

    closeChat() {
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        
        this.isOpen = false;
        window.classList.remove('active');
        toggle.classList.remove('active');
        toggle.innerHTML = '💬';
    }

    showWelcomeMessage() {
        setTimeout(() => {
            this.addMessage('bot', 'Olá! Sou o assistente virtual da Associação Criança Feliz. 👋');
            setTimeout(() => {
                this.showMainMenu();
            }, 1000);
        }, 500);
    }

    addMessage(type, text) {
        const content = document.getElementById('chatbot-content');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${type}`;
        messageDiv.textContent = text;
        content.appendChild(messageDiv);
        content.scrollTop = content.scrollHeight;
    }

    showTyping() {
        const content = document.getElementById('chatbot-content');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-typing';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        content.appendChild(typingDiv);
        content.scrollTop = content.scrollHeight;
    }

    hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) {
            typing.remove();
        }
    }

    showMainMenu() {
        const content = document.getElementById('chatbot-content');
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'chatbot-options';
        optionsDiv.innerHTML = `
            <button class="chatbot-option" onclick="chatbot.handleOption('como-usar')">
                🔍 Como usar o site?
            </button>
            <button class="chatbot-option" onclick="chatbot.handleOption('acessar-fichas')">
                📋 Como acessar as fichas?
            </button>
            <button class="chatbot-option" onclick="chatbot.handleOption('cadastrar-fichas')">
                ➕ Como cadastrar fichas?
            </button>
            <button class="chatbot-option" onclick="chatbot.handleOption('info-criancas')">
                👶 Onde ver informações das crianças?
            </button>
            <button class="chatbot-option" onclick="chatbot.handleOption('info-responsaveis')">
                👨‍👩‍👧‍👦 Onde ver informações dos responsáveis?
            </button>
            <button class="chatbot-option" onclick="chatbot.handleOption('calendario')">
                📅 Como adicionar anotação no calendário?
            </button>
        `;
        content.appendChild(optionsDiv);
        content.scrollTop = content.scrollHeight;
        this.currentMenu = 'main';
    }

    handleOption(option) {
        // Remove opções anteriores
        const existingOptions = document.querySelectorAll('.chatbot-options');
        existingOptions.forEach(opt => opt.remove());

        // Adiciona mensagem do usuário
        const optionTexts = {
            'como-usar': '🔍 Como usar o site?',
            'acessar-fichas': '📋 Como acessar as fichas?',
            'cadastrar-fichas': '➕ Como cadastrar fichas?',
            'info-criancas': '👶 Onde ver informações das crianças?',
            'info-responsaveis': '👨‍👩‍👧‍👦 Onde ver informações dos responsáveis?',
            'calendario': '📅 Como adicionar anotação no calendário?'
        };

        this.addMessage('user', optionTexts[option]);
        
        // Mostra indicador de digitação
        this.showTyping();
        
        // Responde após um delay
        setTimeout(() => {
            this.hideTyping();
            this.showResponse(option);
        }, 1500);
    }

    showResponse(option) {
        const responses = {
            'como-usar': `O Sistema Criança Feliz é muito fácil de usar! 😊

🔐 **Login**: Use suas credenciais para acessar o sistema
📊 **Dashboard**: Após o login, você verá o painel principal com todas as funcionalidades
🧭 **Navegação**: Use o menu lateral para acessar diferentes seções
📱 **Responsivo**: O sistema funciona em computadores, tablets e celulares

O sistema foi desenvolvido para ser intuitivo e fácil de navegar!`,

            'acessar-fichas': `Para acessar as fichas das crianças: 📋

1️⃣ **Faça login** no sistema com suas credenciais
2️⃣ **No dashboard**, procure pelas opções:
   • "Fichas de Acolhimento" 
   • "Fichas Socioeconômicas"
3️⃣ **Clique na opção** desejada para ver a lista
4️⃣ **Visualize** as fichas cadastradas e seus detalhes

As fichas contêm todas as informações importantes das crianças e famílias atendidas.`,

            'cadastrar-fichas': `Para cadastrar novas fichas: ➕

📝 **Ficha de Acolhimento**:
• Acesse "Acolhimento" no menu
• Clique em "Nova Ficha"
• Preencha os dados da criança e família
• Salve as informações

📊 **Ficha Socioeconômica**:
• Acesse "Socioeconômico" no menu  
• Clique em "Nova Avaliação"
• Complete o formulário detalhado
• Confirme o cadastro

Todas as informações são importantes para o acompanhamento adequado!`,

            'info-criancas': `Para ver informações das crianças: 👶

📍 **Locais onde encontrar**:
• **Dashboard**: Resumo geral das crianças atendidas
• **Fichas de Acolhimento**: Dados pessoais e familiares
• **Fichas Socioeconômicas**: Situação social e econômica
• **Prontuários**: Histórico completo de atendimentos

🔍 **Informações disponíveis**:
• Dados pessoais e contato
• Situação familiar
• Histórico de atendimentos
• Avaliações socioeconômicas`,

            'info-responsaveis': `Para ver informações dos responsáveis: 👨‍👩‍👧‍👦

📋 **Onde encontrar**:
• **Fichas de Acolhimento**: Dados completos dos pais/responsáveis
• **Avaliação Socioeconômica**: Situação familiar detalhada
• **Prontuários**: Histórico de interações

ℹ️ **Informações incluem**:
• Nome, CPF e documentos
• Endereço e contatos
• Situação profissional
• Renda familiar
• Composição familiar`,

            'calendario': `Para adicionar anotações no calendário: 📅

📝 **Como fazer**:
1️⃣ **Acesse o Dashboard** após fazer login
2️⃣ **Localize o calendário** na tela principal
3️⃣ **Clique na data** desejada
4️⃣ **Digite sua anotação** na área de texto
5️⃣ **Salve** a informação

💡 **Dicas**:
• Use para marcar consultas, reuniões ou eventos
• As anotações ficam visíveis para toda a equipe
• Ideal para organizar a agenda da associação`
        };

        this.addMessage('bot', responses[option]);
        this.showBackButton();
    }

    showBackButton() {
        const content = document.getElementById('chatbot-content');
        const backDiv = document.createElement('div');
        backDiv.className = 'chatbot-options';
        backDiv.innerHTML = `
            <button class="chatbot-option chatbot-back" onclick="chatbot.goBack()">
                ⬅️ Voltar ao menu principal
            </button>
        `;
        content.appendChild(backDiv);
        content.scrollTop = content.scrollHeight;
    }

    goBack() {
        // Remove botão de voltar
        const existingOptions = document.querySelectorAll('.chatbot-options');
        existingOptions.forEach(opt => opt.remove());

        this.addMessage('user', '⬅️ Voltar ao menu principal');
        
        setTimeout(() => {
            this.addMessage('bot', 'Como posso ajudar você? Escolha uma das opções abaixo:');
            this.showMainMenu();
        }, 800);
    }

    // Método para limpar o chat
    clearChat() {
        const content = document.getElementById('chatbot-content');
        content.innerHTML = '';
        this.showWelcomeMessage();
    }
}

// Inicializar o chatbot quando a página carregar
let chatbot;
document.addEventListener('DOMContentLoaded', function() {
    chatbot = new ChatBot();
});

// Função global para acessibilidade
window.toggleChatbot = function() {
    if (chatbot) {
        chatbot.toggleChat();
    }
};
