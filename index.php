<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM items WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (item_name LIKE ? OR description LIKE ? OR location LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (in_array($status, ['Lost', 'Found', 'Claimed'], true)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Lost Item Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Campus Lost Item Tracker</h1>
    <p>Helping our campus community reunite with lost belongings.</p>
</header>

<main class="container">
    <div class="top-bar">
        <h2>Lost and Found Items</h2>
        <a class="button" href="create.php">+ Report an Item</a>
    </div>

    <form class="filters" method="GET" action="index.php">
        <input
            type="text"
            name="search"
            placeholder="Search name, description, or location"
            value="<?= e($search) ?>"
        >

        <select name="status">
            <option value="">All Statuses</option>
            <?php foreach (['Lost', 'Found', 'Claimed'] as $option): ?>
                <option value="<?= e($option) ?>"
                    <?= $status === $option ? 'selected' : '' ?>>
                    <?= e($option) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
        <a class="button secondary" href="index.php">Reset</a>
    </form>

    <?php if (isset($_GET['message'])): ?>
        <p class="notice"><?= e($_GET['message']) ?></p>
    <?php endif; ?>

    <?php if (count($items) > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Date Reported</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Claim Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['item_name']) ?></td>
                            <td><?= nl2br(e($item['description'])) ?></td>
                            <td><?= e($item['location']) ?></td>
                            <td><?= e($item['date_reported']) ?></td>
                            <td><?= e($item['contact_info']) ?></td>
                            <td>
                                <span class="badge <?= strtolower(e($item['status'])) ?>">
                                    <?= e($item['status']) ?>
                                </span>
                            </td>
                            <td><?= e($item['claim_code']) ?></td>
                            <td class="actions">
                                <a class="button small" href="edit.php?id=<?= (int)$item['id'] ?>">
                                    Edit
                                </a>

                                <form method="POST" action="delete.php"
                                      onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                    <button class="button small danger" type="submit">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No items found</h3>
            <p>Try another search or report a lost or found item.</p>
        </div>
    <?php endif; ?>
</main>


</body>
</html>