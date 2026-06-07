<?php
// auth/login.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/User.php';

// If already logged in, redirect straight to dashboard
if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/dashboard/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = User::getByUsername($username);

    if (!$user) {
        $error = 'Invalid username or password';
    } else {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            header("Location: " . BASE_URL . "/dashboard/index.php");
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - <?= APP_NAME ?></title>
    <!-- Basic structural layout, Renz will style this via Bootstrap later -->
</head>
<body>
    <div class="login-container">
        <h2><?= APP_NAME ?> Login</h2>
        
        <?php if ($error): ?>
            <div class="error-alert" style="color: red; margin-bottom: 15px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div>
                <label>Username</label><br>
                <input type="text" name="username" required>
            </div>
            <br>
            <div>
                <label>Password</label><br>
                <input type="password" name="password" required>
            </div>
            <br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>