# Ghi chú bản nộp cuối cùng

## Mục tiêu của đợt cuối
- cleanup an toàn phần legacy
- chốt lại đường dẫn/action để giảm rủi ro sai trang
- bổ sung checklist test toàn hệ thống
- không thay đổi giao diện
- không thay đổi cấu trúc chính của project
- không chỉnh lại logic nghiệp vụ cốt lõi

## Các thay đổi đã chốt
1. Giữ nguyên toàn bộ cấu trúc thư mục và entrypoint hiện có (`index.php`, `web.php`).
2. Chuẩn hóa các form/link còn dùng `?action=...` hoặc `index.php?action=...` sang helper route nội bộ ở các màn admin trọng yếu và nút đặt vé từ trang chi tiết phim.
3. Giữ lớp `legacyAliases` trong `routes/web.php` để tương thích ngược với action cũ, tránh gãy đường dẫn cũ.
4. Không xóa các file legacy/tool cũ khỏi project để tránh phát sinh phụ thuộc ngoài ý muốn; chúng được coi là file tham khảo, không nằm trong runtime chính.
5. Bổ sung checklist test toàn hệ thống trong `FINAL_TEST_CHECKLIST.md`.

## File runtime đã được dọn đường dẫn
- `views/admin/movies/create.php`
- `views/admin/movies/delete_modal.php`
- `views/admin/movies/edit.php`
- `views/admin/movies/index.php`
- `views/admin/rooms/create.php`
- `views/admin/rooms/delete_room.php`
- `views/admin/rooms/edit.php`
- `views/admin/rooms/index.php`
- `views/admin/rooms/seats.php`
- `views/admin/showtimes/create.php`
- `views/admin/showtimes/delete_modal.php`
- `views/admin/showtimes/edit.php`
- `views/admin/showtimes/index.php`
- `views/admin/users/create.php`
- `views/admin/users/delete_modal.php`
- `views/admin/users/edit.php`
- `views/admin/users/form.php`
- `views/admin/users/index.php`
- `views/movies/detail.php`
- `controllers/OAuthController.php`
- `controllers/MovieController.php`

## Lưu ý nộp bài
- Ưu tiên import `database/final_xampp_setup.sql` rồi `database/sample_data.sql`.
- Nếu giảng viên/tester chạy trong thư mục con của XAMPP, bản này an toàn hơn ở lớp điều hướng do đã giảm đường dẫn hardcode.
- Giữ nguyên tên thư mục project nếu môi trường test đang trỏ sẵn vào tên cũ.
