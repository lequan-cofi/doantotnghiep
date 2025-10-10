@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa Hợp đồng')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Chỉnh sửa Hợp đồng</h1>
                <p>Cập nhật thông tin hợp đồng thuê</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.leases.show', $lease->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="card">
            <div class="card-body">
                <form id="leaseForm" method="POST" action="{{ route('manager.leases.update', $lease->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin cơ bản</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                <select name="property_id" id="propertySelect" class="form-select" required>
                                    <option value="">Chọn bất động sản</option>
                                    @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" {{ $property->id == old('property_id', $lease->unit->property_id) ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phòng <span class="text-danger">*</span></label>
                                <select name="unit_id" id="unitSelect" class="form-select" required>
                                    <option value="">Chọn phòng</option>
                                    @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->id == old('unit_id', $lease->unit_id) ? 'selected' : '' }}>
                                        {{ $unit->code ?? 'Phòng ' . $unit->id }}
                                        @if ($unit->floor) (Tầng {{ $unit->floor }}) @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Khách thuê <span class="text-danger">*</span></label>
                                <select name="tenant_id" class="form-select" required>
                                    <option value="">Chọn khách thuê</option>
                                    @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" {{ $tenant->id == old('tenant_id', $lease->tenant_id) ? 'selected' : '' }}>
                                        {{ $tenant->full_name }} ({{ $tenant->email }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nhân viên phụ trách</label>
                                <select name="agent_id" class="form-select">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ $agent->id == old('agent_id', $lease->agent_id) ? 'selected' : '' }}>
                                        {{ $agent->full_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số hợp đồng</label>
                                <div class="input-group">
                                    <input type="text" name="contract_no" class="form-control" 
                                           value="{{ old('contract_no', $lease->contract_no) }}" 
                                           placeholder="Nhập số hợp đồng">
                                    <span class="input-group-text">
                                        <i class="fas fa-file-contract text-primary"></i>
                                    </span>
                                </div>
                                <small class="form-text text-muted">Mã hợp đồng hiện tại: {{ $lease->contract_no ?? 'Chưa có' }}</small>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin hợp đồng</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" 
                                           value="{{ old('start_date', $lease->start_date ? $lease->start_date->format('Y-m-d') : '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" 
                                           value="{{ old('end_date', $lease->end_date ? $lease->end_date->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tiền thuê/tháng <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="rent_amount" class="form-control" 
                                           value="{{ old('rent_amount', $lease->rent_amount) }}" 
                                           placeholder="0" min="0" step="1000" required>
                                    <span class="input-group-text">VND</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tiền cọc</label>
                                <div class="input-group">
                                    <input type="number" name="deposit_amount" class="form-control" 
                                           value="{{ old('deposit_amount', $lease->deposit_amount) }}" 
                                           placeholder="0" min="0" step="1000">
                                    <span class="input-group-text">VND</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày thanh toán hàng tháng</label>
                                <select name="billing_day" class="form-select">
                                    @for ($i = 1; $i <= 28; $i++)
                                    <option value="{{ $i }}" {{ $i == old('billing_day', $lease->billing_day) ? 'selected' : '' }}>
                                        Ngày {{ $i }}
                                    </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" {{ old('status', $lease->status) == 'draft' ? 'selected' : '' }}>Nháp</option>
                                    <option value="active" {{ old('status', $lease->status) == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                    <option value="terminated" {{ old('status', $lease->status) == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                    <option value="expired" {{ old('status', $lease->status) == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày ký hợp đồng</label>
                                <input type="date" name="signed_at" class="form-control" 
                                       value="{{ old('signed_at', $lease->signed_at ? $lease->signed_at->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Services Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">Dịch vụ kèm theo</h5>
                            <div id="servicesContainer">
                                @if ($lease->leaseServices->count() > 0)
                                    @foreach ($lease->leaseServices as $index => $leaseService)
                                    <div class="service-item row g-3 mb-3">
                                        <div class="col-md-6">
                                            <select name="services[{{ $index }}][service_id]" class="form-select">
                                                <option value="">Chọn dịch vụ</option>
                                                @foreach ($services as $service)
                                                <option value="{{ $service->id }}" {{ $service->id == $leaseService->service_id ? 'selected' : '' }}>
                                                    {{ $service->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input type="number" name="services[{{ $index }}][price]" class="form-control" 
                                                       value="{{ $leaseService->price }}" placeholder="0" min="0" step="1000">
                                                <span class="input-group-text">VND</span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="service-item row g-3 mb-3">
                                    <div class="col-md-6">
                                        <select name="services[0][service_id]" class="form-select">
                                            <option value="">Chọn dịch vụ</option>
                                            @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="number" name="services[0][price]" class="form-control" placeholder="0" min="0" step="1000">
                                            <span class="input-group-text">VND</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <button type="button" id="addService" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus"></i> Thêm dịch vụ
                            </button>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('manager.leases.show', $lease->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Cập nhật hợp đồng
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('propertySelect');
    const unitSelect = document.getElementById('unitSelect');
    const addServiceBtn = document.getElementById('addService');
    const servicesContainer = document.getElementById('servicesContainer');
    let serviceIndex = {{ $lease->leaseServices->count() }};

    // Property change handler
    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;
        
        if (!propertyId) {
            unitSelect.innerHTML = '<option value="">Chọn bất động sản trước</option>';
            unitSelect.disabled = true;
            return;
        }

        // Fetch units for selected property
        fetch(`/manager/api/properties/${propertyId}/units`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
                if (data.error) {
                    throw new Error(data.error);
                }
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.code || `Phòng ${unit.id}`;
                    if (unit.floor) {
                        option.textContent += ` (Tầng ${unit.floor})`;
                    }
                    
                    // Kiểm tra nếu phòng đã có hợp đồng hoạt động (trừ phòng hiện tại của hợp đồng này)
                    const currentUnitId = {{ $lease->unit_id }};
                    if (unit.has_active_lease && unit.id !== currentUnitId) {
                        option.textContent += ' - Đã có hợp đồng hoạt động';
                        option.disabled = true;
                        option.style.color = '#dc3545';
                    }
                    
                    unitSelect.appendChild(option);
                });
                unitSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching units:', error);
                unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                Notify.error('Không thể tải danh sách phòng. Vui lòng thử lại.', 'Lỗi tải dữ liệu');
            });
    });

    // Add service handler
    addServiceBtn.addEventListener('click', function() {
        const serviceItem = document.createElement('div');
        serviceItem.className = 'service-item row g-3 mb-3';
        serviceItem.innerHTML = `
            <div class="col-md-6">
                <select name="services[${serviceIndex}][service_id]" class="form-select">
                    <option value="">Chọn dịch vụ</option>
                    @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="number" name="services[${serviceIndex}][price]" class="form-control" placeholder="0" min="0" step="1000">
                    <span class="input-group-text">VND</span>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        servicesContainer.appendChild(serviceItem);
        serviceIndex++;
    });

    // Remove service handler
    servicesContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-service')) {
            e.target.closest('.service-item').remove();
        }
    });

    // Form submission
    document.getElementById('leaseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Kiểm tra phòng đã có hợp đồng hoạt động (trừ phòng hiện tại)
        const selectedUnit = unitSelect.options[unitSelect.selectedIndex];
        const currentUnitId = {{ $lease->unit_id }};
        if (selectedUnit && selectedUnit.disabled && selectedUnit.value != currentUnitId) {
            Notify.error('Phòng này đã có hợp đồng hoạt động. Vui lòng chọn phòng khác hoặc chấm dứt hợp đồng hiện tại trước.', 'Không thể cập nhật hợp đồng');
            return;
        }
        
        if (window.Preloader) {
            window.Preloader.show();
        }

        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Thành công!');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể cập nhật hợp đồng: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
});
</script>
@endpush
@endsection
