@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa hợp đồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa hợp đồng
                    </h1>
                    <p class="text-muted mb-0">{{ $lease->contract_no ?? 'Hợp đồng #' . $lease->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.leases.show', $lease->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-contract me-2"></i>Thông tin hợp đồng
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.leases.update', $lease->id) }}" method="POST" id="leaseForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Property Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="property_id" class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                <select class="form-select @error('property_id') is-invalid @enderror" id="property_id" name="property_id" required>
                                    <option value="">Chọn bất động sản</option>
                                    @forelse($properties as $property)
                                        <option value="{{ $property->id }}" 
                                            {{ old('property_id', $lease->unit->property_id) == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Không có bất động sản nào có phòng trống</option>
                                    @endforelse
                                </select>
                                @error('property_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($properties->isEmpty())
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Hiện tại không có bất động sản nào có phòng trống. Bất động sản hiện tại vẫn được hiển thị để chỉnh sửa.
                                    </div>
                                @endif
                            </div>

                            <!-- Unit Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="unit_id" class="form-label">Phòng <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                    <option value="">Chọn phòng</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" 
                                            {{ old('unit_id', $lease->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->code }} - {{ $unit->unit_type }} ({{ $unit->area_m2 }}m²)
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Existing Deposits Display -->
                                <div id="existing-deposits" class="mt-3" style="display: none;">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white py-2">
                                            <h6 class="mb-0">
                                                <i class="fas fa-money-bill-wave me-2"></i>Cọc hiện có của phòng
                                            </h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div id="deposits-content">
                                                <!-- Deposits will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tenant Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="tenant_id" class="form-label">Khách thuê <span class="text-danger">*</span></label>
                                <select class="form-select @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id" required>
                                    <option value="">Chọn khách thuê</option>
                                    @foreach($tenants as $tenant)
                                        <option value="{{ $tenant->id }}" 
                                            {{ old('tenant_id', $lease->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                            {{ $tenant->full_name }} ({{ $tenant->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tenant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contract Number -->
                            <div class="col-md-6 mb-3">
                                <label for="contract_no" class="form-label">Mã hợp đồng</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('contract_no') is-invalid @enderror" 
                                           id="contract_no" name="contract_no" value="{{ old('contract_no', $lease->contract_no) }}">
                                    <button type="button" class="btn btn-outline-secondary" id="generateContractNo">
                                        <i class="fas fa-sync-alt"></i> Tự động
                                    </button>
                                </div>
                                @error('contract_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Start Date -->
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" 
                                       value="{{ old('start_date', $lease->start_date ? $lease->start_date->format('Y-m-d') : '') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" 
                                       value="{{ old('end_date', $lease->end_date ? $lease->end_date->format('Y-m-d') : '') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Rent Amount -->
                            <div class="col-md-6 mb-3">
                                <label for="rent_amount" class="form-label">Tiền thuê/tháng <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('rent_amount') is-invalid @enderror" 
                                           id="rent_amount" name="rent_amount" 
                                           value="{{ old('rent_amount', number_format($lease->rent_amount)) }}" 
                                           placeholder="Nhập số tiền" required>
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('rent_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Deposit Amount -->
                            <div class="col-md-6 mb-3">
                                <label for="deposit_amount" class="form-label">Tiền cọc</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('deposit_amount') is-invalid @enderror" 
                                           id="deposit_amount" name="deposit_amount" 
                                           value="{{ old('deposit_amount', $lease->deposit_amount ? number_format($lease->deposit_amount) : '') }}" 
                                           placeholder="Nhập số tiền">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('deposit_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Billing Day -->
                            <div class="col-md-6 mb-3">
                                <label for="billing_day" class="form-label">Ngày tạo hóa đơn</label>
                                <select class="form-select @error('billing_day') is-invalid @enderror" id="billing_day" name="billing_day">
                                    @for($i = 1; $i <= 28; $i++)
                                        <option value="{{ $i }}" {{ old('billing_day', $lease->billing_day) == $i ? 'selected' : '' }}>
                                            Ngày {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <div class="form-text">Ngày trong tháng để tạo hóa đơn</div>
                                @error('billing_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Cycle Settings -->
                            <div class="col-md-6 mb-3">
                                <label for="lease_payment_cycle" class="form-label">Chu kỳ thanh toán</label>
                                <select class="form-select @error('lease_payment_cycle') is-invalid @enderror" id="lease_payment_cycle" name="lease_payment_cycle">
                                    <option value="">-- Chọn chu kỳ --</option>
                                    <option value="monthly" {{ old('lease_payment_cycle', $lease->lease_payment_cycle) == 'monthly' ? 'selected' : '' }}>Hàng tháng</option>
                                    <option value="quarterly" {{ old('lease_payment_cycle', $lease->lease_payment_cycle) == 'quarterly' ? 'selected' : '' }}>Hàng quý</option>
                                    <option value="yearly" {{ old('lease_payment_cycle', $lease->lease_payment_cycle) == 'yearly' ? 'selected' : '' }}>Hàng năm</option>
                                    <option value="custom" {{ old('lease_payment_cycle', $lease->lease_payment_cycle) == 'custom' ? 'selected' : '' }}>Tùy chỉnh (nhập số tháng)</option>
                                </select>
                                @error('lease_payment_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3" id="lease_custom_months_field" style="display: {{ old('lease_payment_cycle', $lease->lease_payment_cycle) == 'custom' ? 'block' : 'none' }};">
                                <label for="lease_custom_months" class="form-label">Số tháng tùy chỉnh</label>
                                <input type="number" class="form-control @error('lease_custom_months') is-invalid @enderror" 
                                       id="lease_custom_months" name="lease_custom_months" 
                                       value="{{ old('lease_custom_months', $lease->lease_custom_months) }}" min="1" max="60" 
                                       placeholder="Nhập số tháng (1-60)">
                                <div class="form-text">Số tháng cho chu kỳ thanh toán tùy chỉnh (1-60)</div>
                                @error('lease_custom_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lease_payment_day" class="form-label">Hạn thanh toán</label>
                                <select class="form-select @error('lease_payment_day') is-invalid @enderror" id="lease_payment_day" name="lease_payment_day">
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ old('lease_payment_day', $lease->lease_payment_day) == $i ? 'selected' : '' }}>
                                            Ngày {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <div class="form-text">Ngày hạn thanh toán trong chu kỳ</div>
                                @error('lease_payment_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="lease_payment_notes" class="form-label">Ghi chú chu kỳ thanh toán</label>
                                <textarea class="form-control @error('lease_payment_notes') is-invalid @enderror" 
                                          id="lease_payment_notes" name="lease_payment_notes" rows="2" 
                                          placeholder="Ghi chú về chu kỳ thanh toán...">{{ old('lease_payment_notes', $lease->lease_payment_notes) }}</textarea>
                                @error('lease_payment_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="draft" {{ old('status', $lease->status) == 'draft' ? 'selected' : '' }}>Nháp</option>
                                    <option value="active" {{ old('status', $lease->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="terminated" {{ old('status', $lease->status) == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                    <option value="expired" {{ old('status', $lease->status) == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Signed Date -->
                            <div class="col-md-6 mb-3">
                                <label for="signed_at" class="form-label">Ngày ký hợp đồng</label>
                                <input type="datetime-local" class="form-control @error('signed_at') is-invalid @enderror" 
                                       id="signed_at" name="signed_at" 
                                       value="{{ old('signed_at', $lease->signed_at ? $lease->signed_at->format('Y-m-d\TH:i') : '') }}">
                                @error('signed_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Services Section -->
                        <hr>
                        <h6 class="mb-3">
                            <i class="fas fa-cogs me-2"></i>Dịch vụ bổ sung
                        </h6>
                        
                        <div id="services-container">
                            @if($lease->leaseServices->count() > 0)
                                @foreach($lease->leaseServices as $index => $leaseService)
                                    <div class="service-item row mb-3">
                                        <div class="col-md-6">
                                            <select class="form-select" name="services[{{ $index }}][service_id]">
                                                <option value="">Chọn dịch vụ</option>
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}" 
                                                        {{ $leaseService->service_id == $service->id ? 'selected' : '' }}>
                                                        {{ $service->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="services[{{ $index }}][price]" 
                                                       placeholder="Giá" step="0.01" min="0" value="{{ $leaseService->price }}">
                                                <span class="input-group-text">đ</span>
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
                                <div class="service-item row mb-3">
                                    <div class="col-md-6">
                                        <select class="form-select" name="services[0][service_id]">
                                            <option value="">Chọn dịch vụ</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="services[0][price]" placeholder="Giá" step="0.01" min="0">
                                            <span class="input-group-text">đ</span>
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
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-service">
                            <i class="fas fa-plus me-1"></i>Thêm dịch vụ
                        </button>

                        <!-- Form Actions -->
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.leases.show', $lease->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Cập nhật hợp đồng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let serviceIndex = {{ $lease->leaseServices->count() }};

    // Property change handler
    document.getElementById('property_id').addEventListener('change', function() {
        const propertyId = this.value;
        const unitSelect = document.getElementById('unit_id');
        
        unitSelect.innerHTML = '<option value="">Đang tải...</option>';
        
        // Hide existing deposits when property changes
        document.getElementById('existing-deposits').style.display = 'none';
        
        if (propertyId) {
            fetch(`/agent/api/leases/units/${propertyId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
                    
                    // Check if data is an array
                    if (Array.isArray(data)) {
                        data.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = `${unit.code} - ${unit.unit_type} (${unit.area_m2}m²)`;
                            // Keep current selection if it matches
                            if (unit.id == {{ $lease->unit_id }}) {
                                option.selected = true;
                            }
                            unitSelect.appendChild(option);
                        });
                        
                        if (data.length === 0) {
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'Không có phòng nào';
                            unitSelect.appendChild(option);
                        }
                    } else if (data.error) {
                        // Handle error response
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Lỗi: ' + data.error;
                        unitSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                    // Show error notification
                    if (typeof Notify !== 'undefined') {
                        Notify.error('Có lỗi xảy ra khi tải danh sách phòng', 'Lỗi tải dữ liệu');
                    }
                });
        } else {
            unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
        }
    });

    // Unit change handler - load existing deposits
    document.getElementById('unit_id').addEventListener('change', function() {
        const unitId = this.value;
        const depositsContainer = document.getElementById('existing-deposits');
        const depositsContent = document.getElementById('deposits-content');
        
        if (unitId) {
            // Show loading state
            depositsContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải thông tin cọc...</div>';
            depositsContainer.style.display = 'block';
            
            fetch(`/agent/api/leases/deposits/${unitId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.deposits.length > 0) {
                        let html = `
                            <div class="mb-3">
                                <strong>Tổng cọc hiện có: <span class="text-success">${data.total_amount_formatted}</span></strong>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Loại cọc</th>
                                            <th>Số tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Khách hàng</th>
                                            <th>Ngày tạo</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.deposits.forEach(deposit => {
                            const statusClass = deposit.payment_status === 'paid' ? 'success' : 
                                              deposit.payment_status === 'pending' ? 'warning' : 'secondary';
                            
                            html += `
                                <tr>
                                    <td>
                                        <span class="badge bg-info">${deposit.deposit_type_text}</span>
                                    </td>
                                    <td class="fw-bold">${deposit.amount_formatted}</td>
                                    <td>
                                        <span class="badge bg-${statusClass}">${deposit.payment_status_text}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>${deposit.tenant_name}</strong><br>
                                            <small class="text-muted">${deposit.tenant_phone}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>${deposit.created_at}</small>
                                        ${deposit.hold_until ? `<br><small class="text-muted">Hết hạn: ${deposit.hold_until}</small>` : ''}
                                    </td>
                                    <td>
                                        ${deposit.notes ? `<small>${deposit.notes}</small>` : '-'}
                                        ${deposit.reference_number ? `<br><small class="text-muted">Mã: ${deposit.reference_number}</small>` : ''}
                                    </td>
                                </tr>
                            `;
                        });
                        
                        html += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Khi chỉnh sửa hợp đồng, hãy xem xét các khoản cọc hiện có để tránh trùng lặp.
                            </div>
                        `;
                        
                        depositsContent.innerHTML = html;
                    } else {
                        depositsContent.innerHTML = `
                            <div class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                Phòng này chưa có cọc nào
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading deposits:', error);
                    depositsContent.innerHTML = `
                        <div class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Lỗi tải thông tin cọc
                        </div>
                    `;
                });
        } else {
            depositsContainer.style.display = 'none';
        }
    });

    // Generate contract number
    document.getElementById('generateContractNo').addEventListener('click', function() {
        fetch('/agent/api/leases/next-contract-number')
            .then(response => response.json())
            .then(data => {
                if (data.contract_no) {
                    document.getElementById('contract_no').value = data.contract_no;
                }
            })
            .catch(error => {
                console.error('Error generating contract number:', error);
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error('Có lỗi xảy ra khi tạo mã hợp đồng', 'Lỗi hệ thống');
                }
            });
    });

    // Add service
    document.getElementById('add-service').addEventListener('click', function() {
        const container = document.getElementById('services-container');
        const newService = document.createElement('div');
        newService.className = 'service-item row mb-3';
        newService.innerHTML = `
            <div class="col-md-6">
                <select class="form-select" name="services[${serviceIndex}][service_id]">
                    <option value="">Chọn dịch vụ</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="number" class="form-control" name="services[${serviceIndex}][price]" placeholder="Giá" step="0.01" min="0">
                    <span class="input-group-text">đ</span>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newService);
        serviceIndex++;
    });

    // Remove service
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-service')) {
            e.target.closest('.service-item').remove();
        }
    });

    // Payment cycle change handler
    document.getElementById('lease_payment_cycle').addEventListener('change', function() {
        const customMonthsField = document.getElementById('lease_custom_months_field');
        if (this.value === 'custom') {
            customMonthsField.style.display = 'block';
        } else {
            customMonthsField.style.display = 'none';
        }
    });

    // Trigger change event on page load to show/hide custom months field
    document.addEventListener('DOMContentLoaded', function() {
        const paymentCycleSelect = document.getElementById('lease_payment_cycle');
        if (paymentCycleSelect) {
            paymentCycleSelect.dispatchEvent(new Event('change'));
        }
    });

    // Format currency inputs
    function formatCurrency(input) {
        let value = input.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
        }
        input.value = value;
    }

    document.getElementById('rent_amount').addEventListener('input', function() {
        formatCurrency(this);
    });

    document.getElementById('deposit_amount').addEventListener('input', function() {
        formatCurrency(this);
    });

    // Trigger property change if pre-selected
    const propertySelect = document.getElementById('property_id');
    if (propertySelect.value) {
        propertySelect.dispatchEvent(new Event('change'));
    }

    // Trigger unit change to load deposits for current unit
    const unitSelect = document.getElementById('unit_id');
    if (unitSelect.value) {
        unitSelect.dispatchEvent(new Event('change'));
    }

    // Form submission notification
    const form = document.getElementById('leaseForm');
    form.addEventListener('submit', function(e) {
        // Show loading notification
        if (typeof Notify !== 'undefined') {
            Notify.info('Đang cập nhật hợp đồng...', 'Đang xử lý');
        }
    });

    // Show success notification if redirected with success message
    @if(session('success'))
        if (typeof Notify !== 'undefined') {
            Notify.success('{{ session('success') }}', 'Thành công');
        }
    @endif

    // Show error notification if redirected with error message
    @if(session('error'))
        if (typeof Notify !== 'undefined') {
            Notify.error('{{ session('error') }}', 'Lỗi');
        }
    @endif
});
</script>
@endpush
