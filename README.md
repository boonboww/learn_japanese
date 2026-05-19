# 🇯🇵 Learn Japanese - Học Tiếng Nhật Thông Minh

Chào mừng bạn đến với **Learn Japanese** - Một ứng dụng học tiếng Nhật toàn diện (Hiragana, Katakana, Kanji, Từ vựng, Trò chơi và Đánh giá) được cấu trúc dưới dạng **Monorepo** hiện đại.

Dự án bao gồm cả phần Frontend (Nuxt.js) và Backend (Laravel + Docker + PostgreSQL).

---

## 📁 Cấu Trúc Thư Mục Dự Án

*   **`learn_japanese_fe/`**: Nuxt.js 3 Frontend (Vue.js, TypeScript).
*   **`learn_japanese_be/`**: Laravel Backend + Dockerized Postgres & Nginx.

---

## 🛠️ Hướng Dẫn Khởi Chạy Nhanh

### 🟢 1. Khởi chạy Backend (Laravel + Docker)
Di chuyển vào thư mục `learn_japanese_be/` và khởi chạy các container:
```bash
cd learn_japanese_be
docker compose up -d
```
Chạy migrations tạo bảng dữ liệu trên PostgreSQL:
```bash
docker compose exec app php artisan migrate
```
*   **Backend URL**: `http://localhost:8081`
*   **Cổng PostgreSQL**: `5433` (để tránh xung đột cổng `5432` mặc định)

### 🔵 2. Khởi chạy Frontend (Nuxt.js)
Di chuyển vào thư mục `learn_japanese_fe/`, cài đặt các thư viện dependencies và khởi chạy dev server:
```bash
cd learn_japanese_fe
npm install
npm run dev
```
*   **Frontend URL**: `http://localhost:3000`

---

## ⚙️ Các Cổng Kết Nối & CORS
*   Hệ thống Backend chạy trên cổng **`8081`**.
*   Hệ thống Frontend chạy trên cổng **`3000`**.
*   Backend đã được cấu hình **CORS** hoàn chỉnh trong `config/cors.php` để cho phép cổng `3000` của Frontend gọi API mà không bị chặn bởi trình duyệt.
