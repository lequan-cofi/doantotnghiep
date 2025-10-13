@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Hợp đồng')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Chi tiết Hợp đồng</h1>
                <p>Thông tin chi tiết hợp đồng thuê</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.leases.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="{{ route('manager.leases.edit', $lease->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Số hợp đồng</label>
                                    <div>
                                        @if ($lease->contract_no)
                                        <code class="bg-light px-2 py-1 rounded">{{ $lease->contract_no }}</code>
                                        @else
                                        <span class="text-muted">Chưa có</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Trạng thái</label>
                                    <div>
                                        @switch($lease->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">Nháp</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-success">Đang hoạt động</span>
                                                @break
                                            @case('terminated')
                                                <span class="badge bg-warning">Đã chấm dứt</span>
                                                @break
                                            @case('expired')
                                                <span class="badge bg-danger">Đã hết hạn</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $lease->status }}</span>
                                        @endswitch
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small">Thời hạn hợp đồng</label>
                                    <div>
                                        <strong>Từ:</strong> {{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('d/m/Y') : '-' }}<br>
                                        <strong>Đến:</strong> {{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d/m/Y') : '-' }}
                                        @if ($lease->end_date && \Carbon\Carbon::parse($lease->end_date)->isPast())
                                        <br><small class="text-danger"><strong>Đã hết hạn</strong></small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Tiền thuê/tháng</label>
                                    <div>
                                        <span class="h5 text-success">{{ number_format($lease->rent_amount) }} VND</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small">Tiền cọc</label>
                                    <div>
                                        <span class="h6">{{ number_format($lease->deposit_amount) }} VND</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small">Ngày tạo hóa đơn</label>
                                    <div>
                                        Ngày {{ $lease->billing_day }} hàng tháng
                                    </div>
                                </div>

                                @if ($lease->signed_at)
                                <div class="mb-3">
                                    <label class="text-muted small">Ngày ký hợp đồng</label>
                                    <div>
                                        {{ \Carbon\Carbon::parse($lease->signed_at)->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Cycle Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Chu kỳ thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Chu kỳ thanh toán</label>
                                    <div>
                                        @if($lease->lease_payment_cycle)
                                            @switch($lease->lease_payment_cycle)
                                                @case('monthly')
                                                    <span class="badge bg-primary fs-6">Hàng tháng</span>
                                                    @break
                                                @case('quarterly')
                                                    <span class="badge bg-info fs-6">Hàng quý</span>
                                                    @break
                                                @case('yearly')
                                                    <span class="badge bg-success fs-6">Hàng năm</span>
                                                    @break
                                                @case('custom')
                                                    <span class="badge bg-warning fs-6">
                                                        {{ $lease->lease_custom_months ? $lease->lease_custom_months . ' tháng' : 'Tùy chỉnh' }}
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary fs-6">{{ $lease->lease_payment_cycle }}</span>
                                            @endswitch
                                        @else
                                            <span class="text-muted">Chưa thiết lập</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small">Hạn thanh toán</label>
                                    <div>
                                        @if($lease->lease_payment_day)
                                            <strong>Ngày {{ $lease->lease_payment_day }}</strong>
                                        @else
                                            <span class="text-muted">Chưa thiết lập</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Ngày tạo hóa đơn (cũ)</label>
                                    <div>
                                        <span class="text-muted">Ngày {{ $lease->billing_day }} hàng tháng</span>
                                        <br><small class="text-muted">Thông tin cũ, có thể được thay thế bởi chu kỳ thanh toán mới</small>
                                    </div>
                                </div>

                                @if($lease->lease_payment_notes)
                                <div class="mb-3">
                                    <label class="text-muted small">Ghi chú chu kỳ thanh toán</label>
                                    <div>
                                        <div class="bg-light p-3 rounded">
                                            {{ $lease->lease_payment_notes }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin bất động sản</h5>
                    </div>
                    <div class="card-body">
                        @if ($lease->unit && $lease->unit->property)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Bất động sản</label>
                                    <div>
                                        <strong>{{ $lease->unit->property->name }}</strong>
                                        @if ($lease->unit->property->propertyType)
                                        <br><small class="text-muted">{{ $lease->unit->property->propertyType->name }}</small>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small">Phòng</label>
                                    <div>
                                        <span class="badge bg-info">{{ $lease->unit->code ?? 'Phòng ' . $lease->unit->id }}</span>
                                        @if ($lease->unit->floor)
                                        <br><small class="text-muted">Tầng {{ $lease->unit->floor }}</small>
                                        @endif
                                        @if ($lease->unit->area_m2)
                                        <br><small class="text-muted">Diện tích: {{ $lease->unit->area_m2 }} m²</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Địa chỉ</label>
                                    <div>
                                        @if ($lease->unit->property->location)
                                        <div class="mb-1">
                                            <small class="text-primary">
                                                <i class="fas fa-map-marker-alt"></i> <strong>Cũ:</strong>
                                            </small>
                                            <br>
                                            <small>
                                                {{ $lease->unit->property->location->street }},
                                                {{ $lease->unit->property->location->ward }},
                                                {{ $lease->unit->property->location->district }},
                                                {{ $lease->unit->property->location->city }}
                                            </small>
                                        </div>
                                        @endif
                                        
                                        @if ($lease->unit->property->location2025)
                                        <div>
                                            <small class="text-success">
                                                <i class="fas fa-map-marker-alt"></i> <strong>Mới 2025:</strong>
                                            </small>
                                            <br>
                                            <small>
                                                {{ $lease->unit->property->location2025->street }},
                                                {{ $lease->unit->property->location2025->ward }},
                                                {{ $lease->unit->property->location2025->city }}
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="text-muted">Không có thông tin bất động sản</div>
                        @endif
                    </div>
                </div>

                <!-- Services -->
                @if ($lease->leaseServices->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Dịch vụ kèm theo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lease->leaseServices as $leaseService)
                                    <tr>
                                        <td>{{ $leaseService->service->name }}</td>
                                        <td>{{ number_format($leaseService->price) }} VND</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Tenant Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin khách thuê</h5>
                    </div>
                    <div class="card-body">
                        @if ($lease->tenant)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $lease->tenant->full_name }}</h6>
                                @if ($lease->tenant->email)
                                <small class="text-muted">{{ $lease->tenant->email }}</small>
                                @endif
                            </div>
                        </div>
                        
                        @if ($lease->tenant->phone)
                        <div class="mb-2">
                            <small class="text-muted">Số điện thoại:</small><br>
                            <strong>{{ $lease->tenant->phone }}</strong>
                        </div>
                        @endif

                        <div class="mb-2">
                            <small class="text-muted">Trạng thái:</small><br>
                            @if ($lease->tenant->status)
                            <span class="badge bg-success">Hoạt động</span>
                            @else
                            <span class="badge bg-warning">Tạm ngưng</span>
                            @endif
                        </div>
                        @else
                        <div class="text-muted">Không có thông tin khách thuê</div>
                        @endif
                    </div>
                </div>

                <!-- Agent Information -->
                @if ($lease->agent)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Nhân viên phụ trách</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $lease->agent->full_name }}</h6>
                                @if ($lease->agent->email)
                                <small class="text-muted">{{ $lease->agent->email }}</small>
                                @endif
                            </div>
                        </div>
                        
                        @if ($lease->agent->phone)
                        <div class="mb-2">
                            <small class="text-muted">Số điện thoại:</small><br>
                            <strong>{{ $lease->agent->phone }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Organization Information -->
                @if ($lease->organization)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Tổ chức</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-building text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $lease->organization->name }}</h6>
                                @if ($lease->organization->email)
                                <small class="text-muted">{{ $lease->organization->email }}</small>
                                @endif
                            </div>
                        </div>
                        
                        @if ($lease->organization->phone)
                        <div class="mb-2">
                            <small class="text-muted">Số điện thoại:</small><br>
                            <strong>{{ $lease->organization->phone }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Hành động</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('manager.leases.edit', $lease->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <button class="btn btn-outline-danger" onclick="deleteLease({{ $lease->id }}, '{{ $lease->contract_no ?? 'Hợp đồng #' . $lease->id }}')">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
function deleteLease(id, name) {
    Notify.confirmDelete(`hợp đồng "${name}"`, () => {
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

        fetch(`/manager/leases/${id}`, {
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
                    window.location.href = '{{ route("manager.leases.index") }}';
                }, 1000);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể xóa hợp đồng: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
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
