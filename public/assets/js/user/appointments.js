// Appointments Page JavaScript
// Global variables
let currentAppointmentId = null;
let appointmentCards = [];
let filterTabs = [];
let searchInput = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Appointments page loaded');
    
    // Wait for Bootstrap to be available
    if (typeof bootstrap === 'undefined') {
        console.warn('Bootstrap not loaded, retrying...');
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined') {
                initializeAppointments();
            } else {
                console.error('Bootstrap failed to load');
            }
        }, 1000);
        return;
    }
    
    initializeAppointments();
});

// Initialize appointments functionality
function initializeAppointments() {
    console.log('Initializing appointments functionality');
    
    // Initialize elements
    filterTabs = document.querySelectorAll('.filter-tab');
    searchInput = document.getElementById('searchInput');
    appointmentCards = document.querySelectorAll('.appointment-card');
    
    console.log('Elements found:', {
        filterTabs: filterTabs.length,
        searchInput: searchInput ? 'Yes' : 'No',
        appointmentCards: appointmentCards.length
    });
    
    // Initialize filter functionality
    initializeFilters();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize appointment actions
    initializeAppointmentActions();
    
    // Show welcome notification
    if (appointmentCards.length > 0) {
        window.Notify?.success(
            `Bạn có ${appointmentCards.length} lịch hẹn`,
            'Chào mừng trở lại!'
        );
    }
}

// Initialize filter functionality
function initializeFilters() {
    if (filterTabs.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const status = this.dataset.status;
                console.log('Filter tab clicked:', status);
                
                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Filter cards
                filterCards(status);
            });
        });
    }
}

// Initialize search functionality
function initializeSearch() {
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const activeTab = document.querySelector('.filter-tab.active');
            const currentStatus = activeTab ? activeTab.dataset.status : 'all';
            console.log('Search triggered:', { searchTerm, currentStatus });
            filterCards(currentStatus, searchTerm);
        });
    }
}

// Initialize appointment actions
function initializeAppointmentActions() {
    // Add event listeners for cancel buttons
    document.addEventListener('click', function(e) {
        const cancelButton = e.target.closest('.btn-outline-danger');
        if (cancelButton && cancelButton.textContent.includes('Hủy lịch')) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = cancelButton.getAttribute('data-id') || 
                      cancelButton.getAttribute('onclick')?.match(/\d+/)?.[0];
            
            if (id) {
                console.log('Cancel button clicked, ID:', id);
                cancelAppointment(id);
            }
        }
    });
}

// Filter cards function
function filterCards(status, searchTerm = '') {
    let visibleCount = 0;
    
    if (appointmentCards.length === 0) {
        console.log('No appointment cards found');
        return;
    }
    
    appointmentCards.forEach((card, index) => {
        const cardStatus = card.dataset.status;
        const cardText = card.textContent.toLowerCase();
        
        // Handle status matching
        let statusMatch = false;
        if (status === 'all') {
            statusMatch = true;
        } else if (status === 'done' && cardStatus === 'done') {
            statusMatch = true;
        } else if (status === 'cancelled' && cardStatus === 'cancelled') {
            statusMatch = true;
        } else if (status === 'requested' && cardStatus === 'requested') {
            statusMatch = true;
        } else if (status === 'confirmed' && cardStatus === 'confirmed') {
            statusMatch = true;
        }
        
        const searchMatch = searchTerm === '' || cardText.includes(searchTerm);
        
        if (statusMatch && searchMatch) {
            card.style.display = 'block';
            visibleCount++;
    } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
    
    console.log(`Filter: status=${status}, search="${searchTerm}", visible=${visibleCount}`);
}

// Cancel appointment function
function cancelAppointment(id) {
    console.log('cancelAppointment called with id:', id);
    
    if (!id) {
        window.Notify?.error('Không tìm thấy ID lịch hẹn');
        return;
    }
    
    currentAppointmentId = id;
    
    // Use notification system for confirmation
    window.Notify?.confirm({
        title: 'Xác nhận hủy lịch hẹn',
        message: 'Bạn có chắc chắn muốn hủy lịch hẹn này không?',
        details: 'Lịch hẹn sẽ được hủy và không thể khôi phục.',
        type: 'warning',
        confirmText: 'Hủy lịch',
        cancelText: 'Không',
        onConfirm: () => {
            showCancelReasonModal();
        }
    });
}

// Show cancel reason modal
function showCancelReasonModal() {
    // Remove existing modal if any
    const existingModal = document.getElementById('cancelReasonModal');
    if (existingModal) {
        const modalInstance = bootstrap.Modal.getInstance(existingModal);
        if (modalInstance) {
            modalInstance.dispose();
        }
        existingModal.remove();
    }
    
    // Create dynamic modal for cancel reason
    const modalHtml = `
        <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelReasonModalLabel">
                            <i class="fas fa-times-circle text-danger"></i>
                            Hủy lịch hẹn
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Vui lòng cho biết lý do hủy lịch hẹn (tùy chọn):</p>
                        <div class="mb-3">
                            <textarea class="form-control" id="cancelReason" rows="4" 
                                placeholder="Nhập lý do hủy lịch..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Không
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmCancel()">
                            <i class="fas fa-check"></i> Xác nhận hủy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Wait for DOM to be ready
    setTimeout(() => {
        const modalElement = document.getElementById('cancelReasonModal');
        if (modalElement) {
            // Show modal
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            modal.show();
            
            // Focus on textarea
            setTimeout(() => {
                document.getElementById('cancelReason')?.focus();
            }, 300);
        }
    }, 100);
}

// Confirm cancel function
function confirmCancel() {
    console.log('confirmCancel called, currentAppointmentId:', currentAppointmentId);
    
    if (!currentAppointmentId) {
        window.Notify?.error('Không tìm thấy ID lịch hẹn');
        return;
    }
    
    const reason = document.getElementById('cancelReason')?.value || '';
    console.log('Cancel reason:', reason);
    
    // Show loading state
    const confirmBtn = document.querySelector('#cancelReasonModal .btn-danger');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    confirmBtn.disabled = true;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        window.Notify?.error('Không tìm thấy CSRF token');
        resetButton(confirmBtn, originalText);
        return;
    }
    
    const token = csrfToken.getAttribute('content');
    if (!token) {
        window.Notify?.error('CSRF token trống');
        resetButton(confirmBtn, originalText);
        return;
    }
    
    console.log('Making API call to:', `/tenant/appointments/${currentAppointmentId}/cancel`);
    
    // Make API call
    fetch(`/tenant/appointments/${currentAppointmentId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('CSRF token mismatch. Vui lòng refresh trang và thử lại.');
            } else if (response.status === 401) {
                throw new Error('Bạn chưa đăng nhập. Vui lòng đăng nhập lại.');
            } else if (response.status === 403) {
                throw new Error('Bạn không có quyền thực hiện hành động này.');
            } else if (response.status === 404) {
                throw new Error('Không tìm thấy lịch hẹn này.');
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Close modal
            cleanupModal('cancelReasonModal');
            
            // Show success notification
            window.Notify?.success('Đã hủy lịch hẹn thành công!');
            
            // Reload page after delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            window.Notify?.error(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.Notify?.error(error.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
    })
    .finally(() => {
        resetButton(confirmBtn, originalText);
        currentAppointmentId = null;
    });
}

// Helper function to reset button
function resetButton(button, originalText) {
    if (button) {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Helper function to cleanup modal
function cleanupModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.dispose();
        }
        modal.remove();
    }
}

