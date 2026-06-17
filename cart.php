<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $id = (int) ($_POST['item_id'] ?? 0);
            if ($id > 0) {
                cart_add($id, (int) ($_POST['quantity'] ?? 1));
                flash('Item added to your cart.');
            }
            break;

        case 'update':
            foreach (($_POST['qty'] ?? []) as $id => $qty) {
                cart_set((int) $id, (int) $qty);
            }
            flash('Cart updated.');
            break;

        case 'remove':
            cart_remove((int) ($_POST['item_id'] ?? 0));
            flash('Item removed from cart.');
            break;

        case 'clear':
            cart_clear();
            flash('Cart cleared.');
            break;
    }

    redirect('cart.php');
}

$details = cart_details();
$lines = $details['lines'];
$totals = order_totals($details['subtotal']);

$pageTitle = 'Your Cart';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Your Cart</h1>
</div>

<?php if (!$lines): ?>
    <div class="empty-state">
        <p>Your cart is empty.</p>
        <a class="btn btn-primary" href="<?= e(url('menu.php')) ?>">Browse the menu</a>
    </div>
<?php else: ?>
    <div class="cart-layout">
        <form class="cart-table-wrap" method="post" action="<?= e(url('cart.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                        <tr>
                            <td data-label="Item"><?= e($line['name']) ?></td>
                            <td data-label="Price"><?= money($line['price']) ?></td>
                            <td data-label="Qty">
                                <input class="qty-input" type="number" min="0" name="qty[<?= $line['id'] ?>]"
                                       value="<?= $line['quantity'] ?>">
                            </td>
                            <td data-label="Total"><?= money($line['line_total']) ?></td>
                            <td>
                                <button class="link-danger" type="submit" form="remove-<?= $line['id'] ?>"
                                        title="Remove item">&times;</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-actions">
                <button class="btn btn-outline" type="submit">Update Cart</button>
                <button class="btn btn-ghost" type="submit" name="action" value="clear">Clear Cart</button>
            </div>
        </form>

        <aside class="summary-card">
            <h3>Order Summary</h3>
            <div class="summary-row"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
            <div class="summary-row"><span>Delivery</span><span><?= money($totals['delivery_fee']) ?></span></div>
            <div class="summary-row"><span>Tax (<?= (int) round(TAX_RATE * 100) ?>%)</span><span><?= money($totals['tax']) ?></span></div>
            <div class="summary-row summary-total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
            <a class="btn btn-primary btn-block" href="<?= e(url('checkout.php')) ?>">Proceed to Checkout</a>
        </aside>
    </div>

    <?php // Hidden single-item remove forms for each row ?>
    <?php foreach ($lines as $line): ?>
        <form id="remove-<?= $line['id'] ?>" method="post" action="<?= e(url('cart.php')) ?>" hidden>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="item_id" value="<?= $line['id'] ?>">
        </form>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
