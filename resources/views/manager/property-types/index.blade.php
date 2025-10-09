@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Loại Bất động sản')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Quản lý Loại Bất động sản</h1>
                <p>Danh sách tất cả loại bất động sản trong hệ thống</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.property-types.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm loại mới
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('manager.property-types.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" class="form-control" placeholder="Tên, mã code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="{{ route('manager.property-types.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Property Types Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã Code</th>
                                <th>Tên</th>
                                <th>Icon</th>
                                <th>Số BĐS</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($propertyTypes as $type)
                            <tr>
                                <td>{{ $type->id }}</td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $type->key_code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $type->name }}</strong>
                                </td>
                                <td>
                                    @if ($type->icon)
                                    <i class="{{ $type->icon }} text-primary fa-lg"></i>
                                    @else
                                    <i class="fas fa-building text-muted"></i>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $type->properties_count }}</span>
                                </td>
                                <td>
                                    @if ($type->status == 1)
                                    <span class="badge bg-success">Hoạt động</span>
                                    @else
                                    <span class="badge bg-warning">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $type->created_at ? $type->created_at->format('d/m/Y H:i') : '-' }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('manager.property-types.show', $type->id) }}" class="btn btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.property-types.edit', $type->id) }}" class="btn btn-outline-primary" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" onclick="deletePropertyType({{ $type->id }})" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Không có loại bất động sản nào</p>
                                        <a href="{{ route('manager.property-types.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Thêm loại đầu tiên
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($propertyTypes->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $propertyTypes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa loại bất động sản này?</p>
                <p class="text-danger"><small>Hành động này có thể được khôi phục.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deleteId = null;

function deletePropertyType(id) {
    Notify.confirmDelete('loại bất động sản này', () => {
        deleteId = id;
        // Trigger the actual delete
        document.getElementById('confirmDelete').click();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (!confirmDeleteBtn) return;
    
    confirmDeleteBtn.addEventListener('click', function() {
        if (deleteId) {
            // Show preloader
            if (window.Preloader) {
                window.Preloader.show();
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found');
                showAlert('error', 'Lỗi bảo mật: Không tìm thấy CSRF token');
                if (window.Preloader) {
                    window.Preloader.hide();
                }
                return;
            }

            fetch(`/manager/property-types/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Content-Type': 'application/json',
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
                    // Show success message
                    Notify.success(data.message, 'Đã xóa!');
                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Notify.error('Có lỗi xảy ra khi xóa loại bất động sản: ' + error.message, 'Lỗi hệ thống!');
            })
            .finally(() => {
                // Hide preloader
                if (window.Preloader) {
                    window.Preloader.hide();
                }
            });
        }
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of content
    const content = document.getElementById('content');
    content.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = content.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}
</script>
@endpush
@endsection
