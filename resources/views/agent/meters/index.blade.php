@extends('layouts.agent_dashboard')

@section('title', 'Quản lý công tơ đo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-tachometer-alt me-2"></i>Quản lý công tơ đo
                    </h1>
                    <p class="text-muted mb-0">Quản lý các công tơ đo điện, nước trong bất động sản</p>
                </div>
                <a href="{{ route('agent.meters.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Thêm công tơ mới
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
            <form method="GET" action="{{ route('agent.meters.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="property_id" class="form-label">Bất động sản</label>
                    <select class="form-select" id="property_id" name="property_id">
                        <option value="">Tất cả bất động sản</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                {{ $property->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="service_id" class="form-label">Loại dịch vụ</label>
                    <select class="form-select" id="service_id" name="service_id">
                        <option value="">Tất cả dịch vụ</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} ({{ $service->key_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Lọc
                        </button>
                        <a href="{{ route('agent.meters.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Meters Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($meters->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mã công tơ</th>
                                <th>Bất động sản</th>
                                <th>Phòng</th>
                                <th>Dịch vụ</th>
                                <th>Số liệu cuối</th>
                                <th>Ngày đo cuối</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meters as $meter)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $meter->serial_no }}</div>
                                        <small class="text-muted">ID: {{ $meter->id }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $meter->property->name }}</div>
                                        <small class="text-muted">{{ $meter->property->address ?? 'Chưa có địa chỉ' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $meter->unit->code }}</div>
                                        <small class="text-muted">{{ $meter->unit->unit_type }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $meter->service->name }}</div>
                                        <small class="text-muted">{{ $meter->service->key_code }}</small>
                                    </td>
                                    <td>
                                        @if($meter->readings->count() > 0)
                                            <div class="fw-bold text-primary">
                                                {{ number_format($meter->readings->first()->value, 3) }}
                                            </div>
                                            <small class="text-muted">{{ $meter->service->unit_label }}</small>
                                        @else
                                            <span class="text-muted">Chưa có số liệu</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($meter->readings->count() > 0)
                                            <div>{{ $meter->readings->first()->reading_date->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $meter->readings->first()->takenBy->name ?? 'N/A' }}</small>
                                        @else
                                            <span class="text-muted">Chưa có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($meter->status)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary">Ngừng hoạt động</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.meters.show', $meter->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('agent.meters.edit', $meter->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('agent.meter-readings.create', ['meter_id' => $meter->id]) }}" 
                                               class="btn btn-sm btn-outline-info" title="Thêm số liệu đo">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteMeter({{ $meter->id }}, '{{ $meter->serial_no }}')" 
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Hiển thị {{ $meters->firstItem() }} - {{ $meters->lastItem() }} 
                        trong tổng số {{ $meters->total() }} công tơ
                    </div>
                    {{ $meters->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tachometer-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có công tơ đo nào</h5>
                    <p class="text-muted">Hãy thêm công tơ đo đầu tiên để bắt đầu quản lý.</p>
                    <a href="{{ route('agent.meters.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Thêm công tơ mới
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Xác nhận xóa công tơ đo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa công tơ đo <strong id="meterSerial"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Hành động này sẽ xóa vĩnh viễn công tơ đo và không thể khôi phục.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteMeter(meterId, serialNo) {
    document.getElementById('meterSerial').textContent = serialNo;
    document.getElementById('deleteForm').action = `/agent/meters/${meterId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit filters on change
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#property_id, #service_id, #status');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
