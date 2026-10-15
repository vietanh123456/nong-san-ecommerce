# Nông Sản Việt

Nông Sản Việt là website thương mại điện tử dành cho nông sản và đặc sản vùng miền. Người dùng có thể tìm kiếm sản phẩm, thêm vào giỏ hàng, đặt hàng, theo dõi đơn hàng, lưu sản phẩm yêu thích và đánh giá sau mua.

Website hỗ trợ ba nhóm người dùng:

- **Khách hàng:** xem sản phẩm, giỏ hàng, thanh toán, quản lý hồ sơ/địa chỉ, yêu thích và đánh giá.
- **Người bán:** đăng ký trở thành seller, quản lý sản phẩm, lô hàng, chứng nhận và đơn hàng.
- **Quản trị viên:** quản lý tài khoản, yêu cầu seller, sản phẩm, chứng nhận, đánh giá, mã giảm giá và thống kê.

Ngoài ra, mỗi lô hàng có thể được tạo mã QR để truy xuất thông tin nguồn gốc, ngày thu hoạch, đóng gói, hạn dùng và các chứng nhận đã được duyệt.

## Công nghệ sử dụng

- PHP 8.2+ và Laravel 12
- MySQL 8+ (khuyến nghị) hoặc SQLite cho môi trường phát triển đơn giản
- Blade, Vite, Tailwind CSS
- Composer và Node.js/NPM
- `endroid/qr-code` để tạo QR truy xuất nguồn gốc

## Yêu cầu cấu hình

Trước khi cài đặt, bảo đảm máy có:

- PHP **8.2** trở lên, bật các extension Laravel/MySQL thông dụng (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `ctype`).
- Composer 2.
- Node.js 20+ và NPM.
- MySQL 8+ hoặc MariaDB tương thích.
- Git.

## Hướng dẫn setup

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

### 3. Tạo file môi trường

```bash
copy .env.example .env
php artisan key:generate
```

Trên macOS/Linux, dùng lệnh sau thay cho `copy`:

```bash
cp .env.example .env
```

### 4. Cấu hình cơ sở dữ liệu

Tạo một database MySQL, ví dụ `nong_san_ecommerce`, rồi cập nhật các dòng sau trong `.env`:

```dotenv
APP_NAME="Nông Sản Việt"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nong_san_ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

> Không commit file `.env`, vì file này có thể chứa mật khẩu và thông tin riêng của máy.

Nếu nhóm đã cung cấp file `database.sql`, hãy import file đó vào database trước. Sau đó vẫn chạy migration ở bước tiếp theo để bảo đảm schema mới nhất được áp dụng.

### 5. Khởi tạo dữ liệu và storage

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
```

Tài khoản seed mặc định:

| Vai trò | Email | Mật khẩu |
| --- | --- | --- |
| Quản trị viên | `admin@example.com` | `password` |
| Người bán | `seller@example.com` | `password` |

### 6. Chạy dự án

Mở hai terminal tại thư mục dự án.

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Truy cập website tại [http://127.0.0.1:8000](http://127.0.0.1:8000).

Hoặc chạy đồng thời server, queue, log và Vite:

```bash
composer dev
```

## Một số đường dẫn chính

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
| Truy xuất lô hàng | `/trace/{ma-lo}` |

## Kiểm thử

Chạy toàn bộ test:

```bash
php artisan test
```

Hoặc dùng script Composer:

```bash
composer test
```

## Xử lý chứng nhận cũ

Chứng nhận mới được lưu riêng tư tại `storage/app/private/certificates`. Nếu có file chứng nhận cũ ở vùng public, chạy một lần:

```bash
php artisan traceability:secure-certificates
```

Lệnh sẽ chuyển file cũ sang vùng riêng tư để việc xem/tải file luôn đi qua kiểm soát phân quyền.

## Lưu ý phát triển

- Sau khi pull code mới có migration, chạy `php artisan migrate`.
- Sau khi thay đổi dependency frontend, chạy lại `npm install`.
- Không commit `.env`, thư mục `vendor/`, `node_modules/`, hoặc file backup cá nhân.
- Khi gặp lỗi cache cấu hình/view, chạy `php artisan optimize:clear`.
