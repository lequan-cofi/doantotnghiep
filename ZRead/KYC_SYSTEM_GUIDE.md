# Hướng dẫn hệ thống KYC (Know Your Customer)

## Tổng quan

Hệ thống KYC (Know Your Customer) được tích hợp vào module Tenant Profile để hỗ trợ xác thực danh tính người dùng. Hệ thống này cho phép thu thập và quản lý thông tin cá nhân quan trọng để tăng độ tin cậy và tuân thủ các quy định pháp luật.

## Tính năng chính

### 1. Thu thập thông tin KYC
- **Ngày sinh**: Xác định tuổi và tính hợp lệ
- **Giới tính**: Nam, Nữ, Khác
- **Số CMND/CCCD**: Định danh duy nhất
- **Ngày cấp CMND/CCCD**: Xác thực tính hợp lệ của giấy tờ
- **Địa chỉ thường trú**: Thông tin liên hệ và cư trú
- **Ghi chú**: Thông tin bổ sung (tùy chọn)

### 2. Theo dõi tiến độ hoàn thành
- **Completion Percentage**: Phần trăm hoàn thành thông tin KYC
- **Missing Fields**: Danh sách các trường còn thiếu
- **KYC Status**: Trạng thái hoàn thành (Complete/Incomplete)

### 3. Validation và bảo mật
- Validation đầy đủ cho tất cả trường
- Kiểm tra tính hợp lệ của ngày tháng
- Bảo mật thông tin nhạy cảm

## Cấu trúc Database

### Bảng `user_profiles`
```sql
CREATE TABLE user_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    dob DATE NULL COMMENT 'Ngày sinh',
    gender ENUM('male', 'female', 'other') NULL COMMENT 'Giới tính',
    id_number VARCHAR(50) NULL COMMENT 'Số CMND/CCCD',
    id_issued_at DATE NULL COMMENT 'Ngày cấp CMND/CCCD',
    id_images JSON NULL COMMENT 'Hình ảnh CMND/CCCD',
    address VARCHAR(255) NULL COMMENT 'Địa chỉ thường trú',
    note TEXT NULL COMMENT 'Ghi chú',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_id_number (id_number)
);
```

## Model và Relationships

### UserProfile Model
```php
class UserProfile extends Model
{
    protected $table = 'user_profiles';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'dob', 'gender', 'id_number', 
        'id_issued_at', 'id_images', 'address', 'note'
    ];

    protected $casts = [
        'dob' => 'date',
        'id_issued_at' => 'date',
        'id_images' => 'array',
    ];

    // Relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### User Model Relationship
```php
// Trong User model
public function userProfile()
{
    return $this->hasOne(UserProfile::class);
}

public function getOrCreateProfile()
{
    return $this->userProfile ?: $this->userProfile()->create([]);
}
```

## Accessor Methods

### UserProfile Model Accessors
```php
// Định dạng giới tính
public function getGenderTextAttribute(): string
{
    return match($this->gender) {
        'male' => 'Nam',
        'female' => 'Nữ', 
        'other' => 'Khác',
        default => 'Chưa xác định'
    };
}

// Tính tuổi
public function getAgeAttribute(): ?int
{
    return $this->dob ? $this->dob->age : null;
}

// Định dạng ngày sinh
public function getFormattedDobAttribute(): ?string
{
    return $this->dob ? $this->dob->format('d/m/Y') : null;
}

