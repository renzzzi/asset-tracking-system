<?php
// users/edit.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/User.php';
require_once '../includes/flash.php';

requireAdmin();

$id   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$user = User::getById($id);

if (!$user) {
    setFlash('danger', 'User not found.');
    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username'         => trim($_POST['username']         ?? ''),
        'full_name'        => trim($_POST['full_name']        ?? ''),
        'email'            => trim($_POST['email']            ?? ''),
        'role'             => trim($_POST['role']             ?? 'staff'),
        'password'         => $_POST['password']              ?? '',
        'password_confirm' => $_POST['password_confirm']      ?? '',
    ];

    // Validate
    if ($data['full_name'] === '') {
        $errors['full_name'] = 'Full name is required.';
    }
    if ($data['username'] === '') {
        $errors['username'] = 'Username is required.';
    } elseif (User::usernameExists($data['username'], $id)) {
        $errors['username'] = 'Username already taken.';
    }
    if ($data['password'] !== '') {
        if (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }
    }
    if (!in_array($data['role'], ['admin', 'staff'])) {
        $errors['role'] = 'Invalid role.';
    }

    if (empty($errors)) {
        $ok = User::update($id, $data);
        if ($ok) {
            setFlash('success', 'User updated successfully.');
            header('Location: ' . BASE_URL . '/users/index.php');
            exit;
        } else {
            setFlash('danger', 'Failed to update user. Please try again.');
        }
    }

    // Re-populate with submitted values on error
    $user = array_merge($user, $data);
}

$pageTitle  = 'Edit User';
$activePage = 'users';
require_once '../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/users/index.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
    <h1 class="h3 fw-bold mb-0">Edit User</h1>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="" novalidate>
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="row g-3">
                <!-- Full Name -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="full_name"
                           class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($user['full_name']) ?>">
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Username -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Username <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="username"
                           class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($user['username']) ?>">
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>

                <!-- Role -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Role <span class="text-danger">*</span>
                    </label>
                    <select name="role"
                            class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>">
                        <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>
                            Staff
                        </option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <div class="invalid-feedback"><?= $errors['role'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- New Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Leave blank to keep current">
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Confirm New Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="password_confirm"
                           class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                           placeholder="Leave blank to keep current">
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?= $errors['password_confirm'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="<?= BASE_URL ?>/users/index.php"
                   class="btn btn-outline-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>