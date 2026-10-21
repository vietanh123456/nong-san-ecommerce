# Nông Sản Việt

Nông Sản Việt là website thương mại điện tử dành cho nông sản và đặc sản vùng miền. Hệ thống hỗ trợ khách hàng mua sắm, người bán quản lý sản phẩm/lô hàng và quản trị viên kiểm duyệt hoạt động trên nền tảng.

Mỗi lô hàng có thể có mã QR truy xuất nguồn gốc. Khi người bán tạo sản phẩm, hệ thống tự tạo lô hàng và QR tương ứng; QR dẫn tới trang công khai `/trace/{ma-lo}` để người mua xem thông tin nguồn gốc và chứng nhận đã được duyệt.

## Công nghệ

- PHP 8.2+ và Laravel 12
- MySQL 8+ làm cơ sở dữ liệu chính
- Blade, Vite 7 và Tailwind CSS 4
- Composer và Node.js/NPM
- `endroid/qr-code` để tạo QR dạng SVG
- Supabase Storage (tùy chọn) để lưu QR công khai
- PHPUnit 11 cho kiểm thử

## Yêu cầu hệ thống

Trước khi cài đặt, cần có:

- PHP 8.2 trở lên với các extension thông dụng: PDO, OpenSSL, Fileinfo, DOM/XML, Ctype, Tokenizer và Session.
- PHP extension `pdo_mysql` để kết nối MySQL.
- Composer 2.
- Node.js 20+ và NPM.
- Git.

Kiểm tra môi trường:

```bash
php -v
composer --version
node -v
npm -v
```

## Cài đặt dự án

### 1. Clone mã nguồn

```bash
git clone https://github.com/vietanh123456/nong-san-ecommerce.git
cd nong-san-ecommerce
```

### 2. Cài dependency

```bash
composer install
npm install
```

### 3. Tạo file môi trường và application key

Windows PowerShell/CMD:

```powershell
copy .env.example .env
php artisan key:generate
```

macOS/Linux:

```bash
cp .env.example .env
php artisan key:generate
```


### 4. Cấu hình ứng dụng

Cấu hình tối thiểu trong `.env`:

```dotenv
APP_NAME="Nông Sản Việt"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
TRACE_PUBLIC_URL=http://127.0.0.1:8000

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

`TRACE_PUBLIC_URL` là domain được ghi vào nội dung QR. URL này phải truy cập được từ thiết bị quét QR. Khi chỉ phát triển trên máy local, có thể để giống `APP_URL`; khi dùng ngrok hoặc deploy, phải đổi thành URL công khai.

Sau mỗi lần thay đổi `.env`, chạy:

```bash
php artisan optimize:clear
```

### 5. Cấu hình MySQL

Khởi động MySQL và tạo database bằng MySQL Workbench, phpMyAdmin hoặc MySQL CLI:

```sql
CREATE DATABASE nong_san_ecommerce
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Cập nhật kết nối trong `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nong_san_ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

Thay `DB_USERNAME` và `DB_PASSWORD` theo tài khoản MySQL trên máy. Sau khi thay cấu hình, kiểm tra kết nối bằng:

```bash
php artisan migrate:status
```

### 6. Khởi tạo database và storage

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
```

`migrate --seed` tạo schema cùng dữ liệu mẫu. `storage:link` cần thiết khi dùng local public storage cho QR hoặc các tệp công khai.

Tài khoản dữ liệu mẫu:

| Vai trò | Email | Mật khẩu |
| --- | --- | --- |
| Quản trị viên | `admin@example.com` | `password` |
| Quản trị viên | `admin@gmail.com` | `password` |
| Người bán mẫu | `test@example.com` | `password` |


### 7. Build frontend

Trong lúc phát triển, chạy Vite dev server:

```bash
npm run dev
```

Để tạo frontend dùng cho production:

```bash
npm run build
```

Kết quả build nằm trong `public/build` và không được commit theo cấu hình `.gitignore` hiện tại.

## Cấu hình lưu QR

### Lưu QR trên máy local

```dotenv
QR_CODE_DISK=public
```

Sau đó bảo đảm đã chạy:

```bash
php artisan storage:link
```

### Lưu QR trên Supabase Storage

1. Tạo một bucket trong Supabase Storage, ví dụ `qr-products`.
2. Đặt bucket ở chế độ **Public** để người mua có thể tải ảnh QR.
3. Lấy Project URL và Secret Key trong Supabase Dashboard.
4. Cấu hình `.env`:

```dotenv
QR_CODE_DISK=supabase
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_SECRET_KEY=your-server-side-secret-key
SUPABASE_BUCKET=qr-products
```

Ứng dụng upload QR qua Supabase Storage REST API, không dùng cấu hình S3. `SUPABASE_SECRET_KEY` chỉ được dùng ở backend, có quyền cao và tuyệt đối không đưa vào JavaScript, Git hoặc tài liệu công khai.

Kiểm tra lại cấu hình sau khi sửa `.env`:

```bash
php artisan optimize:clear
```

Với sản phẩm được import trực tiếp từ SQL và chưa có lô hàng, xem trước
số lượng cần xử lý:

```bash
php artisan qr:backfill --dry-run
```

Tạo lô và QR cho tất cả sản phẩm chưa có lô:

```bash
php artisan qr:backfill
```

Chỉ xử lý một ID sản phẩm:

```bash
php artisan qr:backfill --product=123
```

Lệnh có thể chạy lại an toàn vì sẽ bỏ qua sản phẩm đã có lô hàng.

Tạo lại QR của một lô:

```bash
php artisan qr:regenerate --batch=MA_LO
```

Tạo lại toàn bộ QR:

```bash
php artisan qr:regenerate --all
```

Chỉ chạy `--all` khi `TRACE_PUBLIC_URL` đã đúng. Nếu cả nhóm dùng chung database, không nên tạo lại toàn bộ QR bằng domain ngrok cá nhân.

## Cài đặt và sử dụng ngrok

Ngrok tạo một URL HTTPS công khai trỏ tới Laravel đang chạy trên máy, giúp điện thoại hoặc thành viên khác truy cập trang truy xuất QR.

### 1. Cài ngrok

Trên Windows, mở PowerShell:

```powershell
winget install ngrok -s msstore
```

Trên macOS với Homebrew:

```bash
brew install ngrok/ngrok/ngrok
```

Với Linux hoặc khi không dùng trình quản lý package, tải ngrok tại [ngrok.com/download](https://ngrok.com/download) và làm theo hướng dẫn cho hệ điều hành tương ứng.

Kiểm tra sau khi cài:

```bash
ngrok version
```

### 2. Đăng nhập và thêm authtoken

1. Tạo hoặc đăng nhập tài khoản tại [Ngrok Dashboard](https://dashboard.ngrok.com/).
2. Mở trang **Your Authtoken** và sao chép authtoken của tài khoản.
3. Chạy lệnh sau, thay giá trị mẫu bằng token thật:

```bash
ngrok config add-authtoken YOUR_NGROK_AUTHTOKEN
```

Không ghi authtoken vào `.env`, README hoặc Git. Có thể kiểm tra vị trí file cấu hình bằng:

```bash
ngrok config check
```

### 3. Lấy domain tĩnh

Trong Ngrok Dashboard, mở **Domains** và lấy dev domain được cấp, thường có dạng:

```text
https://example.ngrok-free.dev
```


### 4. Cấu hình Laravel

Ví dụ domain tĩnh được cấp là `https://example.ngrok-free.dev`, cập nhật `.env`:

```dotenv
APP_URL=https://example.ngrok-free.dev
TRACE_PUBLIC_URL=https://example.ngrok-free.dev
```

Xóa cache cấu hình:

```bash
php artisan optimize:clear
```

### 5. Khởi động Laravel và ngrok

Mở terminal thứ nhất và chạy Laravel:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Mở terminal thứ hai và chạy ngrok bằng đúng domain tĩnh:

```bash
ngrok http 8000 --url https://example.ngrok-free.dev
```

Giữ cả hai terminal hoạt động. Truy cập URL ngrok trên trình duyệt hoặc điện thoại để kiểm tra.

### 6. Cập nhật QR sau khi đổi domain

QR đã tạo trước đó vẫn chứa URL cũ. Tạo lại một lô:

```bash
php artisan qr:regenerate --batch=MA_LO
```

Hoặc tạo lại toàn bộ:

```bash
php artisan qr:regenerate --all
```

Quét thử QR bằng điện thoại sử dụng 4G/5G hoặc mạng khác. URL trong QR phải có dạng:

```text
https://example.ngrok-free.dev/trace/MA_LO
```

Máy tính, Laravel và ngrok đều phải đang chạy thì người khác mới truy cập được. Khi dùng domain tĩnh và luôn khởi động ngrok với `--url`, không cần sửa `.env` hoặc tạo lại QR ở những lần chạy sau.

## Cấu hình dịch vụ tùy chọn

### Email qua Brevo

Mặc định email được ghi vào log. Để gửi qua Brevo, cấu hình:

```dotenv
MAIL_MAILER=brevo
BREVO_KEY=your-brevo-smtp-key
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Thanh toán VNPAY sandbox

```dotenv
VNPAY_TMN_CODE=your-tmn-code
VNPAY_HASH_SECRET=your-hash-secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://127.0.0.1:8000/vnpay/return
```

Khi dùng ngrok hoặc deploy, thay `VNPAY_RETURN_URL` bằng domain công khai tương ứng.

## Chạy dự án

Cách đơn giản, mở hai terminal.

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Truy cập [http://127.0.0.1:8000](http://127.0.0.1:8000).

Hoặc chạy server Laravel, queue worker, log viewer và Vite cùng lúc:

```bash
composer dev
```

Nếu không dùng `composer dev` nhưng cần xử lý job trong queue database, mở thêm terminal:

```bash
php artisan queue:listen --tries=1
```

## Đường dẫn chính

| Chức năng | Đường dẫn |
| --- | --- |
| Trang chủ | `/` |
| Đăng nhập | `/login` |
| Giỏ hàng | `/cart` |
| Đơn hàng | `/orders` |
| Seller dashboard | `/seller/dashboard` |
| Lô hàng của seller | `/seller/batches` |
| Admin dashboard | `/admin/dashboard` |
| Quản lý mã giảm giá | `/admin/coupons` |
| Thống kê admin | `/admin/statistics` |
| Truy xuất lô hàng công khai | `/trace/{ma-lo}` |

## Kiểm thử và kiểm tra code

Chạy toàn bộ test:

```bash
composer test
```

Hoặc:

```bash
php artisan test
```

Chạy một file hoặc một test cụ thể:

```bash
php artisan test tests/Feature/TenTest.php
php artisan test --filter=ten_test
```

Kiểm tra yêu cầu PHP của package:

```bash
composer check-platform-reqs
```

Định dạng PHP bằng Laravel Pint:

```bash
vendor/bin/pint
```

Trên Windows có thể dùng:

```powershell
vendor\bin\pint.bat
```

## Xử lý chứng nhận cũ

Chứng nhận mới được lưu riêng tư tại `storage/app/private/certificates`. Nếu có chứng nhận cũ trong public storage, chạy một lần:

```bash
php artisan traceability:secure-certificates
```

## Lỗi thường gặp

### Thay đổi `.env` nhưng ứng dụng chưa nhận

```bash
php artisan optimize:clear
```

### Thiếu bảng database

```bash
php artisan migrate
```

### CSS/JavaScript không tải

Trong môi trường phát triển:

```bash
npm run dev
```

Hoặc build lại asset:

```bash
npm run build
```

### QR vẫn chứa localhost hoặc domain cũ

Kiểm tra `TRACE_PUBLIC_URL`, xóa cache rồi tạo lại QR:

```bash
php artisan optimize:clear
php artisan qr:regenerate --all
```

### Không thấy QR local

```bash
php artisan storage:link
```

## Lưu ý bảo mật

- Không commit `.env`, secret key, mật khẩu, access token hoặc file backup database.
- Không dùng `SUPABASE_SECRET_KEY` ở frontend.
- Không bật `APP_DEBUG=true` trên website công khai.
- Không triển khai các script sửa dữ liệu hoặc tạo tài khoản trực tiếp trong thư mục `public`.
- `public/fix_admin.php` là script khôi phục dữ liệu có thông tin đăng nhập cố định; phải xóa file này trước khi đưa website lên Internet.
- Luôn thay hoặc xóa tài khoản seed trước khi đưa hệ thống lên production.
