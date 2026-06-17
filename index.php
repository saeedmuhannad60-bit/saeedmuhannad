<?php
require_once __DIR__ . '/includes/functions.php';

$featured = db()->query(
    'SELECT m.*, c.name AS category_name
       FROM menu_items m
       JOIN categories c ON c.id = m.category_id
      WHERE m.is_featured = 1 AND m.is_available = 1
      ORDER BY m.name
      LIMIT 6'
)->fetchAll();

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Delicious food, <span>delivered to your door.</span></h1>
        <p><?= e(APP_TAGLINE) ?> Order online from our freshly prepared menu and track your delivery in real time.</p>
        <a class="btn btn-primary btn-lg" href="<?= e(url('menu.php')) ?>">Order Now</a>
        <a class="btn btn-outline btn-lg" href="<?= e(url('track.php')) ?>">Track Order</a>
    </div>
</section>

<section class="features">
    <div class="feature">
        <span class="feature-icon">&#128666;</span>
        <h3>Fast Delivery</h3>
        <p>Hot meals delivered to your doorstep in under 40 minutes.</p>
    </div>
    <div class="feature">
        <span class="feature-icon">&#127859;</span>
        <h3>Fresh Ingredients</h3>
        <p>Locally sourced produce prepared by our expert chefs.</p>
    </div>
    <div class="feature">
        <span class="feature-icon">&#128179;</span>
        <h3>Easy Ordering</h3>
        <p>Browse, add to cart and checkout in just a few clicks.</p>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Featured Dishes</h2>
        <a href="<?= e(url('menu.php')) ?>">View full menu &rarr;</a>
    </div>

    <?php if (!$featured): ?>
        <p class="muted">No featured dishes yet. Check out our <a href="<?= e(url('menu.php')) ?>">full menu</a>.</p>
    <?php else: ?>
        <div class="menu-grid">
            <?php foreach ($featured as $item): ?>
                <article class="menu-card">
                    <div class="menu-card-thumb"><?= e(mb_substr($item['name'], 0, 1)) ?></div>
                    <div class="menu-card-body">
                        <span class="tag"><?= e($item['category_name']) ?></span>
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
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