// Edit appointment function
function editAppointment(id) {
    console.log('editAppointment called with id:', id);
    window.location.href = `/tenant/appointments/${id}/edit`;
}

// Rate property function
function rateProperty(id) {
    console.log('rateProperty called with id:', id);
    currentAppointmentId = id;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('ratingModal');
    if (existingModal) {
        const modalInstance = bootstrap.Modal.getInstance(existingModal);
        if (modalInstance) {
            modalInstance.dispose();
        }
        existingModal.remove();
    }
    
    // Create rating modal
    const modalHtml = `
        <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ratingModalLabel">
                            <i class="fas fa-star text-warning"></i>
                            Đánh giá phòng trọ
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="rating-section mb-4">
                            <label class="form-label fw-bold">Đánh giá tổng thể</label>
                            <div class="star-rating">
                                <i class="fas fa-star" data-rating="1"></i>
                                <i class="fas fa-star" data-rating="2"></i>
                                <i class="fas fa-star" data-rating="3"></i>
                                <i class="fas fa-star" data-rating="4"></i>
                                <i class="fas fa-star" data-rating="5"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reviewText" class="form-label fw-bold">Nhận xét</label>
                            <textarea class="form-control" id="reviewText" rows="4" 
                                placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                        <button type="button" class="btn btn-primary" onclick="submitRating()">
                            <i class="fas fa-paper-plane"></i> Gửi đánh giá
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Wait for DOM to be ready
    setTimeout(() => {
        const modalElement = document.getElementById('ratingModal');
        if (modalElement) {
            // Show modal
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            modal.show();
            
            // Initialize star rating
            initializeStarRating();
        }
    }, 100);
}

// Initialize star rating
function initializeStarRating() {
    const stars = document.querySelectorAll('#ratingModal .star-rating .fas.fa-star');
    const starRatingContainer = document.querySelector('#ratingModal .star-rating');
    
    if (stars.length > 0) {
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                } else {
                        s.classList.remove('active');
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.style.color = '#ffc107';
                } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
        
        if (starRatingContainer) {
            starRatingContainer.addEventListener('mouseleave', function() {
                const stars = this.querySelectorAll('.fas.fa-star');
                stars.forEach(star => {
                    if (star.classList.contains('active')) {
                        star.style.color = '#ffc107';
                } else {
                        star.style.color = '#ddd';
                    }
                });
            });
        }
    }
}

// Submit rating function
function submitRating() {
    if (!currentAppointmentId) return;
    
    const rating = document.querySelector('#ratingModal .star-rating .fas.fa-star.active')?.dataset.rating || 0;
    const review = document.getElementById('reviewText')?.value || '';
    
    if (rating == 0) {
        window.Notify?.warning('Vui lòng chọn đánh giá');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#ratingModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual implementation)
    setTimeout(() => {
        // Close modal
        cleanupModal('ratingModal');
        
        // Show success notification
        window.Notify?.success('Cảm ơn bạn đã đánh giá!');
        
        // Reset
        currentAppointmentId = null;
        resetButton(submitBtn, originalText);
    }, 1500);
}

// Reschedule appointment function
function rescheduleAppointment(id) {
    console.log('rescheduleAppointment called with id:', id);
    window.Notify?.info('Chức năng đổi lịch đang được phát triển');
}

// Initialize filter on page load
if (appointmentCards.length > 0) {
    console.log('Initializing filter with all cards');
    filterCards('all');
} else {
    console.log('No appointment cards found, skipping filter initialization');
}