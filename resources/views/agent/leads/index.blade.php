@extends('layouts.agent_dashboard')

@section('title', 'Quản lý Leads')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-users me-2"></i>Quản lý Leads
                    </h1>
                    <p class="text-muted mb-0">Danh sách khách hàng tiềm năng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.leads.statistics') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-1"></i>Thống kê
                    </a>
                    <a href="{{ route('agent.leads.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo Lead
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('agent.leads.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ $request->search }}" placeholder="Tên, SĐT, email...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tất cả</option>
                                <option value="new" {{ $request->status == 'new' ? 'selected' : '' }}>Mới</option>
                                <option value="contacted" {{ $request->status == 'contacted' ? 'selected' : '' }}>Đã liên hệ</option>
                                <option value="qualified" {{ $request->status == 'qualified' ? 'selected' : '' }}>Đủ điều kiện</option>
                                <option value="proposal" {{ $request->status == 'proposal' ? 'selected' : '' }}>Đề xuất</option>
                                <option value="negotiation" {{ $request->status == 'negotiation' ? 'selected' : '' }}>Đàm phán</option>
                                <option value="converted" {{ $request->status == 'converted' ? 'selected' : '' }}>Đã chuyển đổi</option>
                                <option value="lost" {{ $request->status == 'lost' ? 'selected' : '' }}>Mất khách</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="source" class="form-label">Nguồn</label>
                            <select class="form-select" id="source" name="source">
                                <option value="">Tất cả</option>
                                <option value="facebook" {{ $request->source == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="google" {{ $request->source == 'google' ? 'selected' : '' }}>Google</option>
                                <option value="referral" {{ $request->source == 'referral' ? 'selected' : '' }}>Giới thiệu</option>
                                <option value="walk-in" {{ $request->source == 'walk-in' ? 'selected' : '' }}>Đến trực tiếp</option>
                                <option value="phone" {{ $request->source == 'phone' ? 'selected' : '' }}>Điện thoại</option>
                                <option value="other" {{ $request->source == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sắp xếp theo</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                                <option value="name" {{ $request->sort_by == 'name' ? 'selected' : '' }}>Tên</option>
                                <option value="phone" {{ $request->sort_by == 'phone' ? 'selected' : '' }}>Số điện thoại</option>
                                <option value="status" {{ $request->sort_by == 'status' ? 'selected' : '' }}>Trạng thái</option>
                                <option value="source" {{ $request->sort_by == 'source' ? 'selected' : '' }}>Nguồn</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_order" class="form-label">Thứ tự</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" {{ $request->sort_order == 'desc' ? 'selected' : '' }}>Giảm dần</option>
                                <option value="asc" {{ $request->sort_order == 'asc' ? 'selected' : '' }}>Tăng dần</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                            <a href="{{ route('agent.leads.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Danh sách Leads ({{ $leads->total() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($leads->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tên khách hàng</th>
                                        <th>Liên hệ</th>
                                        <th>Nguồn</th>
                                        <th>Ngân sách</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leads as $lead)
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $lead->name }}</div>
                                                    @if($lead->desired_city)
                                                        <small class="text-muted">{{ $lead->desired_city }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $lead->phone }}</div>
                                                    @if($lead->email)
                                                        <small class="text-muted">{{ $lead->email }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($lead->source) }}</span>
                                            </td>
                                            <td>
                                                @if($lead->budget_min && $lead->budget_max)
                                                    {{ number_format($lead->budget_min) }}đ - {{ number_format($lead->budget_max) }}đ
                                                @elseif($lead->budget_min)
                                                    Từ {{ number_format($lead->budget_min) }}đ
                                                @elseif($lead->budget_max)
                                                    Đến {{ number_format($lead->budget_max) }}đ
                                                @else
                                                    <span class="text-muted">Chưa xác định</span>
                                                @endif
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm status-select" data-lead-id="{{ $lead->id }}">
                                                    <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>Mới</option>
                                                    <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Đã liên hệ</option>
                                                    <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Đủ điều kiện</option>
                                                    <option value="proposal" {{ $lead->status == 'proposal' ? 'selected' : '' }}>Đề xuất</option>
                                                    <option value="negotiation" {{ $lead->status == 'negotiation' ? 'selected' : '' }}>Đàm phán</option>
                                                    <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Đã chuyển đổi</option>
                                                    <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Mất khách</option>
                                                </select>
                                            </td>
                                            <td>
                                                {{ $lead->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.leads.show', $lead->id) }}" 
                                                       class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('agent.leads.edit', $lead->id) }}" 
                                                       class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($lead->status != 'converted')
                                                        <a href="{{ route('agent.leads.create-lease', $lead->id) }}" 
                                                           class="btn btn-outline-success btn-sm" title="Tạo hợp đồng">
                                                            <i class="fas fa-file-contract"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-lead-btn" 
                                                            data-lead-id="{{ $lead->id }}" 
                                                            data-lead-name="{{ $lead->name }}" 
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $leads->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có lead nào</h5>
                            <p class="text-muted">Bắt đầu tạo lead đầu tiên của bạn</p>
                            <a href="{{ route('agent.leads.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tạo Lead
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status change handler
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const leadId = this.dataset.leadId;
            const newStatus = this.value;
            
            fetch(`/agent/leads/${leadId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                    if (typeof Notify !== 'undefined') {
                        Notify.success(data.message, 'Cập nhật trạng thái');
                    } else {
                        // Fallback to alert
                        alert(data.message);
                    }
                } else {
                    // Show error notification
                    if (typeof Notify !== 'undefined') {
                        Notify.error(data.message, 'Lỗi cập nhật');
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                    // Revert selection
                    this.value = this.dataset.originalValue || 'new';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error('Có lỗi xảy ra khi cập nhật trạng thái', 'Lỗi hệ thống');
                } else {
                    alert('Có lỗi xảy ra khi cập nhật trạng thái');
                }
                // Revert selection
                this.value = this.dataset.originalValue || 'new';
            });
        });
        
        // Store original value
        select.dataset.originalValue = select.value;
    });

    // Delete lead functionality
    document.querySelectorAll('.delete-lead-btn').forEach(button => {
        button.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            const leadName = this.dataset.leadName;
            
            // Show confirmation dialog
            if (typeof Notify !== 'undefined') {
                Notify.confirmDelete(`lead "${leadName}"`, () => {
                    // User confirmed deletion
                    deleteLead(leadId);
                });
            } else {
                // Fallback to browser confirm
                if (confirm(`Bạn có chắc chắn muốn xóa lead "${leadName}"?`)) {
                    deleteLead(leadId);
                }
            }
        });
    });

    // Delete lead function
    function deleteLead(leadId) {
        fetch(`/agent/leads/${leadId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof Notify !== 'undefined') {
                    Notify.success(data.message, 'Xóa thành công');
                } else {
                    alert(data.message);
                }
                // Remove the row from table
                const row = document.querySelector(`[data-lead-id="${leadId}"]`).closest('tr');
                if (row) {
                    row.remove();
                }
            } else {
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error(data.message, 'Lỗi xóa');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Có lỗi xảy ra khi xóa lead', 'Lỗi hệ thống');
            } else {
                alert('Có lỗi xảy ra khi xóa lead');
            }
        });
    }
});
</script>
@endpush
