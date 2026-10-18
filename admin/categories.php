<?php
require_once 'auth.php';
require_once '../includes/functions.php';

$success = '';
$error   = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if category has books
    $count = $pdo->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
    $count->execute([$id]);
    if ($count->fetchColumn() > 0) {
        $error = 'Cannot delete category — it has books assigned to it.';
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        header('Location: categories.php?deleted=1');
        exit;
    }
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'folder');

    if (!$name) {
        $error = 'Category name is required.';
    } else {
        $slug = makeSlug($name);
        $checkSlug = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $checkSlug->execute([$slug]);
        if ($checkSlug->fetch()) {
            $slug = $slug . '-' . time();
        }
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $desc, $icon]);
        $success = 'Category added successfully!';
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'folder');

    if (!$name) {
        $error = 'Category name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, description=?, icon=? WHERE id=?");
        $stmt->execute([$name, $desc, $icon, $id]);
        $success = 'Category updated successfully!';
    }
}

$categories = $pdo->query("SELECT c.*, COUNT(b.id) as book_count 
                            FROM categories c 
                            LEFT JOIN books b ON c.id = b.category_id 
                            GROUP BY c.id 
                            ORDER BY c.name ASC")->fetchAll();

// Common Font Awesome icons for categories
$iconOptions = [
    'book' => 'Book', 'book-open' => 'Book Open', 'hands' => 'Hands',
    'scale-balanced' => 'Scale', 'star' => 'Star', 'user' => 'User',
    'image' => 'Image', 'file-alt' => 'File', 'folder' => 'Folder',
    'mosque' => 'Mosque', 'heart' => 'Heart', 'globe' => 'Globe',
    'graduation-cap' => 'Education', 'scroll' => 'Scroll', 'feather' => 'Feather', 'calendar' => 'Calendar','clock' => 'Clock',
    'cogs' => 'Settings', 'users' => 'Users', 'chart-bar' => 'Statistics', 'code' => 'Code', 'music' => 'Music', 'camera' => 'Camera',
    'link' => 'Link', 'lightbulb' => 'Idea', 'pencil-alt' => 'Edit', 'shield-alt' => 'Security', 'thumbs-up' => 'Like', 'tags' => 'Tags', 'gift' => 'Gift', 'coffee' => 'Coffee'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Panel</title>
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
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-tags w-5"></i>Categories
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Categories</h1>

        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i><?= clean($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i>Category deleted.
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Add Category Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-bold text-gray-800 mb-4">Add New Category</h2>
                    <form method="POST" class="space-y-4">
                         <?php csrfField(); ?>
                        <input type="hidden" name="action" value="add">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm"
                                   placeholder="Category name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm resize-none"
                                      placeholder="Optional description"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <select name="icon"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm">
                                <?php foreach ($iconOptions as $val => $label): ?>
                                <option value="<?= $val ?>">
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit"
                                class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-2.5 rounded-lg transition text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Category
                        </button>
                    </form>
                </div>
            </div>

            <!-- Categories List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Books</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="categoriesTable">
                            <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-gray-50" id="row-<?= $cat['id'] ?>">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-<?= clean($cat['icon']) ?> text-primary text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800" id="name-<?= $cat['id'] ?>">
                                                <?= clean($cat['name']) ?>
                                            </p>
                                            <p class="text-xs text-gray-400"><?= clean($cat['slug']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                    <?= $cat['book_count'] ?> books
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button onclick="editCategory(<?= $cat['id'] ?>, '<?= clean($cat['name']) ?>', '<?= clean($cat['description'] ?? '') ?>', '<?= clean($cat['icon']) ?>')"
                                                class="text-blue-500 hover:text-blue-700 text-xs font-medium">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if ($cat['book_count'] == 0): ?>
                                        <a href="categories.php?delete=<?= $cat['id'] ?>"
                                           onclick="return confirm('Delete this category?')"
                                           class="text-red-500 hover:text-red-700 text-xs font-medium">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-gray-800 mb-4">Edit Category</h3>
        <form method="POST" class="space-y-4">
             <?php csrfField(); ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" id="editName" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="editDesc" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                <select name="icon" id="editIcon"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm">
                    <?php foreach ($iconOptions as $val => $label): ?>
                    <option value="<?= $val ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary-dark text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <button type="button" onclick="closeModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-lg transition text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, desc, icon) {
    document.getElementById('editId').value   = id;
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editIcon').value = icon;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal on backdrop click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>