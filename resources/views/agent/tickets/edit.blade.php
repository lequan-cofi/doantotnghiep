@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa Ticket #' . $ticket->id)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Ticket #{{ $ticket->id }}</h1>
            <p class="mb-0">{{ $ticket->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('agent.tickets.show', $ticket->id) }}" class="btn btn-outline-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="{{ route('agent.tickets.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <form id="ticketForm" method="POST" action="{{ route('agent.tickets.update', $ticket->id) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin Ticket</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title', $ticket->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Độ ưu tiên <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="">Chọn độ ưu tiên</option>
                                    <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>Thấp</option>
                                    <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                    <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>Cao</option>
                                    <option value="urgent" {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>Khẩn cấp</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>Mở</option>
                                    <option value="in_progress" {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                                    <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>Đã giải quyết</option>
                                    <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                                    <option value="cancelled" {{ old('status', $ticket->status) == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Người phụ trách</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                        id="assigned_to" name="assigned_to">
                                    <option value="">Chọn người phụ trách</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $ticket->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Mô tả chi tiết về vấn đề cần xử lý...">{{ old('description', $ticket->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Property & Unit Selection -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Liên kết</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="property_id" class="form-label">Tòa nhà</label>
                            <select class="form-select @error('property_id') is-invalid @enderror" 
                                    id="property_id" name="property_id">
                                <option value="">Chọn tòa nhà (tùy chọn)</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', $ticket->unit->property_id ?? '') == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Phòng</label>
                            <select class="form-select @error('unit_id') is-invalid @enderror" 
                                    id="unit_id" name="unit_id" {{ !$ticket->unit ? 'disabled' : '' }}>
                                <option value="">Chọn phòng (tùy chọn)</option>
                                @if($ticket->unit)
                                    <option value="{{ $ticket->unit->id }}" selected>{{ $ticket->unit->code }}</option>
                                @endif
                            </select>
                            @error('unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lease_id" class="form-label">Hợp đồng</label>
                            <select class="form-select @error('lease_id') is-invalid @enderror" 
                                    id="lease_id" name="lease_id" {{ !$ticket->unit ? 'disabled' : '' }}>
                                <option value="">Chọn hợp đồng (tùy chọn)</option>
                                @if($ticket->lease)
                                    <option value="{{ $ticket->lease->id }}" selected>
                                        {{ $ticket->lease->contract_no ?: 'HD#' . $ticket->lease->id }} - {{ $ticket->lease->tenant->full_name ?? 'Chưa có khách thuê' }}
                                    </option>
                                @endif
                            </select>
                            @error('lease_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Current Info -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin hiện tại</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Người tạo:</small><br>
                            <strong>{{ $ticket->createdBy->full_name ?? 'N/A' }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Ngày tạo:</small><br>
                            <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted">Cập nhật cuối:</small><br>
                            <strong>{{ $ticket->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật Ticket
                            </button>
                            <a href="{{ route('agent.tickets.show', $ticket->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
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
    const form = document.getElementById('ticketForm');
    const propertySelect = document.getElementById('property_id');
    const unitSelect = document.getElementById('unit_id');
    const leaseSelect = document.getElementById('lease_id');
    
    // Cascading dropdowns
    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;
        
        // Reset unit and lease selects
        unitSelect.innerHTML = '<option value="">Chọn phòng (tùy chọn)</option>';
        leaseSelect.innerHTML = '<option value="">Chọn hợp đồng (tùy chọn)</option>';
        leaseSelect.disabled = true;
        
        if (propertyId) {
            // Fetch units for selected property
            fetch(`/agent/api/tickets/properties/${propertyId}/units`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(units => {
                units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.code;
                    unitSelect.appendChild(option);
                });
                unitSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching units:', error);
                Notify.error('Có lỗi khi tải danh sách phòng', 'Lỗi');
            });
        } else {
            unitSelect.disabled = true;
        }
    });
    
    unitSelect.addEventListener('change', function() {
        const unitId = this.value;
        
        // Reset lease select
        leaseSelect.innerHTML = '<option value="">Chọn hợp đồng (tùy chọn)</option>';
        
        if (unitId) {
            // Fetch leases for selected unit
            fetch(`/agent/api/tickets/units/${unitId}/leases`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(leases => {
                leases.forEach(lease => {
                    const option = document.createElement('option');
                    option.value = lease.id;
                    option.textContent = `${lease.contract_no || 'HD#' + lease.id} - ${lease.tenant ? lease.tenant.full_name : 'Chưa có khách thuê'}`;
                    leaseSelect.appendChild(option);
                });
                leaseSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching leases:', error);
                Notify.error('Có lỗi khi tải danh sách hợp đồng', 'Lỗi');
            });
        } else {
            leaseSelect.disabled = true;
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
        submitBtn.disabled = true;
        
        // Submit form
        const formData = new FormData(form);
        
        fetch(form.action, {
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
                Notify.success(data.message, 'Cập nhật thành công!');
                
                // Redirect after success
                setTimeout(() => {
                    window.location.href = '{{ route("agent.tickets.show", $ticket->id) }}';
                }, 1500);
            } else {
                Notify.error(data.message, 'Lỗi cập nhật ticket');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Có lỗi xảy ra khi cập nhật ticket. Vui lòng thử lại.', 'Lỗi hệ thống');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>
@endpush
