@extends('layouts.agent_dashboard')

@section('title', 'Quản lý số liệu đo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-chart-line me-2"></i>Quản lý số liệu đo
                    </h1>
                    <p class="text-muted mb-0">Theo dõi và quản lý số liệu đo công tơ điện, nước</p>
                </div>
                <a href="{{ route('agent.meter-readings.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Thêm số liệu đo
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
            <form method="GET" action="{{ route('agent.meter-readings.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="meter_id" class="form-label">Công tơ đo</label>
                    <select class="form-select" id="meter_id" name="meter_id">
                        <option value="">Tất cả công tơ</option>
                        @foreach($meters as $meter)
                            <option value="{{ $meter->id }}" {{ request('meter_id') == $meter->id ? 'selected' : '' }}>
                                {{ $meter->serial_no }} - {{ $meter->property->name }} - {{ $meter->unit->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Lọc
                        </button>
                        <a href="{{ route('agent.meter-readings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Readings Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($readings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày đo</th>
                                <th>Công tơ</th>
                                <th>Bất động sản</th>
                                <th>Phòng</th>
                                <th>Dịch vụ</th>
                                <th>Số liệu</th>
                                <th>Lượng sử dụng</th>
                                <th>Người đo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readings as $reading)
                                @php
                                    // Get previous reading for usage calculation
                                    $previousReading = \App\Models\MeterReading::where('meter_id', $reading->meter_id)
                                        ->where('reading_date', '<', $reading->reading_date)
                                        ->latest('reading_date')
                                        ->first();
                                    $usage = $previousReading ? $reading->value - $previousReading->value : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $reading->reading_date->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $reading->reading_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $reading->meter->serial_no }}</div>
                                        <small class="text-muted">ID: {{ $reading->meter->id }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $reading->meter->property->name }}</div>
                                        <small class="text-muted">{{ $reading->meter->property->address ?? 'Chưa có địa chỉ' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $reading->meter->unit->code }}</div>
                                        <small class="text-muted">{{ $reading->meter->unit->unit_type }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $reading->meter->service->name }}</div>
                                        <small class="text-muted">{{ $reading->meter->service->key_code }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">
                                            {{ number_format($reading->value, 3) }}
                                        </div>
                                        <small class="text-muted">{{ $reading->meter->service->unit_label }}</small>
                                    </td>
                                    <td>
                                        @if($usage > 0)
                                            <div class="fw-bold text-success">
                                                +{{ number_format($usage, 3) }}
                                            </div>
                                            <small class="text-muted">{{ $reading->meter->service->unit_label }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $reading->takenBy->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $reading->takenBy->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.meter-readings.show', $reading->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('agent.meter-readings.edit', $reading->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteReading({{ $reading->id }}, '{{ $reading->reading_date->format('d/m/Y') }}')" 
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
                        Hiển thị {{ $readings->firstItem() }} - {{ $readings->lastItem() }} 
                        trong tổng số {{ $readings->total() }} số liệu đo
                    </div>
                    {{ $readings->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có số liệu đo nào</h5>
                    <p class="text-muted">Hãy thêm số liệu đo đầu tiên để bắt đầu theo dõi.</p>
                    <a href="{{ route('agent.meter-readings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Thêm số liệu đo
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
                    Xác nhận xóa số liệu đo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa số liệu đo ngày <strong id="readingDate"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Hành động này sẽ xóa vĩnh viễn số liệu đo và có thể ảnh hưởng đến tính toán hóa đơn.
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
function deleteReading(readingId, readingDate) {
    document.getElementById('readingDate').textContent = readingDate;
    document.getElementById('deleteForm').action = `/agent/meter-readings/${readingId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit filters on change
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#meter_id');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
