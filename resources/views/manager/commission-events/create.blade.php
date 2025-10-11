@extends('layouts.manager_dashboard')

@section('title', 'Tạo Sự kiện Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tạo Sự kiện Hoa hồng</h1>
            <p class="mb-0">Tạo sự kiện hoa hồng mới cho nhân viên</p>
        </div>
        <a href="{{ route('manager.commission-events.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="{{ route('manager.commission-events.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
                                <select class="form-select @error('agent_id') is-invalid @enderror" 
                                        name="agent_id" required>
                                    <option value="">Chọn nhân viên</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->full_name }} ({{ $agent->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chính sách hoa hồng <span class="text-danger">*</span></label>
                                <select class="form-select @error('policy_id') is-invalid @enderror" 
                                        name="policy_id" id="policy_id" required>
                                    <option value="">Chọn chính sách</option>
                                    @foreach($policies as $policy)
                                        <option value="{{ $policy->id }}" 
                                                data-trigger="{{ $policy->trigger_event }}"
                                                data-calc-type="{{ $policy->calc_type }}"
                                                data-percent="{{ $policy->percent_value }}"
                                                data-flat="{{ $policy->flat_amount }}"
                                                {{ old('policy_id') == $policy->id ? 'selected' : '' }}>
                                            {{ $policy->title }} ({{ $policy->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('policy_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sự kiện kích hoạt <span class="text-danger">*</span></label>
                                <select class="form-select @error('trigger_event') is-invalid @enderror" 
                                        name="trigger_event" id="trigger_event" required>
                                    <option value="">Chọn sự kiện kích hoạt</option>
                                    @foreach($triggerEvents as $key => $label)
                                        <option value="{{ $key }}" {{ old('trigger_event') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('trigger_event')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày xảy ra <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('occurred_at') is-invalid @enderror" 
                                       name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('occurred_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Records -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Bản ghi liên quan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hợp đồng thuê</label>
                                <select class="form-select @error('lease_id') is-invalid @enderror" 
                                        name="lease_id" id="lease_id">
                                    <option value="">Chọn hợp đồng thuê</option>
                                    @foreach($leases as $lease)
                                        <option value="{{ $lease->id }}" {{ old('lease_id') == $lease->id ? 'selected' : '' }}>
                                            {{ $lease->unit->name ?? 'N/A' }} - {{ $lease->tenant->full_name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lease_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phòng/Đơn vị</label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" 
                                        name="unit_id" id="unit_id">
                                    <option value="">Chọn phòng/đơn vị</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} - {{ $unit->property->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amount Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin số tiền</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số tiền gốc (VND) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('amount_base') is-invalid @enderror" 
                                       name="amount_base" id="amount_base" value="{{ old('amount_base') }}" 
                                       min="0" step="1000" required>
                                @error('amount_base')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hoa hồng tính toán (VND)</label>
                                <input type="number" class="form-control" id="calculated_commission" 
                                       readonly placeholder="Sẽ được tính tự động">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hoa hồng thực tế (VND)</label>
                                <input type="number" class="form-control @error('commission_total') is-invalid @enderror" 
                                       name="commission_total" id="commission_total" value="{{ old('commission_total') }}" 
                                       min="0" step="1000">
                                @error('commission_total')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        name="status">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', 'pending') == $key ? 'selected' : '' }}>
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
                </div>
            </div>

            <!-- Actions & Preview -->
            <div class="col-lg-4">
                <!-- Commission Preview -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Xem trước hoa hồng</h6>
                    </div>
                    <div class="card-body">
                        <div id="commission-preview">
                            <p class="text-muted">Chọn chính sách và nhập số tiền để xem trước</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Tạo sự kiện
                        </button>
                        <a href="{{ route('manager.commission-events.index') }}" class="btn btn-secondary w-100">
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
document.addEventListener('DOMContentLoaded', function() {
    // Show session messages
    @if(session('success'))
        Notify.success('{{ session('success') }}', 'Thành công!');
    @endif

    @if(session('error'))
        Notify.error('{{ session('error') }}', 'Lỗi!');
    @endif

    @if(session('warning'))
        Notify.warning('{{ session('warning') }}', 'Cảnh báo!');
    @endif

    @if(session('info'))
        Notify.info('{{ session('info') }}', 'Thông tin!');
    @endif

    // Initialize commission calculation
    calculateCommission();
});

// Calculate commission when policy or amount changes
function calculateCommission() {
    const policySelect = document.getElementById('policy_id');
    const amountInput = document.getElementById('amount_base');
    const calculatedInput = document.getElementById('calculated_commission');
    const totalInput = document.getElementById('commission_total');
    const preview = document.getElementById('commission-preview');
    
    if (!policySelect.value || !amountInput.value) {
        calculatedInput.value = '';
        totalInput.value = '';
        preview.innerHTML = '<p class="text-muted">Chọn chính sách và nhập số tiền để xem trước</p>';
        return;
    }
    
    const selectedOption = policySelect.options[policySelect.selectedIndex];
    const calcType = selectedOption.dataset.calcType;
    const percentValue = parseFloat(selectedOption.dataset.percent) || 0;
    const flatAmount = parseFloat(selectedOption.dataset.flat) || 0;
    const amount = parseFloat(amountInput.value) || 0;
    
    let calculated = 0;
    let previewHtml = '';
    
    if (calcType === 'percent') {
        calculated = (amount * percentValue) / 100;
        previewHtml = `
            <div class="mb-2">
                <strong>Loại:</strong> Phần trăm (${percentValue}%)
            </div>
            <div class="mb-2">
                <strong>Số tiền gốc:</strong> ${amount.toLocaleString()} VND
            </div>
            <div class="mb-2">
                <strong>Hoa hồng:</strong> ${calculated.toLocaleString()} VND
            </div>
        `;
    } else if (calcType === 'flat') {
        calculated = flatAmount;
        previewHtml = `
            <div class="mb-2">
                <strong>Loại:</strong> Số tiền cố định
            </div>
            <div class="mb-2">
                <strong>Số tiền gốc:</strong> ${amount.toLocaleString()} VND
            </div>
            <div class="mb-2">
                <strong>Hoa hồng:</strong> ${calculated.toLocaleString()} VND
            </div>
        `;
    } else {
        previewHtml = '<p class="text-muted">Loại tính toán bậc thang chưa được hỗ trợ</p>';
    }
    
    calculatedInput.value = calculated;
    if (!totalInput.value) {
        totalInput.value = calculated;
    }
    preview.innerHTML = previewHtml;
}

// Form submission with loading state
function submitForm() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading notification
    const loadingToast = Notify.toast({
        title: 'Đang tạo sự kiện...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 0
    });

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';

    // Submit form
    form.submit();
}

// Form validation with notifications
function validateForm() {
    const requiredFields = [
        { id: 'agent_id', name: 'Nhân viên' },
        { id: 'policy_id', name: 'Chính sách hoa hồng' },
        { id: 'trigger_event', name: 'Sự kiện kích hoạt' },
        { id: 'occurred_at', name: 'Ngày xảy ra' },
        { id: 'amount_base', name: 'Số tiền gốc' }
    ];

    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        if (!element.value.trim()) {
            Notify.warning(`Vui lòng nhập ${field.name.toLowerCase()}.`, 'Thiếu thông tin!');
            element.focus();
            return false;
        }
    }

    // Validate amount
    const amount = parseFloat(document.getElementById('amount_base').value);
    if (amount <= 0) {
        Notify.warning('Số tiền gốc phải lớn hơn 0.', 'Dữ liệu không hợp lệ!');
        document.getElementById('amount_base').focus();
        return false;
    }

    // Validate date
    const occurredAt = new Date(document.getElementById('occurred_at').value);
    const now = new Date();
    if (occurredAt > now) {
        Notify.warning('Ngày xảy ra không thể lớn hơn ngày hiện tại.', 'Dữ liệu không hợp lệ!');
        document.getElementById('occurred_at').focus();
        return false;
    }

    return true;
}

// Cancel form function
function cancelForm() {
    Notify.confirm('Bạn có chắc chắn muốn hủy tạo sự kiện hoa hồng? Dữ liệu đã nhập sẽ bị mất.', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang chuyển hướng...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 1000
        });

        // Navigate back
        window.location.href = '{{ route('manager.commission-events.index') }}';
    });
}

// Event listeners
document.getElementById('policy_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('trigger_event').value = selectedOption.dataset.trigger;
    }
    calculateCommission();
});

document.getElementById('amount_base').addEventListener('input', calculateCommission);

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const cancelBtn = form.querySelector('a[href*="index"]');

    // Override form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            submitForm();
        }
    });

    // Override cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cancelForm();
        });
    }
});
</script>
@endpush
