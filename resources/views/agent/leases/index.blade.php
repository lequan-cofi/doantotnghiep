@extends('layouts.agent_dashboard')

@section('title', 'Quản lý hợp đồng')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Quản lý hợp đồng</h1>
                <p>Danh sách các hợp đồng do bạn tạo</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.leases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo hợp đồng mới
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('agent.leases.index') }}" class="row g-3">
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
                            <option value="draft" {{ $selectedStatus == 'draft' ? 'selected' : '' }}>Nháp</option>
                            <option value="active" {{ $selectedStatus == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="terminated" {{ $selectedStatus == 'terminated' ? 'selected' : '' }}>Chấm dứt</option>
                            <option value="expired" {{ $selectedStatus == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Nhập mã hợp đồng, tên khách thuê..." value="{{ $search }}">
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

        @if($leases->count() > 0)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
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
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'contract_no', 'sort_order' => request('sort_by') == 'contract_no' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Mã hợp đồng
                                            @if(request('sort_by') == 'contract_no')
                                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Khách thuê</th>
                                    <th>Phòng</th>
                                    <th>Bất động sản</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'rent_amount', 'sort_order' => request('sort_by') == 'rent_amount' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Giá thuê
                                            @if(request('sort_by') == 'rent_amount')
                                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'start_date', 'sort_order' => request('sort_by') == 'start_date' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Thời hạn
                                            @if(request('sort_by') == 'start_date')
                                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
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
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leases as $lease)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $lease->contract_no }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            @if($lease->tenant)
                                                <div class="fw-bold">{{ $lease->tenant->full_name }}</div>
                                                <small class="text-muted">{{ $lease->tenant->email }}</small>
                                            @else
                                                <div class="fw-bold text-muted">Chưa gán</div>
                                                <small class="text-muted">-</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $lease->unit->code }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $lease->unit->property->name }}</div>
                                            @if($lease->unit->property->owner)
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ $lease->unit->property->owner->full_name }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($lease->rent_amount, 0, ',', '.') }} VNĐ
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted">Từ:</small> {{ $lease->start_date->format('d/m/Y') }}<br>
                                            <small class="text-muted">Đến:</small> {{ $lease->end_date->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        @switch($lease->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">Nháp</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-success">Hoạt động</span>
                                                @break
                                            @case('terminated')
                                                <span class="badge bg-danger">Chấm dứt</span>
                                                @break
                                            @case('expired')
                                                <span class="badge bg-warning">Hết hạn</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.leases.show', $lease->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('agent.leases.edit', $lease->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('agent.leases.destroy', $lease->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')">
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
                    
                    <!-- Pagination -->
        {{-- Pagination temporarily removed --}}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-file-contract fa-3x text-muted"></i>
                </div>
                <h4 class="text-muted">Chưa có hợp đồng nào</h4>
                <p class="text-muted">
                    @if($properties->count() > 0)
                        Bạn chưa tạo hợp đồng nào. Hãy tạo hợp đồng đầu tiên cho khách thuê.
                    @else
                        Bạn chưa được gán quản lý bất động sản nào.
                    @endif
                </p>
                @if($properties->count() > 0)
                    <a href="{{ route('agent.leases.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo hợp đồng đầu tiên
                    </a>
                @endif
            </div>
        @endif
    </div>
</main>
@endsection

@push('styles')
<style>
/* Sorting Styles */
.table th a {
    color: inherit;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.table th a:hover {
    color: #0d6efd;
    text-decoration: none;
}

.table th a i {
    font-size: 0.8rem;
    opacity: 0.7;
}

.table th a:hover i {
    opacity: 1;
}

/* Custom Pagination Styles for Lease Management */
.pagination-wrapper {
display: flex;
justify-content: center;
align-items: center;
margin-top: 1.5rem;
gap: 0.75rem;
flex-wrap: wrap;
}


.pagination-wrapper .pagination {
display: flex;
flex-wrap: wrap;
list-style: none;
padding: 0;
margin: 0;
gap: 0.4rem;
}


.pagination-wrapper .pagination .page-item {
display: inline-block;
}


.pagination-wrapper .pagination .page-link {
display: flex;
justify-content: center;
align-items: center;
min-width: 2.5rem;
height: 2.5rem;
padding: 0.5rem 0.75rem;
color: #495057;
text-decoration: none;
background-color: #ffffff;
border: 1px solid #dee2e6;
border-radius: 0.375rem;
font-size: 0.875rem;
transition: all 0.2s ease-in-out;
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}


.pagination-wrapper .pagination .page-link:hover {
color: #0d6efd;
background-color: #f1f5ff;
border-color: #b6d4fe;
transform: translateY(-2px);
}


.pagination-wrapper .pagination .page-item.active .page-link {
color: #fff;
background-color: #0d6efd;
border-color: #0d6efd;
font-weight: 600;
box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);
}


.pagination-wrapper .pagination .page-item.disabled .page-link {
color: #adb5bd;
background-color: #f8f9fa;
border-color: #dee2e6;
cursor: not-allowed;
opacity: 0.6;
}


.pagination-wrapper .pagination .page-item.disabled .page-link:hover {
transform: none;
background-color: #f8f9fa;
border-color: #dee2e6;
}


/* Compact mode for small screens */
@media (max-width: 768px) {
.pagination-wrapper {
flex-direction: column;
align-items: center;
gap: 0.5rem;
}


.pagination-wrapper .pagination .page-link {
min-width: 2rem;
height: 2rem;
padding: 0.375rem 0.5rem;
font-size: 0.75rem;
}
}

</style>
@endpush
