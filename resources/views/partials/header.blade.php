<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
			<div class="me-2 p-2 rounded" style="background: linear-gradient(135deg, #ff6b35, #ff8563);">
				<i class="fas fa-home text-white"></i>
			</div>
			<span class="fw-bold" style="color: #ff6b35;">StayConnect</span>
		</a>
		
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
			<span class="navbar-toggler-icon"></span>
		</button>
		
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav me-auto">
				<li class="nav-item">
					<a class="nav-link fw-500" href="{{ route('home') }}">Trang chủ</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle fw-500" href="{{ route('property.index') }}" data-bs-toggle="dropdown">Cho thuê</a>
					<ul class="dropdown-menu">
						@php
							$propertyTypes = \App\Models\PropertyType::where('status', 1)
								->whereNull('deleted_at')
								->orderBy('name')
								->get();
						@endphp
						@foreach($propertyTypes as $type)
							<li><a class="dropdown-item" href="{{ route('property.index', ['property_type' => $type->id]) }}">
								@if($type->icon)
									<i class="{{ $type->icon }} me-2"></i>
								@else
									🏠
								@endif
								{{ $type->name }}
							</a></li>
						@endforeach
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="{{ route('property.index') }}">📋 Xem tất cả</a></li>
					</ul>
				</li>
				<li class="nav-item">
					<a class="nav-link fw-500" href="{{ route('news.index') }}">Tin tức</a>
				</li>
                    <li class="nav-item">
                        <a class="nav-link fw-500" href="{{ route('contact') }}">Liên hệ</a>
                    </li>
			</ul>
			
			<div class="d-flex align-items-center gap-2">
				<div class="d-none d-lg-flex">
					<div class="input-group" style="width: 300px;">
						<input type="text" class="form-control" placeholder="Tìm kiếm phòng trọ...">
						<button class="btn text-white" style="background: #ff6b35;">
							<i class="fas fa-search"></i>
						</button>
					</div>
				</div>
				
				<button class="btn btn-outline-danger d-none d-md-inline-flex">
					<i class="fas fa-heart me-1"></i>
				</button>
				
				<!-- Notifications Dropdown -->
				<div class="dropdown d-none d-md-inline-block">
					<button class="btn btn-outline-warning position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown">
						<i class="fas fa-bell"></i>
						<span class="notification-badge">5</span>
					</button>
					<div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
						<div class="notification-header">
							<h6>Thông báo</h6>
							<a href="{{ route('tenant.notifications') }}" class="view-all">Xem tất cả</a>
						</div>
						<div class="notification-items">
							<div class="notification-item unread">
								<div class="item-icon urgent">
									<i class="fas fa-exclamation-triangle"></i>
								</div>
								<div class="item-content">
									<div class="item-title">Hóa đơn quá hạn</div>
									<div class="item-message">HD2023001 đã quá hạn 3 ngày</div>
									<div class="item-time">2 giờ trước</div>
								</div>
							</div>
							<div class="notification-item unread">
								<div class="item-icon contract">
									<i class="fas fa-file-contract"></i>
								</div>
								<div class="item-content">
									<div class="item-title">Hợp đồng sắp hết hạn</div>
									<div class="item-message">HD2022002 hết hạn trong 7 ngày</div>
									<div class="item-time">1 ngày trước</div>
								</div>
							</div>
							<div class="notification-item unread">
								<div class="item-icon review">
									<i class="fas fa-reply"></i>
								</div>
								<div class="item-content">
									<div class="item-title">Phản hồi đánh giá</div>
									<div class="item-message">Chị Lan đã phản hồi đánh giá</div>
									<div class="item-time">3 giờ trước</div>
								</div>
							</div>
							<div class="notification-item">
								<div class="item-icon appointment">
									<i class="fas fa-calendar-check"></i>
								</div>
								<div class="item-content">
									<div class="item-title">Lịch hẹn xác nhận</div>
									<div class="item-message">Lịch xem phòng đã được xác nhận</div>
									<div class="item-time">5 giờ trước</div>
								</div>
							</div>
						</div>
						<div class="notification-footer">
							<button class="btn btn-sm btn-outline-primary w-100" onclick="markAllHeaderAsRead()">
								<i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
							</button>
						</div>
					</div>
				</div>
				
					@auth
						<!-- User Menu Dropdown -->
						<div class="dropdown d-none d-md-inline-block">
							<button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
								<i class="fas fa-user me-1"></i>
								{{ Auth::user()->name }}
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								<li>
									<div class="dropdown-item user-item">
										<div class="user-info">
											<div class="user-name">{{ Auth::user()->name }}</div>
											<div class="user-role">{{ Auth::user()->role ?? 'User' }}</div>
										</div>
									</div>
								</li>
								<li>
									
									<a class="dropdown-item action-item" href="{{ route('dashboard') }}">
									<i class="fas fa-tachometer-alt"></i>
									<span>Dashboard</span>
									</a>
								</li>
								<li><a class="dropdown-item action-item" href="{{ route('tenant.appointments') }}">
									<i class="fas fa-calendar-alt"></i>
									<span>Lịch đặt của tôi</span>
									</a>
								</li>
								<li>
									
									<a class="dropdown-item action-item" href="{{ route('tenant.contracts.index') }}">
									<i class="fas fa-file-contract"></i>
									<span>Hợp đồng</span>
									</a>
								</li>
								<li>
									
									<a class="dropdown-item action-item" href="{{ route('tenant.invoices.index') }}">
									<i class="fas fa-file-invoice"></i>
									<span>Hóa đơn</span>
									</a>
								</li>
								<li>
									
									<a class="dropdown-item action-item" href="{{ route('tenant.tickets.index') }}">
									<i class="fas fa-tools"></i>
									<span>Sửa chữa</span>
									</a>
								</li>
								<li>
									
									<a class="dropdown-item action-item" href="{{ route('tenant.profile') }}">
									<i class="fas fa-user-circle"></i>
									<span>Hồ sơ cá nhân</span>
									</a>
								</li>
								
							
								<li><hr class="dropdown-divider"></li>
								<li>
									<form method="POST" action="{{ route('logout') }}" class="d-inline">
										@csrf
										<button type="submit" class="dropdown-item logout-item border-0 bg-transparent w-100 text-start">
											<i class="fas fa-sign-out-alt"></i>
											<span>Đăng xuất</span>
										</button>
									</form>
								</li>
							</ul>
						</div>
					@else
						<a href="{{ route('login') }}" class="btn btn-outline-primary d-none d-md-inline-flex">
							<i class="fas fa-user me-1"></i>
						</a>
					@endauth
				
				{{-- <button class="btn btn-primary text-white fw-600">
					<i class="fas fa-plus me-1"></i>Đăng tin
				</button> --}}
			</div>
		</div>
		
		<!-- Mobile Search -->
		<div class="d-lg-none mt-3">
			<div class="search-box input-group">
				<input type="text" class="form-control" placeholder="Tìm kiếm phòng trọ...">
				<i class="fas fa-search search-icon"></i>
				<button class="btn text-white" style="background: #ff6b35;">
					<i class="fas fa-search"></i>
				</button>
			</div>
		</div>
	</div>
