# Admin module - Cinema Booking System

## Phạm vi đã hoàn thiện
Các mục admin từ **4.3.12 đến 4.3.19**:

- Quản lý nhân viên
- Quản lý khách hàng
- Quản lý thông tin phim
- Quản lý suất chiếu
- Quản lý phòng chiếu và ghế
- Quản lý khuyến mãi
- Quản lý hóa đơn
- Tạo báo cáo thống kê

## Công nghệ
- PHP thuần
- CSS tách riêng trong `assets/css/admin.css`
- MySQL / XAMPP
- Bootstrap chỉ dùng cho layout hỗ trợ và modal

## Cách chạy trên XAMPP
1. Giải nén project vào `htdocs`
2. Tạo database `movie_booking`
3. Import file:
   - `database/admin_xampp_setup.sql`
4. Mở trình duyệt:
   - `http://localhost/<ten-thu-muc_project>/index.php?action=dashboard`
   - 'http://localhost/cinema-booking-system/index.php?action=employees'   (Admin)
   - 'http://localhost/cinema-booking-system/web.php?action=google-callback' (users)

## Tài khoản admin mẫu
- Email: `admin@cinemacentral.vn`
- Password: `Admin@123`

## Ghi chú
- Toàn bộ upload ảnh admin sẽ được lưu tại:
  - `assets/uploads/avatars`
  - `assets/uploads/movies`
  - `assets/uploads/promotions`
- Các nút đã có hiệu ứng hover và shadow bằng CSS.
- Báo cáo đang lấy dữ liệu trực tiếp từ bảng đơn hàng, vé, phim, khuyến mãi và khách hàng.
- Thêm database : 'final_xampp_setup.sql' và 'sample_data.sql'
