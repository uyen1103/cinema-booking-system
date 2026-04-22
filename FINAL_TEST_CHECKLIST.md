# Checklist test toàn hệ thống - bản nộp cuối cùng

## 1) Cài đặt và import
- [ ] Đặt project trong `htdocs/` và giữ nguyên tên thư mục nếu đã cấu hình theo tên cũ.
- [ ] Cập nhật `config/database.php` đúng host, db name, user, password trên XAMPP.
- [ ] Import `database/final_xampp_setup.sql`.
- [ ] Import tiếp `database/sample_data.sql`.
- [ ] Mở trang chủ qua `index.php` và xác nhận không có lỗi PHP fatal.

## 2) Kiểm tra customer public flow
- [ ] Trang chủ hiển thị danh sách phim.
- [ ] Trang chi tiết phim mở được từ trang chủ.
- [ ] Chọn ngày / rạp / suất chiếu hoạt động bình thường.
- [ ] Nút đặt vé dẫn đúng sang màn đặt ghế.
- [ ] Trang khuyến mãi khách hàng mở được.
- [ ] Trang hệ thống rạp mở được.

## 3) Kiểm tra customer auth flow
- [ ] Đăng ký tài khoản khách hàng mới thành công.
- [ ] Đăng nhập khách hàng thành công.
- [ ] Đăng xuất khách hàng thành công.
- [ ] Hồ sơ cá nhân hiển thị đúng tên, email, số điện thoại, avatar.
- [ ] Cập nhật hồ sơ không làm mất dữ liệu cũ.
- [ ] Đổi mật khẩu hoạt động đúng.
- [ ] Trang voucher hiển thị được.
- [ ] Trang liên kết tài khoản ngân hàng mở được.

## 4) Kiểm tra đặt vé / checkout
- [ ] Chọn ghế không lỗi và hiển thị giá đúng theo loại ghế.
- [ ] Không chọn được ghế đã khóa / ghế không hợp lệ.
- [ ] Checkout hiển thị đúng phim, suất chiếu, ghế, tổng tiền.
- [ ] Mã khuyến mãi hợp lệ được áp dụng đúng.
- [ ] Mã khuyến mãi không đủ điều kiện bị chặn đúng.
- [ ] Đặt vé thành công tạo được đơn hàng mới.
- [ ] Trang success hiển thị đúng mã đơn / thông tin đơn.
- [ ] Lịch sử đặt vé hiển thị đơn vừa tạo.

## 5) Kiểm tra hủy vé
- [ ] Khách hàng gửi yêu cầu hủy từ lịch sử đặt vé được.
- [ ] Admin nhìn thấy yêu cầu hủy trong danh sách hủy vé.
- [ ] Duyệt yêu cầu hủy cập nhật đúng trạng thái đơn.
- [ ] Từ dashboard, phần yêu cầu hủy gần đây thao tác được.

## 6) Kiểm tra admin auth / profile
- [ ] Đăng nhập admin hoạt động đúng.
- [ ] Admin không bị chuyển nhầm sang customer flow.
- [ ] Trang hồ sơ admin hiển thị đúng.
- [ ] Chỉnh sửa hồ sơ admin hoạt động đúng.
- [ ] Đổi mật khẩu admin hoạt động đúng.
- [ ] Đăng xuất admin hoạt động đúng.

## 7) Kiểm tra dashboard / reports
- [ ] Dashboard hiển thị số liệu tổng quan.
- [ ] Doanh thu, số vé, đơn hàng gần đây có dữ liệu.
- [ ] Reports mở được và có số liệu thống kê.
- [ ] Không có warning kiểu `Undefined array key`.
- [ ] Không có ô dữ liệu bị lệch tên cột / null bất thường.

## 8) Kiểm tra admin modules
### Người dùng
- [ ] Danh sách khách hàng hiển thị được.
- [ ] Danh sách nhân viên hiển thị được.
- [ ] Tạo mới khách hàng / nhân viên hoạt động đúng.
- [ ] Sửa khách hàng / nhân viên hoạt động đúng.
- [ ] Đổi trạng thái người dùng hoạt động đúng.
- [ ] Xóa người dùng hoạt động đúng.

### Phim
- [ ] Danh sách phim hiển thị đúng ảnh/poster.
- [ ] Tạo phim mới thành công.
- [ ] Sửa phim thành công.
- [ ] Xóa phim thành công.

### Suất chiếu
- [ ] Danh sách suất chiếu hiển thị đúng phim / phòng / giờ.
- [ ] Tạo suất chiếu thành công.
- [ ] Sửa suất chiếu thành công.
- [ ] Xóa suất chiếu thành công.

### Phòng / ghế
- [ ] Danh sách phòng chiếu hiển thị đúng.
- [ ] Tạo phòng mới thành công.
- [ ] Sửa phòng thành công.
- [ ] Xóa phòng thành công.
- [ ] Màn sơ đồ ghế mở đúng phòng.
- [ ] Generate seats hoạt động đúng.
- [ ] Toggle ghế hoạt động đúng.
- [ ] Không bị lệch key `row_name/seat_row`, `type/seat_type`.

### Khuyến mãi
- [ ] Danh sách khuyến mãi hiển thị đúng.
- [ ] Tạo khuyến mãi thành công.
- [ ] Sửa khuyến mãi thành công.
- [ ] Xóa khuyến mãi thành công.

### Hóa đơn / đơn hàng
- [ ] Danh sách hóa đơn hiển thị đúng.
- [ ] Chi tiết hóa đơn mở được.
- [ ] Không còn warning `promotion_code`.
- [ ] Đơn không có mã giảm giá hiện “Không áp dụng”.
- [ ] Duyệt / hủy / cập nhật trạng thái đơn hoạt động đúng.

## 9) Kiểm tra đồng bộ customer ↔ admin
- [ ] Customer mới tạo/đăng ký xuất hiện ở admin customer list.
- [ ] Đơn do customer tạo xuất hiện ở admin orders.
- [ ] Hủy vé từ customer xuất hiện ở admin cancellations.
- [ ] Dữ liệu dashboard/reports phản ánh đơn mới tạo.
- [ ] Với DB cũ còn `users/user_id`, dữ liệu vẫn đọc được.

## 10) Kiểm tra đường dẫn và điều hướng
- [ ] Không có link nào nhảy sai trang do dùng đường dẫn tương đối.
- [ ] Các action admin vẫn hoạt động khi project chạy trong thư mục con của `htdocs`.
- [ ] `web.php` vẫn redirect đúng về `index.php`.
- [ ] Không có form nào submit nhầm sang action cũ bị thiếu alias.

## 11) Kiểm tra cuối trước khi nộp
- [ ] Chạy lint PHP toàn bộ project: không có lỗi syntax.
- [ ] Xóa cache trình duyệt hoặc hard refresh sau khi import/test lại.
- [ ] Giữ nguyên cấu trúc thư mục khi nộp.
- [ ] Nộp đúng file zip bản cuối cùng, không nộp nhầm bản đợt 1/2.
