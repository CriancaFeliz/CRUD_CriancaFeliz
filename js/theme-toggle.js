/**
 * Sistema de Modo Escuro - Criança Feliz
 * Controla a alternância entre tema claro e escuro
 */

class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        // Aplicar tema salvo
        this.applyTheme(this.currentTheme);
        
        // Criar toggle se não existir
        this.createToggleButton();
        
        // Adicionar event listeners
        this.addEventListeners();
        
        console.log('Theme Manager inicializado - Tema atual:', this.currentTheme);
    }


    createToggleButton() {
        // Verificar se já existe um toggle
        if (document.querySelector('.theme-toggle') || document.querySelector('.dashboard-theme-toggle')) return;

        // Determinar qual tipo de toggle criar baseado na página
        const isDashboard = document.body.classList.contains('dashboard-page') || 
                          document.querySelector('.app') || 
                          document.querySelector('.sidebar');

        const toggle = document.createElement('div');
        toggle.className = isDashboard ? 'dashboard-theme-toggle' : 'theme-toggle';
        
        this.updateToggleContent(toggle);
        
        if (isDashboard) {
            // Para páginas internas, verificar se é dashboard ou página de fichas
            const userArea = document.querySelector('.user');
            const actionsArea = document.querySelector('.actions');
            const topbar = document.querySelector('.topbar');
            
            if (userArea) {
                // Dashboard: inserir antes do avatar/email
                userArea.insertBefore(toggle, userArea.firstChild);
            } else if (actionsArea) {
                // Páginas de fichas: inserir na área de ações, ANTES dos botões existentes
                // Isso fará com que apareça à esquerda do botão "Voltar"
                actionsArea.insertBefore(toggle, actionsArea.firstChild);
            } else if (topbar) {
                // Se não há área de ações, mas há topbar, criar uma área de ações
                const newActionsArea = document.createElement('div');
                newActionsArea.className = 'actions';
                newActionsArea.style.cssText = 'display: flex; gap: 10px; align-items: center;';
                
                // Mover botões existentes para a nova área de ações
                const existingButtons = topbar.querySelectorAll('.btn, button, a[class*="btn"]');
                existingButtons.forEach(btn => {
                    newActionsArea.appendChild(btn);
                });
                
                // Adicionar o toggle primeiro (à esquerda)
                newActionsArea.insertBefore(toggle, newActionsArea.firstChild);
                
                // Adicionar a área de ações ao topbar
                topbar.appendChild(newActionsArea);
            } else {
                // Último fallback: adicionar ao body
                document.body.appendChild(toggle);
            }
        } else {
            // Para páginas de login, adicionar ao body
            document.body.appendChild(toggle);
        }
    }

    updateToggleContent(toggle) {
        const isDashboard = toggle.classList.contains('dashboard-theme-toggle');
        
        if (isDashboard) {
            toggle.innerHTML = `
                <span class="theme-icon"><i class="fas fa-${this.currentTheme === 'light' ? 'moon' : 'sun'}"></i></span>
                <span class="theme-text">${this.currentTheme === 'light' ? 'Escuro' : 'Claro'}</span>
            `;
        } else {
            toggle.innerHTML = `
                <span class="theme-toggle-icon"><i class="fas fa-${this.currentTheme === 'light' ? 'moon' : 'sun'}"></i></span>
            `;
        }
    }

    addEventListeners() {
        // Event listener para o toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('.theme-toggle') || e.target.closest('.dashboard-theme-toggle')) {
                this.toggleTheme();
            }
        });

        // Event listener para mudanças de sistema (opcional)
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Só aplicar automaticamente se o usuário não tiver preferência salva
                if (!localStorage.getItem('theme')) {
                    this.currentTheme = e.matches ? 'dark' : 'light';
                    this.applyTheme(this.currentTheme);
                    this.updateAllToggles();
                }
            });
        }
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.currentTheme);
        this.saveTheme();
        this.updateAllToggles();
        
        // Adicionar feedback visual
        this.showThemeChangeNotification();
        
        console.log('🎨 Tema alterado para:', this.currentTheme);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Adicionar classe ao body para compatibilidade
        document.body.classList.remove('light-theme', 'dark-theme');
        document.body.classList.add(`${theme}-theme`);
    }

    saveTheme() {
        localStorage.setItem('theme', this.currentTheme);
    }

    updateAllToggles() {
        const toggles = document.querySelectorAll('.theme-toggle, .dashboard-theme-toggle');
        toggles.forEach(toggle => {
            this.updateToggleContent(toggle);
        });
    }

    showThemeChangeNotification() {
        // Criar notificação temporária
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--primary-green);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            z-index: 1002;
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(111, 182, 79, 0.3);
        `;
        
        notification.textContent = `Modo ${this.currentTheme === 'dark' ? 'escuro' : 'claro'} ativado! 🎨`;
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remover após 3 segundos
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Método público para forçar um tema específico
    setTheme(theme) {
        if (theme === 'light' || theme === 'dark') {
            this.currentTheme = theme;
            this.applyTheme(theme);
            this.saveTheme();
            this.updateAllToggles();
        }
    }

    // Método público para obter o tema atual
    getTheme() {
        return this.currentTheme;
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Fallback para páginas que carregam o script após o DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.themeManager) {
            window.themeManager = new ThemeManager();
        }
    });
} else {
    if (!window.themeManager) {
        window.themeManager = new ThemeManager();
    }
}
