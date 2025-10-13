@extends('layouts.agent_dashboard')

@section('title', 'Tạo đơn ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tạo đơn ứng lương</h1>
            <p class="mb-0">Tạo đơn ứng lương mới</p>
        </div>
        <a href="{{ route('agent.salary-advances.index') }}" class="btn btn-secondary">
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
                    <form action="{{ route('agent.salary-advances.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Số tiền ứng <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount') }}" 
                                               min="100000" max="50000000" step="1000" required>
                                        <span class="input-group-text">VND</span>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Tối thiểu 100,000 VND, tối đa 50,000,000 VND</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Đơn vị tiền tệ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                        <option value="VND" {{ old('currency', 'VND') == 'VND' ? 'selected' : '' }}>VND</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="advance_date" class="form-label">Ngày ứng lương <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('advance_date') is-invalid @enderror" 
                                           id="advance_date" name="advance_date" value="{{ old('advance_date', date('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}" required>
                                    @error('advance_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expected_repayment_date" class="form-label">Ngày hoàn trả dự kiến <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expected_repayment_date') is-invalid @enderror" 
                                           id="expected_repayment_date" name="expected_repayment_date" 
                                           value="{{ old('expected_repayment_date') }}" required>
                                    @error('expected_repayment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Lý do ứng lương <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason" rows="3" maxlength="1000" required 
                                      placeholder="Mô tả chi tiết lý do cần ứng lương...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Tối đa 1000 ký tự</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="repayment_method" class="form-label">Phương thức hoàn trả <span class="text-danger">*</span></label>
                                    <select class="form-select @error('repayment_method') is-invalid @enderror" 
                                            id="repayment_method" name="repayment_method" required>
                                        <option value="">Chọn phương thức hoàn trả</option>
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
                            </div>
                            <div class="col-md-6" id="installment_months_field" style="display: none;">
                                <div class="mb-3">
                                    <label for="installment_months" class="form-label">Số tháng trả góp</label>
                                    <select class="form-select @error('installment_months') is-invalid @enderror" 
                                            id="installment_months" name="installment_months">
                                        <option value="">Chọn số tháng</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ old('installment_months') == $i ? 'selected' : '' }}>
                                                {{ $i }} tháng
                                            </option>
                                        @endfor
                                    </select>
                                    @error('installment_months')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="monthly_deduction_field" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="monthly_deduction" class="form-label">Số tiền trừ hàng tháng</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('monthly_deduction') is-invalid @enderror" 
                                               id="monthly_deduction" name="monthly_deduction" 
                                               value="{{ old('monthly_deduction') }}" min="0" step="1000">
                                        <span class="input-group-text">VND</span>
                                    </div>
                                    @error('monthly_deduction')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Để trống để tự động tính toán</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="2" maxlength="1000" 
                                      placeholder="Ghi chú thêm (không bắt buộc)...">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Tối đa 1000 ký tự</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.salary-advances.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo đơn ứng lương
                            </button>
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
                    <div class="mb-3">
                        <h6 class="text-primary">Quy trình ứng lương:</h6>
                        <ol class="small">
                            <li>Điền đầy đủ thông tin đơn ứng lương</li>
                            <li>Gửi đơn và chờ manager duyệt</li>
                            <li>Nhận thông báo kết quả duyệt</li>
                            <li>Thực hiện hoàn trả theo phương thức đã chọn</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-primary">Phương thức hoàn trả:</h6>
                        <ul class="small">
                            <li><strong>Trừ lương:</strong> Tự động trừ vào lương hàng tháng</li>
                            <li><strong>Thanh toán trực tiếp:</strong> Chuyển khoản trực tiếp</li>
                            <li><strong>Trả góp:</strong> Chia nhỏ thành nhiều lần</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Lưu ý:</strong> Đơn ứng lương sẽ được gửi đến manager để duyệt. Bạn có thể chỉnh sửa hoặc xóa đơn khi chưa được duyệt.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show/hide fields based on repayment method
    document.getElementById('repayment_method').addEventListener('change', function() {
        const installmentField = document.getElementById('installment_months_field');
        const monthlyDeductionField = document.getElementById('monthly_deduction_field');
        
        if (this.value === 'installment') {
            installmentField.style.display = 'block';
            monthlyDeductionField.style.display = 'none';
        } else if (this.value === 'payroll_deduction') {
            installmentField.style.display = 'none';
            monthlyDeductionField.style.display = 'block';
        } else {
            installmentField.style.display = 'none';
            monthlyDeductionField.style.display = 'none';
        }
    });

    // Set default expected repayment date (30 days from advance date)
    document.getElementById('advance_date').addEventListener('change', function() {
        const advanceDate = new Date(this.value);
        const expectedDate = new Date(advanceDate);
        expectedDate.setDate(expectedDate.getDate() + 30);
        
        const expectedDateInput = document.getElementById('expected_repayment_date');
        if (!expectedDateInput.value) {
            expectedDateInput.value = expectedDate.toISOString().split('T')[0];
        }
    });

    // Show notifications
    @if(session('success'))
        notify.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        notify.error('{{ session('error') }}');
    @endif

    // Initialize fields visibility
    document.getElementById('repayment_method').dispatchEvent(new Event('change'));
</script>
@endpush
