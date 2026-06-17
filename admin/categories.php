<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save') {
        $id    = (int) ($_POST['id'] ?? 0);
        $name  = trim((string) ($_POST['name'] ?? ''));
        $order = (int) ($_POST['sort_order'] ?? 0);
        $slug  = slugify($name);

        if ($name === '' || $slug === '') {
            flash('Category name is required.', 'error');
        } else {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare('UPDATE categories SET name=?, slug=?, sort_order=? WHERE id=?');
                    $stmt->execute([$name, $slug, $order, $id]);
                    flash('Category updated.');
                } else {
                    $stmt = $pdo->prepare('INSERT INTO categories (name, slug, sort_order) VALUES (?,?,?)');
                    $stmt->execute([$name, $slug, $order]);
                    flash('Category added.');
                }
            } catch (PDOException $e) {
                flash('A category with that name already exists.', 'error');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $count = (int) $pdo->query('SELECT COUNT(*) FROM menu_items WHERE category_id = ' . $id)->fetchColumn();
        if ($count > 0) {
            flash('Cannot delete: category still has ' . $count . ' item(s).', 'error');
        } else {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            flash('Category deleted.');
        }
    }

    redirect('admin/categories.php');
}

$editCat = null;
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch() ?: null;
}

$categories = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM menu_items m WHERE m.category_id = c.id) AS item_count
       FROM categories c ORDER BY c.sort_order, c.name'
)->fetchAll();

$pageTitle = 'Categories';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="order-grid">
    <div class="card">
        <h2><?= $editCat ? 'Edit Category' : 'Add Category' ?></h2>
        <form method="post" action="<?= e(url('admin/categories.php')) ?>" class="stacked-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">
            <label>Name
                <input type="text" name="name" value="<?= e($editCat['name'] ?? '') ?>" required>
            </label>
            <label>Sort Order
                <input type="number" name="sort_order" value="<?= e((string) ($editCat['sort_order'] ?? '0')) ?>">
            </label>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= $editCat ? 'Update' : 'Add' ?></button>
                <?php if ($editCat): ?>
                    <a class="btn btn-ghost" href="<?= e(url('admin/categories.php')) ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Categories (<?= count($categories) ?>)</h2>
        <?php if (!$categories): ?>
            <p class="muted">No categories yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Slug</th><th>Items</th><th>Order</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?= e($c['name']) ?></td>
                            <td class="muted"><?= e($c['slug']) ?></td>
                            <td><?= (int) $c['item_count'] ?></td>
                            <td><?= (int) $c['sort_order'] ?></td>
                            <td class="row-actions">
                                <a class="btn btn-outline btn-sm" href="<?= e(url('admin/categories.php?edit=' . $c['id'])) ?>">Edit</a>
                                <form method="post" action="<?= e(url('admin/categories.php')) ?>"
                                      onsubmit="return confirm('Delete this category?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
