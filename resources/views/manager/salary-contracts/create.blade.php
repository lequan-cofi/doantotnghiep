@extends('layouts.manager_dashboard')

@section('title', 'Tạo hợp đồng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus text-primary"></i>
                Tạo hợp đồng lương
            </h1>
            <p class="text-muted mb-0">Tạo hợp đồng lương mới cho nhân viên</p>
        </div>
        <a href="{{ route('manager.salary-contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin hợp đồng lương</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manager.salary-contracts.store') }}">
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
                                    <label for="base_salary">Lương cơ bản <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="base_salary" id="base_salary" class="form-control @error('base_salary') is-invalid @enderror" 
                                               value="{{ old('base_salary') }}" step="0.01" min="0" required>
                                        <select name="currency" class="form-control @error('currency') is-invalid @enderror" style="max-width: 100px;">
                                            <option value="VND" {{ old('currency', 'VND') == 'VND' ? 'selected' : '' }}>VND</option>
                                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
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
                                            <option value="{{ $key }}" {{ old('pay_cycle') == $key ? 'selected' : '' }}>
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
                                           value="{{ old('pay_day', 1) }}" min="1" max="31" required>
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
                                            <option value="{{ $key }}" {{ old('status', 'active') == $key ? 'selected' : '' }}>
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
                                           value="{{ old('effective_from', date('Y-m-d')) }}" required>
                                    @error('effective_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_to">Ngày hết hạn</label>
                                    <input type="date" name="effective_to" id="effective_to" class="form-control @error('effective_to') is-invalid @enderror" 
                                           value="{{ old('effective_to') }}">
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
                                <div class="col-md-4">
                                    <div class="input-group mb-2">
                                        <input type="text" name="allowance_names[]" class="form-control" placeholder="Tên phụ cấp" value="Phụ cấp ăn trưa">
                                        <input type="number" name="allowance_amounts[]" class="form-control" placeholder="Số tiền" step="0.01" min="0" value="500000">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeAllowance(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group mb-2">
                                        <input type="text" name="allowance_names[]" class="form-control" placeholder="Tên phụ cấp" value="Phụ cấp xăng xe">
                                        <input type="number" name="allowance_amounts[]" class="form-control" placeholder="Số tiền" step="0.01" min="0" value="300000">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeAllowance(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group mb-2">
                                        <input type="text" name="allowance_names[]" class="form-control" placeholder="Tên phụ cấp" value="Phụ cấp điện thoại">
                                        <input type="number" name="allowance_amounts[]" class="form-control" placeholder="Số tiền" step="0.01" min="0" value="200000">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeAllowance(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAllowance()">
                                <i class="fas fa-plus"></i> Thêm phụ cấp
                            </button>
                        </div>

                        <!-- KPI Targets Section -->
                        <div class="form-group">
                            <label>Mục tiêu KPI</label>
                            <div class="row" id="kpi-container">
                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <input type="text" name="kpi_names[]" class="form-control" placeholder="Tên KPI" value="Doanh số bán hàng">
                                        <input type="number" name="kpi_targets[]" class="form-control" placeholder="Mục tiêu" step="0.01" min="0" value="10000000">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeKPI(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <input type="text" name="kpi_names[]" class="form-control" placeholder="Tên KPI" value="Tỷ lệ hoa hồng">
                                        <input type="number" name="kpi_targets[]" class="form-control" placeholder="Mục tiêu (%)" step="0.01" min="0" value="5">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeKPI(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addKPI()">
                                <i class="fas fa-plus"></i> Thêm KPI
                            </button>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo hợp đồng lương
                            </button>
                            <a href="{{ route('manager.salary-contracts.index') }}" class="btn btn-secondary">
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
                    <h6>Chu kỳ trả lương:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Hàng tháng:</strong> Trả lương mỗi tháng</li>
                        <li><strong>Hàng tuần:</strong> Trả lương mỗi tuần</li>
                        <li><strong>Hàng ngày:</strong> Trả lương mỗi ngày</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Phụ cấp:</h6>
                    <ul class="list-unstyled text-muted">
                        <li>• Phụ cấp ăn trưa</li>
                        <li>• Phụ cấp xăng xe</li>
                        <li>• Phụ cấp điện thoại</li>
                        <li>• Phụ cấp khác...</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Lưu ý:</h6>
                    <ul class="list-unstyled text-muted">
                        <li>• Mỗi nhân viên chỉ có 1 hợp đồng hoạt động</li>
                        <li>• Có thể chỉnh sửa khi chưa chấm dứt</li>
                        <li>• Phụ cấp và KPI có thể để trống</li>
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
