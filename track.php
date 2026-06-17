<?php
require_once __DIR__ . '/includes/functions.php';

$code = strtoupper(trim((string) ($_GET['code'] ?? '')));
$order = null;
$lines = [];

if ($code !== '') {
    $stmt = db()->prepare('SELECT * FROM orders WHERE code = ?');
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if ($order) {
        $itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemStmt->execute([$order['id']]);
        $lines = $itemStmt->fetchAll();
    }
}

$pageTitle = 'Track Order';
require __DIR__ . '/includes/header.php';

$steps = ['pending' => 'Order Placed', 'preparing' => 'Preparing', 'out_for_delivery' => 'Out for Delivery', 'completed' => 'Delivered'];
?>

<div class="page-head">
    <h1>Track Your Order</h1>
    <p class="muted">Enter the order code you received at checkout.</p>
</div>

<form class="track-form" method="get" action="<?= e(url('track.php')) ?>">
    <input type="text" name="code" placeholder="e.g. A1B2C3" value="<?= e($code) ?>" required>
    <button class="btn btn-primary" type="submit">Track</button>
</form>

<?php if ($code !== '' && !$order): ?>
    <div class="alert alert-error">No order found with code <strong><?= e($code) ?></strong>.</div>
<?php endif; ?>

<?php if ($order): ?>
    <div class="order-detail">
        <div class="order-detail-head">
            <h2>Order #<?= e($order['code']) ?></h2>
            <span class="status status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
        </div>
        <p class="muted">Placed on <?= e($order['created_at']) ?> UTC</p>

        <?php if ($order['status'] === 'cancelled'): ?>
            <div class="alert alert-error">This order was cancelled.</div>
        <?php else: ?>
            <ol class="progress">
                <?php
                $statusKeys = array_keys($steps);
                $currentIndex = array_search($order['status'], $statusKeys, true);
                $currentIndex = $currentIndex === false ? 0 : $currentIndex;
                foreach (array_values($steps) as $i => $label):
                    $cls = $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'active' : '');
                ?>
                    <li class="<?= $cls ?>"><span class="dot"></span><?= e($label) ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <h3>Items</h3>
        <table class="cart-table">
            <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                    <tr>
                        <td data-label="Item"><?= e($line['item_name']) ?></td>
                        <td data-label="Price"><?= money((float) $line['unit_price']) ?></td>
                        <td data-label="Qty"><?= (int) $line['quantity'] ?></td>
                        <td data-label="Total"><?= money((float) $line['unit_price'] * (int) $line['quantity']) ?></td>
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
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
