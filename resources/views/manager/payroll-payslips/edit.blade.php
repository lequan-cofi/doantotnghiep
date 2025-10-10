@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa Phiếu Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Phiếu Lương</h1>
            <p class="mb-0">{{ $payrollPayslip->user->full_name }} - {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->format('m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('manager.payroll-payslips.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('manager.payroll-payslips.show', $payrollPayslip->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
        </div>
    </div>

    <form action="{{ route('manager.payroll-payslips.update', $payrollPayslip->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin Phiếu Lương</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nhân viên</label>
                                <div class="form-control-plaintext">
                                    <strong>{{ $payrollPayslip->user->full_name }}</strong>
                                    <br><small class="text-muted">{{ $payrollPayslip->user->email }}</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kỳ lương</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-secondary">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->format('m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin Lương</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tổng lương (VND) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('gross_amount') is-invalid @enderror" 
                                       name="gross_amount" id="gross_amount" value="{{ old('gross_amount', $payrollPayslip->gross_amount) }}" 
                                       min="0" step="1000" required>
                                @error('gross_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Bao gồm lương cơ bản + phụ cấp + hoa hồng</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khấu trừ (VND) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('deduction_amount') is-invalid @enderror" 
                                       name="deduction_amount" id="deduction_amount" value="{{ old('deduction_amount', $payrollPayslip->deduction_amount) }}" 
                                       min="0" step="1000" required>
                                @error('deduction_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Thuế, bảo hiểm, khấu trừ khác</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thực lĩnh (VND)</label>
                                <input type="number" class="form-control" id="net_amount" 
                                       readonly placeholder="Sẽ được tính tự động">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          name="note" rows="3" placeholder="Ghi chú về phiếu lương...">{{ old('note', $payrollPayslip->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculation Preview -->
            <div class="col-lg-4">
                <!-- Salary Breakdown -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Phân tích Lương</h6>
                    </div>
                    <div class="card-body">
                        <div id="salary-breakdown">
                            <div class="mb-2">
                                <strong>Lương cơ bản:</strong> 
                                <span id="basic-salary">0 VND</span>
                            </div>
                            <div class="mb-2">
                                <strong>Phụ cấp:</strong> 
                                <span id="allowances">0 VND</span>
                            </div>
                            <div class="mb-2">
                                <strong>Hoa hồng:</strong> 
                                <span id="commission">0 VND</span>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Tổng lương:</strong> 
                                <span id="total-gross" class="text-primary">0 VND</span>
                            </div>
                            <div class="mb-2">
                                <strong>Khấu trừ:</strong> 
                                <span id="total-deductions" class="text-warning">0 VND</span>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Thực lĩnh:</strong> 
                                <span id="final-net" class="text-success">0 VND</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Cập nhật phiếu lương
                        </button>
                        <a href="{{ route('manager.payroll-payslips.show', $payrollPayslip->id) }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Calculate net amount when gross or deduction changes
function calculateNetAmount() {
    const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
    const deductionAmount = parseFloat(document.getElementById('deduction_amount').value) || 0;
    const netAmount = grossAmount - deductionAmount;
    
    document.getElementById('net_amount').value = netAmount;
    
    // Update breakdown display
    document.getElementById('total-gross').textContent = grossAmount.toLocaleString() + ' VND';
    document.getElementById('total-deductions').textContent = deductionAmount.toLocaleString() + ' VND';
    document.getElementById('final-net').textContent = netAmount.toLocaleString() + ' VND';
}

// Event listeners
document.getElementById('gross_amount').addEventListener('input', calculateNetAmount);
document.getElementById('deduction_amount').addEventListener('input', calculateNetAmount);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateNetAmount();
});
</script>
@endpush
