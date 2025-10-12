@extends('layouts.app')

@section('title', 'Đặt lịch xem phòng')

@section('content')
<div class="booking-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="booking-header text-center mb-5">
                    <div class="booking-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h1 class="booking-title">Đặt Lịch Xem Phòng</h1>
                    <p class="booking-subtitle">Điền thông tin để đặt lịch xem phòng trọ với chủ nhà</p>
                </div>

                <!-- Property Info -->
                @if(isset($property))
                <div class="property-preview">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            @if($property->images && count($property->images) > 0)
                                <img src="{{ Storage::url($property->images[0]) }}" 
                                     class="img-fluid rounded property-thumb" alt="{{ $property->name }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" 
                                     class="img-fluid rounded property-thumb" alt="{{ $property->name }}">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4 class="property-title">{{ $property->name }}</h4>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $property->location2025 ? $property->location2025->street . ', ' . $property->location2025->ward . ', ' . $property->location2025->city : 'Chưa cập nhật địa chỉ' }}
                            </p>
                            @if(isset($unit))
                                <div class="property-price">{{ number_format($unit->base_rent, 0, ',', '.') }} VNĐ/tháng</div>
                                <div class="unit-info mt-2">
                                    <span class="badge bg-info">{{ $unit->code }}</span>
                                    <span class="text-muted ms-2">{{ $unit->area_m2 ?? 'N/A' }}m²</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Booking Form -->
                <div class="booking-form-container">
                    <form id="bookingForm" class="booking-form" action="{{ route('viewings.store') }}" method="POST">
                        @csrf
                        
                        <!-- Hidden fields -->
                        <input type="hidden" name="property_id" value="{{ $property->id ?? '' }}">
                        <input type="hidden" name="unit_id" value="{{ $unit->id ?? '' }}">
                        
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-user"></i>
                                Thông tin cá nhân
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lead_name" class="form-label">Họ và tên <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="lead_name" name="lead_name" 
                                           value="{{ Auth::user()->name ?? '' }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Số điện thoại <span class="required">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="lead_phone" 
                                           value="{{ Auth::user()->phone ?? '' }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lead_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="lead_email" name="lead_email" 
                                           value="{{ Auth::user()->email ?? '' }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="occupation" class="form-label">Nghề nghiệp</label>
                                    <input type="text" class="form-control" id="occupation" name="occupation" 
                                           placeholder="Ví dụ: Sinh viên, Nhân viên văn phòng...">
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Time -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-calendar-alt"></i>
                                Thời gian xem phòng
                            </h3>
                            <p class="section-description">Chọn thời gian bạn muốn xem phòng</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="schedule_date" class="form-label">Ngày xem <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="schedule_date" name="schedule_date" 
                                           required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="schedule_time" class="form-label">Giờ xem <span class="required">*</span></label>
                                    <select class="form-select" id="schedule_time" name="schedule_time" required>
                                        <option value="">Chọn giờ</option>
                                        <option value="08:00">8:00 - 9:00</option>
                                        <option value="09:00">9:00 - 10:00</option>
                                        <option value="10:00">10:00 - 11:00</option>
                                        <option value="11:00">11:00 - 12:00</option>
                                        <option value="14:00">14:00 - 15:00</option>
                                        <option value="15:00">15:00 - 16:00</option>
                                        <option value="16:00">16:00 - 17:00</option>
                                        <option value="17:00">17:00 - 18:00</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                    <div class="form-text">Giờ sẽ được cập nhật theo lịch trống của agent</div>
                                </div>
                            </div>
                            
                            <!-- Available Time Slots Info -->
                            <div class="time-slots-info" id="timeSlotsInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Lưu ý:</strong> Các khung giờ đã được đặt sẽ không hiển thị trong danh sách.
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-comment"></i>
                                Thông tin bổ sung
                            </h3>
                            
                            <div class="mb-3">
                                <label for="note" class="form-label">Ghi chú cho chủ nhà</label>
                                <textarea class="form-control" id="note" name="note" rows="4" 
                                          placeholder="Chia sẻ thêm về bản thân, mong muốn về phòng trọ hoặc câu hỏi cho chủ nhà..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mục đích thuê</label>
                                <div class="purpose-options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="purpose" id="personal" value="personal" checked>
                                        <label class="form-check-label" for="personal">Ở cá nhân</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="purpose" id="couple" value="couple">
                                        <label class="form-check-label" for="couple">Ở cặp đôi</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="purpose" id="family" value="family">
                                        <label class="form-check-label" for="family">Gia đình nhỏ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="purpose" id="business" value="business">
                                        <label class="form-check-label" for="business">Kinh doanh</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Submit -->
                        <div class="form-section">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    Tôi đồng ý với <a href="#" class="text-primary">điều khoản sử dụng</a> và 
                                    <a href="#" class="text-primary">chính sách bảo mật</a> <span class="required">*</span>
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline-secondary me-3" onclick="history.back()">
                                    <i class="fas fa-arrow-left"></i>
                                    Quay lại
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-check"></i>
                                    Gửi yêu cầu đặt lịch
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="contact-support">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="support-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h6>Hỗ trợ qua điện thoại</h6>
                                    <p>1900 1234 (8:00 - 22:00)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="support-item">
                                <i class="fas fa-comments"></i>
                                <div>
                                    <h6>Chat hỗ trợ</h6>
                                    <p>Phản hồi trong 5 phút</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="mt-3">Đặt lịch thành công!</h4>
                <p class="text-muted">Chủ nhà sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận lịch hẹn.</p>
                <div class="mt-4">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                    <a href="{{ route('viewings.appointments') }}" class="btn btn-outline-primary">Xem lịch đặt của tôi</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.booking-container {
    padding: 40px 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.booking-header {
    margin-bottom: 40px;
}

.booking-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 2rem;
}

