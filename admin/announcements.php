<?php
require_once 'auth.php';
require_once '../includes/functions.php';

$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([(int)$_GET['delete']]);
    header('Location: announcements.php?deleted=1');
    exit;
}

// Handle toggle
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $cur = $pdo->prepare("SELECT is_active FROM announcements WHERE id = ?");
    $cur->execute([$id]);
    $row = $cur->fetch();
    $pdo->prepare("UPDATE announcements SET is_active = ? WHERE id = ?")
        ->execute([$row['is_active'] ? 0 : 1, $id]);
    header('Location: announcements.php');
    exit;
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type    = $_POST['type'] ?? 'general';
    $expires = $_POST['expires_at'] ?? null;

    if ($title && $message) {
        $stmt = $pdo->prepare("INSERT INTO announcements 
            (title, message, type, is_active, expires_at) VALUES (?, ?, ?, 1, ?)");
        $stmt->execute([$title, $message, $type, $expires ?: null]);
        $success = 'Announcement published!';
    }
}

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Admin</title>
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

<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">
        📢 Moon Sighting & Announcements
    </h1>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
        Announcement deleted.
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Add Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-bold text-gray-800 mb-4">New Announcement</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Type</label>
                    <select name="type"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="moon">🌙 Moon Sighting</option>
                        <option value="eid">🎉 Eid Announcement</option>
                        <option value="muharram">🖤 Muharram</option>
                        <option value="general">📢 General</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Title *</label>
                    <input type="text" name="title" required
                           placeholder="e.g. Eid al-Fitr Moon Sighted!"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Message *</label>
                    <textarea name="message" rows="3" required
                              placeholder="e.g. The moon has been sighted. Eid al-Fitr is tomorrow..."
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary resize-none"></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">
                        Expires On <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="date" name="expires_at"
                           min="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
                <button type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-bullhorn mr-2"></i>Publish Announcement
                </button>
            </form>
        </div>

        <!-- List -->
        <div class="space-y-3">
            <h2 class="font-bold text-gray-800">All Announcements</h2>
            <?php if (empty($announcements)): ?>
            <div class="bg-white rounded-xl p-8 text-center text-gray-400">
                No announcements yet.
            </div>
            <?php else: ?>
            <?php foreach ($announcements as $ann): ?>
            <?php
            $typeIcons = ['moon'=>'🌙','eid'=>'🎉','muharram'=>'🖤','general'=>'📢'];
            $icon = $typeIcons[$ann['type']] ?? '📢';
            $expired = $ann['expires_at'] && $ann['expires_at'] < date('Y-m-d');
            ?>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 <?= $ann['is_active'] && !$expired ? 'border-primary' : 'border-gray-200' ?>">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span><?= $icon ?></span>
                            <span class="font-semibold text-sm text-gray-800">
                                <?= clean($ann['title']) ?>
                            </span>
                            <?php if ($ann['is_active'] && !$expired): ?>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                Active
                            </span>
                            <?php elseif ($expired): ?>
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                Expired
                            </span>
                            <?php else: ?>
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                                Hidden
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 line-clamp-2">
                            <?= clean($ann['message']) ?>
                        </p>
                        <?php if ($ann['expires_at']): ?>
                        <p class="text-xs text-gray-400 mt-1">
                            Expires: <?= formatDate($ann['expires_at']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-1">
                        <a href="announcements.php?toggle=<?= $ann['id'] ?>"
                           class="text-xs px-3 py-1 rounded-lg text-center transition
                                  <?= $ann['is_active'] ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-primary text-white hover:bg-primary-dark' ?>">
                            <?= $ann['is_active'] ? 'Hide' : 'Show' ?>
                        </a>
                        <a href="announcements.php?delete=<?= $ann['id'] ?>"
                           onclick="return confirm('Delete this announcement?')"
                           class="text-xs px-3 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-center transition">
                            Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>