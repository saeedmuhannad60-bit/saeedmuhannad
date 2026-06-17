<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save') {
        $id          = (int) ($_POST['id'] ?? 0);
        $categoryId  = (int) ($_POST['category_id'] ?? 0);
        $name        = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price       = (float) ($_POST['price'] ?? 0);
        $available   = isset($_POST['is_available']) ? 1 : 0;
        $featured    = isset($_POST['is_featured']) ? 1 : 0;

        if ($name === '' || $categoryId <= 0) {
            flash('Name and category are required.', 'error');
        } elseif ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE menu_items SET category_id=?, name=?, description=?, price=?, is_available=?, is_featured=? WHERE id=?'
            );
            $stmt->execute([$categoryId, $name, $description, $price, $available, $featured, $id]);
            flash('Menu item updated.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured) VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([$categoryId, $name, $description, $price, $available, $featured]);
            flash('Menu item added.');
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->execute([(int) ($_POST['id'] ?? 0)]);
        flash('Menu item deleted.');
    }

    redirect('admin/menu.php');
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();

// Editing an existing item?
$editItem = null;
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch() ?: null;
}

$items = $pdo->query(
    'SELECT m.*, c.name AS category_name
       FROM menu_items m JOIN categories c ON c.id = m.category_id
      ORDER BY c.sort_order, m.name'
)->fetchAll();

$pageTitle = 'Menu Items';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="order-grid">
    <div class="card">
        <h2><?= $editItem ? 'Edit Item' : 'Add New Item' ?></h2>
        <?php if (!$categories): ?>
            <p class="muted">Create a <a href="<?= e(url('admin/categories.php')) ?>">category</a> first.</p>
        <?php else: ?>
        <form method="post" action="<?= e(url('admin/menu.php')) ?>" class="stacked-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editItem['id'] ?? 0 ?>">

            <label>Name
                <input type="text" name="name" value="<?= e($editItem['name'] ?? '') ?>" required>
            </label>
            <label>Category
                <select name="category_id" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"
                            <?= ($editItem && (int) $editItem['category_id'] === (int) $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Description
                <textarea name="description" rows="3"><?= e($editItem['description'] ?? '') ?></textarea>
            </label>
            <label>Price (<?= e(CURRENCY) ?>)
                <input type="number" step="0.01" min="0" name="price" value="<?= e((string) ($editItem['price'] ?? '0')) ?>" required>
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_available" <?= (!$editItem || $editItem['is_available']) ? 'checked' : '' ?>> Available
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_featured" <?= ($editItem && $editItem['is_featured']) ? 'checked' : '' ?>> Featured on home page
            </label>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= $editItem ? 'Update' : 'Add' ?> Item</button>
                <?php if ($editItem): ?>
                    <a class="btn btn-ghost" href="<?= e(url('admin/menu.php')) ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>All Items (<?= count($items) ?>)</h2>
        <?php if (!$items): ?>
            <p class="muted">No menu items yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <?= e($it['name']) ?>
                                <?php if ($it['is_featured']): ?><span class="badge">Featured</span><?php endif; ?>
                            </td>
                            <td class="muted"><?= e($it['category_name']) ?></td>
                            <td><?= money((float) $it['price']) ?></td>
                            <td>
                                <?php if ($it['is_available']): ?>
                                    <span class="status status-completed">Available</span>
                                <?php else: ?>
                                    <span class="status status-cancelled">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td class="row-actions">
                                <a class="btn btn-outline btn-sm" href="<?= e(url('admin/menu.php?edit=' . $it['id'])) ?>">Edit</a>
                                <form method="post" action="<?= e(url('admin/menu.php')) ?>"
                                      onsubmit="return confirm('Delete this item?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
