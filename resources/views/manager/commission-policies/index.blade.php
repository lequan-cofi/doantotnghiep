@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Chính sách Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chính sách Hoa hồng</h1>
            <p class="mb-0">Quản lý các chính sách hoa hồng cho nhân viên</p>
        </div>
        <a href="{{ route('manager.commission-policies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo chính sách mới
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.commission-policies.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" placeholder="Tên hoặc mã chính sách...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sự kiện kích hoạt</label>
                        <select class="form-select" name="trigger_event">
                            <option value="">Tất cả</option>
                            <option value="deposit_paid" {{ request('trigger_event') == 'deposit_paid' ? 'selected' : '' }}>Thanh toán cọc</option>
                            <option value="lease_signed" {{ request('trigger_event') == 'lease_signed' ? 'selected' : '' }}>Ký hợp đồng</option>
                            <option value="invoice_paid" {{ request('trigger_event') == 'invoice_paid' ? 'selected' : '' }}>Thanh toán hóa đơn</option>
                            <option value="viewing_done" {{ request('trigger_event') == 'viewing_done' ? 'selected' : '' }}>Hoàn thành xem phòng</option>
                            <option value="listing_published" {{ request('trigger_event') == 'listing_published' ? 'selected' : '' }}>Đăng tin</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Loại tính toán</label>
                        <select class="form-select" name="calc_type">
                            <option value="">Tất cả</option>
                            <option value="percent" {{ request('calc_type') == 'percent' ? 'selected' : '' }}>Phần trăm</option>
                            <option value="flat" {{ request('calc_type') == 'flat' ? 'selected' : '' }}>Số tiền cố định</option>
                            <option value="tiered" {{ request('calc_type') == 'tiered' ? 'selected' : '' }}>Bậc thang</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="active">
                            <option value="">Tất cả</option>
                            <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('manager.commission-policies.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Policies Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Chính sách Hoa hồng</h6>
        </div>
        <div class="card-body">
            @if($policies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên chính sách</th>
                                <th>Sự kiện kích hoạt</th>
                                <th>Loại tính toán</th>
                                <th>Giá trị</th>
                                <th>Trạng thái</th>
                                <th>Số sự kiện</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $policy->code }}</span>
                                </td>
                                <td>
                                    <strong>{{ $policy->title }}</strong>
                                </td>
                                <td>
                                    @php
                                        $triggerLabels = [
                                            'deposit_paid' => 'Thanh toán cọc',
                                            'lease_signed' => 'Ký hợp đồng',
                                            'invoice_paid' => 'Thanh toán hóa đơn',
                                            'viewing_done' => 'Hoàn thành xem phòng',
                                            'listing_published' => 'Đăng tin'
                                        ];
                                    @endphp
                                    <span class="badge bg-info">{{ $triggerLabels[$policy->trigger_event] ?? $policy->trigger_event }}</span>
                                </td>
                                <td>
                                    @php
                                        $calcLabels = [
                                            'percent' => 'Phần trăm',
                                            'flat' => 'Số tiền cố định',
                                            'tiered' => 'Bậc thang'
                                        ];
                                    @endphp
                                    {{ $calcLabels[$policy->calc_type] ?? $policy->calc_type }}
                                </td>
                                <td>
                                    @if($policy->calc_type == 'percent')
                                        {{ $policy->percent_value }}%
                                    @elseif($policy->calc_type == 'flat')
                                        {{ number_format($policy->flat_amount, 0, ',', '.') }} VND
                                    @else
                                        Bậc thang
                                    @endif
                                </td>
                                <td>
                                    @if($policy->active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Không hoạt động</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $policy->events_count ?? 0 }}</span>
                                </td>
                                <td>{{ $policy->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manager.commission-policies.show', $policy->id) }}" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.commission-policies.edit', $policy->id) }}" 
                                           class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deletePolicy({{ $policy->id }})" title="Xóa">
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
                <div class="d-flex justify-content-center">
                    {{ $policies->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có chính sách hoa hồng nào</h5>
                    <p class="text-muted">Hãy tạo chính sách hoa hồng đầu tiên để bắt đầu quản lý.</p>
                    <a href="{{ route('manager.commission-policies.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo chính sách mới
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
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa chính sách hoa hồng này?</p>
                <p class="text-danger"><strong>Hành động này không thể hoàn tác!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deletePolicy(policyId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/manager/commission-policies/${policyId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush
