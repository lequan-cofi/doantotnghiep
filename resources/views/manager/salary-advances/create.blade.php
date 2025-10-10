@extends('layouts.manager_dashboard')

@section('title', 'Tạo đơn ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus text-primary"></i>
                Tạo đơn ứng lương
            </h1>
            <p class="text-muted mb-0">Tạo đơn ứng lương mới cho nhân viên</p>
        </div>
        <a href="{{ route('manager.salary-advances.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn ứng lương</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manager.salary-advances.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">Nhân viên <span class="text-danger">*</span></label>
                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        <option value="">Chọn nhân viên</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->full_name ?? 'N/A' }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Số tiền ứng <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                               value="{{ old('amount') }}" step="0.01" min="0" required>
                                        <select name="currency" class="form-control @error('currency') is-invalid @enderror" style="max-width: 100px;">
                                            <option value="VND" {{ old('currency', 'VND') == 'VND' ? 'selected' : '' }}>VND</option>
                                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="advance_date">Ngày ứng <span class="text-danger">*</span></label>
                                    <input type="date" name="advance_date" id="advance_date" class="form-control @error('advance_date') is-invalid @enderror" 
                                           value="{{ old('advance_date', date('Y-m-d')) }}" required>
                                    @error('advance_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expected_repayment_date">Ngày trả dự kiến <span class="text-danger">*</span></label>
                                    <input type="date" name="expected_repayment_date" id="expected_repayment_date" class="form-control @error('expected_repayment_date') is-invalid @enderror" 
                                           value="{{ old('expected_repayment_date') }}" required>
                                    @error('expected_repayment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Lý do ứng lương <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                                      rows="3" required placeholder="Nhập lý do ứng lương...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="repayment_method">Phương thức trả <span class="text-danger">*</span></label>
                            <select name="repayment_method" id="repayment_method" class="form-control @error('repayment_method') is-invalid @enderror" required>
                                @foreach($repaymentMethods as $key => $label)
                                    <option value="{{ $key }}" {{ old('repayment_method') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('repayment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Conditional fields based on repayment method -->
                        <div id="installment_fields" style="display: none;">
                            <div class="form-group">
                                <label for="installment_months">Số tháng trả góp</label>
                                <input type="number" name="installment_months" id="installment_months" class="form-control @error('installment_months') is-invalid @enderror" 
                                       value="{{ old('installment_months') }}" min="1" max="12">
                                @error('installment_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="payroll_deduction_fields" style="display: none;">
                            <div class="form-group">
                                <label for="monthly_deduction">Số tiền trừ hàng tháng (VND)</label>
                                <input type="number" name="monthly_deduction" id="monthly_deduction" class="form-control @error('monthly_deduction') is-invalid @enderror" 
                                       value="{{ old('monthly_deduction') }}" step="0.01" min="0">
                                <small class="form-text text-muted">Để trống để tự động tính toán</small>
                                @error('monthly_deduction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="note">Ghi chú</label>
                            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" 
                                      rows="2" placeholder="Ghi chú thêm (không bắt buộc)...">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo đơn ứng lương
                            </button>
                            <a href="{{ route('manager.salary-advances.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hướng dẫn</h6>
                </div>
                <div class="card-body">
                    <h6>Phương thức trả:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Trừ lương:</strong> Tự động trừ vào lương hàng tháng</li>
                        <li><strong>Thanh toán trực tiếp:</strong> Nhân viên trả trực tiếp</li>
                        <li><strong>Trả góp:</strong> Chia thành nhiều tháng</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Lưu ý:</h6>
                    <ul class="list-unstyled text-muted">
                        <li>• Đơn ứng lương sẽ ở trạng thái "Chờ duyệt"</li>
                        <li>• Cần duyệt trước khi có hiệu lực</li>
                        <li>• Có thể chỉnh sửa khi chưa duyệt</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/notifications.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const repaymentMethodSelect = document.getElementById('repayment_method');
    const installmentFields = document.getElementById('installment_fields');
    const payrollDeductionFields = document.getElementById('payroll_deduction_fields');
    
    function toggleFields() {
        const method = repaymentMethodSelect.value;
        
        // Hide all conditional fields
        installmentFields.style.display = 'none';
        payrollDeductionFields.style.display = 'none';
        
        // Show relevant fields
        if (method === 'installment') {
            installmentFields.style.display = 'block';
        } else if (method === 'payroll_deduction') {
            payrollDeductionFields.style.display = 'block';
        }
    }
    
    repaymentMethodSelect.addEventListener('change', toggleFields);
    
    // Initialize on page load
    toggleFields();
    
    // Set default expected repayment date (1 month from advance date)
    const advanceDateInput = document.getElementById('advance_date');
    const expectedRepaymentDateInput = document.getElementById('expected_repayment_date');
    
    advanceDateInput.addEventListener('change', function() {
        if (this.value && !expectedRepaymentDateInput.value) {
            const advanceDate = new Date(this.value);
            const expectedDate = new Date(advanceDate);
            expectedDate.setMonth(expectedDate.getMonth() + 1);
            expectedRepaymentDateInput.value = expectedDate.toISOString().split('T')[0];
        }
    });
});
</script>
@endpush
