@extends('layouts.app')

@section('title', 'Đặt lịch xem phòng')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/booking.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/booking.js') }}"></script>
@endpush

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
                <div class="property-preview">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" 
                                 class="img-fluid rounded property-thumb" alt="Phòng trọ">
                        </div>
                        <div class="col-md-8">
                            <h4 class="property-title">Phòng trọ cao cấp Cầu Giấy</h4>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội
                            </p>
                            <div class="property-price">2.500.000 VNĐ/tháng</div>
                        </div>
                    </div>
                </div>

                <!-- Booking Form -->
                <div class="booking-form-container">
                    <form id="bookingForm" class="booking-form" action="#" method="POST">
                        @csrf
                        
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-user"></i>
                                Thông tin cá nhân
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fullName" class="form-label">Họ và tên <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="fullName" name="full_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="birthYear" class="form-label">Năm sinh <span class="required">*</span></label>
                                    <select class="form-select" id="birthYear" name="birth_year" required>
                                        <option value="">Chọn năm sinh</option>
                                        @for($year = date('Y') - 18; $year >= date('Y') - 80; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Số điện thoại <span class="required">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="occupation" class="form-label">Nghề nghiệp</label>
                                <input type="text" class="form-control" id="occupation" name="occupation" placeholder="Ví dụ: Sinh viên, Nhân viên văn phòng...">
                            </div>
                        </div>

                        <!-- Time Slots -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-clock"></i>
                                Thời gian rảnh
                            </h3>
                            <p class="section-description">Thêm các khung thời gian bạn có thể xem phòng</p>
                            
                            <div id="timeSlots">
                                <!-- Default time slot -->
                                <div class="time-slot" data-slot="1">
                                    <div class="time-slot-header">
                                        <h5>Khung thời gian 1</h5>
                                        <button type="button" class="btn-remove-slot" onclick="removeTimeSlot(1)" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Ngày <span class="required">*</span></label>
                                            <input type="date" class="form-control" name="slots[1][date]" required min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Từ giờ <span class="required">*</span></label>
                                            <input type="time" class="form-control" name="slots[1][start_time]" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Đến giờ <span class="required">*</span></label>
                                            <input type="time" class="form-control" name="slots[1][end_time]" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú cho khung thời gian này</label>
                                        <input type="text" class="form-control" name="slots[1][note]" placeholder="Ví dụ: Sáng chủ nhật, sau 18h...">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn-add-slot" onclick="addTimeSlot()">
                                <i class="fas fa-plus"></i>
                                Thêm khung thời gian khác
                            </button>
                        </div>

                        <!-- Additional Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-comment"></i>
                                Thông tin bổ sung
                            </h3>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Tin nhắn cho chủ nhà</label>
                                <textarea class="form-control" id="message" name="message" rows="4" 
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
                                <button type="submit" class="btn-submit">
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
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Về trang chủ</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
