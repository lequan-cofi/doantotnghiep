@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Loại Bất động sản')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Chi tiết Loại Bất động sản</h1>
                <p>Thông tin chi tiết về loại bất động sản</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.property-types.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="{{ route('manager.property-types.edit', $propertyType->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row">
            <!-- Property Type Info -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Thông tin cơ bản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mã Code:</label>
                                    <div>
                                        <code class="bg-light px-2 py-1 rounded">{{ $propertyType->key_code }}</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tên loại BĐS:</label>
                                    <div><strong>{{ $propertyType->name }}</strong></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Icon:</label>
                                    <div>
                                        @if ($propertyType->icon)
                                            <i class="{{ $propertyType->icon }} text-primary fa-2x"></i>
                                            <span class="ms-2 text-muted">{{ $propertyType->icon }}</span>
                                        @else
                                            <i class="fas fa-building text-muted fa-2x"></i>
                                            <span class="ms-2 text-muted">Không có icon</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái:</label>
                                    <div>
                                        @if ($propertyType->status == 1)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-warning">Tạm ngưng</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả:</label>
                            <div class="bg-light p-3 rounded">
                                {{ $propertyType->description ?? 'Không có mô tả' }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái:</label>
                                    <div>
                                        @if ($propertyType->status == 1)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-warning">Tạm ngưng</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Số bất động sản:</label>
                                    <div>
                                        <span class="badge bg-info fs-6">{{ $propertyType->properties_count ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <!-- Timestamps -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clock"></i> Thông tin thời gian
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Ngày tạo:</small>
                            <div>{{ $propertyType->created_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Cập nhật lần cuối:</small>
                            <div>{{ $propertyType->updated_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                        @if ($propertyType->deleted_at)
                        <div class="mb-2">
                            <small class="text-muted">Đã xóa:</small>
                            <div class="text-danger">{{ $propertyType->deleted_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cogs"></i> Hành động
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('manager.property-types.edit', $propertyType->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            
                            @if ($propertyType->properties_count == 0)
                            <button class="btn btn-danger" onclick="deletePropertyType({{ $propertyType->id }})">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                            @else
                            <button class="btn btn-danger" disabled title="Không thể xóa vì đang được sử dụng">
                                <i class="fas fa-trash"></i> Xóa ({{ $propertyType->properties_count }} BĐS)
                            </button>
                            @endif

                            <a href="{{ route('manager.property-types.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> Danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties using this type -->
        @if ($propertyType->properties_count > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-building"></i> Bất động sản sử dụng loại này ({{ $propertyType->properties_count }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên BĐS</th>
                                <th>Chủ sở hữu</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($propertyType->properties as $property)
                            <tr>
                                <td>{{ $property->id }}</td>
                                <td>
                                    <strong>{{ $property->name }}</strong>
                                </td>
                                <td>
                                    {{ $property->owner->full_name ?? '-' }}
                                </td>
                                <td>
                                    @if ($property->status == 1)
                                    <span class="badge bg-success">Hoạt động</span>
                                    @else
                                    <span class="badge bg-warning">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $property->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('manager.properties.show', $property->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Không có bất động sản nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
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
                <p>Bạn có chắc chắn muốn xóa loại bất động sản <strong>{{ $propertyType->name }}</strong>?</p>
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
let deleteId = {{ $propertyType->id }};

function deletePropertyType(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    // Show preloader
    if (window.Preloader) {
        window.Preloader.show();
    }

    fetch(`/manager/property-types/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message);
            // Redirect to index after success
            setTimeout(() => {
                window.location.href = '{{ route("manager.property-types.index") }}';
            }, 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Có lỗi xảy ra khi xóa loại bất động sản');
    })
    .finally(() => {
        // Hide preloader
        if (window.Preloader) {
            window.Preloader.hide();
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
