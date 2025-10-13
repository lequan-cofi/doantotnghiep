@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa đơn ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa đơn ứng lương</h1>
            <p class="mb-0">Chỉnh sửa đơn ứng lương #{{ $salaryAdvance->id }}</p>
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
                    <form action="{{ route('agent.salary-advances.update', $salaryAdvance->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Số tiền ứng <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount', $salaryAdvance->amount) }}" 
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
                                        <option value="VND" {{ old('currency', $salaryAdvance->currency) == 'VND' ? 'selected' : '' }}>VND</option>
                                        <option value="USD" {{ old('currency', $salaryAdvance->currency) == 'USD' ? 'selected' : '' }}>USD</option>
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
                                           id="advance_date" name="advance_date" 
                                           value="{{ old('advance_date', $salaryAdvance->advance_date->format('Y-m-d')) }}" 
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
                                           value="{{ old('expected_repayment_date', $salaryAdvance->expected_repayment_date->format('Y-m-d')) }}" required>
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
                                      placeholder="Mô tả chi tiết lý do cần ứng lương...">{{ old('reason', $salaryAdvance->reason) }}</textarea>
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
                                            <option value="{{ $key }}" {{ old('repayment_method', $salaryAdvance->repayment_method) == $key ? 'selected' : '' }}>
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
                                            <option value="{{ $i }}" {{ old('installment_months', $salaryAdvance->installment_months) == $i ? 'selected' : '' }}>
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
                                               value="{{ old('monthly_deduction', $salaryAdvance->monthly_deduction) }}" min="0" step="1000">
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
                                      placeholder="Ghi chú thêm (không bắt buộc)...">{{ old('note', $salaryAdvance->note) }}</textarea>
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
                                <i class="fas fa-save"></i> Cập nhật đơn ứng lương
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin hiện tại</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Trạng thái:</label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-{{ $salaryAdvance->status_color }}">
                                {{ $salaryAdvance->status_label }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ngày tạo:</label>
                        <p class="form-control-plaintext">{{ $salaryAdvance->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cập nhật lần cuối:</label>
                        <p class="form-control-plaintext">{{ $salaryAdvance->updated_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($salaryAdvance->approver)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Người duyệt:</label>
                            <p class="form-control-plaintext">{{ $salaryAdvance->approver->full_name }}</p>
                        </div>
                    @endif

                    @if($salaryAdvance->rejector)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Người từ chối:</label>
                            <p class="form-control-plaintext">{{ $salaryAdvance->rejector->full_name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lưu ý</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Chú ý:</strong> Bạn chỉ có thể chỉnh sửa đơn ứng lương khi trạng thái là "Chờ duyệt" hoặc "Đã từ chối".
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Thông tin:</strong> Sau khi cập nhật, đơn ứng lương sẽ được gửi lại để manager duyệt.
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
