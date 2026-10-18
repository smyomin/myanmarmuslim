<?php
// memorial.php
require '../includes/config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT d.*, p.section, p.plot_number, c.name AS cemetery_name, c.city
    FROM deceased d
    LEFT JOIN plots p ON d.plot_id = p.id
    LEFT JOIN cemeteries c ON d.cemetery_id = c.id
    WHERE d.id = :id
");
$stmt->execute(['id' => $id]);
$person = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$person) {
    die("Record not found.");
}

// Handle new condolence submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name !== '' && $message !== '') {
        $insert = $pdo->prepare("INSERT INTO condolences (deceased_id, name, message) VALUES (:id, :name, :message)");
        $insert->execute(['id' => $id, 'name' => $name, 'message' => $message]);
        $submitted = true;
    }
}

// Fetch approved condolences
$condStmt = $pdo->prepare("SELECT name, message FROM condolences WHERE deceased_id = :id AND approved = 1 ORDER BY created_at DESC");
$condStmt->execute(['id' => $id]);
$condolences = $condStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($person['full_name']) ?> - Memorial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: sans-serif; max-width: 500px; margin: 20px auto; padding: 0 15px;">

    <a href="search.php">← Back to search</a>

    <h2 style="margin-bottom: 0;"><?= htmlspecialchars($person['full_name']) ?></h2>
    <p style="color: #666;">
        <?= $person['date_of_birth'] ? date('Y', strtotime($person['date_of_birth'])) : '?' ?> – 
        <?= date('Y', strtotime($person['date_of_death'])) ?>
    </p>

    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin: 12px 0;">
        <p><strong>Cemetery:</strong> <?= htmlspecialchars($person['cemetery_name']) ?> (<?= htmlspecialchars($person['city']) ?>)</p>
        <p><strong>Plot:</strong> Section <?= htmlspecialchars($person['section']) ?>, No. <?= htmlspecialchars($person['plot_number']) ?></p>
        <?php if ($person['family_name']): ?>
            <p><strong>Family:</strong> <?= htmlspecialchars($person['family_name']) ?></p>
        <?php endif; ?>
        <?php if ($person['bio']): ?>
            <p><?= nl2br(htmlspecialchars($person['bio'])) ?></p>
        <?php endif; ?>
    </div>

    <h3>Condolences (<?= count($condolences) ?>)</h3>

    <?php if (!empty($submitted)): ?>
        <p style="color: green;">Thank you. Your message will appear after review.</p>
    <?php endif; ?>

    <?php foreach ($condolences as $c): ?>
        <div style="border: 1px solid #eee; border-radius: 8px; padding: 10px; margin-bottom: 8px;">
            <p style="margin: 0;"><?= htmlspecialchars($c['message']) ?></p>
            <p style="margin: 4px 0 0; font-size: 12px; color: #999;">— <?= htmlspecialchars($c['name']) ?></p>
        </div>
    <?php endforeach; ?>

    <form method="POST" style="margin-top: 15px;">
        <input type="text" name="name" placeholder="Your name" required style="width: 100%; padding: 8px; margin-bottom: 6px;">
        <textarea name="message" placeholder="Leave a condolence message" required style="width: 100%; padding: 8px; height: 60px;"></textarea>
        <button type="submit" style="padding: 8px 16px; margin-top: 6px;">Submit</button>
    </form>

</body>
</html>