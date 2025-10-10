<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
			<div class="me-2 p-2 rounded" style="background: linear-gradient(135deg, #ff6b35, #ff8563);">
				<i class="fas fa-home text-white"></i>
			</div>
			<span class="fw-bold" style="color: #ff6b35;">PhòngTrọ24</span>
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
					<a class="nav-link dropdown-toggle fw-500" href="{{ route('rooms.index') }}" data-bs-toggle="dropdown">Cho thuê</a>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="{{ route('rooms.index', ['type' => 'phongtro']) }}">🏠 Phòng trọ</a></li>
						<li><a class="dropdown-item" href="{{ route('rooms.index', ['type' => 'chungcumini']) }}">🏢 Chung cư mini</a></li>
						<li><a class="dropdown-item" href="{{ route('rooms.index', ['type' => 'nhanguyencan']) }}">🏘️ Nhà nguyên căn</a></li>
						<li><a class="dropdown-item" href="{{ route('rooms.index', ['type' => 'matbang']) }}">🏪 Mặt bằng</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="{{ route('rooms.index') }}">📋 Xem tất cả</a></li>
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
						<a href="{{ route('dashboard') }}" class="btn btn-outline-primary d-none d-md-inline-flex">
							<i class="fas fa-user me-1"></i>
						</a>
					@else
						<a href="{{ route('login') }}" class="btn btn-outline-primary d-none d-md-inline-flex">
							<i class="fas fa-user me-1"></i>
						</a>
					@endauth
				
				<button class="btn text-white fw-600" style="background: linear-gradient(135deg, #ff6b35, #ff8563);">
					<i class="fas fa-plus me-1"></i>Đăng tin
				</button>
			</div>
		</div>
		
		<!-- Mobile Search -->
		<div class="d-lg-none mt-3">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="Tìm kiếm phòng trọ...">
				<button class="btn text-white" style="background: #ff6b35;">
					<i class="fas fa-search"></i>
				</button>
			</div>
		</div>
	</div>
</nav>