<?php
require_once __DIR__ . '/CustomerAuthController.php'; // Controller đăng ký/đăng nhập/profile khách hàng
require_once __DIR__ . '/AdminAuthController.php'; // Controller quản lý profile nhân viên/quản trị viên

/**
 * Controller điều phối đăng nhập/đăng ký cho cả khách hàng và admin.
 * - Nhắm tới việc tái sử dụng CustomerAuthController cho user bình thường
 * - Và AdminAuthController cho nhân viên/quản trị
 */
class AuthController {
    private CustomerAuthController $customerAuth;
    private AdminAuthController $adminAuth;

    public function __construct() {
        $this->customerAuth = new CustomerAuthController();
        $this->adminAuth = new AdminAuthController();
    }

    private function currentScope(): string {
        return currentAuthScope() === 'employee' ? 'employee' : 'customer';
    }

    public function register(): void {
        $this->customerAuth->register();
    }

    public function login(): void {
        $this->customerAuth->login();
    }

    public function forgotPassword(): void {
        $this->customerAuth->forgotPassword();
    }

    public function profile(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->profile();
            return;
        }
        $this->customerAuth->profile();
    }

    public function editProfile(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->editProfile();
            return;
        }
        $this->customerAuth->editProfile();
    }

    public function changePassword(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->changePassword();
            return;
        }
        $this->customerAuth->changePassword();
    }

    public function linkBankAccount(): void {
        $this->customerAuth->linkBankAccount();
    }

    public function vouchers(): void {
        $this->customerAuth->vouchers();
    }

    public function logout(): void {
        if ($this->currentScope() === 'employee') {
            $this->adminAuth->logout();
            return;
        }
        $this->customerAuth->logout();
    }
}
