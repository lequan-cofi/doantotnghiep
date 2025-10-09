// Appointments Page JavaScript
var currentAppointmentId = null;
var currentRating = 0;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeSearch();
    initializeStarRating();
    setupModals();
});

// Filter functionality
function initializeFilters() {
    var filterTabs = document.querySelectorAll('.filter-tab');
    
    for (var i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            var status = this.getAttribute('data-status');
            
            // Update active tab
            for (var j = 0; j < filterTabs.length; j++) {
                filterTabs[j].classList.remove('active');
            }
            this.classList.add('active');
            
            // Filter appointments
            filterAppointments(status);
        });
    }
}

// Filter appointments by status
function filterAppointments(status) {
    var appointments = document.querySelectorAll('.appointment-card');
    var visibleCount = 0;
    
    for (var i = 0; i < appointments.length; i++) {
        var appointmentStatus = appointments[i].getAttribute('data-status');
        
        if (status === 'all' || appointmentStatus === status) {
            appointments[i].style.display = 'block';
            visibleCount++;
        } else {
            appointments[i].style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
}

// Search functionality
function initializeSearch() {
    var searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            searchAppointments(searchTerm);
        });
    }
}

// Search appointments
function searchAppointments(searchTerm) {
    var appointments = document.querySelectorAll('.appointment-card');
    var visibleCount = 0;
    
    for (var i = 0; i < appointments.length; i++) {
        var appointment = appointments[i];
        var title = appointment.querySelector('.property-title').textContent.toLowerCase();
        var location = appointment.querySelector('.property-location').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || location.includes(searchTerm)) {
            appointment.style.display = 'block';
            visibleCount++;
        } else {
            appointment.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
        emptyState.querySelector('h3').textContent = 'Không tìm thấy lịch hẹn nào';
        emptyState.querySelector('p').textContent = 'Không có lịch hẹn nào khớp với từ khóa "' + searchTerm + '".';
    } else {
        emptyState.style.display = 'none';
    }
}

// Cancel appointment
function cancelAppointment(appointmentId) {
    currentAppointmentId = appointmentId;
    
    if (typeof bootstrap !== 'undefined') {
        var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
        cancelModal.show();
    } else {
        if (confirm('Bạn có chắc chắn muốn hủy lịch hẹn này không?')) {
            confirmCancel();
        }
    }
}

// Confirm cancel
function confirmCancel() {
    var reason = document.getElementById('cancelReason') ? document.getElementById('cancelReason').value : '';
    
    // Update appointment status
    var appointment = document.querySelector('.appointment-card[data-status="pending"]');
    if (appointment) {
        appointment.setAttribute('data-status', 'cancelled');
        
        var statusElement = appointment.querySelector('.appointment-status');
        statusElement.className = 'appointment-status cancelled';
        statusElement.innerHTML = '<i class="fas fa-times-circle"></i><span>Đã hủy</span>';
        
        // Update actions
        var actionsElement = appointment.querySelector('.appointment-actions');
        actionsElement.innerHTML = '<span class="text-muted">Lịch hẹn đã bị hủy</span>';
    }
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelModal'));
        if (cancelModal) {
            cancelModal.hide();
        }
    }
    
    // Show success message
    showToast('Đã hủy lịch hẹn thành công', 'info');
    
    // Update stats
    updateStats();
}

// Edit appointment
function editAppointment(appointmentId) {
    currentAppointmentId = appointmentId;
    
    // Populate current data (mock data)
    document.getElementById('editDate').value = '2023-12-25';
    document.getElementById('editStartTime').value = '09:00';
    document.getElementById('editEndTime').value = '11:00';
    document.getElementById('editNote').value = '';
    
    if (typeof bootstrap !== 'undefined') {
        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }
}

// Save edit
function saveEdit() {
    var date = document.getElementById('editDate').value;
    var startTime = document.getElementById('editStartTime').value;
    var endTime = document.getElementById('editEndTime').value;
    var note = document.getElementById('editNote').value;
    
    // Validation
    if (!date || !startTime || !endTime) {
        showToast('Vui lòng điền đầy đủ thông tin', 'error');
        return;
    }
    
    if (startTime >= endTime) {
        showToast('Giờ kết thúc phải sau giờ bắt đầu', 'error');
        return;
    }
    
    // Update appointment display (mock update)
    showToast('Đã cập nhật lịch hẹn thành công', 'success');
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
        if (editModal) {
            editModal.hide();
        }
    }
}

// Reschedule appointment
function rescheduleAppointment(appointmentId) {
    editAppointment(appointmentId);
}

// Mark as completed
function markCompleted(appointmentId) {
    if (confirm('Xác nhận bạn đã xem phòng này?')) {
        // Update appointment status
        var appointment = document.querySelector('.appointment-card[data-status="confirmed"]');
        if (appointment) {
            appointment.setAttribute('data-status', 'completed');
            
            var statusElement = appointment.querySelector('.appointment-status');
            statusElement.className = 'appointment-status completed';
            statusElement.innerHTML = '<i class="fas fa-calendar-check"></i><span>Đã hoàn thành</span>';
            
            // Update actions
            var actionsElement = appointment.querySelector('.appointment-actions');
            actionsElement.innerHTML = 
                '<button class="btn btn-outline-primary btn-sm" onclick="rateProperty(' + appointmentId + ')">' +
                    '<i class="fas fa-star"></i> Đánh giá' +
                '</button>' +
                '<button class="btn btn-outline-success btn-sm" onclick="bookProperty(' + appointmentId + ')">' +
                    '<i class="fas fa-home"></i> Thuê phòng' +
                '</button>';
        }
        
        showToast('Đã đánh dấu hoàn thành lịch hẹn', 'success');
        updateStats();
    }
}

