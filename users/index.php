<?php
// users/index.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/User.php';
require_once '../includes/flash.php';

requireAdmin();

$users = User::getAll();

$pageTitle  = 'User Management';
$activePage = 'users';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">User Management</h1>
        <p class="text-muted mb-0">Manage admin and staff accounts</p>
    </div>
    <a href="<?= BASE_URL ?>/users/add.php" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Add User
    </a>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $isSelf     = $user['id'] === currentUser()['id'];
                        $roleBadge  = $user['role'] === 'admin'
                            ? '<span class="badge bg-primary">Admin</span>'
                            : '<span class="badge bg-secondary">Staff</span>';
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold">
                                <?= htmlspecialchars($user['full_name']) ?>
                                <?php if ($isSelf): ?>
                                    <span class="badge bg-success ms-1">You</span>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace">
                                <?= htmlspecialchars($user['username']) ?>
                            </td>
                            <td class="text-muted">
                                <?= htmlspecialchars($user['email'] ?? '—') ?>
                            </td>
                            <td><?= $roleBadge ?></td>
                            <td class="text-muted small">
                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/users/edit.php?id=<?= $user['id'] ?>"
                                   class="btn btn-sm btn-secondary me-1">Edit</a>

                                <?php if ($isSelf): ?>
                                    <button class="btn btn-sm btn-danger disabled" disabled
                                            title="You cannot delete your own account.">
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <form method="POST"
                                          action="<?= BASE_URL ?>/users/delete.php"
                                          class="d-inline">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete user <?= htmlspecialchars($user['full_name']) ?>?')">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>