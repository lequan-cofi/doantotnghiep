@extends('layouts.agent_dashboard')

@section('title', 'Quản lý phòng trọ')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Quản lý phòng trọ</h1>
                <p>Danh sách các phòng trọ trong các bất động sản bạn quản lý</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.units.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm phòng mới
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('agent.units.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="property_id" class="form-label">Bất động sản</label>
                        <select name="property_id" id="property_id" class="form-select">
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
                        <select name="status" id="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="available" {{ $selectedStatus == 'available' ? 'selected' : '' }}>Trống</option>
                            <option value="reserved" {{ $selectedStatus == 'reserved' ? 'selected' : '' }}>Đã đặt</option>
                            <option value="occupied" {{ $selectedStatus == 'occupied' ? 'selected' : '' }}>Đã thuê</option>
                            <option value="maintenance" {{ $selectedStatus == 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Nhập mã phòng..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($units->count() > 0)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã phòng</th>
                                    <th>Bất động sản</th>
                                    <th>Tầng</th>
                                    <th>Loại</th>
                                    <th>Diện tích</th>
                                    <th>Giá thuê</th>
                                    <th>Trạng thái</th>
                                    <th>Khách thuê</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($units as $unit)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $unit->code }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $unit->property->name }}</div>
                                            @if($unit->property->owner)
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ $unit->property->owner->full_name }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($unit->floor)
                                            <span class="badge bg-secondary">Tầng {{ $unit->floor }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($unit->unit_type)
                                            @case('room')
                                                <span class="badge bg-info">Phòng trọ</span>
                                                @break
                                            @case('apartment')
                                                <span class="badge bg-primary">Căn hộ</span>
                                                @break
                                            @case('dorm')
                                                <span class="badge bg-warning">Ký túc xá</span>
                                                @break
                                            @case('shared')
                                                <span class="badge bg-secondary">Chung</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($unit->area_m2)
                                            {{ $unit->area_m2 }} m²
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($unit->base_rent, 0, ',', '.') }} VNĐ
                                        </span>
                                    </td>
                                    <td>
                                        @switch($unit->status)
                                            @case('available')
                                                <span class="badge bg-success">Trống</span>
                                                @break
                                            @case('reserved')
                                                <span class="badge bg-warning">Đã đặt</span>
                                                @break
                                            @case('occupied')
                                                <span class="badge bg-danger">Đã thuê</span>
                                                @break
                                            @case('maintenance')
                                                <span class="badge bg-secondary">Bảo trì</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($unit->is_rented && $unit->current_lease)
                                            <div>
                                                <div class="fw-bold">{{ $unit->current_lease->tenant->full_name }}</div>
                                                <small class="text-muted">{{ $unit->current_lease->tenant->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.units.show', $unit->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('agent.units.edit', $unit->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('agent.units.destroy', $unit->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
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
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-door-open fa-3x text-muted"></i>
                </div>
                <h4 class="text-muted">Chưa có phòng nào</h4>
                <p class="text-muted">
                    @if($properties->count() > 0)
                        Bạn chưa tạo phòng nào trong các bất động sản được gán quản lý.
                    @else
                        Bạn chưa được gán quản lý bất động sản nào.
                    @endif
                </p>
                @if($properties->count() > 0)
                    <a href="{{ route('agent.units.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo phòng đầu tiên
                    </a>
                @endif
            </div>
        @endif
    </div>
</main>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush
