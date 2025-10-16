# Hướng dẫn Hệ thống Upload Ảnh

## Tổng quan
Hệ thống upload ảnh đã được xây dựng với khả năng mở rộng cho production, hỗ trợ cả local storage và cloud storage (AWS S3).

## Cấu trúc hệ thống

### 1. ImageService (`app/Services/ImageService.php`)
- Xử lý upload, resize, và quản lý ảnh
- Tự động tạo thumbnails (small, medium, large)
- Hỗ trợ validation ảnh
- Tương thích với local và S3 storage

### 2. ImageController (`app/Http/Controllers/Api/ImageController.php`)
- API endpoints cho upload/delete ảnh
- Validation và error handling
- Hỗ trợ upload single và multiple images

### 3. Database Schema
- Cột `images` kiểu JSON trong bảng `properties` và `units`
- Lưu trữ array các đường dẫn ảnh

## Cấu hình cho Production

### 1. Environment Variables
Thêm vào file `.env`:

```env
# Image Storage Configuration
IMAGE_STORAGE_DRIVER=s3

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket-name.s3.amazonaws.com
```

### 2. Local Development
```env
IMAGE_STORAGE_DRIVER=local
```

### 3. Cấu hình Filesystem
File `config/filesystems.php` đã được cập nhật với disk `images` tùy chỉnh.

## API Endpoints

### Upload Single Image
```
POST /api/images/upload
Content-Type: multipart/form-data

Parameters:
- image: file (required)
- folder: string (optional, default: 'general')
```

### Upload Multiple Images
```
POST /api/images/upload-multiple
Content-Type: multipart/form-data

Parameters:
- images[]: file[] (required, max 10 files)
- folder: string (optional, default: 'general')
```

### Delete Image
```
DELETE /api/images/delete
Content-Type: application/json

Body:
{
    "path": "images/properties/2024/01/image.jpg"
}
```

### Get Image URL
```
GET /api/images/url?path=images/properties/2024/01/image.jpg&size=medium
```

## Cấu trúc thư mục

### Local Storage
```
storage/app/public/images/
├── properties/
│   ├── 2024/
│   │   └── 01/
│   │       ├── image1.jpg
│   │       └── thumbnails/
│   │           ├── small_image1.jpg
│   │           ├── medium_image1.jpg
│   │           └── large_image1.jpg
│   └── 2024/02/
└── units/
    └── 2024/01/
```

### S3 Storage
```
bucket-name/
├── images/
│   ├── properties/
│   │   └── 2024/01/
│   └── units/
│       └── 2024/01/
```

## Validation Rules

### File Upload
- **Types**: JPEG, PNG, GIF, WebP
- **Size**: Tối đa 5MB mỗi file
- **Dimensions**: Tối đa 4000x4000 pixels
- **Multiple files**: Tối đa 10 files mỗi lần upload

### Thumbnail Sizes
- **Small**: 150x150px
- **Medium**: 300x300px
- **Large**: 600x600px

## Sử dụng trong Controllers

### PropertyController
```php
// Upload images
if ($request->hasFile('images')) {
    $uploadedImages = $this->imageService->uploadMultipleImages(
        $request->file('images'), 
        'properties'
    );
    $imagePaths = array_column($uploadedImages, 'original');
}

// Delete images
if ($request->has('deleted_images')) {
    $this->imageService->deleteMultipleImages($request->deleted_images);
}
```

### UnitController
```php
// Upload images
if ($request->hasFile('images')) {
    $uploadedImages = $this->imageService->uploadMultipleImages(
        $request->file('images'), 
        'units'
    );
    $imagePaths = array_column($uploadedImages, 'original');
}
```

## Frontend Integration

### Form Upload
```html
<form enctype="multipart/form-data">
    <input type="file" name="images[]" accept="image/*" multiple>
</form>
```

### JavaScript Preview
```javascript
function previewImage(input) {
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('preview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}
```

## Triển khai Production

### 1. Cài đặt AWS S3
1. Tạo S3 bucket
2. Cấu hình CORS policy
3. Tạo IAM user với quyền S3
4. Cập nhật environment variables

### 2. CORS Policy cho S3
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::your-bucket-name/*"
        }
    ]
}
```

### 3. IAM Policy
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:PutObjectAcl",
                "s3:GetObject",
                "s3:DeleteObject"
            ],
            "Resource": "arn:aws:s3:::your-bucket-name/*"
        }
    ]
}
```

## Performance Optimization

### 1. CDN Integration
- Sử dụng CloudFront với S3
- Cấu hình cache headers
- Enable gzip compression

### 2. Image Optimization
- Tự động resize ảnh
- Tạo thumbnails
- Compress ảnh (quality: 85%)

### 3. Lazy Loading
```html
<img src="placeholder.jpg" data-src="actual-image.jpg" loading="lazy">
```

## Monitoring & Maintenance

### 1. Storage Statistics
```php
$stats = $imageService->getStorageStats();
// Returns: total_size, total_size_mb, file_count
```

### 2. Cleanup Script
```php
// Xóa ảnh không sử dụng
$unusedImages = $this->findUnusedImages();
$this->imageService->deleteMultipleImages($unusedImages);
```

### 3. Backup Strategy
- S3 versioning
- Cross-region replication
- Regular backups

## Troubleshooting

### Common Issues

1. **Storage link not working**
   ```bash
   php artisan storage:link
   ```

2. **Permission denied**
   ```bash
   chmod -R 755 storage/
   chown -R www-data:www-data storage/
   ```

3. **S3 connection failed**
   - Kiểm tra AWS credentials
   - Verify bucket permissions
   - Check network connectivity

### Debug Commands
```bash
# Test storage connection
php artisan tinker
>>> Storage::disk('images')->put('test.txt', 'test');

# Check image service
>>> app(\App\Services\ImageService::class)->getStorageStats();
```

## Security Considerations

1. **File Validation**: Luôn validate file type và size
2. **Access Control**: Restrict upload permissions
3. **Virus Scanning**: Implement virus scanning for uploads
4. **Rate Limiting**: Limit upload frequency
5. **HTTPS**: Sử dụng HTTPS cho tất cả uploads

## Migration từ URL sang File Upload

### 1. Backup dữ liệu hiện tại
```sql
SELECT id, images FROM properties WHERE images IS NOT NULL;
SELECT id, images FROM units WHERE images IS NOT NULL;
```

### 2. Download và re-upload ảnh
```php
// Script để migrate existing URLs
foreach ($properties as $property) {
    if ($property->images) {
        $newPaths = [];
        foreach ($property->images as $url) {
            $newPath = $this->downloadAndUploadImage($url, 'properties');
            $newPaths[] = $newPath;
        }
        $property->update(['images' => $newPaths]);
    }
}
```

Hệ thống đã sẵn sàng cho production với khả năng mở rộng cao và bảo mật tốt!