.booking-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.booking-subtitle {
    font-size: 1.1rem;
    color: #6c757d;
    margin: 0;
}

.property-preview {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.property-thumb {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.property-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
}

.property-location {
    color: #6c757d;
    margin-bottom: 15px;
}

.property-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
}

.unit-info {
    margin-top: 10px;
}

.booking-form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e9ecef;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #667eea;
}

.section-description {
    color: #6c757d;
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.required {
    color: #dc3545;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.purpose-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.form-check {
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.form-check:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.form-check-input:checked + .form-check-label {
    color: #667eea;
    font-weight: 600;
}

.form-check-input:checked ~ .form-check {
    border-color: #667eea;
    background: #f8f9ff;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
}

.contact-support {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.support-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
}

.support-item i {
    font-size: 1.5rem;
    color: #667eea;
    width: 40px;
    text-align: center;
}

.support-item h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #2c3e50;
}

.support-item p {
    margin: 0;
    color: #6c757d;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #28a745;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2rem;
}

.time-slots-info {
    margin-top: 15px;
}

@media (max-width: 768px) {
    .booking-title {
        font-size: 2rem;
    }
    
    .booking-form-container {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .purpose-options {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Load available time slots when date changes
document.getElementById('schedule_date').addEventListener('change', function() {
    const date = this.value;
    const timeSelect = document.getElementById('schedule_time');
    const propertyId = document.querySelector('input[name="property_id"]').value;
    
    if (!date || !propertyId) {
        timeSelect.innerHTML = '<option value="">Chọn giờ</option>';
        return;
    }
    
    // Show loading
    timeSelect.innerHTML = '<option value="">Đang tải...</option>';
    timeSelect.disabled = true;
    
    // Fetch available slots
    fetch(`/viewings/available-slots?property_id=${propertyId}&date=${date}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            timeSelect.innerHTML = '<option value="">Chọn giờ</option>';
            
            if (data.available_slots.length > 0) {
                data.available_slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = `${slot} - ${getNextHour(slot)}`;
                    timeSelect.appendChild(option);
                });
                document.getElementById('timeSlotsInfo').style.display = 'block';
            } else {
                timeSelect.innerHTML = '<option value="">Không có khung giờ trống</option>';
                document.getElementById('timeSlotsInfo').style.display = 'none';
            }
        } else {
            timeSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        timeSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
    })
    .finally(() => {
        timeSelect.disabled = false;
    });
});

function getNextHour(time) {
    const [hour, minute] = time.split(':');
    const nextHour = parseInt(hour) + 1;
    return `${nextHour.toString().padStart(2, '0')}:${minute}`;
}

// Form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    // Show loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    // Submit form
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        } else {
            // Show error
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endpush