</nav>

<style>
/* Enhanced Dropdown Menu Styles */
.navbar .dropdown-menu {
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    padding: 8px 0;
    margin-top: 8px;
    background: white;
    min-width: 220px;
    animation: dropdownFadeIn 0.3s ease-out;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.navbar .dropdown-item {
    padding: 12px 20px;
    color: #374151;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 2px 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar .dropdown-item:hover {
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    color: #1f2937;
    transform: translateX(4px);
}

.navbar .dropdown-item i {
    width: 16px;
    text-align: center;
    color: #6b7280;
    transition: color 0.3s ease;
}

.navbar .dropdown-item:hover i {
    color: #3b82f6;
}

.navbar .dropdown-divider {
    margin: 8px 0;
    border-color: #e5e7eb;
}

/* Enhanced Navbar Links */
.navbar .nav-link {
    color: #374151;
    font-weight: 500;
    padding: 12px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.navbar .nav-link:hover {
    color: #1f2937;
    background: rgba(59, 130, 246, 0.1);
}

.navbar .nav-link.dropdown-toggle::after {
    margin-left: 8px;
    transition: transform 0.3s ease;
}

.navbar .dropdown.show .nav-link.dropdown-toggle::after {
    transform: rotate(180deg);
}

/* User Dropdown Enhancement */
.navbar .dropdown-menu-end {
    right: 0;
    left: auto;
}

.navbar .dropdown-item.user-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    margin-bottom: 8px;
}

.navbar .dropdown-item.user-item:hover {
    background: transparent;
    transform: none;
}

.navbar .dropdown-item.user-item .user-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.navbar .dropdown-item.user-item .user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.95rem;
}

