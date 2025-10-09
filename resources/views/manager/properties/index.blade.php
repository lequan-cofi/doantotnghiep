@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Bất động sản')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Quản lý Bất động sản</h1>
                <p>Danh sách tất cả bất động sản đang quản lý</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.properties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm BĐS mới
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('manager.properties.index') }}">
                    <!-- First row -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" placeholder="Tên BĐS, đường, quận, phường, chủ sở hữu..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Loại BĐS</label>
                            <select name="type" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($propertyTypes as $type)
                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Chủ sở hữu</label>
                            <select name="owner" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" {{ request('owner') == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Second row -->
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Tỉnh/Thành phố (Cũ)</label>
                            <select name="province" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($provinces as $province)
                                <option value="{{ $province->code }}" {{ request('province') == $province->code ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quận/Huyện (Cũ)</label>
                            <select name="district" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($districts as $district)
                                <option value="{{ $district->code }}" {{ request('district') == $district->code ? 'selected' : '' }}>
                                    {{ $district->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tỉnh/Thành phố (Mới 2025)</label>
                            <select name="province_2025" id="province2025Filter" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach ($provinces2025 ?? [] as $province)
                                <option value="{{ $province->code }}" {{ request('province_2025') == $province->code ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Phường/Xã (Mới 2025)</label>
                            <select name="ward_2025" id="ward2025Filter" class="form-select" {{ !request('province_2025') ? 'disabled' : '' }}>
                                <option value="">Tất cả</option>
                                @foreach ($wards2025 ?? [] as $ward)
                                <option value="{{ $ward->code }}" {{ request('ward_2025') == $ward->code ? 'selected' : '' }}>
                                    {{ $ward->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('manager.properties.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Properties Table -->
        <div class="card">
            <div class="card-body">
                <!-- Debug Info -->
                {{-- <div class="alert alert-info mb-3">
                    <strong>Debug Info:</strong> 
                    Tìm thấy {{ $properties->total() }} bất động sản 
                    @if(request()->hasAny(['search', 'type', 'owner', 'status', 'province', 'district', 'province_2025', 'ward_2025', 'date_from', 'date_to']))
                        với bộ lọc: {{ json_encode(request()->except(['page'])) }}
                    @endif
                    <br>
                    <small>
                        Total properties in DB: {{ \App\Models\Property::count() }} | 
                        Properties with location: {{ \App\Models\Property::whereNotNull('location_id')->count() }} |
                        Properties with owner: {{ \App\Models\Property::whereNotNull('owner_id')->count() }}
                    </small>
                </div>
                 --}}
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên BĐS</th>
                                <th>Loại</th>
                                <th>Địa chỉ</th>
                                <th>Chủ sở hữu</th>
                                <th>Tổng phòng</th>
                                <th>Tỷ lệ lấp đầy</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($properties as $property)
                            <tr>
                                <td>{{ $property->id }}</td>
                                <td>
                                    <strong>{{ $property->name }}</strong>
                                    @if ($property->total_floors)
                                    <br><small class="text-muted">{{ $property->total_floors }} tầng</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($property->propertyType)
                                    <span class="badge bg-info">{{ $property->propertyType->name_local ?? $property->propertyType->name }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="address-info">
                                        @if ($property->location)
                                        <div class="mb-1">
                                            <small class="text-primary">
                                                <i class="fas fa-map-marker-alt"></i> <strong>Cũ:</strong>
                                            </small>
                                            <br>
                                            <small>
                                                {{ $property->location->street }},
                                                {{ $property->location->ward }},
                                                {{ $property->location->district }},
                                                {{ $property->location->city }}
                                            </small>
                                        </div>
                                        @endif
                                        
                                        @if ($property->location2025)
                                        <div>
                                            <small class="text-success">
                                                <i class="fas fa-map-marker-alt"></i> <strong>Mới 2025:</strong>
                                            </small>
                                            <br>
                                            <small>
                                                {{ $property->location2025->street }},
                                                {{ $property->location2025->ward }},
                                                {{ $property->location2025->city }}
                                            </small>
                                        </div>
                                        @endif
                                        
                                        @if (!$property->location && !$property->location2025)
                                        <span class="text-muted">Chưa có địa chỉ</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($property->owner)
                                    {{ $property->owner->full_name }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-primary">{{ $property->total_rooms ?? 0 }}</span>
                                        <small class="text-muted">Tổng: {{ $property->total_rooms ?? 0 }}</small>
                                        <small class="text-info">Thực tế: {{ $property->units->count() }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $occupancyRate = $property->getOccupancyRate();
                                        $occupancyStatus = $property->getOccupancyStatusAttribute();
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <div class="progress" style="height: 8px; width: 60px;">
                                            <div class="progress-bar 
                                                @if($occupancyStatus == 'full') bg-danger
                                                @elseif($occupancyStatus == 'high') bg-warning
                                                @elseif($occupancyStatus == 'medium') bg-info
                                                @else bg-success
                                                @endif" 
                                                role="progressbar" 
                                                style="width: {{ $occupancyRate }}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $occupancyRate }}%</small>
                                        <small class="badge 
                                            @if($occupancyStatus == 'full') bg-danger
                                            @elseif($occupancyStatus == 'high') bg-warning
                                            @elseif($occupancyStatus == 'medium') bg-info
                                            @else bg-success
                                            @endif">
                                            {{ $property->occupancy_status_text }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if ($property->status == 1)
                                    <span class="badge bg-success">Hoạt động</span>
                                    @else
                                    <span class="badge bg-warning">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('manager.properties.show', $property->id) }}" class="btn btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.properties.edit', $property->id) }}" class="btn btn-outline-primary" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" onclick="deleteProperty({{ $property->id }}, '{{ $property->name }}')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Chưa có bất động sản nào
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $properties->links() }}
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
<style>
.address-info {
    max-width: 300px;
}
.address-info small {
    line-height: 1.3;
}
.address-info .text-primary,
.address-info .text-success {
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
// Cascading dropdown for 2025 address system
document.addEventListener('DOMContentLoaded', function() {
    const province2025Filter = document.getElementById('province2025Filter');
    const ward2025Filter = document.getElementById('ward2025Filter');
    
    if (province2025Filter && ward2025Filter) {
        province2025Filter.addEventListener('change', function() {
            const provinceCode = this.value;
            
            // Reset ward filter
            ward2025Filter.innerHTML = '<option value="">Tất cả</option>';
            
            if (!provinceCode) {
                ward2025Filter.disabled = true;
                return;
            }
            
            // Fetch wards for selected province
            fetch(`/api/geo/wards-2025/${provinceCode}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(ward => {
                        const option = document.createElement('option');
                        option.value = ward.code;
                        option.textContent = ward.name_local || ward.name;
                        ward2025Filter.appendChild(option);
                    });
                    ward2025Filter.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching wards:', error);
                    ward2025Filter.disabled = true;
                });
        });
    }
});

function deleteProperty(id, name) {
    Notify.confirmDelete(`bất động sản "${name}"`, () => {
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            Notify.error('Lỗi bảo mật: Không tìm thấy CSRF token. Vui lòng tải lại trang và thử lại.', 'Lỗi bảo mật!');
            if (window.Preloader) {
                window.Preloader.hide();
            }
            return;
        }

        fetch(`/manager/properties/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Đã xóa!');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể xóa bất động sản: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
}
</script>
@endpush
@endsection

