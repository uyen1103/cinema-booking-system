# cinema-booking-system
A web-based cinema ticket booking system developed using PHP, MySQL, HTML, and CSS

## Setup Instructions

1. **Database Setup:**
   - Create MySQL database named `movie_booking`
   - Run SQL files in order:
     - `database/cinema_booking.sql`
     - `database/sample_data.sql` (optional, for testing)
     - `database/add_oauth_columns.sql`
     - `database/create_admin.sql`
  

2. **Environment Configuration:**
   - Copy `.env.example` to `.env`
   - Configure Google OAuth credentials if needed

3. **Web Server:**
   - Place project in web root (e.g., `htdocs/cinema-booking-system-main`)
   - Access via: (`http://localhost/cinema-booking-system/web.php?action=google-callback')
   - Change to the folder name "cinema-booking-system"


Chức năng này sẽ kiểm tra tất cả các chức năng đặt vé:
- Danh sách phim và thông tin chi tiết
- Chọn giờ chiếu
- Kiểm tra chỗ ngồi
- Mã khuyến mãi
- Tạo đơn hàng
- Xử lý thanh toán
- Lịch sử đơn hàng
- Yêu cầu hủy đơn hàng
- Phê duyệt của quản trị viên

## Tính năng

- Đăng ký và xác thực người dùng
- Duyệt phim và thông tin chi tiết
- Chọn giờ chiếu và đặt chỗ ngồi
- Xử lý thanh toán
- Quản lý đơn hàng
- Hệ thống hủy đơn hàng
- Quy trình phê duyệt của quản trị viên
- Mã khuyến mãi