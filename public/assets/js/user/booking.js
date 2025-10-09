// Booking Page JavaScript
let slotCounter = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Make functions globally accessible after they're defined
    window.addTimeSlot = addTimeSlot;
    window.removeTimeSlot = removeTimeSlot;
    
    // Initialize form
    initializeForm();
    
    // Form validation
    setupFormValidation();
    
    // Form submission
    setupFormSubmission();
});

// Initialize form functionality
function initializeForm() {
    // Set minimum date to today
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => {
        input.min = today;
    });
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', formatPhoneNumber);
    }
    
    // Time validation
    setupTimeValidation();
    
    // Purpose option styling
    setupPurposeOptions();
}

// Add new time slot
function addTimeSlot() {
    slotCounter++;
    const timeSlotsContainer = document.getElementById('timeSlots');
    
    if (!timeSlotsContainer) {
        console.error('timeSlots container not found');
        return;
    }
    
    const newSlot = document.createElement('div');
    newSlot.className = 'time-slot';
    newSlot.setAttribute('data-slot', slotCounter);
    
    const today = new Date().toISOString().split('T')[0];
    
    newSlot.innerHTML = `
        <div class="time-slot-header">
            <h5>Khung thời gian ${slotCounter}</h5>
            <button type="button" class="btn-remove-slot" onclick="removeTimeSlot(${slotCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Ngày <span class="required">*</span></label>
                <input type="date" class="form-control" name="slots[${slotCounter}][date]" required min="${today}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Từ giờ <span class="required">*</span></label>
                <input type="time" class="form-control" name="slots[${slotCounter}][start_time]" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Đến giờ <span class="required">*</span></label>
                <input type="time" class="form-control" name="slots[${slotCounter}][end_time]" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Ghi chú cho khung thời gian này</label>
            <input type="text" class="form-control" name="slots[${slotCounter}][note]" placeholder="Ví dụ: Sáng chủ nhật, sau 18h...">
        </div>
    `;
    
    timeSlotsContainer.appendChild(newSlot);
    
    // Add animation
    newSlot.style.opacity = '0';
    newSlot.style.transform = 'translateY(20px)';
    setTimeout(() => {
        newSlot.style.transition = 'all 0.3s ease';
        newSlot.style.opacity = '1';
        newSlot.style.transform = 'translateY(0)';
    }, 10);
    
    // Setup time validation for new slot
    setupTimeValidationForSlot(slotCounter);
    
    // Update remove button visibility
    updateRemoveButtonVisibility();
    
    // Show success message
    if (typeof showToast === 'function') {
        showToast('Đã thêm khung thời gian mới', 'success');
    }
}

// Remove time slot
function removeTimeSlot(slotId) {
    const slot = document.querySelector(`[data-slot="${slotId}"]`);
    if (slot) {
        // Add animation
        slot.style.transition = 'all 0.3s ease';
        slot.style.opacity = '0';
        slot.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            slot.remove();
            updateRemoveButtonVisibility();
            if (typeof showToast === 'function') {
                showToast('Đã xóa khung thời gian', 'info');
            }
        }, 300);
    }
}

// Update remove button visibility
function updateRemoveButtonVisibility() {
    const slots = document.querySelectorAll('.time-slot');
    slots.forEach((slot, index) => {
        const removeBtn = slot.querySelector('.btn-remove-slot');
        if (removeBtn) {
            removeBtn.style.display = slots.length > 1 ? 'block' : 'none';
        }
    });
}

// Setup time validation
function setupTimeValidation() {
    document.addEventListener('change', function(e) {
        if (e.target.type === 'time') {
            validateTimeSlot(e.target);
        }
    });
}

// Setup time validation for specific slot
function setupTimeValidationForSlot(slotId) {
    const slot = document.querySelector(`[data-slot="${slotId}"]`);
    if (slot) {
        const timeInputs = slot.querySelectorAll('input[type="time"]');
        timeInputs.forEach(input => {
            input.addEventListener('change', function() {
                validateTimeSlot(this);
            });
        });
    }
}

// Validate time slot
function validateTimeSlot(input) {
    const slot = input.closest('.time-slot');
    const startTime = slot.querySelector('input[name*="[start_time]"]');
    const endTime = slot.querySelector('input[name*="[end_time]"]');
    
    if (startTime.value && endTime.value) {
        const start = new Date(`2000-01-01 ${startTime.value}`);
        const end = new Date(`2000-01-01 ${endTime.value}`);
        
        if (end <= start) {
            endTime.setCustomValidity('Thời gian kết thúc phải sau thời gian bắt đầu');
            endTime.classList.add('is-invalid');
            showValidationError(endTime, 'Thời gian kết thúc phải sau thời gian bắt đầu');
        } else {
            endTime.setCustomValidity('');
            endTime.classList.remove('is-invalid');
            hideValidationError(endTime);
        }
    }
}

// Phone number formatting
function formatPhoneNumber(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    // Limit to 10 digits
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    
    // Format as XXX XXX XXXX
    if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{1,3})/, '$1 $2');
    }
    
    e.target.value = value;
}

