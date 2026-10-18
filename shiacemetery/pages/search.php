<?php
// search.php
require '../includes/config.php';

$results = [];
$query = trim($_GET['q'] ?? '');

if ($query !== '') {
    $stmt = $pdo->prepare("
        SELECT d.id, d.full_name, d.date_of_birth, d.date_of_death, 
               p.section, p.plot_number, c.city
        FROM deceased d
        LEFT JOIN plots p ON d.plot_id = p.id
        LEFT JOIN cemeteries c ON d.cemetery_id = c.id
        WHERE d.full_name LIKE :q OR d.family_name LIKE :q
        ORDER BY d.date_of_death DESC
    ");
    $stmt->execute(['q' => '%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Search - Myanmar Shia Cemetery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: sans-serif; max-width: 500px; margin: 20px auto; padding: 0 15px;">

    <h2>Search Records</h2>
    <form method="GET">
        <input type="text" name="q" placeholder="Search a name..." 
               value="<?= htmlspecialchars($query) ?>" 
               style="width: 100%; padding: 10px; font-size: 16px;">
        <button type="submit" style="padding: 10px 20px; margin-top: 8px;">Search</button>
    </form>

    <?php if ($query !== ''): ?>
        <p><?= count($results) ?> result(s) found</p>

        <?php foreach ($results as $row): ?>
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 10px;">
                <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                <span style="color: #666; font-size: 14px;">
                    <?= $row['date_of_birth'] ? date('Y', strtotime($row['date_of_birth'])) : '?' ?> – 
                    <?= date('Y', strtotime($row['date_of_death'])) ?>
                    · Plot <?= htmlspecialchars($row['section'] . '-' . $row['plot_number']) ?>
                    · <?= htmlspecialchars($row['city']) ?>
                </span><br>
                <a href="memorial.php?id=<?= $row['id'] ?>">View memorial page →</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>