<?php
require_once __DIR__ . '/includes/functions.php';

$details = cart_details();
if (!$details['lines']) {
    flash('Your cart is empty.', 'error');
    redirect('cart.php');
}

$totals = order_totals($details['subtotal']);
$errors = [];
$old = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $old['name']    = trim((string) ($_POST['name'] ?? ''));
    $old['phone']   = trim((string) ($_POST['phone'] ?? ''));
    $old['email']   = trim((string) ($_POST['email'] ?? ''));
    $old['address'] = trim((string) ($_POST['address'] ?? ''));
    $old['notes']   = trim((string) ($_POST['notes'] ?? ''));

    if ($old['name'] === '')    $errors['name'] = 'Please enter your name.';
    if ($old['phone'] === '')   $errors['phone'] = 'Please enter a contact phone number.';
    if ($old['address'] === '') $errors['address'] = 'Please enter a delivery address.';
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $code = strtoupper(bin2hex(random_bytes(3))); // e.g. A1B2C3

            $stmt = $pdo->prepare(
                'INSERT INTO orders
                    (code, customer_name, customer_phone, customer_email, address, notes,
                     subtotal, delivery_fee, tax, total, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $code, $old['name'], $old['phone'], $old['email'], $old['address'], $old['notes'],
                $totals['subtotal'], $totals['delivery_fee'], $totals['tax'], $totals['total'], 'pending',
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, item_id, item_name, unit_price, quantity)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($details['lines'] as $line) {
                $itemStmt->execute([$orderId, $line['id'], $line['name'], $line['price'], $line['quantity']]);
            }

            $pdo->commit();
            cart_clear();
            flash('Order placed successfully! Your order code is ' . $code . '.');
            redirect('track.php?code=' . urlencode($code));
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors['general'] = 'Something went wrong placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Checkout</h1>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-error"><?= e($errors['general']) ?></div>
<?php endif; ?>

<div class="cart-layout">
    <form class="checkout-form" method="post" action="<?= e(url('checkout.php')) ?>">
        <?= csrf_field() ?>
        <h3>Delivery Details</h3>

        <label>Full Name *
            <input type="text" name="name" value="<?= e($old['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
        </label>

        <div class="form-row">
            <label>Phone *
                <input type="text" name="phone" value="<?= e($old['phone']) ?>" required>
                <?php if (!empty($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
            </label>
            <label>Email
                <input type="email" name="email" value="<?= e($old['email']) ?>">
                <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
            </label>
        </div>

        <label>Delivery Address *
            <textarea name="address" rows="3" required><?= e($old['address']) ?></textarea>
            <?php if (!empty($errors['address'])): ?><span class="field-error"><?= e($errors['address']) ?></span><?php endif; ?>
        </label>

        <label>Order Notes (optional)
            <textarea name="notes" rows="2" placeholder="e.g. leave at the door, no onions"><?= e($old['notes']) ?></textarea>
        </label>

        <p class="muted">Payment: Cash on delivery.</p>
        <button class="btn btn-primary btn-lg" type="submit">Place Order</button>
    </form>

    <aside class="summary-card">
        <h3>Order Summary</h3>
        <?php foreach ($details['lines'] as $line): ?>
            <div class="summary-row">
                <span><?= e($line['name']) ?> &times; <?= $line['quantity'] ?></span>
                <span><?= money($line['line_total']) ?></span>
            </div>
        <?php endforeach; ?>
        <hr>
        <div class="summary-row"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
        <div class="summary-row"><span>Delivery</span><span><?= money($totals['delivery_fee']) ?></span></div>
        <div class="summary-row"><span>Tax</span><span><?= money($totals['tax']) ?></span></div>
        <div class="summary-row summary-total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
    </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
