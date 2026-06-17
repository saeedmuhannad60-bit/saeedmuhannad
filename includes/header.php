<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('index.php')) ?>">
            <span class="brand-icon">&#127869;</span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
        </a>
        <nav class="main-nav">
            <a href="<?= e(url('index.php')) ?>">Home</a>
            <a href="<?= e(url('menu.php')) ?>">Menu</a>
            <a href="<?= e(url('track.php')) ?>">Track Order</a>
            <a href="<?= e(url('admin/login.php')) ?>">Admin</a>
            <a class="cart-link" href="<?= e(url('cart.php')) ?>">
                &#128722; Cart <span class="cart-badge"><?= cart_count() ?></span>
            </a>
        </nav>
    </div>
</header>
<main class="container page">
<?php foreach (get_flashes() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
