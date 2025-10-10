/**
 * Simple Notification System for QLPhongTro
 * Unified popup confirmations and toast notifications
 */

// Simple notification functions
window.Notify = {
    // Toast notifications
    toast: function(options) {
        if (typeof options === 'string') {
            options = { message: options, type: 'info' };
        }
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas fa-info-circle text-${options.type || 'info'} me-2"></i>
                    <strong class="me-auto">${options.title || 'Thông báo'}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${options.message || ''}
                </div>
            </div>
        `;
        
        // Create container if not exists
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: options.duration !== 0,
            delay: options.duration || 5000
        });
        
        toast.show();
        
        return toastId;
    },
    
    // Success toast
    success: function(message, title = 'Thành công') {
        return this.toast({ message, title, type: 'success' });
    },
    
    // Error toast
    error: function(message, title = 'Lỗi') {
        return this.toast({ message, title, type: 'danger' });
    },
    
    // Warning toast
    warning: function(message, title = 'Cảnh báo') {
        return this.toast({ message, title, type: 'warning' });
    },
    
    // Info toast
    info: function(message, title = 'Thông tin') {
        return this.toast({ message, title, type: 'info' });
    },
    
    // Confirmation modal
    confirm: function(options) {
        if (typeof options === 'string') {
            options = { message: options };
        }
        
        // If onConfirm callback is provided, use it
        if (options.onConfirm) {
            const modalId = 'confirm-modal-' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}Label">${options.title || 'Xác nhận'}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>${options.message || 'Bạn có chắc chắn muốn thực hiện hành động này?'}</p>
                                ${options.details ? `<p class="text-muted">${options.details}</p>` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-${options.type === 'danger' ? 'danger' : 'primary'}" id="${modalId}-confirm">
                                    ${options.confirmText || 'Xác nhận'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            
            // Handle confirm button
            document.getElementById(`${modalId}-confirm`).addEventListener('click', () => {
                modal.hide();
                options.onConfirm();
            });
            
            // Handle cancel
            modalElement.addEventListener('hidden.bs.modal', () => {
                modalElement.remove();
            });
            
            modal.show();
            return;
        }
        
        return new Promise((resolve) => {
            const modalId = 'confirm-modal-' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}Label">${options.title || 'Xác nhận'}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>${options.message || 'Bạn có chắc chắn muốn thực hiện hành động này?'}</p>
                                ${options.details ? `<p class="text-muted">${options.details}</p>` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-${options.type === 'danger' ? 'danger' : 'primary'}" id="${modalId}-confirm">
                                    ${options.confirmText || 'Xác nhận'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            
            // Handle confirm button
            document.getElementById(`${modalId}-confirm`).addEventListener('click', () => {
                modal.hide();
                resolve(true);
            });
            
            // Handle cancel
            modalElement.addEventListener('hidden.bs.modal', () => {
                modalElement.remove();
                resolve(false);
            });
            
            modal.show();
        });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification system initialized');
});
