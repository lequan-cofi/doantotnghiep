# Fix: Lỗi SQL Usage Keyword

## Vấn đề
```
SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'usage,
                COUNT(*) as reading_count,
                MIN(reading_' at line 5
```

## Nguyên nhân
Từ `usage` là **reserved keyword** trong MySQL, không thể sử dụng trực tiếp trong SQL query.

## Giải pháp đã áp dụng

### 1. Sửa SQL Query
```php
// Trước (SAI):
->selectRaw('
    DATE_FORMAT(reading_date, "%Y-%m") as month,
    MIN(value) as start_reading,
    MAX(value) as end_reading,
    MAX(value) - MIN(value) as usage,  // ❌ Lỗi: usage là reserved keyword
    COUNT(*) as reading_count,
    MIN(reading_date) as first_reading_date,
    MAX(reading_date) as last_reading_date
')

// Sau (ĐÚNG):
->selectRaw('
    DATE_FORMAT(reading_date, "%Y-%m") as month,
    MIN(value) as start_reading,
    MAX(value) as end_reading,
    MAX(value) - MIN(value) as `usage`,  // ✅ Đúng: sử dụng backticks
    COUNT(*) as reading_count,
    MIN(reading_date) as first_reading_date,
    MAX(reading_date) as last_reading_date
')
```

### 2. File đã sửa
- **File**: `app/Services/MeterBillingService.php`
- **Method**: `getBillingHistory()`
- **Line**: 223

## MySQL Reserved Keywords

Các từ khóa reserved trong MySQL cần sử dụng backticks:
- `usage`
- `order`
- `group`
- `select`
- `where`
- `from`
- `table`
- `index`
- `key`
- `value`
- `date`
- `time`
- `year`
- `month`
- `day`
- `hour`
- `minute`
- `second`

## Cách sử dụng backticks

### Đúng:
```sql
SELECT `usage`, `order`, `group` FROM table_name;
```

### Sai:
```sql
SELECT usage, order, group FROM table_name;  -- Lỗi syntax
```

## Test sau khi fix

### 1. Test Meter Show Page
```
URL: http://127.0.0.1:8000/agent/meters/1
```

### 2. Expected Results
- ✅ Page load thành công
- ✅ Hiển thị billing history
- ✅ Không có SQL errors

### 3. Kiểm tra Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

## Code Changes Made

### app/Services/MeterBillingService.php
```php
public function getBillingHistory($meterId, $limit = 12)
{
    return MeterReading::where('meter_id', $meterId)
        ->with(['meter.service'])
        ->selectRaw('
            DATE_FORMAT(reading_date, "%Y-%m") as month,
            MIN(value) as start_reading,
            MAX(value) as end_reading,
            MAX(value) - MIN(value) as `usage`,  // ✅ Fixed: added backticks
            COUNT(*) as reading_count,
            MIN(reading_date) as first_reading_date,
            MAX(reading_date) as last_reading_date
        ')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit($limit)
        ->get()
        ->map(function($item) use ($meterId) {
            // ... rest of the method
        });
}
```

## Prevention Tips

### 1. Sử dụng backticks cho tất cả column names
```php
->selectRaw('
    `column1`,
    `column2`,
    `usage`,
    `order`
')
```

### 2. Kiểm tra MySQL documentation
- [MySQL Reserved Keywords](https://dev.mysql.com/doc/refman/8.0/en/keywords.html)

### 3. Sử dụng Laravel Query Builder
```php
// Thay vì raw SQL
->selectRaw('MAX(value) - MIN(value) as `usage`')

// Có thể sử dụng
->selectRaw('MAX(value) - MIN(value) as usage_amount')
```

## Troubleshooting

### Nếu vẫn có lỗi SQL:
1. Kiểm tra tất cả reserved keywords
2. Thêm backticks cho tất cả column names
3. Kiểm tra MySQL version compatibility

### Nếu có lỗi khác:
1. Kiểm tra Laravel logs
2. Kiểm tra database connection
3. Kiểm tra table structure

## Expected Results

Sau khi fix:
- ✅ Meter show page load thành công
- ✅ Billing history hiển thị đúng
- ✅ Không có SQL syntax errors
- ✅ Tất cả functionality hoạt động bình thường

---

**Lưu ý**: Luôn sử dụng backticks cho column names trong raw SQL queries để tránh conflicts với reserved keywords.
