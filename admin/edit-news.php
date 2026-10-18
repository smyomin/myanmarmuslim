<?php
require_once 'auth.php';
require_once '../includes/functions.php';

$id      = (int)($_GET['id'] ?? 0);
$article = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
}

if (!$article) {
    header('Location: news.php');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $title        = trim($_POST['title'] ?? '');
    $content      = trim($_POST['content'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if (!$title || !$content) {
        $error = 'Title and content are required.';
    } else {
        $coverName = $article['cover_image'];

        // Handle new cover image
        if (!empty($_FILES['cover_image']['name'])) {
            $coverFile = $_FILES['cover_image'];
            $coverExt  = strtolower(pathinfo($coverFile['name'], PATHINFO_EXTENSION));
            $allowedImg = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($coverExt, $allowedImg) && $coverFile['size'] <= 5 * 1024 * 1024) {
                if ($article['cover_image'] && file_exists(UPLOAD_PATH . 'news/' . $article['cover_image'])) {
                    unlink(UPLOAD_PATH . 'news/' . $article['cover_image']);
                }
                $coverName = time() . '_news_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $coverFile['name']);
                move_uploaded_file($coverFile['tmp_name'], UPLOAD_PATH . 'news/' . $coverName);
            } else {
                $error = 'Cover image must be JPG/PNG/WebP under 5MB.';
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, cover_image=?, is_published=? WHERE id=?");
            $stmt->execute([$title, $content, $coverName, $is_published, $id]);
            $success = 'Article updated successfully!';
            $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                        gold: { DEFAULT: '#c9a84c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">

<nav class="bg-primary text-white px-6 py-4 flex justify-between items-center shadow">
    <div class="flex items-center gap-3">
        <img src="<?= SITE_URL ?>/assets/images/logowhite.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
        <span class="font-bold text-lg">Myanmar Muslims Admin</span>
    </div>
    <div class="flex items-center gap-3">
        <a href="dashboard.php" class="text-green-200 hover:text-white text-sm">
            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
        </a>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
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
            <a href="add-book.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-plus-circle w-5"></i>Add New Book
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-newspaper w-5"></i>Manage News
            </a>
            <a href="add-news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-plus-circle w-5"></i>Add News
            </a>
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-envelope w-5"></i>Subscribers
            </a>
            <a href="notify.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-bell w-5"></i>Send Notification
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tags w-5"></i>Categories
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <div class="max-w-3xl mx-auto">

            <div class="flex items-center gap-3 mb-6">
                <a href="news.php" class="text-gray-400 hover:text-primary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Edit News Article</h1>
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

            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
                 <?php csrfField(); ?>
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="<?= clean($article['title']) ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                           required>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" rows="12" required
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary resize-none"
                              ><?= clean($article['content']) ?></textarea>
                </div>

                <!-- Current Cover -->
                <?php if ($article['cover_image']): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current Cover Image</p>
                    <img src="<?= UPLOAD_URL ?>news/<?= clean($article['cover_image']) ?>"
                         class="h-32 object-cover rounded-lg border border-gray-200">
                </div>
                <?php endif; ?>

                <!-- Replace Cover -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Replace Cover Image <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition">
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                    </div>
                    <div id="coverPreview" class="mt-3 hidden">
                        <img id="previewImg" src="" alt="Preview"
                             class="h-32 object-cover rounded-lg border border-gray-200">
                    </div>
                </div>

                <!-- Published -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_published" id="is_published" value="1"
                           <?= $article['is_published'] ? 'checked' : '' ?>
                           class="w-4 h-4 accent-primary">
                    <label for="is_published" class="text-sm font-medium text-gray-700">
                        Published (visible on website)
                    </label>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="bg-primary hover:bg-primary-dark text-white font-semibold px-8 py-3 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                    <a href="news.php"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-8 py-3 rounded-lg transition">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
document.querySelector('input[name="cover_image"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('coverPreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>