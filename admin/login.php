<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_admin()) {
    redirect('admin/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'       => (int) $admin['id'],
            'username' => $admin['username'],
            'name'     => $admin['name'],
        ];
        flash('Welcome back, ' . $admin['name'] . '!');
        redirect('admin/dashboard.php');
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login &middot; <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="login-body">
    <div class="login-card">
        <h1><?= e(APP_NAME) ?></h1>
        <p class="muted">Admin Panel</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('admin/login.php')) ?>">
            <?= csrf_field() ?>
            <label>Username
                <input type="text" name="username" required autofocus>
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <button class="btn btn-primary btn-block" type="submit">Log In</button>
        </form>

        <p class="login-hint muted">Default credentials: <code>admin</code> / <code>admin123</code></p>
        <p><a href="<?= e(url('index.php')) ?>">&larr; Back to site</a></p>
    </div>
</body>
</html>
