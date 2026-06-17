<?php
/**
 * Database connection, schema bootstrap and seeding.
 *
 * Uses PDO + SQLite so the application runs with no external DB server.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $isNew = !file_exists(DB_PATH);
    if (!is_dir(dirname(DB_PATH))) {
        mkdir(dirname(DB_PATH), 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        init_schema($pdo);
        seed_data($pdo);
    }

    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE categories (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT NOT NULL UNIQUE,
            slug        TEXT NOT NULL UNIQUE,
            sort_order  INTEGER NOT NULL DEFAULT 0
        );

        CREATE TABLE menu_items (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id  INTEGER NOT NULL,
            name         TEXT NOT NULL,
            description  TEXT NOT NULL DEFAULT '',
            price        REAL NOT NULL DEFAULT 0,
            image        TEXT NOT NULL DEFAULT '',
            is_available INTEGER NOT NULL DEFAULT 1,
            is_featured  INTEGER NOT NULL DEFAULT 0,
            created_at   TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        );

        CREATE TABLE orders (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            code            TEXT NOT NULL UNIQUE,
            customer_name   TEXT NOT NULL,
            customer_phone  TEXT NOT NULL,
            customer_email  TEXT NOT NULL DEFAULT '',
            address         TEXT NOT NULL,
            notes           TEXT NOT NULL DEFAULT '',
            subtotal        REAL NOT NULL DEFAULT 0,
            delivery_fee    REAL NOT NULL DEFAULT 0,
            tax             REAL NOT NULL DEFAULT 0,
            total           REAL NOT NULL DEFAULT 0,
            status          TEXT NOT NULL DEFAULT 'pending',
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE order_items (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id    INTEGER NOT NULL,
            item_id     INTEGER,
            item_name   TEXT NOT NULL,
            unit_price  REAL NOT NULL,
            quantity    INTEGER NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );

        CREATE TABLE admins (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            name          TEXT NOT NULL DEFAULT 'Administrator',
            created_at    TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");
}

function seed_data(PDO $pdo): void
{
    // Default admin account: admin / admin123
    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, name) VALUES (?, ?, ?)');
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Restaurant Manager']);

    $categories = [
        ['Starters', 'starters'],
        ['Main Course', 'main-course'],
        ['Pizza', 'pizza'],
        ['Burgers', 'burgers'],
        ['Desserts', 'desserts'],
        ['Drinks', 'drinks'],
    ];
    $catStmt = $pdo->prepare('INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)');
    foreach ($categories as $i => $c) {
        $catStmt->execute([$c[0], $c[1], $i]);
    }

    // category slug => list of [name, description, price, featured]
    $items = [
        'starters' => [
            ['Garlic Bread', 'Toasted ciabatta with garlic butter and herbs.', 5.50, 0],
            ['Mozzarella Sticks', 'Crispy breaded mozzarella with marinara dip.', 6.75, 1],
            ['Chicken Wings', 'Spicy buffalo wings with blue cheese dressing.', 8.90, 0],
        ],
        'main-course' => [
            ['Grilled Salmon', 'Atlantic salmon with lemon butter and seasonal veg.', 16.50, 1],
            ['Chicken Alfredo', 'Fettuccine in creamy parmesan sauce with grilled chicken.', 13.90, 0],
            ['Beef Steak', '8oz sirloin steak with fries and pepper sauce.', 19.50, 1],
        ],
        'pizza' => [
            ['Margherita', 'Classic tomato, mozzarella and fresh basil.', 10.00, 0],
            ['Pepperoni', 'Loaded with pepperoni and extra cheese.', 12.50, 1],
            ['BBQ Chicken', 'Grilled chicken, red onion and smoky BBQ sauce.', 13.00, 0],
        ],
        'burgers' => [
            ['Classic Cheeseburger', 'Beef patty, cheddar, lettuce, tomato and house sauce.', 9.50, 1],
            ['Double Bacon Burger', 'Two patties, crispy bacon and melted cheese.', 12.90, 0],
            ['Veggie Burger', 'Grilled plant-based patty with avocado.', 9.00, 0],
        ],
        'desserts' => [
            ['Chocolate Lava Cake', 'Warm molten chocolate cake with vanilla ice cream.', 6.50, 1],
            ['Cheesecake', 'New York style cheesecake with berry compote.', 6.00, 0],
        ],
        'drinks' => [
            ['Fresh Lemonade', 'Hand-squeezed lemons with mint.', 3.50, 0],
            ['Iced Coffee', 'Cold brew with a splash of milk.', 4.00, 0],
            ['Soft Drink', 'Choice of cola, lemon-lime or orange.', 2.50, 0],
        ],
    ];

    $catIds = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $row) {
        $catIds[$row['slug']] = (int) $row['id'];
    }

    $itemStmt = $pdo->prepare(
        'INSERT INTO menu_items (category_id, name, description, price, is_featured) VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($items as $slug => $list) {
        foreach ($list as $it) {
            $itemStmt->execute([$catIds[$slug], $it[0], $it[1], $it[2], $it[3]]);
        }
    }
}
