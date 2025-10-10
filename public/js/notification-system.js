/**
 * Notification System
 * A simple notification system for displaying toast messages and confirmations
 */
class NotificationSystem {
    constructor() {
        this.container = null;
        this.modal = null;
        this.init();
    }

    init() {
        this.createContainer();
        this.createModal();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
    }

    createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'confirmation-modal';
        this.modal.innerHTML = `
            <div class="confirmation-content">
                <h4 class="confirmation-title">Xác nhận</h4>
                <p class="confirmation-message"></p>
                <div class="confirmation-buttons">
                    <button class="confirmation-btn cancel">Hủy</button>
                    <button class="confirmation-btn confirm">Xác nhận</button>
                </div>
            </div>
        `;
        document.body.appendChild(this.modal);

        // Add event listeners
        this.modal.querySelector('.cancel').addEventListener('click', () => {
            this.hideModal();
        });

        this.modal.querySelector('.confirm').addEventListener('click', () => {
            if (this.confirmCallback) {
                this.confirmCallback();
            }
            this.hideModal();
        });

        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hideModal();
            }
        });
    }

    toast(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const icon = this.getIcon(type);
        
        notification.innerHTML = `
            <div class="notification-header">
                <h6 class="notification-title">${this.getTitle(type)}</h6>
                <button class="notification-close">&times;</button>
            </div>
            <p class="notification-message">${message}</p>
            <div class="notification-progress"></div>
        `;

        // Add close functionality
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.removeNotification(notification);
        });

        this.container.appendChild(notification);

        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.removeNotification(notification);
            }, duration);
        }

        // Progress bar animation
        const progress = notification.querySelector('.notification-progress');
        if (progress && duration > 0) {
            progress.style.width = '100%';
            progress.style.transition = `width ${duration}ms linear`;
            setTimeout(() => {
                progress.style.width = '0%';
            }, 10);
        }

        return notification;
    }

    confirm(message, callback, options = {}) {
        this.confirmCallback = callback;
        
        const modal = this.modal;
        const title = modal.querySelector('.confirmation-title');
        const messageEl = modal.querySelector('.confirmation-message');
        const confirmBtn = modal.querySelector('.confirm');
        const cancelBtn = modal.querySelector('.cancel');

        title.textContent = options.title || 'Xác nhận';
        messageEl.textContent = message;

        // Update button styles based on type
        if (options.type === 'delete') {
            confirmBtn.textContent = options.confirmText || 'Xóa';
            confirmBtn.className = 'confirmation-btn confirm';
        } else if (options.type === 'success') {
            confirmBtn.textContent = options.confirmText || 'Xác nhận';
            confirmBtn.className = 'confirmation-btn success';
        } else {
            confirmBtn.textContent = options.confirmText || 'Xác nhận';
            confirmBtn.className = 'confirmation-btn confirm';
        }

        cancelBtn.textContent = options.cancelText || 'Hủy';

        this.showModal();
    }

    confirmDelete(message, callback) {
        this.confirm(message, callback, {
            type: 'delete',
            title: 'Xác nhận xóa',
            confirmText: 'Xóa',
            cancelText: 'Hủy'
        });
    }

    showModal() {
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    hideModal() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
        this.confirmCallback = null;
    }

    removeNotification(notification) {
        notification.classList.add('slide-out');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    getTitle(type) {
        const titles = {
            success: 'Thành công',
            error: 'Lỗi',
            warning: 'Cảnh báo',
            info: 'Thông báo'
        };
        return titles[type] || titles.info;
    }

    // Static methods for easy access
    static toast(message, type, duration) {
        if (!window.notificationSystem) {
            window.notificationSystem = new NotificationSystem();
        }
        return window.notificationSystem.toast(message, type, duration);
    }

    static confirm(message, callback, options) {
        if (!window.notificationSystem) {
            window.notificationSystem = new NotificationSystem();
        }
        return window.notificationSystem.confirm(message, callback, options);
    }

    static confirmDelete(message, callback) {
        if (!window.notificationSystem) {
            window.notificationSystem = new NotificationSystem();
        }
        return window.notificationSystem.confirmDelete(message, callback);
    }
}

// Create global Notify object for backward compatibility
window.Notify = {
    toast: NotificationSystem.toast,
    confirm: NotificationSystem.confirm,
    confirmDelete: NotificationSystem.confirmDelete
};

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSystem;
}
