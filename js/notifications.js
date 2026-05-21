/**
 * Sistema de Notificações Elegantes - Criança Feliz
 * Notificações toast para ações do usuário (salvar, editar, excluir)
 */

class NotificationSystem {
    constructor() {
        this.container = null;
        this.notifications = [];
        this.init();
    }

    init() {
        // Criar container de notificações
        this.createContainer();
        console.log('Sistema de Notificações inicializado');
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notifications-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 4000) {
        const notification = this.createNotification(message, type, duration);
        this.container.appendChild(notification);
        this.notifications.push(notification);

        // Animar entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 100);

        // Auto remover
        setTimeout(() => {
            this.remove(notification);
        }, duration);

        return notification;
    }

    createNotification(message, type, duration) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Definir cores e ícones por tipo
        const config = this.getTypeConfig(type);
        
        notification.innerHTML = `
            <div class="notification-icon">${config.icon}</div>
            <div class="notification-content">
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close" onclick="window.notificationSystem.remove(this.parentElement)">×</button>
        `;

        notification.style.cssText = `
            background: ${config.background};
            color: ${config.color};
            border: 1px solid ${config.border};
            border-radius: 12px;
            padding: 16px;
            min-width: 320px;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: auto;
            backdrop-filter: blur(10px);
            border-left: 4px solid ${config.accent};
        `;

        return notification;
    }

    getTypeConfig(type) {
        const configs = {
            success: {
                icon: '✅',
                background: 'rgba(34, 197, 94, 0.95)',
                color: '#ffffff',
                border: 'rgba(34, 197, 94, 0.3)',
                accent: '#22c55e'
            },
            error: {
                icon: '❌',
                background: 'rgba(239, 68, 68, 0.95)',
                color: '#ffffff',
                border: 'rgba(239, 68, 68, 0.3)',
                accent: '#ef4444'
            },
            warning: {
                icon: '⚠️',
                background: 'rgba(245, 158, 11, 0.95)',
                color: '#ffffff',
                border: 'rgba(245, 158, 11, 0.3)',
                accent: '#f59e0b'
            },
            info: {
                icon: 'ℹ️',
                background: 'rgba(59, 130, 246, 0.95)',
                color: '#ffffff',
                border: 'rgba(59, 130, 246, 0.3)',
                accent: '#3b82f6'
            },
            save: {
                icon: '💾',
                background: 'rgba(111, 182, 79, 0.95)',
                color: '#ffffff',
                border: 'rgba(111, 182, 79, 0.3)',
                accent: '#6fb64f'
            },
            edit: {
                icon: '✏️',
                background: 'rgba(240, 163, 107, 0.95)',
                color: '#ffffff',
                border: 'rgba(240, 163, 107, 0.3)',
                accent: '#f0a36b'
            },
            delete: {
                icon: '🗑️',
                background: 'rgba(231, 76, 60, 0.95)',
                color: '#ffffff',
                border: 'rgba(231, 76, 60, 0.3)',
                accent: '#e74c3c'
            }
        };

        return configs[type] || configs.info;
    }

    remove(notification) {
        if (!notification || !notification.parentElement) return;

        // Animar saída
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';

        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
                this.notifications = this.notifications.filter(n => n !== notification);
            }
        }, 300);
    }

    // Métodos de conveniência
    success(message, duration = 4000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 5000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 4000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 4000) {
        return this.show(message, 'info', duration);
    }

    save(message = 'Dados salvos com sucesso!', duration = 3000) {
        return this.show(message, 'save', duration);
    }

    edit(message = 'Dados editados com sucesso!', duration = 3000) {
        return this.show(message, 'edit', duration);
    }

    delete(message = 'Item excluído com sucesso!', duration = 3000) {
        return this.show(message, 'delete', duration);
    }

    // Limpar todas as notificações
    clear() {
        this.notifications.forEach(notification => {
            this.remove(notification);
        });
    }
}

// Inicializar sistema quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }
});

// Fallback para páginas que carregam o script após DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.notificationSystem) {
            window.notificationSystem = new NotificationSystem();
        }
    });
} else {
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }
}

// Adicionar estilos CSS
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .notification-content {
        flex: 1;
    }

    .notification-message {
        font-weight: 500;
        line-height: 1.4;
    }

    .notification-close {
        background: none;
        border: none;
        color: inherit;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        opacity: 0.7;
        transition: opacity 0.2s ease;
        flex-shrink: 0;
    }

    .notification-close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.2);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        #notifications-container {
            top: 10px;
            right: 10px;
            left: 10px;
        }

        .notification {
            min-width: auto !important;
            max-width: none !important;
        }
    }

    /* Modo escuro */
    [data-theme="dark"] .notification {
        backdrop-filter: blur(15px) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    }
`;
document.head.appendChild(notificationStyles);
