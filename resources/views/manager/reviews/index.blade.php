@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Đánh giá')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Quản lý Đánh giá</h1>
            <p class="mb-0">Theo dõi và quản lý các đánh giá từ khách thuê</p>
        </div>
        <div>
            <button class="btn btn-info" onclick="loadStatistics()">
                <i class="fas fa-chart-bar"></i> Thống kê
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statisticsCards" style="display: none;">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng đánh giá</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalReviews">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Điểm trung bình</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="averageRating">0.0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Đã đăng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="publishedReviews">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đã ẩn</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="hiddenReviews">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.reviews.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã đăng</option>
                        <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Đã ẩn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Phòng</label>
                    <select name="unit_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->property ? $unit->property->name : 'N/A' }} - {{ $unit->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Khách thuê</label>
                    <select name="tenant_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Điểm từ</label>
                    <select name="rating_min" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('rating_min') == '1' ? 'selected' : '' }}>1 sao</option>
                        <option value="2" {{ request('rating_min') == '2' ? 'selected' : '' }}>2 sao</option>
                        <option value="3" {{ request('rating_min') == '3' ? 'selected' : '' }}>3 sao</option>
                        <option value="4" {{ request('rating_min') == '4' ? 'selected' : '' }}>4 sao</option>
                        <option value="5" {{ request('rating_min') == '5' ? 'selected' : '' }}>5 sao</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Điểm đến</label>
                    <select name="rating_max" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('rating_max') == '1' ? 'selected' : '' }}>1 sao</option>
                        <option value="2" {{ request('rating_max') == '2' ? 'selected' : '' }}>2 sao</option>
                        <option value="3" {{ request('rating_max') == '3' ? 'selected' : '' }}>3 sao</option>
                        <option value="4" {{ request('rating_max') == '4' ? 'selected' : '' }}>4 sao</option>
                        <option value="5" {{ request('rating_max') == '5' ? 'selected' : '' }}>5 sao</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Giới thiệu</label>
                    <select name="recommend" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="yes" {{ request('recommend') == 'yes' ? 'selected' : '' }}>Có</option>
                        <option value="maybe" {{ request('recommend') == 'maybe' ? 'selected' : '' }}>Có thể</option>
                        <option value="no" {{ request('recommend') == 'no' ? 'selected' : '' }}>Không</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="Tìm theo tiêu đề, nội dung, tên khách thuê...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="row w-100">
                                <div class="col-6">
                                    <label class="form-label">Đến ngày</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="{{ request('date_to') }}">
                                </div>
                                <div class="col-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Lọc
                                    </button>
                                    <a href="{{ route('manager.reviews.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Xóa bộ lọc
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Đánh giá ({{ $reviews->total() }} kết quả)</h6>
        </div>
        <div class="card-body">
            @if($reviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Khách thuê</th>
                                <th>Phòng</th>
                                <th>Điểm</th>
                                <th>Tiêu đề</th>
                                <th>Trạng thái</th>
                                <th>Giới thiệu</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                            <tr>
                                <td>#{{ $review->id }}</td>
                                <td>
                                    <div class="small">
                                        <strong>{{ $review->tenant ? $review->tenant->full_name : 'N/A' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    @if($review->unit)
                                        <div class="small">
                                            <strong>{{ $review->unit->property ? $review->unit->property->name : 'N/A' }}</strong><br>
                                            Phòng: {{ $review->unit->code }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->overall_rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-muted"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-2 small">{{ $review->overall_rating }}/5</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $review->title ?: 'Không có tiêu đề' }}</div>
                                    @if($review->content)
                                        <small class="text-muted">{{ Str::limit($review->content, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'published' => 'success',
                                            'hidden' => 'warning'
                                        ];
                                        $statusLabels = [
                                            'published' => 'Đã đăng',
                                            'hidden' => 'Đã ẩn'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$review->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$review->status] ?? ucfirst($review->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $recommendColors = [
                                            'yes' => 'success',
                                            'maybe' => 'warning',
                                            'no' => 'danger'
                                        ];
                                        $recommendLabels = [
                                            'yes' => 'Có',
                                            'maybe' => 'Có thể',
                                            'no' => 'Không'
                                        ];
                                    @endphp
                                    @if($review->recommend)
                                        <span class="badge bg-{{ $recommendColors[$review->recommend] ?? 'secondary' }}">
                                            {{ $recommendLabels[$review->recommend] ?? ucfirst($review->recommend) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $review->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manager.reviews.show', $review->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.reviews.edit', $review->id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteReview({{ $review->id }}, '{{ $review->title ?: 'Đánh giá #' . $review->id }}')" 
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
                <div class="d-flex justify-content-center">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có đánh giá nào</h5>
                    <p class="text-muted">Chưa có đánh giá nào hoặc không tìm thấy kết quả phù hợp.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Session Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection

@push('scripts')
<script>
function loadStatistics() {
    const cards = document.getElementById('statisticsCards');
    if (cards.style.display === 'none') {
        cards.style.display = 'block';
        
        // Show loading
        document.getElementById('totalReviews').textContent = '...';
        document.getElementById('averageRating').textContent = '...';
        document.getElementById('publishedReviews').textContent = '...';
        document.getElementById('hiddenReviews').textContent = '...';
        
        fetch('{{ route("manager.reviews.statistics") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalReviews').textContent = data.data.total_reviews || 0;
                document.getElementById('averageRating').textContent = (data.data.average_rating || 0).toFixed(1);
                document.getElementById('publishedReviews').textContent = data.data.published_reviews || 0;
                document.getElementById('hiddenReviews').textContent = data.data.hidden_reviews || 0;
            } else {
                Notify.error(data.message || 'Không thể tải thống kê');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Đã xảy ra lỗi khi tải thống kê');
        });
    } else {
        cards.style.display = 'none';
    }
}

function deleteReview(id, name) {
    Notify.confirmDelete(`đánh giá "${name}"`, function() {
        const loadingToast = Notify.toast({
            title: 'Đang xử lý...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });
        
        fetch(`/manager/reviews/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
        .then(response => {
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Xóa thành công!');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                Notify.error(data.message, 'Không thể xóa đánh giá');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Có lỗi xảy ra khi xóa đánh giá. Vui lòng thử lại.', 'Lỗi hệ thống');
        });
    });
}
</script>
@endpush
