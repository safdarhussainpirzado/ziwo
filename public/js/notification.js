// notification.js - Premium Edition for NHMP CRM
(function () {
    'use strict';

    class NotificationSystem {
        constructor() {
            this.container = null;
            this.notificationCount = 0;
            this.maxNotifications = 3;
            this.timeouts = new Map();
            this.init();
        }

        init() {
            let container = document.querySelector('#notification-container');
            if (!container) {
                this.container = document.createElement('div');
                this.container.id = 'notification-container';
                this.container.className = 'fixed top-6 right-6 z-[10000] max-w-sm w-full p-0 space-y-3 pointer-events-none flex flex-col items-end';
                document.body.appendChild(this.container);
                this.addStyles();
            } else {
                this.container = container;
            }
        }

        addStyles() {
            if (document.getElementById('notification-styles')) return;

            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                #notification-container {
                    position: fixed !important;
                    top: 24px !important;
                    right: 24px !important;
                    z-index: 2147483647 !important;
                    display: flex !important;
                    flex-direction: column !important;
                    align-items: flex-end !important;
                    pointer-events: none !important;
                    width: 380px !important;
                }

                .notif-premium-success {
                    background: #34d399 !important; /* emerald-400 */
                    border: 1px solid rgba(255,255,255,0.4) !important;
                    box-shadow: 0 15px 35px rgba(52, 211, 153, 0.5) !important;
                }

                .notif-premium-error {
                    background: #fb7185 !important; /* rose-400 */
                    border: 1px solid rgba(255,255,255,0.4) !important;
                    box-shadow: 0 15px 35px rgba(251, 113, 133, 0.5) !important;
                }

                .notif-premium-warning {
                    background: #fbbf24 !important; /* amber-400 */
                    border: 1px solid rgba(255,255,255,0.4) !important;
                    box-shadow: 0 15px 35px rgba(251, 191, 36, 0.5) !important;
                }

                .notif-premium-info {
                    background: #60a5fa !important; /* blue-400 */
                    border: 1px solid rgba(255,255,255,0.4) !important;
                    box-shadow: 0 15px 35px rgba(96, 165, 250, 0.5) !important;
                }
                
                .notification-item {
                    animation: pnotifIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
                    pointer-events: auto;
                    width: 100% !important;
                    margin-bottom: 16px;
                }
                
                .p-error-list {
                    margin: 6px 0 0 0;
                    padding-left: 18px;
                    list-style-type: disc;
                }

                .p-error-list li {
                    font-size: 13px;
                    line-height: 1.5;
                    margin-bottom: 4px;
                    color: white !important;
                    font-weight: 800;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
                }

                .p-close-btn {
                    width: 26px;
                    height: 26px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                    background: rgba(255,255,255,0.3);
                    border: 1px solid rgba(255,255,255,0.2);
                    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                }

                .p-close-btn:hover {
                    background: rgba(255,255,255,0.5);
                    transform: scale(1.1);
                }
            `;
            document.head.appendChild(style);
        }


        show(message, type = 'info', title = null, duration = null) {
            if (!this.container) this.init();

            const messages = Array.isArray(message) ? message : [message];

            const theme = this.getTheme(type);

            if (!title) {
                switch (type) {
                    case 'success': title = 'Process Verified'; break;
                    case 'error': title = 'System Violation'; break;
                    case 'warning': title = 'Attention Required'; break;
                    default: title = 'Kernel Notification';
                }
            }

            if (!duration) {
                duration = type === 'error' ? 10000 : 5000;
            }

            const id = 'pnotif-' + Date.now();
            const notification = document.createElement('div');
            notification.id = id;
            notification.className = 'notification-item';

            let contentHtml = '';
            if (messages.length > 1) {
                contentHtml = `<ul class="p-error-list text-white">${messages.map(m => `<li>${this.escapeHtml(m)}</li>`).join('')}</ul>`;
            } else {
                contentHtml = `<p class="text-[13px] font-extrabold text-white tracking-tight leading-relaxed text-shadow-sm">${this.escapeHtml(messages[0])}</p>`;
            }

            notification.innerHTML = `
                <div class="${theme.container} rounded-3xl p-5 relative flex items-start gap-4 overflow-hidden shadow-2xl">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-10 h-10 rounded-2xl ${theme.iconBg} flex items-center justify-center text-[16px] shadow-md border border-white/40">
                            ${theme.icon}
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0 pr-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.3em] mb-2 ${theme.title} text-shadow-sm">${title}</h4>
                        <div class="text-white">
                            ${contentHtml}
                        </div>
                    </div>
                    
                    <button type="button" 
                             class="p-close-btn absolute top-5 right-5 text-white opacity-90 hover:opacity-100 transition-all cursor-pointer">
                        <i class="fa-solid fa-xmark text-[12px]"></i>
                    </button>
                    
                    <div class="absolute bottom-0 left-0 right-0 h-[4px] bg-white/20">
                         <div class="h-full ${theme.progress} origin-left" style="animation: progressNotif ${duration}ms linear forwards"></div>
                    </div>
                </div>
            `;

            // Manual attach click for non-alpine environments or direct removal
            notification.querySelector('.p-close-btn').onclick = () => this.remove(id);

            if (this.notificationCount >= this.maxNotifications) {
                const oldest = this.container.children[0];
                if (oldest) this.remove(oldest.id);
            }

            this.container.appendChild(notification);
            this.notificationCount++;

            const timeoutId = setTimeout(() => this.remove(id), duration);
            this.timeouts.set(id, timeoutId);
            return id;
        }

        getTheme(type) {
            switch (type) {
                case 'success':
                    return {
                        container: 'notif-premium-success',
                        icon: '<i class="fa-solid fa-check-double"></i>',
                        iconBg: 'bg-white text-emerald-500',
                        title: 'text-white',
                        accent: 'bg-white',
                        progress: 'bg-white',
                    };
                case 'error':
                    return {
                        container: 'notif-premium-error',
                        icon: '<i class="fa-solid fa-shield-virus"></i>',
                        iconBg: 'bg-white text-rose-500',
                        title: 'text-white',
                        accent: 'bg-white',
                        progress: 'bg-white',
                    };
                case 'warning':
                    return {
                        container: 'notif-premium-warning',
                        icon: '<i class="fa-solid fa-triangle-exclamation"></i>',
                        iconBg: 'bg-white text-amber-500',
                        title: 'text-white',
                        accent: 'bg-white',
                        progress: 'bg-white',
                    };
                default:
                    return {
                        container: 'notif-premium-info',
                        icon: '<i class="fa-solid fa-bolt"></i>',
                        iconBg: 'bg-white text-blue-500',
                        title: 'text-white',
                        accent: 'bg-white',
                        progress: 'bg-white',
                    };
            }
        }

        remove(id) {
            const notification = document.getElementById(id);
            if (!notification) return;
            if (this.timeouts.has(id)) { clearTimeout(this.timeouts.get(id)); this.timeouts.delete(id); }
            notification.classList.add('notification-slide-out');
            setTimeout(() => { if (notification.parentNode) { notification.parentNode.removeChild(notification); this.notificationCount--; } }, 400);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }

    window.showNotification = (msg, type, title, duration) => window.notificationSystem.show(msg, type, title, duration);
    window.showSuccess = (msg, title, duration) => window.showNotification(msg, 'success', title, duration);
    window.showError = (msg, title, duration) => window.showNotification(msg, 'error', title, duration);
    window.showWarning = (msg, title, duration) => window.showNotification(msg, 'warning', title, duration);
    window.showInfo = (msg, title, duration) => window.showNotification(msg, 'info', title, duration);

    // Global Notification Object (Legacy & Component Support)
    window.Notification = {
        success: window.showSuccess,
        error: window.showError,
        warning: window.showWarning,
        info: window.showInfo
    };
})();
