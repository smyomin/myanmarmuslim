<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once '../includes/functions.php';

$categories = getCategories($pdo);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $language    = trim($_POST['language'] ?? 'Myanmar');
    $type        = trim($_POST['type'] ?? 'ebook');
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if (!$title) {
        $error = 'Book title is required.';
    } elseif (empty($_FILES['book_file']['name'])) {
        $error = 'Please upload a PDF file.';
    } else {
        // Handle PDF upload
        $pdfFile    = $_FILES['book_file'];
        $pdfExt     = strtolower(pathinfo($pdfFile['name'], PATHINFO_EXTENSION));
        $allowedExt = ['pdf'];

        if (!in_array($pdfExt, $allowedExt)) {
            $error = 'Only PDF files are allowed.';
        } elseif ($pdfFile['size'] > 50 * 1024 * 1024) {
            $error = 'PDF file size must be under 50MB.';
        } else {
            // Save PDF
            $pdfName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $pdfFile['name']);
            $pdfPath = UPLOAD_PATH . 'books/' . $pdfName;

            // Handle cover image (optional)
            $coverName = '';
            if (!empty($_FILES['cover_image']['name'])) {
                $coverFile = $_FILES['cover_image'];
                $coverExt  = strtolower(pathinfo($coverFile['name'], PATHINFO_EXTENSION));
                $allowedImg = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($coverExt, $allowedImg) && $coverFile['size'] <= 5 * 1024 * 1024) {
                    $coverName = time() . '_cover_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $coverFile['name']);
                    move_uploaded_file($coverFile['tmp_name'], UPLOAD_PATH . 'covers/' . $coverName);
                }
            }

            if (move_uploaded_file($pdfFile['tmp_name'], $pdfPath)) {
                $stmt = $pdo->prepare("INSERT INTO books 
                    (title, description, author, category_id, language, type, file_path, cover_image, is_published) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title, $description, $author,
                    $category_id ?: null, $language, $type,
                    $pdfName, $coverName, $is_published
                ]);
                $success = 'Book uploaded successfully!';
            } else {
                $error = 'Failed to upload PDF. Check folder permissions.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book - Admin Panel</title>
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

<!-- Navbar -->
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
    <!-- Sidebar -->
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="add-book.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-plus-circle w-5"></i>Add New Book
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
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

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <div class="max-w-3xl mx-auto">

            <div class="flex items-center gap-3 mb-6">
                <a href="books.php" class="text-gray-400 hover:text-primary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Add New Book</h1>
            </div>

            <!-- Success -->
            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
                <a href="books.php" class="ml-3 underline font-medium">View all books</a>
            </div>
            <?php endif; ?>

            <!-- Error -->
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            <?php csrfField(); ?>
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Book Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                           placeholder="Enter book title" required>
                </div>

                <!-- Author & Language -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                        <input type="text" name="author"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                               placeholder="Author name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                        <select name="language"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="Myanmar">Myanmar</option>
                            <option value="English">English</option>
                            <option value="Arabic">Arabic</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Category & Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= clean($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="ebook">Ebook</option>
                            <option value="dua">Dua / Supplication</option>
                            <option value="wallpaper">Wallpaper</option>
                            <option value="research">Research Paper</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                              placeholder="Brief description of the book"></textarea>
                </div>

                <!-- PDF Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        PDF File <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition">
                        <i class="fas fa-file-pdf text-red-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-500 mb-2">Click to select PDF file (max 50MB)</p>
                        <input type="file" name="book_file" accept=".pdf" required
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white hover:file:bg-primary-dark">
                    </div>
                </div>

                <!-- Cover Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Cover Image <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition">
                        <i class="fas fa-image text-blue-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-500 mb-2">JPG, PNG or WebP (max 5MB)</p>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                    </div>
                    <!-- Preview -->
                    <div id="coverPreview" class="mt-3 hidden">
                        <img id="previewImg" src="" alt="Cover preview"
                             class="h-40 object-cover rounded-lg border border-gray-200">
                    </div>
                </div>

                <!-- Published -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_published" id="is_published" value="1" checked
                           class="w-4 h-4 accent-primary">
                    <label for="is_published" class="text-sm font-medium text-gray-700">
                        Publish immediately (visible on website)
                    </label>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="bg-primary hover:bg-primary-dark text-white font-semibold px-8 py-3 rounded-lg transition">
                        <i class="fas fa-upload mr-2"></i>Upload Book
                    </button>
                    <a href="books.php"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-8 py-3 rounded-lg transition">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
// Cover image preview
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