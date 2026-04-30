<?php
// AuthController - Controller trung tâm xử lý authentication
// Mục đích: Ủy thác các tác vụ auth cho CustomerAuthController hoặc AdminAuthController
// Lý do: Tương thích với code cũ, bên trong tách thành 2 controller riêng biệt

// Include 2 controller con để ủy thác
require_once __DIR__ . '/CustomerAuthController.php';   // Xử lý auth cho khách hàng
require_once __DIR__ . '/AdminAuthController.php';       // Xử lý auth cho admin/nhân viên

class AuthController {
    // Khởi tạo 2 controller con để sử dụng
    private CustomerAuthController $customerAuth;
    private AdminAuthController $adminAuth;

    // Constructor: Tạo instance của cả 2 controller
    public function __construct() {
        $this->customerAuth = new CustomerAuthController();
        $this->adminAuth = new AdminAuthController();
    }

    // currentScope(): Xác định scope hiện tại là 'customer' hay 'employee'
    // Dựa vào session 'auth_scope' để quyết định dùng controller nào
    private function currentScope(): string {
        return currentAuthScope() === 'employee' ? 'employee' : 'customer';
    }

    // register(): Đăng ký tài khoản mới
    // Luôn ủy thác cho CustomerAuthController vì chỉ có khách hàng mới đăng ký qua form này
    public function register(): void {
        $this->customerAuth->register();
    }

    // login(): Đăng nhập
    // Ủy thác cho CustomerAuthController xử lý
    public function login(): void {
        $this->customerAuth->login();
    }

    // forgotPassword(): Quên mật khẩu - gửi mã khôi phục qua email
    // Chỉ áp dụng cho khách hàng
    public function forgotPassword(): void {
        $this->customerAuth->forgotPassword();
    }

    // profile(): Xem thông tin profile
    // Tùy scope mà gọi controller tương ứng:
    // - Nếu là employee -> gọi adminAuth->profile()
    // - Nếu là customer -> gọi customerAuth->profile()
    public function profile(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->profile();
            return;
        }
        $this->customerAuth->profile();
    }

    // editProfile(): Sửa thông tin profile
    // Tương tự profile(): tùy scope mà gọi controller phù hợp
    public function editProfile(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->editProfile();
            return;
        }
        $this->customerAuth->editProfile();
    }

    // changePassword(): Đổi mật khẩu
    // Tùy scope để gọi controller tương ứng
    public function changePassword(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->changePassword();
            return;
        }
        $this->customerAuth->changePassword();
    }

    // linkBankAccount(): Liên kết tài khoản ngân hàng
    // Chỉ áp dụng cho khách hàng (admin không cần liên kết ngân hàng)
    public function linkBankAccount(): void {
        $this->customerAuth->linkBankAccount();
    }

    // vouchers(): Xem danh sách voucher của user
    // Chỉ áp dụng cho khách hàng
    public function vouchers(): void {
        $this->customerAuth->vouchers();
    }

    // logout(): Đăng xuất
    // Tùy scope mà gọi controller tương ứng để clear session đúng cách
    public function logout(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->logout();
            return;
        }
        $this->customerAuth->logout();
    }
}