// Setup purpose options
function setupPurposeOptions() {
    const purposeOptions = document.querySelectorAll('.form-check');
    purposeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                
                // Update styling
                purposeOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            }
        });
    });
}

// Form validation setup
function setupFormValidation() {
    const form = document.getElementById('bookingForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Trường này là bắt buộc';
    }
    
    // Specific field validation
    switch (field.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Email không hợp lệ';
            }
            break;
            
        case 'tel':
            if (value && !isValidPhone(value)) {
                isValid = false;
                errorMessage = 'Số điện thoại không hợp lệ';
            }
            break;
            
        case 'date':
            if (value && new Date(value) < new Date().setHours(0,0,0,0)) {
                isValid = false;
                errorMessage = 'Ngày không được trong quá khứ';
            }
            break;
    }
    
    // Name validation
    if (field.id === 'fullName' && value) {
        if (value.length < 2) {
            isValid = false;
            errorMessage = 'Họ tên phải có ít nhất 2 ký tự';
        } else if (!/^[a-zA-ZÀ-ỹ\s]+$/.test(value)) {
            isValid = false;
            errorMessage = 'Họ tên chỉ được chứa chữ cái và khoảng trắng';
        }
    }
    
    // Update field styling
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        hideValidationError(field);
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        showValidationError(field, errorMessage);
    }
    
    return isValid;
}

// Show validation error
function showValidationError(field, message) {
    let errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

// Hide validation error
function hideValidationError(field) {
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.textContent = '';
    }
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone validation
function isValidPhone(phone) {
    const phoneRegex = /^\d{3}\s\d{3}\s\d{4}$|^\d{10}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// Form submission
function setupFormSubmission() {
    const form = document.getElementById('bookingForm');
    const submitBtn = form.querySelector('.btn-submit');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        const inputs = form.querySelectorAll('input, select, textarea');
        let isFormValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        
        // Validate time slots
        if (!validateAllTimeSlots()) {
            isFormValid = false;
        }
        
        // Check terms agreement
        const agreeTerms = document.getElementById('agreeTerms');
        if (!agreeTerms.checked) {
            isFormValid = false;
            showToast('Vui lòng đồng ý với điều khoản sử dụng', 'error');
        }
        
        if (isFormValid) {
            submitForm();
        } else {
            showToast('Vui lòng kiểm tra lại thông tin', 'error');
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}

// Validate all time slots
function validateAllTimeSlots() {
    const slots = document.querySelectorAll('.time-slot');
    let isValid = true;
    
    slots.forEach(slot => {
        const date = slot.querySelector('input[name*="[date]"]');
        const startTime = slot.querySelector('input[name*="[start_time]"]');
        const endTime = slot.querySelector('input[name*="[end_time]"]');
        
        if (!date.value || !startTime.value || !endTime.value) {
            isValid = false;
        }
        
        if (startTime.value && endTime.value) {
            const start = new Date(`2000-01-01 ${startTime.value}`);
            const end = new Date(`2000-01-01 ${endTime.value}`);
            
            if (end <= start) {
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// Submit form
function submitForm() {
    const submitBtn = document.querySelector('.btn-submit');
    
    // Show loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
    // Collect form data
    const formData = new FormData(document.getElementById('bookingForm'));
    
    // Simulate API call
    setTimeout(() => {
        // Hide loading state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        
        // Show success modal
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        
        // Reset form
        document.getElementById('bookingForm').reset();
        
        // Show success message
        showToast('Đặt lịch thành công! Chủ nhà sẽ liên hệ với bạn sớm.', 'success');
        
        console.log('Booking data:', Object.fromEntries(formData));
    }, 2000);
}

// Toast notification system
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${getToastIcon(type)}"></i>
        <span>${message}</span>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add toast styles if not already added
    if (!document.querySelector('#toast-styles')) {
        const styles = document.createElement('style');
        styles.id = 'toast-styles';
        styles.textContent = `
            .toast-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 1000;
                animation: slideInRight 0.3s ease;
                max-width: 400px;
                border-left: 4px solid;
            }
            .toast-success { border-left-color: #10b981; }
            .toast-error { border-left-color: #ef4444; }
            .toast-info { border-left-color: #3b82f6; }
            .toast-warning { border-left-color: #f59e0b; }
            
            .toast-notification i:first-child {
                font-size: 1.2rem;
            }
            .toast-success i:first-child { color: #10b981; }
            .toast-error i:first-child { color: #ef4444; }
            .toast-info i:first-child { color: #3b82f6; }
            .toast-warning i:first-child { color: #f59e0b; }
            
            .toast-close {
                background: none;
                border: none;
                color: #6b7280;
                cursor: pointer;
                padding: 4px;
                margin-left: auto;
            }
            
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Get toast icon
function getToastIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        info: 'info-circle',
        warning: 'exclamation-triangle'
    };
    return icons[type] || 'info-circle';
}
