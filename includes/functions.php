<?php
/**
 * Shared helper functions: escaping, money, cart, CSRF and auth.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/** HTML-escape a string. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Format a number as currency. */
function money(float $amount): string
{
    return CURRENCY . number_format($amount, 2);
}

/** Build a URL relative to the application base. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/* ----------------------------------------------------------------------------
 * CSRF protection
 * ------------------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(400);
        exit('Invalid CSRF token. Please go back and try again.');
    }
}

/* ----------------------------------------------------------------------------
 * Flash messages
 * ------------------------------------------------------------------------- */

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* ----------------------------------------------------------------------------
 * Cart (session based)
 * ------------------------------------------------------------------------- */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_add(int $itemId, int $qty = 1): void
{
    $qty = max(1, $qty);
    $_SESSION['cart'][$itemId] = ($_SESSION['cart'][$itemId] ?? 0) + $qty;
}

function cart_set(int $itemId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$itemId]);
    } else {
        $_SESSION['cart'][$itemId] = $qty;
    }
}

function cart_remove(int $itemId): void
{
    unset($_SESSION['cart'][$itemId]);
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_count(): int
{
    return array_sum(cart());
}

/**
 * Resolve the cart into detailed line items joined with current menu data.
 *
 * @return array{lines: array<int, array>, subtotal: float}
 */
function cart_details(): array
{
    $cart = cart();
    if (!$cart) {
        return ['lines' => [], 'subtotal' => 0.0];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT id, name, price, image FROM menu_items WHERE id IN ($placeholders) AND is_available = 1"
    );
    $stmt->execute($ids);

    $lines = [];
    $subtotal = 0.0;
    foreach ($stmt->fetchAll() as $row) {
        $qty = (int) $cart[$row['id']];
        $lineTotal = $qty * (float) $row['price'];
        $subtotal += $lineTotal;
        $lines[] = [
            'id'         => (int) $row['id'],
            'name'       => $row['name'],
            'price'      => (float) $row['price'],
            'image'      => $row['image'],
            'quantity'   => $qty,
            'line_total' => $lineTotal,
        ];
    }

    return ['lines' => $lines, 'subtotal' => $subtotal];
}

/** Compute totals (subtotal, delivery, tax, total) from a subtotal. */
function order_totals(float $subtotal): array
{
    $delivery = $subtotal > 0 ? DELIVERY_FEE : 0.0;
    $tax = round($subtotal * TAX_RATE, 2);
    return [
        'subtotal'     => round($subtotal, 2),
        'delivery_fee' => round($delivery, 2),
        'tax'          => $tax,
        'total'        => round($subtotal + $delivery + $tax, 2),
    ];
}

/* ----------------------------------------------------------------------------
 * Admin authentication
 * ------------------------------------------------------------------------- */

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_admin(): void
{
    if (!current_admin()) {
        redirect('admin/login.php');
    }
}

function status_label(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

/**
 * Render a single menu item card (shared between the home and menu pages).
 *
 * @param array $item        Row from menu_items (may include category_name).
 * @param bool  $showCategory Show the category tag above the name.
 */
function render_menu_card(array $item, bool $showCategory = false): string
{
    ob_start();
    ?>
    <article class="menu-card">
        <div class="menu-card-thumb"><?= e(mb_substr((string) $item['name'], 0, 1)) ?></div>
        <div class="menu-card-body">
            <?php if ($showCategory && !empty($item['category_name'])): ?>
                <span class="tag"><?= e($item['category_name']) ?></span>
            <?php endif; ?>
            <h3><?= e($item['name']) ?></h3>
            <p class="muted"><?= e($item['description']) ?></p>
            <div class="menu-card-foot">
                <span class="price"><?= money((float) $item['price']) ?></span>
                <form method="post" action="<?= e(url('cart.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                    <button class="btn btn-primary btn-sm" type="submit">Add to Cart</button>
                </form>
            </div>
        </div>
    </article>
    <?php
    return (string) ob_get_clean();
}
