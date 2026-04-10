# Cinema Booking System

Hệ thống đặt vé xem phim được xây dựng bằng **PHP thuần**, **MySQL**, **HTML/CSS**, chạy trên **XAMPP**.  
Dự án gồm 2 phần chính:

- **User/Public**: xem phim, chọn suất chiếu, đặt vé, xem lịch sử vé, gửi yêu cầu hủy vé
- **Admin**: quản lý phim, suất chiếu, phòng chiếu & ghế, nhân viên, khách hàng, khuyến mãi, hóa đơn, báo cáo thống kê, kiểm duyệt/hủy vé

---

## Công nghệ sử dụng

- PHP
- MySQL
- HTML, CSS
- Bootstrap
- XAMPP

---

## Chức năng chính

### 1. Phần người dùng
- Xem danh sách phim
- Xem chi tiết phim
- Xem khuyến mãi
- Chọn rạp / suất chiếu
- Chọn ghế và đặt vé
- Thanh toán / tạo đơn vé
- Xem lịch sử đặt vé
- Gửi yêu cầu hủy vé

### 2. Phần quản trị
- Bảng điều khiển
- Kiểm duyệt vé
- Quản lý phim
- Quản lý suất chiếu
- Quản lý phòng chiếu và ghế
- Quản lý nhân viên
- Quản lý khách hàng
- Quản lý khuyến mãi
- Quản lý hóa đơn
- Báo cáo thống kê doanh thu

---

## Cấu trúc thư mục

```text
cinema-booking-system/
├── assets/
│   ├── css/
│   ├── images/
│   └── uploads/
├── config/
├── controllers/
├── database/
├── helpers/
├── models/
├── routes/
├── views/
├── index.php
├── web.php
└── README.md