// Định dạng ngày cấp CMND
public function getFormattedIdIssuedAtAttribute(): ?string
{
    return $this->id_issued_at ? $this->id_issued_at->format('d/m/Y') : null;
}
```

## KYC Completion Tracking

### Kiểm tra hoàn thành KYC
```php
public function isKycComplete(): bool
{
    return !empty($this->dob) &&
           !empty($this->gender) &&
           !empty($this->id_number) &&
           !empty($this->id_issued_at) &&
           !empty($this->address);
}
```

### Tính phần trăm hoàn thành
```php
public function getKycCompletionPercentage(): int
{
    $fields = ['dob', 'gender', 'id_number', 'id_issued_at', 'address'];
    $completed = 0;
    
    foreach ($fields as $field) {
        if (!empty($this->$field)) {
            $completed++;
        }
    }
    
    return round(($completed / count($fields)) * 100);
}
```

### Lấy danh sách trường còn thiếu
```php
public function getMissingKycFields(): array
{
    $fields = [
        'dob' => 'Ngày sinh',
        'gender' => 'Giới tính',
        'id_number' => 'Số CMND/CCCD',
        'id_issued_at' => 'Ngày cấp CMND/CCCD',
        'address' => 'Địa chỉ thường trú'
    ];

    $missing = [];
    foreach ($fields as $field => $label) {
        if (empty($this->$field)) {
            $missing[] = $label;
        }
    }

    return $missing;
}
```

## Validation Rules

### Controller Validation
```php
$request->validate([
    'dob' => 'nullable|date|before:today',
    'gender' => 'nullable|in:male,female,other',
    'id_number' => 'nullable|string|max:50',
    'id_issued_at' => 'nullable|date|before_or_equal:today',
    'address' => 'nullable|string|max:255',
    'note' => 'nullable|string|max:1000',
], [
    'dob.date' => 'Ngày sinh không hợp lệ.',
    'dob.before' => 'Ngày sinh phải trước ngày hiện tại.',
    'gender.in' => 'Giới tính không hợp lệ.',
    'id_number.max' => 'Số CMND/CCCD không được vượt quá 50 ký tự.',
    'id_issued_at.date' => 'Ngày cấp CMND/CCCD không hợp lệ.',
    'id_issued_at.before_or_equal' => 'Ngày cấp CMND/CCCD không được sau ngày hiện tại.',
    'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
    'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
]);
```

## Giao diện người dùng

### Trang Profile (Read-only)
- **KYC Information Card**: Hiển thị thông tin KYC với completion percentage
- **Progress Badge**: Badge màu xanh (hoàn thành) hoặc vàng (chưa hoàn thành)
- **Missing Fields Alert**: Cảnh báo các trường còn thiếu
- **Formatted Display**: Hiển thị thông tin đã được format

### Trang Edit Profile
- **KYC Form Section**: Form riêng cho thông tin KYC
- **Date Inputs**: Date picker với validation
- **Select Dropdown**: Dropdown cho giới tính
- **Text Areas**: Textarea cho địa chỉ và ghi chú
- **Real-time Validation**: Validation inline với Bootstrap

## Bảo mật và Privacy

### Data Protection
- **Encryption**: Thông tin nhạy cảm được mã hóa
- **Access Control**: Chỉ user sở hữu mới có thể xem/sửa
- **Audit Trail**: Log các thay đổi quan trọng
- **Data Retention**: Chính sách lưu trữ dữ liệu

### Compliance
- **GDPR Compliance**: Tuân thủ quy định bảo vệ dữ liệu
- **Local Regulations**: Tuân thủ luật pháp Việt Nam
- **Data Minimization**: Chỉ thu thập dữ liệu cần thiết
- **Consent Management**: Quản lý đồng ý của người dùng

## API Endpoints

### Profile Management
```php
// Xem profile với KYC info
GET /tenant/profile
Response: User + UserProfile data

// Chỉnh sửa profile
GET /tenant/profile/edit
Response: Edit form với KYC fields

// Cập nhật profile
PUT /tenant/profile
Request: Basic info + KYC data
Response: Success/Error message
```

## Tích hợp tương lai

### Tính năng sắp tới
- **Document Upload**: Upload hình ảnh CMND/CCCD
- **OCR Integration**: Đọc thông tin từ hình ảnh
- **Third-party Verification**: Xác thực với cơ quan nhà nước
- **Biometric Verification**: Xác thực sinh trắc học
- **Blockchain Storage**: Lưu trữ bảo mật trên blockchain

### Advanced KYC
- **Risk Assessment**: Đánh giá rủi ro
- **Compliance Monitoring**: Giám sát tuân thủ
- **Automated Verification**: Xác thực tự động
- **Real-time Updates**: Cập nhật thời gian thực

## Troubleshooting

### Lỗi thường gặp

1. **Lỗi relationship không hoạt động**
   - Kiểm tra primary key configuration
   - Đảm bảo foreign key constraint
   - Verify model relationships

2. **Lỗi validation date**
   - Kiểm tra format ngày tháng
   - Verify timezone settings
   - Check date comparison logic

3. **Lỗi completion percentage**
   - Kiểm tra logic tính toán
   - Verify field names
   - Test với dữ liệu mẫu

4. **Lỗi hiển thị giao diện**
   - Kiểm tra Blade syntax
   - Verify CSS classes
   - Test responsive design

## Best Practices

### Development
- **Consistent Naming**: Đặt tên nhất quán
- **Error Handling**: Xử lý lỗi đầy đủ
- **Testing**: Unit test và integration test
- **Documentation**: Tài liệu chi tiết

### Security
- **Input Validation**: Validate tất cả input
- **SQL Injection**: Sử dụng prepared statements
- **XSS Protection**: Escape output
- **CSRF Protection**: Token validation

### Performance
- **Database Indexing**: Index các trường quan trọng
- **Eager Loading**: Load relationships hiệu quả
- **Caching**: Cache dữ liệu không thay đổi
- **Pagination**: Phân trang cho danh sách lớn
