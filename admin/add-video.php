<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once '../includes/functions.php';

$success = '';
$error   = '';
$preview = '';

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
            $error = 'Invalid YouTube URL. Please paste a valid YouTube link.';
        } else {
            $category = trim($_POST['category'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO videos 
                (title, description, youtube_url, youtube_id, category, is_published)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $youtubeUrl, $youtubeId, $category, $isPublished]);
            $success = 'Video added successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Video - Admin</title>
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

<div class="flex">
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-newspaper w-5"></i>Manage News
            </a>
            <a href="videos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fab fa-youtube w-5"></i>Manage Videos
            </a>
            <a href="add-video.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-plus-circle w-5"></i>Add Video
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-cog w-5"></i>Settings
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <div class="max-w-2xl mx-auto">

            <div class="flex items-center gap-3 mb-6">
                <a href="videos.php" class="text-gray-400 hover:text-primary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Add New Video</h1>
            </div>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
                <a href="videos.php" class="ml-3 underline font-medium">View all videos</a>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-5">

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title"
                           value="<?= clean($_POST['title'] ?? '') ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                           placeholder="e.g. Islamic Panel Discussion - Topic" required>
                </div>

                <!-- YouTube URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        YouTube URL <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fab fa-youtube absolute left-3 top-3 text-red-500 text-lg"></i>
                        <input type="url" name="youtube_url" id="youtubeUrl"
                               value="<?= clean($_POST['youtube_url'] ?? '') ?>"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                               placeholder="https://www.youtube.com/watch?v=..."
                               oninput="previewVideo(this.value)" required>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Supports: youtube.com/watch?v=, youtu.be/, youtube.com/shorts/
                    </p>
                </div>

                <!-- Live Preview -->
                <div id="videoPreview" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                    <div class="rounded-xl overflow-hidden border border-gray-200">
                        <div style="position: relative; padding-bottom: 56.25%; height: 0;">
                            <iframe id="previewFrame"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                    allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">-- Select Category --</option>
                        <option value="quran-tafsir">Quran & Tafsir</option>
                        <option value="history">History</option>
                        <option value="aqeedah">Aqeedah</option>
                        <option value="panel-discussion">Panel Discussion</option>
                        <option value="lecture">Lecture</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary resize-none"
                              placeholder="Brief description of the video content..."><?= clean($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Published -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_published" id="is_published" value="1" checked
                           class="w-4 h-4 accent-primary">
                    <label for="is_published" class="text-sm font-medium text-gray-700">
                        Publish immediately
                    </label>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-3 rounded-lg transition">
                        <i class="fab fa-youtube mr-2"></i>Add Video
                    </button>
                    <a href="videos.php"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-8 py-3 rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function getYoutubeId(url) {
    const patterns = [
        /youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/,
        /youtu\.be\/([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
    ];
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) return match[1];
    }
    return null;
}

function previewVideo(url) {
    const id      = getYoutubeId(url);
    const preview = document.getElementById('videoPreview');
    const frame   = document.getElementById('previewFrame');

    if (id) {
        frame.src = `https://www.youtube.com/embed/${id}`;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
        frame.src = '';
    }
}
</script>

</body>
</html>