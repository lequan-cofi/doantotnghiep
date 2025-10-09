@extends('layouts.manager_dashboard')

@section('title', 'Quản lý hợp đồng')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Quản lý hợp đồng</h1>
                <p class="text-muted mb-0">Danh sách tất cả hợp đồng thuê trong hệ thống</p>
            </div>
            <a href="{{ route('manager.leases.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm hợp đồng mới
            </a>
        </div>

        <!-- Session Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('manager.leases.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Số hợp đồng, tên khách thuê, BĐS..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Nháp</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bất động sản</label>
                            <select name="property_id" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                    {{ $property->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Khách thuê</label>
                            <select name="tenant_id" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Nhân viên</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('manager.leases.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Leases Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Số hợp đồng</th>
                                <th>Bất động sản</th>
                                <th>Phòng</th>
                                <th>Khách thuê</th>
                                <th>Nhân viên</th>
                                <th>Thời hạn</th>
                                <th>Tiền thuê</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($leases && $leases->count() > 0)
                                @foreach($leases as $lease)
                                <tr>
                                    <td>{{ $lease->id }}</td>
                                    <td>
                                        @if($lease->contract_no)
                                            <code class="bg-light px-2 py-1 rounded">{{ $lease->contract_no }}</code>
                                        @else
                                            <span class="text-muted">Chưa có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lease->unit && $lease->unit->property)
                                            <strong>{{ $lease->unit->property->name }}</strong>
                                            @if($lease->unit->property->propertyType)
                                                <br><small class="text-muted">{{ $lease->unit->property->propertyType->name }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lease->unit)
                                            <span class="badge bg-info">{{ $lease->unit->code ?? 'Phòng ' . $lease->unit->id }}</span>
                                            @if($lease->unit->floor)
                                                <br><small class="text-muted">Tầng {{ $lease->unit->floor }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lease->tenant)
                                            <div class="d-flex flex-column">
                                                <strong>{{ $lease->tenant->full_name }}</strong>
                                                @if($lease->tenant->phone)
                                                    <small class="text-muted">{{ $lease->tenant->phone }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lease->agent)
                                            {{ $lease->agent->full_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($lease->start_date)
                                                <strong>{{ $lease->start_date->format('d/m/Y') }}</strong>
                                            @endif
                                            @if($lease->end_date)
                                                <small class="text-muted">Đến {{ $lease->end_date->format('d/m/Y') }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($lease->rent_amount)
                                            <strong>{{ number_format($lease->rent_amount, 0, ',', '.') }}đ</strong>
                                            @if($lease->deposit_amount)
                                                <br><small class="text-muted">Cọc: {{ number_format($lease->deposit_amount, 0, ',', '.') }}đ</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lease->status === 'active')
                                            <span class="badge bg-success">Đang hoạt động</span>
                                        @elseif($lease->status === 'draft')
                                            <span class="badge bg-warning">Nháp</span>
                                        @elseif($lease->status === 'terminated')
                                            <span class="badge bg-danger">Đã chấm dứt</span>
                                        @elseif($lease->status === 'expired')
                                            <span class="badge bg-secondary">Đã hết hạn</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $lease->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manager.leases.show', $lease->id) }}" class="btn btn-sm btn-outline-primary" title="Xem">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('manager.leases.edit', $lease->id) }}" class="btn btn-sm btn-outline-warning" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLease({{ $lease->id }}, '{{ $lease->contract_no ?? 'Hợp đồng #' . $lease->id }}')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-file-contract fa-3x mb-3 text-muted"></i>
                                        <br>Chưa có hợp đồng nào
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($leases && method_exists($leases, 'links'))
                <div class="mt-3">
                    {{ $leases->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
function deleteLease(id, name) {
    // Sử dụng notification system
    Notify.confirmDelete(`hợp đồng "${name}"`, function() {
        // Hiển thị loading toast
        const loadingToast = Notify.toast({
            title: 'Đang xử lý...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0 // Không tự động đóng
        });
        
        fetch(`/manager/leases/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
        .then(response => {
            // Đóng loading toast
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                Notify.success(data.message, 'Xóa thành công!');
                
                // Reload trang sau 1.5 giây
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Hiển thị thông báo lỗi
                Notify.error(data.message, 'Không thể xóa hợp đồng');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hiển thị thông báo lỗi
            Notify.error('Có lỗi xảy ra khi xóa hợp đồng. Vui lòng thử lại.', 'Lỗi hệ thống');
        });
    });
}
</script>
@endpush
