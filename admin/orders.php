<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

// Update order status.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status  = (string) ($_POST['status'] ?? '');
    if ($orderId > 0 && in_array($status, ORDER_STATUSES, true)) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        flash('Order status updated.');
    }
    redirect('admin/orders.php' . ($orderId ? '?id=' . $orderId : ''));
}

$viewId = (int) ($_GET['id'] ?? 0);

$pageTitle = 'Orders';
require __DIR__ . '/includes/admin_header.php';

if ($viewId > 0) {
    // Single order detail view.
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$viewId]);
    $order = $stmt->fetch();

    if (!$order) {
        echo '<div class="alert alert-error">Order not found.</div>';
        require __DIR__ . '/includes/admin_footer.php';
        return;
    }

    $itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $itemStmt->execute([$viewId]);
    $lines = $itemStmt->fetchAll();
    ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/orders.php')) ?>">&larr; Back to orders</a>

    <div class="order-grid">
        <div class="card">
            <div class="card-head">
                <h2>Order #<?= e($order['code']) ?></h2>
                <span class="status status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
            </div>
            <table class="data-table">
                <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($lines as $l): ?>
                        <tr>
                            <td><?= e($l['item_name']) ?></td>
                            <td><?= money((float) $l['unit_price']) ?></td>
                            <td><?= (int) $l['quantity'] ?></td>
                            <td><?= money((float) $l['unit_price'] * (int) $l['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="summary-card">
                <div class="summary-row"><span>Subtotal</span><span><?= money((float) $order['subtotal']) ?></span></div>
                <div class="summary-row"><span>Delivery</span><span><?= money((float) $order['delivery_fee']) ?></span></div>
                <div class="summary-row"><span>Tax</span><span><?= money((float) $order['tax']) ?></span></div>
                <div class="summary-row summary-total"><span>Total</span><span><?= money((float) $order['total']) ?></span></div>
            </div>
        </div>

        <div class="card">
            <h2>Customer</h2>
            <p><strong><?= e($order['customer_name']) ?></strong></p>
            <p class="muted">&#128222; <?= e($order['customer_phone']) ?></p>
            <?php if ($order['customer_email']): ?><p class="muted">&#9993; <?= e($order['customer_email']) ?></p><?php endif; ?>
            <p class="muted">&#128205; <?= nl2br(e($order['address'])) ?></p>
            <?php if ($order['notes']): ?><p><strong>Notes:</strong> <?= e($order['notes']) ?></p><?php endif; ?>
            <p class="muted">Placed: <?= e($order['created_at']) ?> UTC</p>

            <hr>
            <h3>Update Status</h3>
            <form method="post" action="<?= e(url('admin/orders.php')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                <select name="status">
                    <?php foreach (ORDER_STATUSES as $s): ?>
                        <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                            <?= e(status_label($s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        </div>
    </div>
    <?php
    require __DIR__ . '/includes/admin_footer.php';
    return;
}

// List view with optional status filter.
$filter = (string) ($_GET['status'] ?? '');
$sql = 'SELECT * FROM orders';
$params = [];
if (in_array($filter, ORDER_STATUSES, true)) {
    $sql .= ' WHERE status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="filter-bar">
    <a class="chip <?= $filter === '' ? 'chip-active' : '' ?>" href="<?= e(url('admin/orders.php')) ?>">All</a>
    <?php foreach (ORDER_STATUSES as $s): ?>
        <a class="chip <?= $filter === $s ? 'chip-active' : '' ?>"
           href="<?= e(url('admin/orders.php?status=' . urlencode($s))) ?>"><?= e(status_label($s)) ?></a>
    <?php endforeach; ?>
</div>

<div class="card">
    <?php if (!$orders): ?>
        <p class="muted">No orders found.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Code</th><th>Customer</th><th>Phone</th><th>Total</th><th>Status</th><th>Placed</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= e($o['code']) ?></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td class="muted"><?= e($o['customer_phone']) ?></td>
                        <td><?= money((float) $o['total']) ?></td>
                        <td><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                        <td class="muted"><?= e($o['created_at']) ?></td>
                        <td><a class="btn btn-outline btn-sm" href="<?= e(url('admin/orders.php?id=' . $o['id'])) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
