@extends('layouts.agent_dashboard')

@section('title', 'Quản lý phòng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-door-open me-2"></i>Quản lý phòng
                    </h1>
                    <p class="text-muted mb-0">Quản lý các phòng trong bất động sản được gán</p>
                </div>
                <a href="{{ route('agent.units.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Thêm phòng mới
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Notify.success('{{ session('success') }}');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Notify.error('{{ session('error') }}');
            });
        </script>
    @endif

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('agent.units.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="property_id" class="form-label">Bất động sản</label>
                    <select class="form-select" id="property_id" name="property_id">
                        <option value="">Tất cả bất động sản</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ $selectedProperty == $property->id ? 'selected' : '' }}>
                                {{ $property->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="available" {{ $selectedStatus == 'available' ? 'selected' : '' }}>Có sẵn</option>
                        <option value="reserved" {{ $selectedStatus == 'reserved' ? 'selected' : '' }}>Đã đặt</option>
                        <option value="occupied" {{ $selectedStatus == 'occupied' ? 'selected' : '' }}>Đã thuê</option>
                        <option value="maintenance" {{ $selectedStatus == 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $search }}" placeholder="Nhập mã phòng...">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <a href="{{ route('agent.units.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Units Table -->
    @if($units->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'id', 'sort_order' => request('sort_by') == 'id' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        ID
                                        @if(request('sort_by') == 'id')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="border-0">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'code', 'sort_order' => request('sort_by') == 'code' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Mã phòng
                                        @if(request('sort_by') == 'code')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="border-0">Bất động sản</th>
                                <th class="border-0">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'floor', 'sort_order' => request('sort_by') == 'floor' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Tầng
                                        @if(request('sort_by') == 'floor')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="border-0">Diện tích</th>
                                <th class="border-0">Loại</th>
                                <th class="border-0">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') == 'status' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Trạng thái
                                        @if(request('sort_by') == 'status')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="border-0">Giá thuê</th>
                                <th class="border-0">Người thuê</th>
                                <th class="border-0 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td class="align-middle">
                                        <span class="fw-bold text-primary">#{{ $unit->id }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            @if($unit->images && count($unit->images) > 0)
                                                <img src="{{ asset('storage/' . $unit->images[0]) }}" 
                                                     class="rounded me-2" 
                                                     alt="{{ $unit->code }}"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-home text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $unit->code }}</div>
                                                <small class="text-muted">Số người: {{ $unit->max_occupancy }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            <div class="fw-bold">{{ $unit->property->name }}</div>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $unit->property->location->address ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-light text-dark">{{ $unit->floor ?? 'N/A' }}</span>
                                    </td>
                                    <td class="align-middle">
                                        {{ $unit->area_m2 ? $unit->area_m2 . ' m²' : 'N/A' }}
                                    </td>
                                    <td class="align-middle">
                                        @switch($unit->unit_type)
                                            @case('room')
                                                <span class="badge bg-primary">Phòng</span>
                                                @break
                                            @case('apartment')
                                                <span class="badge bg-info">Căn hộ</span>
                                                @break
                                            @case('dorm')
                                                <span class="badge bg-warning">Ký túc xá</span>
                                                @break
                                            @case('shared')
                                                <span class="badge bg-secondary">Chung</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="align-middle">
                                        @switch($unit->status)
                                            @case('available')
                                                <span class="badge bg-success">Có sẵn</span>
                                                @break
                                            @case('reserved')
                                                <span class="badge bg-warning">Đã đặt</span>
                                                @break
                                            @case('occupied')
                                                <span class="badge bg-danger">Đã thuê</span>
                                                @break
                                            @case('maintenance')
                                                <span class="badge bg-info">Bảo trì</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            <div class="fw-bold text-primary">{{ number_format($unit->base_rent) }}đ</div>
                                            @if($unit->deposit_amount > 0)
                                                <small class="text-muted">Cọc: {{ number_format($unit->deposit_amount) }}đ</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($unit->is_rented && $unit->current_lease)
                                            <div>
                                                <div class="fw-bold">{{ $unit->current_lease->tenant->full_name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $unit->current_lease->tenant->phone ?? '' }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Chưa có</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.units.show', $unit->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('agent.units.edit', $unit->id) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('agent.units.destroy', $unit->id) }}" 
                                                  class="d-inline"
                                                  id="delete-form-{{ $unit->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Xóa"
                                                        onclick="confirmDeleteUnit({{ $unit->id }}, '{{ $unit->code }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="empty-state">
                <i class="fas fa-door-open fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Chưa có phòng nào</h4>
                <p class="text-muted mb-4">
                    @if($properties->count() > 0)
                        Bạn chưa tạo phòng nào cho các bất động sản được gán.
                    @else
                        Bạn chưa được gán quản lý bất động sản nào.
                    @endif
                </p>
                @if($properties->count() > 0)
                    <a href="{{ route('agent.units.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo phòng đầu tiên
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/units.css') }}">
@endpush

@push('scripts')
<script>
function confirmDeleteUnit(unitId, unitCode) {
    Notify.confirmDelete(
        `phòng "${unitCode}"`,
        function() {
            // Show loading notification
            Notify.info('Đang xóa phòng...');
            
            // Submit the form
            const form = document.getElementById(`delete-form-${unitId}`);
            if (form) {
                form.submit();
            }
        }
    );
}
</script>
@endpush
