@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa Lead')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa Lead
                    </h1>
                    <p class="text-muted mb-0">Cập nhật thông tin khách hàng tiềm năng</p>
                </div>
                <div>
                    <a href="{{ route('agent.leads.show', $lead->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('agent.leads.index') }}">Leads</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('agent.leads.show', $lead->id) }}">{{ $lead->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>Thông tin Lead
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.leads.update', $lead->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Source -->
                            <div class="col-md-6 mb-3">
                                <label for="source" class="form-label">Nguồn <span class="text-danger">*</span></label>
                                <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                                    <option value="">Chọn nguồn</option>
                                    <option value="facebook" {{ old('source', $lead->source) == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                    <option value="google" {{ old('source', $lead->source) == 'google' ? 'selected' : '' }}>Google</option>
                                    <option value="referral" {{ old('source', $lead->source) == 'referral' ? 'selected' : '' }}>Giới thiệu</option>
                                    <option value="walk-in" {{ old('source', $lead->source) == 'walk-in' ? 'selected' : '' }}>Đến trực tiếp</option>
                                    <option value="phone" {{ old('source', $lead->source) == 'phone' ? 'selected' : '' }}>Điện thoại</option>
                                    <option value="other" {{ old('source', $lead->source) == 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $lead->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $lead->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $lead->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Desired City -->
                            <div class="col-md-6 mb-3">
                                <label for="desired_city" class="form-label">Thành phố mong muốn</label>
                                <input type="text" class="form-control @error('desired_city') is-invalid @enderror" 
                                       id="desired_city" name="desired_city" value="{{ old('desired_city', $lead->desired_city) }}">
                                @error('desired_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="new" {{ old('status', $lead->status) == 'new' ? 'selected' : '' }}>Mới</option>
                                    <option value="contacted" {{ old('status', $lead->status) == 'contacted' ? 'selected' : '' }}>Đã liên hệ</option>
                                    <option value="qualified" {{ old('status', $lead->status) == 'qualified' ? 'selected' : '' }}>Đủ điều kiện</option>
                                    <option value="proposal" {{ old('status', $lead->status) == 'proposal' ? 'selected' : '' }}>Đề xuất</option>
                                    <option value="negotiation" {{ old('status', $lead->status) == 'negotiation' ? 'selected' : '' }}>Đàm phán</option>
                                    <option value="converted" {{ old('status', $lead->status) == 'converted' ? 'selected' : '' }}>Đã chuyển đổi</option>
                                    <option value="lost" {{ old('status', $lead->status) == 'lost' ? 'selected' : '' }}>Mất khách</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Budget Min -->
                            <div class="col-md-6 mb-3">
                                <label for="budget_min" class="form-label">Ngân sách tối thiểu</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('budget_min') is-invalid @enderror" 
                                           id="budget_min" name="budget_min" value="{{ old('budget_min', $lead->budget_min ? number_format($lead->budget_min, 0, ',', '.') : '') }}" 
                                           placeholder="Nhập số tiền">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('budget_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Budget Max -->
                            <div class="col-md-6 mb-3">
                                <label for="budget_max" class="form-label">Ngân sách tối đa</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('budget_max') is-invalid @enderror" 
                                           id="budget_max" name="budget_max" value="{{ old('budget_max', $lead->budget_max ? number_format($lead->budget_max, 0, ',', '.') : '') }}" 
                                           placeholder="Nhập số tiền">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('budget_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Note -->
                            <div class="col-12 mb-3">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          id="note" name="note" rows="4" placeholder="Ghi chú về khách hàng...">{{ old('note', $lead->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.leads.show', $lead->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Cập nhật Lead
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.field-error-message {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-control.is-valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.88-1.88L4.5 2.5l-1.88 1.88L.74 6.73l1.56 1.56z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4 1.4-1.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.input-group .form-control.is-valid,
.input-group .form-control.is-invalid {
    background-position: right calc(0.375em + 0.1875rem + 2.5rem) center;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format currency inputs
    function formatCurrency(input) {
        let value = input.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
        }
        input.value = value;
    }

    // Validate currency input
    function validateCurrency(input, fieldName) {
        const value = input.value.replace(/[^\d]/g, '');
        const numericValue = parseInt(value);
        
        // Remove existing validation classes
        input.classList.remove('is-valid', 'is-invalid');
        
        if (value && (!isNaN(numericValue) && numericValue >= 0)) {
            input.classList.add('is-valid');
            return true;
        } else if (value) {
            input.classList.add('is-invalid');
            showFieldError(input, `${fieldName} phải là số dương hợp lệ.`);
            return false;
        }
        return true;
    }

    // Show field-specific error
    function showFieldError(input, message) {
        // Remove existing error message
        const existingError = input.parentNode.querySelector('.field-error-message');
        if (existingError) {
            existingError.remove();
        }

        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error-message text-danger small mt-1';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }

    // Remove field error
    function removeFieldError(input) {
        const existingError = input.parentNode.querySelector('.field-error-message');
        if (existingError) {
            existingError.remove();
        }
    }

    // Validate budget range
    function validateBudgetRange() {
        const budgetMin = document.getElementById('budget_min');
        const budgetMax = document.getElementById('budget_max');
        
        const minValue = parseInt(budgetMin.value.replace(/[^\d]/g, ''));
        const maxValue = parseInt(budgetMax.value.replace(/[^\d]/g, ''));
        
        if (minValue && maxValue && minValue > maxValue) {
            showFieldError(budgetMax, 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.');
            budgetMax.classList.add('is-invalid');
            return false;
        } else {
            removeFieldError(budgetMax);
            if (budgetMax.value) {
                budgetMax.classList.remove('is-invalid');
                budgetMax.classList.add('is-valid');
            }
        }
        return true;
    }

    // Setup budget min field
    const budgetMinField = document.getElementById('budget_min');
    budgetMinField.addEventListener('input', function() {
        formatCurrency(this);
        validateCurrency(this, 'Ngân sách tối thiểu');
        validateBudgetRange();
    });

    // Setup budget max field
    const budgetMaxField = document.getElementById('budget_max');
    budgetMaxField.addEventListener('input', function() {
        formatCurrency(this);
        validateCurrency(this, 'Ngân sách tối đa');
        validateBudgetRange();
    });

    // Form submission validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate budget fields
        if (!validateCurrency(budgetMinField, 'Ngân sách tối thiểu')) {
            isValid = false;
        }
        
        if (!validateCurrency(budgetMaxField, 'Ngân sách tối đa')) {
            isValid = false;
        }
        
        if (!validateBudgetRange()) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Vui lòng kiểm tra lại thông tin ngân sách.', 'Lỗi nhập liệu');
            } else {
                alert('Vui lòng kiểm tra lại thông tin ngân sách.');
            }
        } else {
            // Show loading notification
            if (typeof Notify !== 'undefined') {
                Notify.info('Đang cập nhật lead...', 'Đang xử lý');
            }
        }
    });

    // Show success notification if redirected with success message
    @if(session('success'))
        if (typeof Notify !== 'undefined') {
            Notify.success('{{ session('success') }}', 'Thành công');
        }
    @endif

    // Show error notification if redirected with error message
    @if(session('error'))
        if (typeof Notify !== 'undefined') {
            Notify.error('{{ session('error') }}', 'Lỗi');
        }
    @endif
});
</script>
@endpush