// Rate property
function rateProperty(appointmentId) {
    currentAppointmentId = appointmentId;
    currentRating = 0;
    
    // Reset stars
    var stars = document.querySelectorAll('.star-rating i');
    for (var i = 0; i < stars.length; i++) {
        stars[i].classList.remove('active');
    }
    
    document.getElementById('reviewText').value = '';
    
    if (typeof bootstrap !== 'undefined') {
        var ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
        ratingModal.show();
    }
}

// Initialize star rating
function initializeStarRating() {
    var stars = document.querySelectorAll('.star-rating i');
    
    for (var i = 0; i < stars.length; i++) {
        stars[i].addEventListener('click', function() {
            var rating = parseInt(this.getAttribute('data-rating'));
            currentRating = rating;
            
            // Update star display
            for (var j = 0; j < stars.length; j++) {
                if (j < rating) {
                    stars[j].classList.add('active');
                } else {
                    stars[j].classList.remove('active');
                }
            }
        });
        
        stars[i].addEventListener('mouseover', function() {
            var rating = parseInt(this.getAttribute('data-rating'));
            
            // Highlight stars on hover
            for (var j = 0; j < stars.length; j++) {
                if (j < rating) {
                    stars[j].style.color = '#fbbf24';
                } else {
                    stars[j].style.color = '';
                }
            }
        });
    }
    
    // Reset hover effect
    var starContainer = document.querySelector('.star-rating');
    if (starContainer) {
        starContainer.addEventListener('mouseleave', function() {
            for (var i = 0; i < stars.length; i++) {
                if (i < currentRating) {
                    stars[i].style.color = '#fbbf24';
                } else {
                    stars[i].style.color = '';
                }
            }
        });
    }
}

// Submit rating
function submitRating() {
    var reviewText = document.getElementById('reviewText').value;
    
    if (currentRating === 0) {
        showToast('Vui lòng chọn số sao đánh giá', 'error');
        return;
    }
    
    // Submit rating (mock)
    showToast('Đã gửi đánh giá thành công. Cảm ơn bạn!', 'success');
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var ratingModal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
        if (ratingModal) {
            ratingModal.hide();
        }
    }
}

// Book property
function bookProperty(appointmentId) {
    if (confirm('Bạn có muốn liên hệ thuê phòng này không?')) {
        showToast('Đã gửi yêu cầu thuê phòng. Chủ nhà sẽ liên hệ với bạn sớm.', 'success');
    }
}

// Setup modals
function setupModals() {
    // Clear form when modals are hidden
    var modals = document.querySelectorAll('.modal');
    
    for (var i = 0; i < modals.length; i++) {
        modals[i].addEventListener('hidden.bs.modal', function() {
            // Reset forms
            var forms = this.querySelectorAll('form');
            for (var j = 0; j < forms.length; j++) {
                forms[j].reset();
            }
            
            // Reset star rating
            if (this.id === 'ratingModal') {
                currentRating = 0;
                var stars = this.querySelectorAll('.star-rating i');
                for (var k = 0; k < stars.length; k++) {
                    stars[k].classList.remove('active');
                }
            }
        });
    }
}

// Update statistics
function updateStats() {
    // This would typically fetch from server
    // For now, just update based on visible appointments
    
    var pendingCount = document.querySelectorAll('.appointment-card[data-status="pending"]').length;
    var confirmedCount = document.querySelectorAll('.appointment-card[data-status="confirmed"]').length;
    var completedCount = document.querySelectorAll('.appointment-card[data-status="completed"]').length;
    var cancelledCount = document.querySelectorAll('.appointment-card[data-status="cancelled"]').length;
    
    // Update stat cards
    var statCards = document.querySelectorAll('.stat-card');
    if (statCards.length >= 4) {
        statCards[0].querySelector('h3').textContent = pendingCount;
        statCards[1].querySelector('h3').textContent = confirmedCount;
        statCards[2].querySelector('h3').textContent = completedCount;
        statCards[3].querySelector('h3').textContent = cancelledCount;
    }
}

// Toast notification function
function showToast(message, type) {
    // Remove existing toasts
    var existingToasts = document.querySelectorAll('.custom-toast');
    for (var i = 0; i < existingToasts.length; i++) {
        existingToasts[i].remove();
    }
    
    var toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast ' + type;
    
    var icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    else if (type === 'error') icon = 'times-circle';
    else if (type === 'warning') icon = 'exclamation-triangle';
    
    toast.innerHTML = '<i class="fas fa-' + icon + '"></i><span>' + message + '</span>';
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(function() {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(function() {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 4000);
}
