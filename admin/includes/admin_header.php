<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$admin = current_admin();
$pageTitle = $pageTitle ?? 'Admin';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; Admin &middot; <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="sidebar">
        <div class="sidebar-brand"><?= e(APP_NAME) ?><small>Admin</small></div>
        <nav class="sidebar-nav">
            <a href="<?= e(url('admin/dashboard.php')) ?>" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">&#128202; Dashboard</a>
            <a href="<?= e(url('admin/orders.php')) ?>" class="<?= $current === 'orders.php' ? 'active' : '' ?>">&#128221; Orders</a>
            <a href="<?= e(url('admin/menu.php')) ?>" class="<?= $current === 'menu.php' ? 'active' : '' ?>">&#127860; Menu Items</a>
            <a href="<?= e(url('admin/categories.php')) ?>" class="<?= $current === 'categories.php' ? 'active' : '' ?>">&#127991; Categories</a>
            <a href="<?= e(url('index.php')) ?>" target="_blank">&#127760; View Site</a>
            <a href="<?= e(url('admin/logout.php')) ?>" class="logout">&#128682; Logout</a>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <h1><?= e($pageTitle) ?></h1>
            <span class="admin-user">&#128100; <?= e($admin['name']) ?></span>
        </header>
        <div class="admin-content">
        <?php foreach (get_flashes() as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
