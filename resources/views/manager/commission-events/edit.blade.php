@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa Sự kiện Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Sự kiện Hoa hồng</h1>
            <p class="mb-0">#{{ $commissionEvent->id }} - {{ $commissionEvent->policy->title }}</p>
        </div>
        <div>
            <a href="{{ route('manager.commission-events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('manager.commission-events.show', $commissionEvent->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
        </div>
    </div>

    <form action="{{ route('manager.commission-events.update', $commissionEvent->id) }}" method="POST">
        @csrf
        @method('PUT')
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
                                        <option value="{{ $agent->id }}" {{ old('agent_id', $commissionEvent->agent_id) == $agent->id ? 'selected' : '' }}>
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
                                                {{ old('policy_id', $commissionEvent->policy_id) == $policy->id ? 'selected' : '' }}>
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
                                        <option value="{{ $key }}" {{ old('trigger_event', $commissionEvent->trigger_event) == $key ? 'selected' : '' }}>
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
                                       name="occurred_at" value="{{ old('occurred_at', $commissionEvent->occurred_at->format('Y-m-d\TH:i')) }}" required>
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
                                        <option value="{{ $lease->id }}" {{ old('lease_id', $commissionEvent->lease_id) == $lease->id ? 'selected' : '' }}>
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
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $commissionEvent->unit_id) == $unit->id ? 'selected' : '' }}>
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
                                       name="amount_base" id="amount_base" value="{{ old('amount_base', $commissionEvent->amount_base) }}" 
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
                                       name="commission_total" id="commission_total" value="{{ old('commission_total', $commissionEvent->commission_total) }}" 
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
                                        <option value="{{ $key }}" {{ old('status', $commissionEvent->status) == $key ? 'selected' : '' }}>
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
                            <i class="fas fa-save"></i> Cập nhật sự kiện
                        </button>
                        <a href="{{ route('manager.commission-events.show', $commissionEvent->id) }}" class="btn btn-secondary w-100">
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
// Calculate commission when policy or amount changes
function calculateCommission() {
    const policySelect = document.getElementById('policy_id');
    const amountInput = document.getElementById('amount_base');
    const calculatedInput = document.getElementById('calculated_commission');
    const totalInput = document.getElementById('commission_total');
    const preview = document.getElementById('commission-preview');
    
    if (!policySelect.value || !amountInput.value) {
        calculatedInput.value = '';
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
    preview.innerHTML = previewHtml;
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateCommission();
});
</script>
@endpush
