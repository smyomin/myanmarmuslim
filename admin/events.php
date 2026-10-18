<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once '../includes/functions.php';

$success = '';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM islamic_events WHERE id = ?")->execute([(int)$_GET['delete']]);
    header('Location: events.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $hijri    = trim($_POST['hijri_date'] ?? '');
    $greg     = trim($_POST['gregorian_date'] ?? '');
    $desc     = trim($_POST['description'] ?? '');

    if ($title && $greg) {
        $stmt = $pdo->prepare("INSERT INTO islamic_events 
            (title, hijri_date, gregorian_date, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $hijri, $greg, $desc]);
        $success = 'Event added!';
    }
}

$events = $pdo->query("SELECT * FROM islamic_events ORDER BY gregorian_date ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Islamic Events - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                gold: { DEFAULT: '#c9a84c' }
            }}}
        }
    </script>
</head>
<body class="bg-gray-100">

<nav class="bg-primary text-white px-6 py-4 flex justify-between items-center shadow">
    <div class="flex items-center gap-3">
        <img src="<?= SITE_URL ?>/assets/images/logowhite.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
        <span class="font-bold text-lg">Myanmar Muslims Admin</span>
    </div>
    <div class="flex gap-3">
        <a href="dashboard.php" class="text-green-200 hover:text-white text-sm">Dashboard</a>
        <a href="logout.php" class="bg-red-500 text-white text-sm px-4 py-2 rounded-lg">Logout</a>
    </div>
</nav>

<div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">📅 Islamic Events</h1>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Add Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-bold text-gray-800 mb-4">Add Event</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Event Name *</label>
                    <input type="text" name="title" required
                           placeholder="e.g. Eid al-Fitr"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Hijri Date</label>
                    <input type="text" name="hijri_date"
                           placeholder="e.g. 1 Shawwal 1448"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Gregorian Date *</label>
                    <input type="date" name="gregorian_date" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary resize-none"></textarea>
                </div>
                <button type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Add Event
                </button>
            </form>
        </div>

        <!-- Events List -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Event</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden md:table-cell">Date</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden md:table-cell">Days</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            No events yet.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <?php
                    $daysLeft = (strtotime($event['gregorian_date']) - strtotime(date('Y-m-d'))) / 86400;
                    $isPast   = $daysLeft < 0;
                    ?>
                    <tr class="hover:bg-gray-50 <?= $isPast ? 'opacity-50' : '' ?>">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800"><?= clean($event['title']) ?></p>
                            <p class="text-xs text-gray-400"><?= clean($event['hijri_date']) ?></p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                            <?= formatDate($event['gregorian_date']) ?>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <?php if ($isPast): ?>
                                <span class="text-xs text-gray-400">Past</span>
                            <?php elseif ($daysLeft == 0): ?>
                                <span class="text-xs font-bold text-primary">Today!</span>
                            <?php else: ?>
                                <span class="text-xs text-gray-600">In <?= (int)$daysLeft ?> days</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <a href="events.php?delete=<?= $event['id'] ?>"
                               onclick="return confirm('Delete this event?')"
                               class="text-red-500 hover:text-red-700 text-xs font-medium">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>