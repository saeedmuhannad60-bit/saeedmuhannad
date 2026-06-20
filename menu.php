<?php
require_once __DIR__ . '/includes/functions.php';

$categories = db()->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();

$activeSlug = isset($_GET['cat']) ? (string) $_GET['cat'] : '';

$sql = 'SELECT m.*, c.slug AS category_slug, c.name AS category_name
          FROM menu_items m
          JOIN categories c ON c.id = m.category_id
         WHERE m.is_available = 1';
$params = [];
if ($activeSlug !== '') {
    $sql .= ' AND c.slug = ?';
    $params[] = $activeSlug;
}
$sql .= ' ORDER BY c.sort_order, m.name';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Group items by category for display.
$grouped = [];
foreach ($items as $item) {
    $grouped[$item['category_name']][] = $item;
}

$pageTitle = 'Menu';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Our Menu</h1>
    <p class="muted">Pick your favourites and add them to your cart.</p>
</div>

<div class="category-filter">
    <a class="chip <?= $activeSlug === '' ? 'chip-active' : '' ?>" href="<?= e(url('menu.php')) ?>">All</a>
    <?php foreach ($categories as $cat): ?>
        <a class="chip <?= $activeSlug === $cat['slug'] ? 'chip-active' : '' ?>"
           href="<?= e(url('menu.php?cat=' . urlencode($cat['slug']))) ?>"><?= e($cat['name']) ?></a>
    <?php endforeach; ?>
</div>

<?php if (!$grouped): ?>
    <p class="muted">No items found in this category.</p>
<?php endif; ?>

<?php foreach ($grouped as $catName => $catItems): ?>
    <section class="section">
        <h2 class="category-title"><?= e($catName) ?></h2>
        <div class="menu-grid">
            <?php foreach ($catItems as $item): ?>
                <?= render_menu_card($item) ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
