<?php
require_once 'auth.php';
require_once '../includes/functions.php';

$id    = (int)($_GET['id'] ?? 0);
$video = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch();
}

if (!$video) {
    header('Location: videos.php');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $youtubeUrl  = trim($_POST['youtube_url'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if (!$title) {
        $error = 'Title is required.';
    } elseif (!$youtubeUrl) {
        $error = 'YouTube URL is required.';
    } else {
        $youtubeId = getYoutubeId($youtubeUrl);
        if (!$youtubeId) {
            $error = 'Invalid YouTube URL.';
        } else {
           $category = trim($_POST['category'] ?? '');
            $stmt = $pdo->prepare("UPDATE videos SET 
                title=?, description=?, youtube_url=?, youtube_id=?, category=?, is_published=?
                WHERE id=?");
            $stmt->execute([$title, $description, $youtubeUrl, $youtubeId, $category, $isPublished, $id]);
            $success = 'Video updated successfully!';
            // Refresh
            $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
            $stmt->execute([$id]);
            $video = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' }
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

<main class="max-w-2xl mx-auto p-6">

    <div class="flex items-center gap-3 mb-6">
        <a href="videos.php" class="text-gray-400 hover:text-primary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Video</h1>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
    </div>
    <?php endif; ?>

    <!-- Current Video Preview -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <p class="text-sm font-medium text-gray-700 mb-3">Current Video</p>
        <div style="position: relative; padding-bottom: 56.25%; height: 0; border-radius: 8px; overflow: hidden;">
            <iframe src="https://www.youtube.com/embed/<?= clean($video['youtube_id']) ?>"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                    allowfullscreen></iframe>
        </div>
    </div>

    <form method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-5">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Title <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" value="<?= clean($video['title']) ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                YouTube URL <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <i class="fab fa-youtube absolute left-3 top-3 text-red-500 text-lg"></i>
                <input type="url" name="youtube_url"
                       value="<?= clean($video['youtube_url']) ?>"
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                       required>
            </div>
        </div>
        <!-- Category -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                <option value="">-- Select Category --</option>
                <option value="quran-tafsir"     <?= ($video['category'] ?? '') === 'quran-tafsir'     ? 'selected' : '' ?>>Quran & Tafsir</option>
                <option value="history"          <?= ($video['category'] ?? '') === 'history'          ? 'selected' : '' ?>>History</option>
                <option value="aqeedah"          <?= ($video['category'] ?? '') === 'aqeedah'          ? 'selected' : '' ?>>Aqeedah</option>
                <option value="panel-discussion" <?= ($video['category'] ?? '') === 'panel-discussion' ? 'selected' : '' ?>>Panel Discussion</option>
                <option value="lecture"          <?= ($video['category'] ?? '') === 'lecture'          ? 'selected' : '' ?>>Lecture</option>
                <option value="other"            <?= ($video['category'] ?? '') === 'other'            ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary resize-none"
                      ><?= clean($video['description'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_published" id="is_published" value="1"
                   <?= $video['is_published'] ? 'checked' : '' ?>
                   class="w-4 h-4 accent-primary">
            <label for="is_published" class="text-sm font-medium text-gray-700">
                Published (visible on website)
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-primary hover:bg-primary-dark text-white font-semibold px-8 py-3 rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
            <a href="videos.php"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-8 py-3 rounded-lg transition">
                Cancel
            </a>
        </div>
    </form>
</main>

</body>
</html>