.navbar .dropdown-item.user-item .user-role {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Action Items */
.navbar .dropdown-item.action-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.navbar .dropdown-item.action-item:hover {
    border-left-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.navbar .dropdown-item.action-item i {
    color: #6b7280;
    transition: color 0.3s ease;
}

.navbar .dropdown-item.action-item:hover i {
    color: #3b82f6;
}

/* Logout Button */
.navbar .dropdown-item.logout-item {
    color: #dc2626;
    border-top: 1px solid #f3f4f6;
    margin-top: 8px;
    padding-top: 16px;
}

.navbar .dropdown-item.logout-item:hover {
    background: rgba(220, 38, 38, 0.1);
    color: #b91c1c;
    border-left-color: #dc2626;
}

.navbar .dropdown-item.logout-item i {
    color: #dc2626;
}

.navbar .dropdown-item.logout-item:hover i {
    color: #b91c1c;
}

/* Responsive Enhancements */
@media (max-width: 991.98px) {
    .navbar .dropdown-menu {
        position: static;
        box-shadow: none;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin: 8px 0;
        background: #f9fafb;
    }
    
    .navbar .dropdown-item {
        margin: 1px 4px;
        border-radius: 6px;
    }
    
    .navbar .dropdown-item:hover {
        transform: none;
        background: rgba(59, 130, 246, 0.1);
    }
}

/* Brand Enhancement */
.navbar-brand {
    transition: transform 0.3s ease;
}

.navbar-brand:hover {
    transform: scale(1.05);
}

.navbar-brand .rounded {
    transition: all 0.3s ease;
}

.navbar-brand:hover .rounded {
    transform: rotate(5deg);
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

/* Search Box Enhancement */
.navbar .search-box {
    position: relative;
    max-width: 300px;
}

.navbar .search-box .form-control {
    border-radius: 25px 0 0 25px;
    padding-left: 40px;
    border: 2px solid #e5e7eb;
    border-right: none;
    transition: all 0.3s ease;
}

.navbar .search-box .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    z-index: 2;
}

.navbar .search-box .search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    transition: color 0.3s ease;
    z-index: 3;
    pointer-events: none;
}

.navbar .search-box .form-control:focus + .search-icon {
    color: #3b82f6;
}

.navbar .search-box .btn {
    border-radius: 0 25px 25px 0;
    border: 2px solid #ff6b35;
    border-left: none;
    transition: all 0.3s ease;
}

.navbar .search-box .btn:hover {
    background: #e55a2b !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

/* Button Enhancements */
.navbar .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.navbar .btn-outline-primary {
    border-color: #3b82f6;
    color: #3b82f6;
}

.navbar .btn-outline-primary:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.navbar .btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border: none;
}

.navbar .btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
</style>