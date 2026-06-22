<?php
// users/add.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/User.php';
require_once '../includes/flash.php';

requireAdmin();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'username'  => trim($_POST['username']  ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email']     ?? ''),
        'role'      => trim($_POST['role']      ?? 'staff'),
        'password'  => $_POST['password']       ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
    ];

    // Validate
    if ($old['full_name'] === '') {
        $errors['full_name'] = 'Full name is required.';
    }
    if ($old['username'] === '') {
        $errors['username'] = 'Username is required.';
    } elseif (User::usernameExists($old['username'])) {
        $errors['username'] = 'Username already taken.';
    }
    if ($old['password'] === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($old['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    } elseif ($old['password'] !== $old['password_confirm']) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }
    if (!in_array($old['role'], ['admin', 'staff'])) {
        $errors['role'] = 'Invalid role selected.';
    }

    if (empty($errors)) {
        $newId = User::create([
            'username'  => $old['username'],
            'password'  => $old['password'],
            'full_name' => $old['full_name'],
            'email'     => $old['email'] ?: null,
            'role'      => $old['role'],
        ]);

        if ($newId) {
            setFlash('success', 'User "' . htmlspecialchars($old['full_name']) . '" created successfully.');
            header('Location: ' . BASE_URL . '/users/index.php');
            exit;
        } else {
            setFlash('danger', 'Failed to create user. Please try again.');
        }
    }
}

$pageTitle  = 'Add User';
$activePage = 'users';
require_once '../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/users/index.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
    <h1 class="h3 fw-bold mb-0">Add User</h1>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="" novalidate>

            <div class="row g-3">
                <!-- Full Name -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="full_name"
                           class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
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
                           value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>

                <!-- Role -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Role <span class="text-danger">*</span>
                    </label>
                    <select name="role"
                            class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>">
                        <option value="staff" <?= ($old['role'] ?? '') === 'staff' ? 'selected' : '' ?>>
                            Staff
                        </option>
                        <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <div class="invalid-feedback"><?= $errors['role'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Password <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Min. 6 characters">
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Confirm Password <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password_confirm"
                           class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                           placeholder="Re-enter password">
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?= $errors['password_confirm'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Create User
                </button>
                <a href="<?= BASE_URL ?>/users/index.php"
                   class="btn btn-outline-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>