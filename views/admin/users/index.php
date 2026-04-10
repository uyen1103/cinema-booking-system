<?php
$isStaff = $userRole === 'staff';
$roleLabel = $isStaff ? 'nhân viên' : 'khách hàng';
$createAction = $isStaff ? 'create_employee' : 'create_customer';

function user_status_badge(string $status, bool $isStaff): string {
    return match ($status) {
        'working', 'active' => '<span class="admin-badge admin-badge--success">● ' . ($isStaff ? 'Đang làm việc' : 'Đang hoạt động') . '</span>',
        'leave' => '<span class="admin-badge admin-badge--warning">● Nghỉ phép</span>',
        'blocked' => '<span class="admin-badge admin-badge--danger">● Bị khóa</span>',
        default => '<span class="admin-badge admin-badge--muted">● Tạm ngưng</span>',
    };
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h2><?= $isStaff ? 'QUẢN LÝ NHÂN VIÊN' : 'QUẢN LÝ KHÁCH HÀNG' ?></h2>
        <p><?= $isStaff ? 'Theo dõi nhân sự, trạng thái làm việc và quyền truy cập hệ thống.' : 'Quản lý tài khoản khách hàng, trạng thái hoạt động và lịch sử giao dịch.' ?></p>
    </div>
    <a class="admin-btn admin-btn--primary" href="?action=<?= h($createAction) ?>">
        <i class="fa-solid fa-plus"></i>
        <span><?= $isStaff ? 'Thêm nhân viên mới' : 'Thêm khách hàng mới' ?></span>
    </a>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-4">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="admin-stat-card__label">Tổng <?= h($roleLabel) ?></div>
            <div class="admin-stat-card__value"><?= (int) ($stats['total'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Số tài khoản đang được quản lý</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-user-check"></i></div>
            </div>
            <div class="admin-stat-card__label"><?= $isStaff ? 'Đang làm việc' : 'Đang hoạt động' ?></div>
            <div class="admin-stat-card__value"><?= (int) ($stats['active_count'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Tài khoản có thể sử dụng bình thường</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--yellow"><i class="fa-solid fa-user-clock"></i></div>
            </div>
            <div class="admin-stat-card__label"><?= $isStaff ? 'Nghỉ phép / khóa' : 'Tạm ngưng / khóa' ?></div>
            <div class="admin-stat-card__value"><?= (int) (($stats['inactive_count'] ?? 0) + ($stats['leave_count'] ?? 0)) ?></div>
            <div class="admin-stat-card__meta">Cần theo dõi và xử lý thêm</div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <form method="GET" class="admin-filter-bar mb-4">
            <input type="hidden" name="action" value="<?= $isStaff ? 'employees' : 'customers' ?>">
            <div class="admin-toolbar mb-0">
                <div class="admin-toolbar__group flex-grow-1">
                    <div class="flex-grow-1">
                        <input class="admin-search" type="text" name="keyword" value="<?= h($filters['keyword'] ?? '') ?>" placeholder="<?= $isStaff ? 'Tìm theo tên, email, số điện thoại...' : 'Tìm theo khách hàng, email, mã người dùng...' ?>">
                    </div>
                    <?php if ($isStaff): ?>
                        <select class="admin-select admin-control--md" name="position">
                            <option value="">Lọc bộ phận</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?= h($position) ?>" <?= ($filters['position'] ?? '') === $position ? 'selected' : '' ?>><?= h($position) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <select class="admin-select admin-control--md" name="status">
                        <option value="">Mọi trạng thái</option>
                        <?php if ($isStaff): ?>
                            <option value="working" <?= ($filters['status'] ?? '') === 'working' ? 'selected' : '' ?>>Đang làm việc</option>
                            <option value="leave" <?= ($filters['status'] ?? '') === 'leave' ? 'selected' : '' ?>>Nghỉ phép</option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm ngưng</option>
                        <?php else: ?>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm ngưng</option>
                            <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Bị khóa</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="admin-toolbar__group">
                    <button class="admin-btn admin-btn--light" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        <span>Lọc dữ liệu</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= $isStaff ? 'Mã NV' : 'Mã KH' ?></th>
                        <th>Họ và tên</th>
                        <th><?= $isStaff ? 'Chức vụ' : 'Liên hệ' ?></th>
                        <th><?= $isStaff ? 'Cơ sở' : 'Ngày sinh' ?></th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">
                                <i class="fa-regular fa-folder-open"></i>
                                <div>Chưa có dữ liệu <?= h($roleLabel) ?> phù hợp với bộ lọc hiện tại.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="fw-bold text-danger">#<?= $isStaff ? 'NV-' : 'KH-' ?><?= str_pad((string) $user['user_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="admin-user-mini">
                                    <img src="<?= h($user['avatar'] ?: 'assets/images/default-avatar.svg') ?>" alt="Avatar">
                                    <div>
                                        <div class="fw-bold"><?= h($user['full_name']) ?></div>
                                        <div class="text-muted small"><?= h($user['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($isStaff): ?>
                                    <span class="admin-chip"><?= h($user['position'] ?: 'Nhân viên') ?></span>
                                <?php else: ?>
                                    <div><?= h($user['phone'] ?: 'Chưa cập nhật') ?></div>
                                    <div class="text-muted small"><?= h($user['address'] ?: 'Chưa cập nhật địa chỉ') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isStaff): ?>
                                    <div><?= h($user['branch_name'] ?: 'Chưa phân công') ?></div>
                                    <div class="text-muted small">Vào làm: <?= h(format_date($user['hire_date'] ?? null)) ?></div>
                                <?php else: ?>
                                    <?= h(format_date($user['birthday'] ?? null)) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= user_status_badge((string) $user['status'], $isStaff) ?></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="admin-btn admin-btn--ghost admin-btn--icon" href="?action=toggle_status_user&id=<?= (int) $user['user_id'] ?>&role=<?= h($user['role']) ?>" title="Đổi trạng thái">
                                        <i class="fa-solid fa-power-off"></i>
                                    </a>
                                    <a class="admin-btn admin-btn--light admin-btn--icon" href="?action=<?= $isStaff ? 'edit_employee' : 'edit_customer' ?>&id=<?= (int) $user['user_id'] ?>" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="admin-btn admin-btn--danger admin-btn--icon" type="button" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= (int) $user['user_id'] ?>" title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                                <?php include __DIR__ . '/delete_modal.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
