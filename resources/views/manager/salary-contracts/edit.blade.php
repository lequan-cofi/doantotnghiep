@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa hợp đồng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-primary"></i>
                Chỉnh sửa hợp đồng lương #{{ $salaryContract->id }}
            </h1>
            <p class="text-muted mb-0">Chỉnh sửa thông tin hợp đồng lương</p>
        </div>
        <div>
            <a href="{{ route('manager.salary-contracts.show', $salaryContract->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="{{ route('manager.salary-contracts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin hợp đồng lương</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manager.salary-contracts.update', $salaryContract->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">Nhân viên <span class="text-danger">*</span></label>
                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        <option value="">Chọn nhân viên</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $salaryContract->user_id) == $user->id ? 'selected' : '' }}>
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
                                    <label for="base_salary">Lương cơ bản <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="base_salary" id="base_salary" class="form-control @error('base_salary') is-invalid @enderror" 
                                               value="{{ old('base_salary', $salaryContract->base_salary) }}" step="0.01" min="0" required>
                                        <select name="currency" class="form-control @error('currency') is-invalid @enderror" style="max-width: 100px;">
                                            <option value="VND" {{ old('currency', $salaryContract->currency) == 'VND' ? 'selected' : '' }}>VND</option>
                                            <option value="USD" {{ old('currency', $salaryContract->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    @error('base_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pay_cycle">Chu kỳ trả lương <span class="text-danger">*</span></label>
                                    <select name="pay_cycle" id="pay_cycle" class="form-control @error('pay_cycle') is-invalid @enderror" required>
                                        @foreach($payCycles as $key => $label)
                                            <option value="{{ $key }}" {{ old('pay_cycle', $salaryContract->pay_cycle) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pay_cycle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pay_day">Ngày trả lương <span class="text-danger">*</span></label>
                                    <input type="number" name="pay_day" id="pay_day" class="form-control @error('pay_day') is-invalid @enderror" 
                                           value="{{ old('pay_day', $salaryContract->pay_day) }}" min="1" max="31" required>
                                    <small class="form-text text-muted">Ngày trong tháng (1-31)</small>
                                    @error('pay_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" {{ old('status', $salaryContract->status) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_from">Ngày hiệu lực <span class="text-danger">*</span></label>
                                    <input type="date" name="effective_from" id="effective_from" class="form-control @error('effective_from') is-invalid @enderror" 
                                           value="{{ old('effective_from', $salaryContract->effective_from->format('Y-m-d')) }}" required>
                                    @error('effective_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_to">Ngày hết hạn</label>
                                    <input type="date" name="effective_to" id="effective_to" class="form-control @error('effective_to') is-invalid @enderror" 
                                           value="{{ old('effective_to', $salaryContract->effective_to ? $salaryContract->effective_to->format('Y-m-d') : '') }}">
                                    <small class="form-text text-muted">Để trống nếu không có thời hạn</small>
                                    @error('effective_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Allowances Section -->
                        <div class="form-group">
                            <label>Phụ cấp</label>
                            <div class="row" id="allowances-container">
                                @if($salaryContract->allowances_json && count($salaryContract->allowances_json) > 0)
                                    @foreach($salaryContract->allowances_json as $name => $amount)
                                        <div class="col-md-4">
                                            <div class="input-group mb-2">
                                                <input type="text" name="allowance_names[]" class="form-control" placeholder="Tên phụ cấp" value="{{ $name }}">
                                                <input type="number" name="allowance_amounts[]" class="form-control" placeholder="Số tiền" step="0.01" min="0" value="{{ $amount }}">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeAllowance(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-md-4">
                                        <div class="input-group mb-2">
                                            <input type="text" name="allowance_names[]" class="form-control" placeholder="Tên phụ cấp">
                                            <input type="number" name="allowance_amounts[]" class="form-control" placeholder="Số tiền" step="0.01" min="0">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeAllowance(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAllowance()">
                                <i class="fas fa-plus"></i> Thêm phụ cấp
                            </button>
                        </div>

                        <!-- KPI Targets Section -->
                        <div class="form-group">
                            <label>Mục tiêu KPI</label>
                            <div class="row" id="kpi-container">
                                @if($salaryContract->kpi_target_json && count($salaryContract->kpi_target_json) > 0)
                                    @foreach($salaryContract->kpi_target_json as $name => $target)
                                        <div class="col-md-6">
                                            <div class="input-group mb-2">
                                                <input type="text" name="kpi_names[]" class="form-control" placeholder="Tên KPI" value="{{ $name }}">
                                                <input type="number" name="kpi_targets[]" class="form-control" placeholder="Mục tiêu" step="0.01" min="0" value="{{ $target }}">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeKPI(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-md-6">
                                        <div class="input-group mb-2">
                                            <input type="text" name="kpi_names[]" class="form-control" placeholder="Tên KPI">
                                            <input type="number" name="kpi_targets[]" class="form-control" placeholder="Mục tiêu" step="0.01" min="0">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeKPI(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addKPI()">
                                <i class="fas fa-plus"></i> Thêm KPI
                            </button>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật hợp đồng lương
                            </button>
                            <a href="{{ route('manager.salary-contracts.show', $salaryContract->id) }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin hiện tại</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $salaryContract->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nhân viên:</strong></td>
                            <td>{{ $salaryContract->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Lương cơ bản:</strong></td>
                            <td>{{ number_format($salaryContract->base_salary) }} {{ $salaryContract->currency }}</td>
                        </tr>
                        <tr>
                            <td><strong>Chu kỳ trả:</strong></td>
                            <td>
                                @switch($salaryContract->pay_cycle)
                                    @case('monthly')
                                        Hàng tháng
                                        @break
                                    @case('weekly')
                                        Hàng tuần
                                        @break
                                    @case('daily')
                                        Hàng ngày
                                        @break
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Trạng thái:</strong></td>
                            <td>
                                @switch($salaryContract->status)
                                    @case('active')
                                        <span class="badge bg-success">Đang hoạt động</span>
                                        @break
                                    @case('inactive')
                                        <span class="badge bg-warning">Tạm dừng</span>
                                        @break
                                    @case('terminated')
                                        <span class="badge bg-danger">Đã chấm dứt</span>
                                        @break
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Ngày tạo:</strong></td>
                            <td>{{ $salaryContract->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lưu ý</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng</h6>
                        <ul class="mb-0">
                            <li>Chỉ có thể chỉnh sửa hợp đồng chưa chấm dứt</li>
                            <li>Thay đổi sẽ có hiệu lực từ ngày hiệu lực mới</li>
                            <li>Kiểm tra kỹ thông tin trước khi lưu</li>
                        </ul>
                    </div>
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
function addAllowance() {
    const container = document.getElementById('allowances-container');
    const newRow = document.createElement('div');
    newRow.className = 'col-md-4';
    newRow.innerHTML = `
        <div class="input-group mb-2">
            <input type="text" name="allowance_names[]" class="form-control" placeholder="Tên phụ cấp">
            <input type="number" name="allowance_amounts[]" class="form-control" placeholder="Số tiền" step="0.01" min="0">
            <button type="button" class="btn btn-outline-danger" onclick="removeAllowance(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
}

function removeAllowance(button) {
    button.closest('.col-md-4').remove();
}

function addKPI() {
    const container = document.getElementById('kpi-container');
    const newRow = document.createElement('div');
    newRow.className = 'col-md-6';
    newRow.innerHTML = `
        <div class="input-group mb-2">
            <input type="text" name="kpi_names[]" class="form-control" placeholder="Tên KPI">
            <input type="number" name="kpi_targets[]" class="form-control" placeholder="Mục tiêu" step="0.01" min="0">
            <button type="button" class="btn btn-outline-danger" onclick="removeKPI(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
}

function removeKPI(button) {
    button.closest('.col-md-6').remove();
}

// Process form data before submit
document.querySelector('form').addEventListener('submit', function(e) {
    // Process allowances
    const allowanceNames = document.querySelectorAll('input[name="allowance_names[]"]');
    const allowanceAmounts = document.querySelectorAll('input[name="allowance_amounts[]"]');
    const allowances = {};
    
    for (let i = 0; i < allowanceNames.length; i++) {
        const name = allowanceNames[i].value.trim();
        const amount = parseFloat(allowanceAmounts[i].value) || 0;
        if (name && amount > 0) {
            allowances[name] = amount;
        }
    }
    
    // Add hidden input for allowances
    const allowanceInput = document.createElement('input');
    allowanceInput.type = 'hidden';
    allowanceInput.name = 'allowances_json';
    allowanceInput.value = JSON.stringify(allowances);
    this.appendChild(allowanceInput);
    
    // Process KPI targets
    const kpiNames = document.querySelectorAll('input[name="kpi_names[]"]');
    const kpiTargets = document.querySelectorAll('input[name="kpi_targets[]"]');
    const kpiTargetsObj = {};
    
    for (let i = 0; i < kpiNames.length; i++) {
        const name = kpiNames[i].value.trim();
        const target = parseFloat(kpiTargets[i].value) || 0;
        if (name && target > 0) {
            kpiTargetsObj[name] = target;
        }
    }
    
    // Add hidden input for KPI targets
    const kpiInput = document.createElement('input');
    kpiInput.type = 'hidden';
    kpiInput.name = 'kpi_target_json';
    kpiInput.value = JSON.stringify(kpiTargetsObj);
    this.appendChild(kpiInput);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    // Re-enable button after 5 seconds as fallback
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 5000);
});
</script>
@endpush
