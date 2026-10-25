<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    exit('Item not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date_reported = $_POST['date_reported'] ?? '';
    $contact_info = trim($_POST['contact_info'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($item_name === '') {
        $errors[] = 'Please enter the item name.';
    }

    if ($description === '') {
        $errors[] = 'Please enter a description.';
    }

    if ($location === '') {
        $errors[] = 'Please enter the location.';
    }

    $dateObject = DateTime::createFromFormat('!Y-m-d', $date_reported);

    if (!$dateObject || $dateObject->format('Y-m-d') !== $date_reported) {
        $errors[] = 'Please enter a valid date.';
    }

    if ($contact_info === '') {
        $errors[] = 'Please enter contact information.';
    }

    if (!in_array($status, ['Lost', 'Found', 'Claimed'], true)) {
        $errors[] = 'Please select a valid status.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            "UPDATE items
             SET item_name = ?, description = ?, location = ?,
                 date_reported = ?, contact_info = ?, status = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $item_name,
            $description,
            $location,
            $date_reported,
            $contact_info,
            $status,
            $id
        ]);

        header('Location: index.php?message=' . urlencode('Item updated successfully.'));
        exit;
    }

    $item = array_merge($item, [
        'item_name' => $item_name,
        'description' => $description,
        'location' => $location,
        'date_reported' => $date_reported,
        'contact_info' => $contact_info,
        'status' => $status
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Campus Lost Item Tracker</h1>
    <p>Edit an item report</p>
</header>

<main class="container form-container">
    <h2>Edit Item</h2>

    <?php if ($errors): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= (int)$id ?>">
        <label for="item_name">Item Name</label>
        <input type="text" id="item_name" name="item_name"
               value="<?= e($item['item_name']) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required><?= e($item['description']) ?></textarea>

        <label for="location">Location Lost or Found</label>
        <input type="text" id="location" name="location"
               value="<?= e($item['location']) ?>" required>

        <label for="date_reported">Date Reported</label>
        <input type="date" id="date_reported" name="date_reported"
               value="<?= e($item['date_reported']) ?>" required>

        <label for="contact_info">Contact Information</label>
        <input type="text" id="contact_info" name="contact_info"
               value="<?= e($item['contact_info']) ?>" required>

        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (['Lost', 'Found', 'Claimed'] as $option): ?>
                <option value="<?= e($option) ?>"
                    <?= $item['status'] === $option ? 'selected' : '' ?>>
                    <?= e($option) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="form-actions">
            <button type="submit">Save Changes</button>
            <a class="button secondary" href="index.php">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>