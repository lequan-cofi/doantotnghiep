@extends('layouts.manager_dashboard')

@section('title', 'Cài đặt chu kỳ thanh toán')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Cài đặt chu kỳ thanh toán</li>
                    </ol>
                </div>
                <h4 class="page-title">Cài đặt chu kỳ thanh toán</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Organization Settings -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-office-building me-2"></i>
                        Cài đặt tổ chức: {{ $organization->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('manager.payment-cycle-settings.organization.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="org_payment_cycle" class="form-label">Chu kỳ thanh toán</label>
                            <select class="form-select" id="org_payment_cycle" name="org_payment_cycle">
                                <option value="">-- Chọn chu kỳ --</option>
                                @foreach($paymentCycleOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $organization->org_payment_cycle == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="org_custom_months_field" style="display: none;">
                            <label for="org_custom_months" class="form-label">Số tháng tùy chỉnh</label>
                            <input type="number" class="form-control" id="org_custom_months" name="org_custom_months" 
                                   value="{{ $organization->org_custom_months }}" min="1" max="60" 
                                   placeholder="Nhập số tháng (1-60)">
                            <div class="form-text">Số tháng cho chu kỳ thanh toán tùy chỉnh (1-60)</div>
                        </div>

                        <div class="mb-3">
                            <label for="org_payment_day" class="form-label">Ngày thanh toán</label>
                            <input type="number" class="form-control" id="org_payment_day" name="org_payment_day" 
                                   value="{{ $organization->org_payment_day }}" min="1" max="31" 
                                   placeholder="Nhập ngày (1-31)">
                            <div class="form-text">Ngày trong tháng để thanh toán (1-31)</div>
                        </div>

                        <div class="mb-3">
                            <label for="org_payment_notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="org_payment_notes" name="org_payment_notes" 
                                      rows="3" placeholder="Ghi chú về chu kỳ thanh toán">{{ $organization->org_payment_notes }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i>
                                Cập nhật tổ chức
                            </button>
                            
                            <button type="button" class="btn btn-success" onclick="applyToProperties()">
                                <i class="mdi mdi-arrow-down-bold me-1"></i>
                                Áp dụng cho tất cả BĐS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Properties List -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-home-city me-2"></i>
                        Danh sách bất động sản
                    </h5>
                </div>
                <div class="card-body">
                    @if($properties->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tên BĐS</th>
                                        <th>Chu kỳ</th>
                                        <th>Ngày</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($properties as $property)
                                        <tr>
                                            <td>
                                                <strong>{{ $property->name }}</strong>
                                            </td>
                                            <td>
                                                @if($property->prop_payment_cycle)
                                                    <span class="badge bg-info">
                                                        @if($property->prop_payment_cycle == 'custom' && $property->prop_custom_months)
                                                            {{ $property->prop_custom_months }} tháng
                                                        @else
                                                            {{ $paymentCycleOptions[$property->prop_payment_cycle] ?? $property->prop_payment_cycle }}
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-muted">Chưa cài đặt</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($property->prop_payment_day)
                                                    <span class="badge bg-secondary">{{ $property->prop_payment_day }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="showPropertySettings({{ $property->id }}, '{{ $property->name }}')">
                                                    <i class="mdi mdi-cog"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-home-city-outline text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Chưa có bất động sản nào</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Property Settings Modal -->
<div class="modal fade" id="propertySettingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cài đặt chu kỳ thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="propertySettingsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apply to Properties Modal -->
<div class="modal fade" id="applyToPropertiesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận áp dụng cài đặt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn áp dụng cài đặt chu kỳ thanh toán của tổ chức cho tất cả bất động sản?</p>
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert-circle me-2"></i>
                    Thao tác này sẽ ghi đè cài đặt hiện tại của tất cả bất động sản.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form action="{{ route('manager.payment-cycle-settings.apply-to-properties') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="apply_to_properties" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-check me-1"></i>
                        Xác nhận áp dụng
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showPropertySettings(propertyId, propertyName) {
    // Show loading
    const contentDiv = document.getElementById('propertySettingsContent');
    if (contentDiv) {
        contentDiv.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Đang tải dữ liệu...</p>
            </div>
        `;
    }
    
    const modalTitle = document.querySelector('#propertySettingsModal .modal-title');
    if (modalTitle) {
        modalTitle.textContent = `Cài đặt: ${propertyName}`;
    }
    
    const modal = document.getElementById('propertySettingsModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    // Load property settings and leases
    const url = `{{ route('manager.payment-cycle-settings.property.leases', ['propertyId' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', propertyId);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(response => {
            if (response.success) {
                const contentDiv = document.getElementById('propertySettingsContent');
                if (contentDiv) {
                    contentDiv.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thông tin bất động sản</h6>
                            <form action="{{ route('manager.payment-cycle-settings.property.update', ['propertyId' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', propertyId) method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label class="form-label">Chu kỳ thanh toán</label>
                                    <select class="form-select" name="prop_payment_cycle" id="prop_payment_cycle_modal">
                                        <option value="">-- Chọn chu kỳ --</option>
                                        <option value="monthly" ${response.property.prop_payment_cycle == 'monthly' ? 'selected' : ''}>Hàng tháng</option>
                                        <option value="quarterly" ${response.property.prop_payment_cycle == 'quarterly' ? 'selected' : ''}>Hàng quý</option>
                                        <option value="yearly" ${response.property.prop_payment_cycle == 'yearly' ? 'selected' : ''}>Hàng năm</option>
                                        <option value="custom" ${response.property.prop_payment_cycle == 'custom' ? 'selected' : ''}>Tùy chỉnh (nhập số tháng)</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="prop_custom_months_field_modal" style="display: none;">
                                    <label class="form-label">Số tháng tùy chỉnh</label>
                                    <input type="number" class="form-control" name="prop_custom_months" 
                                           value="${response.property.prop_custom_months || ''}" min="1" max="60" 
                                           placeholder="Nhập số tháng (1-60)">
                                    <div class="form-text">Số tháng cho chu kỳ thanh toán tùy chỉnh (1-60)</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngày thanh toán</label>
                                    <input type="number" class="form-control" name="prop_payment_day" 
                                           value="${response.property.prop_payment_day || ''}" min="1" max="31">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea class="form-control" name="prop_payment_notes" rows="2">${response.property.prop_payment_notes || ''}</textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-content-save me-1"></i>
                                        Cập nhật
                                    </button>
                                    
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Hợp đồng thuê (${response.leases.length})</h6>
                            <div style="max-height: 300px; overflow-y: auto;">
                                ${response.leases.length > 0 ? response.leases.map(lease => `
                                    <div class="border rounded p-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>${lease.contract_no}</strong><br>
                                                <small class="text-muted">${lease.unit_code} - ${lease.tenant_name}</small>
                                            </div>
                                            <div class="text-end">
                                                ${lease.lease_payment_cycle ? 
                                                    `<span class="badge bg-info">${
                                                        lease.lease_payment_cycle === 'custom' && lease.lease_custom_months ? 
                                                        lease.lease_custom_months + ' tháng' : 
                                                        getPaymentCycleLabel(lease.lease_payment_cycle)
                                                    }</span>` : 
                                                    '<span class="text-muted">Chưa cài</span>'
                                                }
                                                ${lease.lease_payment_day ? `<br><span class="badge bg-secondary">${lease.lease_payment_day}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `).join('') : '<p class="text-muted">Chưa có hợp đồng</p>'}
                            </div>
                        </div>
                    </div>
                    `;
                }
                
                // Trigger change event to show/hide custom months field
                const selectElement = document.getElementById('prop_payment_cycle_modal');
                if (selectElement) {
                    selectElement.dispatchEvent(new Event('change'));
                }
            }
        })
    .catch(error => {
        console.error('Error loading property settings:', error);
        const contentDiv = document.getElementById('propertySettingsContent');
        if (contentDiv) {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle me-2"></i>
                    Có lỗi xảy ra khi tải dữ liệu.
                </div>
            `;
        }
    });
}

function applyToProperties() {
    const modal = document.getElementById('applyToPropertiesModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}


function getPaymentCycleLabel(cycle) {
    const labels = {
        'monthly': 'Hàng tháng',
        'quarterly': 'Hàng quý',
        'yearly': 'Hàng năm',
        'custom': 'Tùy chỉnh'
    };
    return labels[cycle] || cycle;
}

// Toggle custom months field visibility
document.addEventListener('DOMContentLoaded', function() {
    // For organization form
    const orgSelect = document.getElementById('org_payment_cycle');
    const orgCustomField = document.getElementById('org_custom_months_field');
    
    if (orgSelect && orgCustomField) {
        orgSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                orgCustomField.style.display = 'block';
            } else {
                orgCustomField.style.display = 'none';
            }
        });
        
        // Trigger on page load
        orgSelect.dispatchEvent(new Event('change'));
    }
    
    // For property modal form (using event delegation)
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'prop_payment_cycle_modal') {
            const propCustomField = document.getElementById('prop_custom_months_field_modal');
            if (propCustomField) {
                if (e.target.value === 'custom') {
                    propCustomField.style.display = 'block';
                } else {
                    propCustomField.style.display = 'none';
                }
            }
        }
    });
});
</script>
@endpush
