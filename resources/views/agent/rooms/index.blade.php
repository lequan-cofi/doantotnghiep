@extends('layouts.agent_dashboad')

@section('title', 'Quản lý phòng trọ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/rooms.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/agent/rooms.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<!-- Main Content -->
<main class="main-content">
    <!-- Header -->
    <header class="bg-white border-bottom py-4 mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-building text-primary fs-4"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold text-dark">Quản lý phòng trọ</h1>
                            <p class="text-muted mb-0">Quản lý danh sách phòng trọ trong hệ thống</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary" onclick="exportRooms()">
                            <i class="fas fa-download me-2"></i>
                            Xuất Excel
                        </button>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus me-2"></i>
                            Thêm phòng mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Dashboard Content -->
    <div class="container-fluid" id="content">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="card-title text-muted mb-1">Tổng số phòng</h6>
                                <h3 class="mb-0 fw-bold text-dark">120</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-building text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-arrow-up me-1"></i>+15%
                            </span>
                            <small class="text-muted">So với tháng trước</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="card-title text-muted mb-1">Phòng đang cho thuê</h6>
                                <h3 class="mb-0 fw-bold text-dark">85</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-key text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-arrow-up me-1"></i>+8%
                            </span>
                            <small class="text-muted">Tỷ lệ lấp đầy 70.8%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="card-title text-muted mb-1">Phòng trống</h6>
                                <h3 class="mb-0 fw-bold text-dark">35</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-home text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning bg-opacity-10 text-warning me-2">
                                <i class="fas fa-arrow-down me-1"></i>-3%
                            </span>
                            <small class="text-muted">Cần quảng cáo</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="card-title text-muted mb-1">Doanh thu tháng</h6>
                                <h3 class="mb-0 fw-bold text-dark">120M</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-dollar-sign text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-arrow-up me-1"></i>+12%
                            </span>
                            <small class="text-muted">So với tháng trước</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-filter text-primary me-2"></i>
                            Bộ lọc và tìm kiếm
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>
                            Xóa bộ lọc
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-medium">Tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Tìm theo tên, địa chỉ, mô tả..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-medium">Loại phòng</label>
                        <select class="form-select" id="typeFilter">
                            <option value="">Tất cả loại</option>
                            <option value="phongtro">Phòng trọ</option>
                            <option value="chungcumini">Chung cư mini</option>
                            <option value="nhanguyencan">Nhà nguyên căn</option>
                            <option value="matbang">Mặt bằng</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-medium">Trạng thái</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="available">Còn trống</option>
                            <option value="rented">Đã cho thuê</option>
                            <option value="maintenance">Bảo trì</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-medium">Khoảng giá</label>
                        <select class="form-select" id="priceFilter">
                            <option value="">Tất cả mức giá</option>
                            <option value="0-2">Dưới 2 triệu</option>
                            <option value="2-5">2 - 5 triệu</option>
                            <option value="5-10">5 - 10 triệu</option>
                            <option value="10-20">10 - 20 triệu</option>
                            <option value="20+">Trên 20 triệu</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-medium">Quận/Huyện</label>
                        <select class="form-select" id="districtFilter">
                            <option value="">Tất cả quận</option>
                            <option value="caugiay">Cầu Giấy</option>
                            <option value="hoangmai">Hoàng Mai</option>
                            <option value="dongda">Đống Đa</option>
                            <option value="thanhxuan">Thanh Xuân</option>
                            <option value="hadong">Hà Đông</option>
                            <option value="longbien">Long Biên</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-medium">Sắp xếp</label>
                        <select class="form-select" id="sortFilter">
                            <option value="newest">Mới nhất</option>
                            <option value="oldest">Cũ nhất</option>
                            <option value="price-asc">Giá thấp đến cao</option>
                            <option value="price-desc">Giá cao đến thấp</option>
                            <option value="area-asc">Diện tích nhỏ đến lớn</option>
                            <option value="area-desc">Diện tích lớn đến nhỏ</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-building text-primary me-2"></i>
                            Danh sách phòng trọ
                        </h4>
                        <small class="text-muted">Hiển thị <strong id="displayCount">24</strong> trong tổng số <strong>120</strong> phòng</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group me-2" role="group">
                            <button class="btn btn-outline-secondary btn-sm view-btn active" data-view="table" title="Xem dạng bảng">
                                <i class="fas fa-table"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm view-btn" data-view="grid" title="Xem dạng lưới">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshTable()">
                            <i class="fas fa-sync-alt"></i>
                            Làm mới
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="roomsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </div>
                                </th>
                                <th class="border-0" style="width: 100px;">Hình ảnh</th>
                                <th class="border-0">Thông tin phòng</th>
                                <th class="border-0">Địa chỉ</th>
                                <th class="border-0 text-end" style="width: 120px;">Giá thuê</th>
                                <th class="border-0 text-center" style="width: 100px;">Diện tích</th>
                                <th class="border-0 text-center" style="width: 120px;">Trạng thái</th>
                                <th class="border-0 text-center" style="width: 120px;">Ngày tạo</th>
                                <th class="border-0 text-center" style="width: 150px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="roomsTableBody">
                        <!-- Room 1 -->
                        <tr class="room-row align-middle" data-id="1">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input room-checkbox" type="checkbox" value="1">
                                </div>
                            </td>
                            <td>
                                <div class="position-relative">
                                    <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=80&h=60&fit=crop" 
                                         alt="Phòng trọ" class="rounded" style="width: 80px; height: 60px; object-fit: cover;">
                                    <span class="badge bg-dark position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">5 ảnh</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 fw-bold text-dark">Phòng trọ cao cấp Cầu Giấy</h6>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">🏠 Phòng trọ</span>
                                        <small class="text-muted">#R001</small>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.7rem;">WiFi</span>
                                        <span class="badge bg-info bg-opacity-10 text-info" style="font-size: 0.7rem;">Điều hòa</span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 0.7rem;">Máy giặt</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                                    <small class="text-muted">123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="fw-bold text-danger fs-6">2.500.000</span>
                                    <small class="text-muted">VNĐ/tháng</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">25m²</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">Còn trống</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column">
                                    <small class="fw-medium">25/12/2023</small>
                                    <small class="text-muted">14:30</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewRoom(1)" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="editRoom(1)" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="duplicateRoom(1)" title="Sao chép">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Thêm">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="toggleStatus(1)">Thay đổi trạng thái</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="viewHistory(1)">Lịch sử</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteRoom(1)">Xóa</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Room 2 -->
                        <tr class="room-row align-middle" data-id="2">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input room-checkbox" type="checkbox" value="2">
                                </div>
                            </td>
                            <td>
                                <div class="position-relative">
                                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=80&h=60&fit=crop" 
                                         alt="Chung cư mini" class="rounded" style="width: 80px; height: 60px; object-fit: cover;">
                                    <span class="badge bg-dark position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">8 ảnh</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 fw-bold text-dark">Chung cư mini Mạnh Hà</h6>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-info bg-opacity-10 text-info">🏢 Chung cư mini</span>
                                        <small class="text-muted">#R002</small>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.7rem;">WiFi</span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.7rem;">Thang máy</span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 0.7rem;">Ban công</span>
                                        <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.7rem;">Bảo vệ 24/7</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                                    <small class="text-muted">456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="fw-bold text-danger fs-6">10.000.000</span>
                                    <small class="text-muted">VNĐ/tháng</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">45m²</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">Đã cho thuê</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column">
                                    <small class="fw-medium">24/12/2023</small>
                                    <small class="text-muted">09:15</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewRoom(2)" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="editRoom(2)" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="duplicateRoom(2)" title="Sao chép">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Thêm">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="toggleStatus(2)">Thay đổi trạng thái</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="viewHistory(2)">Lịch sử</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteRoom(2)">Xóa</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Room 3 -->
                        <tr class="room-row align-middle" data-id="3">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input room-checkbox" type="checkbox" value="3">
                                </div>
                            </td>
                            <td>
                                <div class="position-relative">
                                    <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=80&h=60&fit=crop" 
                                         alt="Homestay" class="rounded" style="width: 80px; height: 60px; object-fit: cover;">
                                    <span class="badge bg-dark position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">6 ảnh</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 fw-bold text-dark">Homestay Hạnh Đào</h6>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">🏠 Phòng trọ</span>
                                        <small class="text-muted">#R003</small>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.7rem;">WiFi</span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 0.7rem;">Bếp riêng</span>
                                        <span class="badge bg-info bg-opacity-10 text-info" style="font-size: 0.7rem;">Gác lửng</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                                    <small class="text-muted">789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="fw-bold text-danger fs-6">8.000.000</span>
                                    <small class="text-muted">VNĐ/tháng</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">35m²</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">Bảo trì</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column">
                                    <small class="fw-medium">23/12/2023</small>
                                    <small class="text-muted">16:45</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewRoom(3)" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="editRoom(3)" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="duplicateRoom(3)" title="Sao chép">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Thêm">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="toggleStatus(3)">Thay đổi trạng thái</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="viewHistory(3)">Lịch sử</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteRoom(3)">Xóa</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">Hiển thị 1-24 trong tổng số 120 phòng</small>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Rooms pagination">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">4</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">5</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Create/Edit Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalTitle">Thêm phòng mới</h5>
                <a href="{{ route('agent.rooms.create') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-plus me-1"></i>
                    Thêm phòng mới
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                
            </div>
            <div class="modal-body">
                <form id="roomForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-section">
                                <h6 class="section-title">Thông tin cơ bản</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="roomTitle" class="form-label">Tên phòng <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="roomTitle" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="roomType" class="form-label">Loại phòng <span class="required">*</span></label>
                                        <select class="form-select" id="roomType" required>
                                            <option value="">Chọn loại phòng</option>
                                            <option value="phongtro">Phòng trọ</option>
                                            <option value="chungcumini">Chung cư mini</option>
                                            <option value="nhanguyencan">Nhà nguyên căn</option>
                                            <option value="matbang">Mặt bằng</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="roomPrice" class="form-label">Giá thuê (VNĐ/tháng) <span class="required">*</span></label>
                                        <input type="number" class="form-control" id="roomPrice" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="roomArea" class="form-label">Diện tích (m²) <span class="required">*</span></label>
                                        <input type="number" class="form-control" id="roomArea" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="roomDescription" class="form-label">Mô tả chi tiết</label>
                                    <textarea class="form-control" id="roomDescription" rows="4"></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h6 class="section-title">Địa chỉ</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="roomDistrict" class="form-label">Quận/Huyện <span class="required">*</span></label>
                                        <select class="form-select" id="roomDistrict" required>
                                            <option value="">Chọn quận/huyện</option>
                                            <option value="caugiay">Cầu Giấy</option>
                                            <option value="hoangmai">Hoàng Mai</option>
                                            <option value="dongda">Đống Đa</option>
                                            <option value="thanhxuan">Thanh Xuân</option>
                                            <option value="hadong">Hà Đông</option>
                                            <option value="longbien">Long Biên</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="roomWard" class="form-label">Phường/Xã</label>
                                        <input type="text" class="form-control" id="roomWard">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="roomAddress" class="form-label">Địa chỉ chi tiết <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="roomAddress" required>
                                </div>
                            </div>

                            <div class="form-section">
                                <h6 class="section-title">Tiện ích</h6>
                                <div class="amenities-grid">
                                    <label class="amenity-item">
                                        <input type="checkbox" value="wifi">
                                        <span class="amenity-icon">📶</span>
                                        <span class="amenity-name">WiFi</span>
                                    </label>
                                    <label class="amenity-item">
                                        <input type="checkbox" value="aircon">
                                        <span class="amenity-icon">❄️</span>
                                        <span class="amenity-name">Điều hòa</span>
                                    </label>
                                    <label class="amenity-item">
                                        <input type="checkbox" value="washing">
                                        <span class="amenity-icon">🧺</span>
                                        <span class="amenity-name">Máy giặt</span>
                                    </label>
                                    <label class="amenity-item">
                                        <input type="checkbox" value="kitchen">
                                        <span class="amenity-icon">🍳</span>
                                        <span class="amenity-name">Bếp</span>
                                    </label>
                                    <label class="amenity-item">
                                        <input type="checkbox" value="parking">
                                        <span class="amenity-icon">🚗</span>
                                        <span class="amenity-name">Chỗ đậu xe</span>
                                    </label>
                                    <label class="amenity-item">
                                        <input type="checkbox" value="security">
                                        <span class="amenity-icon">🔒</span>
                                        <span class="amenity-name">Bảo vệ</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section">
                                <h6 class="section-title">Hình ảnh</h6>
                                <div class="image-upload-area" id="imageUploadArea">
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Kéo thả hình ảnh vào đây hoặc click để chọn</p>
                                        <small>Hỗ trợ JPG, PNG, GIF (tối đa 5MB)</small>
                                    </div>
                                    <input type="file" id="imageInput" multiple accept="image/*" style="display: none;">
                                </div>
                                <div class="image-preview" id="imagePreview"></div>
                            </div>

                            <div class="form-section">
                                <h6 class="section-title">Trạng thái</h6>
                                <div class="mb-3">
                                    <label for="roomStatus" class="form-label">Trạng thái phòng</label>
                                    <select class="form-select" id="roomStatus">
                                        <option value="available">Còn trống</option>
                                        <option value="rented">Đã cho thuê</option>
                                        <option value="maintenance">Bảo trì</option>
                                        <option value="inactive">Không hoạt động</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="roomPriority" class="form-label">Độ ưu tiên</label>
                                    <select class="form-select" id="roomPriority">
                                        <option value="normal">Bình thường</option>
                                        <option value="high">Cao</option>
                                        <option value="premium">Premium</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveRoom()">Lưu phòng</button>
            </div>
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
                <p>Bạn có chắc chắn muốn xóa phòng này không?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Hành động này không thể hoàn tác!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection
