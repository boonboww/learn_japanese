# 🐳 Hướng Dẫn Sử Dụng Docker & PostgreSQL Cho Learn Japanese BE

Chào mừng bạn đến với tài liệu hướng dẫn vận hành backend **learn_japanese_be** được container hóa hoàn toàn bằng **Docker** và sử dụng cơ sở dữ liệu **PostgreSQL** mạnh mẽ! 

Tài liệu này được thiết kế để giúp bạn dễ dàng khởi chạy, kiểm tra và làm việc với backend mới của mình mà không gây xung đột với các dự án Laravel hiện có.

---

## ⚙️ 1. Cấu Hình Cổng Kết Nối (Port Mapping) Tránh Xung Đột

Dựa vào sơ đồ các cổng đang chạy trên hệ thống của bạn (từ hình ảnh cung cấp):
*   Cổng **`8000`** đã được sử dụng bởi Nginx của dự án `laravel_yt_demo` (`laravel_yt_ngi`).
*   Cổng **`3307`** đã được sử dụng bởi MySQL của dự án `laravel_yt_demo` (`laravel_yt_my`).

Để đảm bảo hai dự án chạy song song mượt mà, **learn_japanese_be** được cấu hình sử dụng các cổng hoàn toàn độc lập sau:

| Dịch vụ (Service) | Cổng bên ngoài (Host Port) | Cổng bên trong (Container Port) | Tên Container | Ghi chú |
| :--- | :--- | :--- | :--- | :--- |
| **Nginx Web Server** | `8081` | `80` | `learn_japanese_nginx` | Tránh xung đột với cổng `8000` |
| **PHP 8.4-FPM App** | *Không ánh xạ* | `9000` | `learn_japanese_app` | Runtime PHP 8.4 cao cấp tích hợp driver pgsql |
| **PostgreSQL Database** | `5433` | `5432` | `learn_japanese_postgres` | Tránh xung đột cổng `5432` (mặc định) và `3307` |

> [!NOTE]
> Bên trong mạng nội bộ của Docker (Docker Network), các container kết nối với nhau bằng tên dịch vụ. Do đó, Laravel kết nối đến Postgres qua máy chủ `postgres` ở cổng mặc định `5432`, trong khi bạn có thể kết nối từ máy ảo hoặc phần mềm ngoài (như DBeaver/TablePlus) thông qua cổng `5433`.

---

## 📂 2. Cấu Trúc Các Tệp Tin Docker Đã Tạo

Các file cấu hình môi trường Docker đã được tối ưu hóa chuẩn Laravel và lưu trữ tại thư mục gốc của dự án:

```text
learn_japanese_be/
├── Dockerfile                         # Khởi tạo PHP 8.4-FPM và cài đặt pdo_pgsql, pgsql, gd, mbstring...
├── docker-compose.yml                 # Định nghĩa 3 dịch vụ: app, nginx, postgres kết nối chung network
├── .env                               # Cập nhật kết nối DB_CONNECTION=pgsql và cổng APP_URL=http://localhost:8081
└── docker/                            # Chứa các file cấu hình chi tiết cho từng dịch vụ
    ├── nginx/
    │   └── default.conf               # Nginx reverse proxy trỏ về container app ở cổng 9000
    └── php/
        └── local.ini                  # Tối ưu hóa PHP runtime (Memory limit 512M, upload size 40M)
```

---

## 🚀 3. Hướng Dẫn Vận Hành Môi Trường (Commands Checklist)

Mọi thao tác đều có thể thực hiện thông qua Docker Compose mà không cần cài đặt PHP hay Postgres trên máy thật của bạn.

### 🟢 3.1. Khởi động môi trường
Mở Terminal tại thư mục `learn_japanese_be` và chạy:
```bash
docker compose up -d
```
> [!TIP]
> Nếu bạn thay đổi bất kỳ dòng lệnh nào trong `Dockerfile` hoặc muốn build lại hoàn toàn từ đầu, hãy thêm flag `--build`:
> ```bash
> docker compose up --build -d
> ```

### 🟡 3.2. Kiểm tra trạng thái hoạt động
Xem danh sách các container đang chạy:
```bash
docker compose ps
```
Hoặc kiểm tra thông tin log thời gian thực:
```bash
docker compose logs -f
```

### 🔀 3.3. Chạy các câu lệnh Artisan (Migrations, Seeders...)
Vì dự án chạy hoàn toàn trên Docker, bạn cần chuyển tiếp lệnh vào bên trong container `app`:
*   **Chạy Migrations tạo bảng dữ liệu:**
    ```bash
    docker compose exec app php artisan migrate
    ```
*   **Tạo mới một Controller:**
    ```bash
    docker compose exec app php artisan make:controller JapaneseWordController
    ```
*   **Xóa cache cấu hình:**
    ```bash
    docker compose exec app php artisan config:clear
    ```

### 🔴 3.4. Dừng môi trường
Để tắt toàn bộ container và giải phóng RAM:
```bash
docker compose down
```

---

## 🌐 4. Kiểm Tra Kết Quả Kết Nối

Sau khi khởi chạy thành công:
1.  Truy cập trang chào mừng Laravel tại: 👉 **[http://localhost:8081](http://localhost:8081)**
2.  Kết nối Database PostgreSQL từ các công cụ quản lý cơ sở dữ liệu (TablePlus, DBeaver, pgAdmin):
    *   **Host:** `127.0.0.1`
    *   **Port:** `5433`
    *   **Database:** `learn_japanese`
    *   **Username:** `postgres`
    *   **Password:** `password`

---
> [!IMPORTANT]
> **Quyền ghi thư mục (Write Permissions):** Trong môi trường Docker trên Windows WSL2, chúng tôi đã cấu hình cấp quyền `chmod -R 777` cho thư mục `storage` và `bootstrap/cache` bên trong container để tránh hoàn toàn lỗi ghi file log hoặc session (Lỗi HTTP 500). Mọi thứ hiện tại đã hoạt động ổn định 100%!
