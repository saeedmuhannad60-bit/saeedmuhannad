<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$totalOrders   = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','preparing','out_for_delivery')")->fetchColumn();
$totalItems    = (int) $pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn();
$revenue       = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status = 'completed'")->fetchColumn();

$recent = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon">&#128221;</span>
        <div><span class="stat-value"><?= $totalOrders ?></span><span class="stat-label">Total Orders</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#9203;</span>
        <div><span class="stat-value"><?= $pendingOrders ?></span><span class="stat-label">Active Orders</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#127860;</span>
        <div><span class="stat-value"><?= $totalItems ?></span><span class="stat-label">Menu Items</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#128176;</span>
        <div><span class="stat-value"><?= money($revenue) ?></span><span class="stat-label">Revenue (completed)</span></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Recent Orders</h2>
        <a class="btn btn-outline btn-sm" href="<?= e(url('admin/orders.php')) ?>">View all</a>
    </div>
    <?php if (!$recent): ?>
        <p class="muted">No orders yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Code</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th></tr></thead>
            <tbody>
                <?php foreach ($recent as $o): ?>
                    <tr>
                        <td><a href="<?= e(url('admin/orders.php?id=' . $o['id'])) ?>">#<?= e($o['code']) ?></a></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><?= money((float) $o['total']) ?></td>
                        <td><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                        <td class="muted"><?= e($o['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
