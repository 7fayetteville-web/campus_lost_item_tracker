<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$errors = [];

$item_name = '';
$description = '';
$location = '';
$date_reported = date('Y-m-d');
$contact_info = '';
$status = 'Lost';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date_reported = $_POST['date_reported'] ?? '';
    $contact_info = trim($_POST['contact_info'] ?? '');
    $status = $_POST['status'] ?? 'Lost';

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

    if (!in_array($status, ['Lost', 'Found'], true)) {
        $errors[] = 'Please select Lost or Found.';
    }

    if (!$errors) {
        $claim_code = makeClaimCode();

        $stmt = $pdo->prepare(
            "INSERT INTO items
            (item_name, description, location, date_reported,
             contact_info, status, claim_code)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $item_name,
            $description,
            $location,
            $date_reported,
            $contact_info,
            $status,
            $claim_code
        ]);

        header('Location: index.php?message=' . urlencode('Item reported successfully.'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report an Item</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Campus Lost Item Tracker</h1>
    <p>Report a lost or found item</p>
</header>

<main class="container form-container">
    <h2>Report an Item</h2>

    <?php if ($errors): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="create.php">
        <label for="item_name">Item Name</label>
        <input type="text" id="item_name" name="item_name"
               value="<?= e($item_name) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required><?= e($description) ?></textarea>

        <label for="location">Location Lost or Found</label>
        <input type="text" id="location" name="location"
               value="<?= e($location) ?>" required>

        <label for="date_reported">Date Reported</label>
        <input type="date" id="date_reported" name="date_reported"
               value="<?= e($date_reported) ?>" required>

        <label for="contact_info">Contact Information</label>
        <input type="text" id="contact_info" name="contact_info"
               value="<?= e($contact_info) ?>" required>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="Lost" <?= $status === 'Lost' ? 'selected' : '' ?>>Lost</option>
            <option value="Found" <?= $status === 'Found' ? 'selected' : '' ?>>Found</option>
        </select>

        <div class="form-actions">
            <button type="submit">Submit Report</button>
            <a class="button secondary" href="index.php">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>