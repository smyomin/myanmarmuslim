<?php
require_once 'auth.php';
require_once '../includes/functions.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id       = (int)$_GET['delete'];
    $book     = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $book->execute([$id]);
    $bookData = $book->fetch();

    if ($bookData) {
        if ($bookData['file_path'] && file_exists(UPLOAD_PATH . 'books/' . $bookData['file_path'])) {
            unlink(UPLOAD_PATH . 'books/' . $bookData['file_path']);
        }
        if ($bookData['cover_image'] && file_exists(UPLOAD_PATH . 'covers/' . $bookData['cover_image'])) {
            unlink(UPLOAD_PATH . 'covers/' . $bookData['cover_image']);
        }
        $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
    }
    header('Location: books.php?deleted=1');
    exit;
}

// Handle toggle publish
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $cur = $pdo->prepare("SELECT is_published FROM books WHERE id = ?");
    $cur->execute([$id]);
    $row = $cur->fetch();
    $pdo->prepare("UPDATE books SET is_published = ? WHERE id = ?")
        ->execute([$row['is_published'] ? 0 : 1, $id]);
    header('Location: books.php?' . http_build_query(array_diff_key($_GET, ['toggle' => ''])));
    exit;
}

// Filters
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$type     = trim($_GET['type'] ?? '');
$status   = trim($_GET['status'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 15;
$offset   = ($page - 1) * $perPage;

$where  = ["1=1"];
$params = [];

if ($search) {
    $where[]  = "(b.title LIKE ? OR b.author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $where[]  = "b.category_id = ?";
    $params[] = $category;
}
if ($type) {
    $where[]  = "b.type = ?";
    $params[] = $type;
}
if ($status !== '') {
    $where[]  = "b.is_published = ?";
    $params[] = (int)$status;
}

$whereSQL = implode(' AND ', $where);

// Total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM books b 
                             LEFT JOIN categories c ON b.category_id = c.id 
                             WHERE $whereSQL");
$countStmt->execute($params);
$totalBooks = $countStmt->fetchColumn();
$totalPages = ceil($totalBooks / $perPage);

// Fetch books
$stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                        FROM books b 
                        LEFT JOIN categories c ON b.category_id = c.id 
                        WHERE $whereSQL
                        ORDER BY b.created_at DESC
                        LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$books = $stmt->fetchAll();

$categories = getCategories($pdo);

// Summary stats
//$totalAll        = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
//$totalPublished  = $pdo->query("SELECT COUNT(*) FROM books WHERE is_published = 1")->fetchColumn();
//$totalDrafts     = $pdo->query("SELECT COUNT(*) FROM books WHERE is_published = 0")->fetchColumn();
//$totalDownloads  = $pdo->query("SELECT SUM(downloads) FROM books")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin Panel</title>
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
    <div class="flex items-center gap-3">
        <a href="dashboard.php" class="text-green-200 hover:text-white text-sm">
            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
        </a>
        <a href="logout.php"
           class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
    </div>
</nav>

<div class="flex">
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block flex-shrink-0">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
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
            <a href="videos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fab fa-youtube w-5"></i>Manage Videos
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
            <a href="prayer-times.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-mosque w-5"></i>Prayer Times
            </a>
            <a href="events.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-calendar w-5"></i>Islamic Events
            </a>
            <a href="announcements.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-bullhorn w-5"></i>Announcements
            </a>
            <a href="analytics.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-chart-bar w-5"></i>Analytics
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-cog w-5"></i>Settings
            </a>
            <a href="admins.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-user-shield w-5"></i>Manage Admins
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6 overflow-x-hidden">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Books</h1>
            <a href="add-book.php"
               class="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-2"></i>Add New Book
            </a>
        </div>
        <!-- Alerts -->
        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i>Book deleted successfully.
        </div>
        <?php endif; ?>

        <!-- Search & Filters -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <!-- Search -->
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input type="text" name="search" value="<?= clean($search) ?>"
                               placeholder="Title or author..."
                               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                    </div>
                </div>

                <!-- Category -->
                <div class="min-w-36">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <select name="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                            <?= clean($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type -->
                <div class="min-w-32">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="">All Types</option>
                        <option value="ebook"     <?= $type === 'ebook'     ? 'selected' : '' ?>>Ebook</option>
                        <option value="dua"       <?= $type === 'dua'       ? 'selected' : '' ?>>Dua</option>
                        <option value="wallpaper" <?= $type === 'wallpaper' ? 'selected' : '' ?>>Wallpaper</option>
                        <option value="research"  <?= $type === 'research'  ? 'selected' : '' ?>>Research</option>
                        <option value="other"     <?= $type === 'other'     ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="min-w-32">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="">All Status</option>
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Published</option>
                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit"
                            class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <?php if ($search || $category || $type || $status !== ''): ?>
                    <a href="books.php"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="flex justify-between items-center mb-3">
            <p class="text-sm text-gray-500">
                <?php if ($search || $category || $type || $status !== ''): ?>
                    Found <strong><?= number_format($totalBooks) ?></strong> books
                    <?= $search ? "matching \"<strong>" . clean($search) . "</strong>\"" : '' ?>
                <?php else: ?>
                    Showing <strong><?= number_format(min($perPage, $totalBooks)) ?></strong>
                    of <strong><?= number_format($totalBooks) ?></strong> books
                <?php endif; ?>
            </p>
            <p class="text-sm text-gray-400">
                Page <?= $page ?> of <?= max(1, $totalPages) ?>
            </p>
        </div>

        <!-- Books Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <?php if (empty($books)): ?>
            <div class="text-center py-16 text-gray-400">
                <i class="fas fa-book text-5xl mb-4"></i>
                <p class="text-lg">No books found.</p>
                <?php if ($search || $category || $type): ?>
                <p class="text-sm mt-2">Try clearing the filters.</p>
                <a href="books.php" class="mt-3 inline-block text-primary hover:underline text-sm">
                    Clear all filters
                </a>
                <?php else: ?>
                <a href="add-book.php" class="mt-3 inline-block text-primary hover:underline">
                    Upload your first book
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Book</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">Category</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Type</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">
                                <i class="fas fa-download mr-1"></i>DL
                            </th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">Date</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($books as $book): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-primary flex items-center justify-center">
                                        <?php if ($book['cover_image']): ?>
                                            <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-book text-white text-sm"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-800 truncate max-w-xs">
                                            <?= clean($book['title']) ?>
                                        </p>
                                        <p class="text-xs text-gray-400 truncate">
                                            <?= clean($book['author'] ?? 'No author') ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell text-xs">
                                <?= clean($book['category_name'] ?? 'General') ?>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs capitalize">
                                    <?= clean($book['type']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell text-xs font-medium">
                                <?= number_format($book['downloads']) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-400 hidden lg:table-cell text-xs">
                                <?= formatDate($book['created_at']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($book['is_published']): ?>
                                <span class="bg-green-50 text-green-700 px-2 py-1 rounded text-xs font-medium">
                                    Published
                                </span>
                                <?php else: ?>
                                <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs font-medium">
                                    Draft
                                </span>
                                <?php endif; ?>
                            </td>
                           <td class="px-4 py-3">
                            <div class="flex items-center gap-2 flex-wrap">

                             <!-- Copy Short URL -->
                                <button
                                    onclick="copyShortUrl(<?= $book['id'] ?>, this)"
                                    class="text-xs font-medium px-2 py-1 rounded-lg transition flex items-center gap-1"
                                    style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;"
                                    title="Copy short URL">
                                    <i class="fas fa-link"></i>
                                    <span>Copy URL</span>
                                </button>

                            <!-- View -->
                                <a href="<?= SITE_URL ?>/pages/book.php?ref=<?= encodeId($book['id']) ?>"
                                target="_blank"
                                class="text-green-500 hover:text-green-700 text-xs font-medium"
                                title="View on site">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Edit -->
                                <a href="edit-book.php?id=<?= $book['id'] ?>"
                                class="text-blue-500 hover:text-blue-700 text-xs font-medium"
                                title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                               

                                <!-- Toggle -->
                                <a href="books.php?toggle=<?= $book['id'] ?>&<?= http_build_query(array_diff_key($_GET, ['toggle'=>'','delete'=>''])) ?>"
                                class="text-yellow-500 hover:text-yellow-700 text-xs font-medium">
                                    <i class="fas fa-<?= $book['is_published'] ? 'eye-slash' : 'eye' ?>"></i>
                                    <?= $book['is_published'] ? 'Hide' : 'Show' ?>
                                </a>

                                <!-- Delete -->
                                <a href="books.php?delete=<?= $book['id'] ?>"
                                onclick="return confirm('Delete \'<?= clean(addslashes($book['title'])) ?>\'? This cannot be undone.')"
                                class="text-red-500 hover:text-red-700 text-xs font-medium"
                                title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>

                            </div>
                        </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="px-4 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-sm text-gray-500">
                    Showing <?= ($offset + 1) ?>–<?= min($offset + $perPage, $totalBooks) ?>
                    of <?= number_format($totalBooks) ?> books
                </p>
                <div class="flex gap-1">
                    <!-- First -->
                    <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                       class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                       class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php
                    // Show limited page numbers
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                       class="px-3 py-1.5 rounded-lg text-xs transition
                              <?= $i === $page
                                  ? 'bg-primary text-white'
                                  : 'bg-gray-100 hover:bg-gray-200 text-gray-600' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                       class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                       class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </main>
</div>


<script>
async function copyShortUrl(bookId, btn) {
    const icon = btn.querySelector('i');
    const text = btn.querySelector('span');

    // Generate short URL using own domain — instant, no API
    const shortUrl = '<?= SITE_URL ?>/b/' + bookId;

    try {
        await navigator.clipboard.writeText(shortUrl);

        // Success state
        icon.className       = 'fas fa-check';
        text.textContent     = 'Copied!';
        btn.style.background = '#dcfce7';
        btn.style.borderColor= '#86efac';
        btn.title            = shortUrl;

    } catch (e) {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = shortUrl;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);

        icon.className   = 'fas fa-check';
        text.textContent = 'Copied!';
    }

    // Reset after 3 seconds
    setTimeout(function() {
        icon.className        = 'fas fa-link';
        text.textContent      = 'Copy URL';
        btn.style.background  = '#f0fdf4';
        btn.style.color       = '#166534';
        btn.style.borderColor = '#bbf7d0';
        btn.title             = 'Copy short URL';
    }, 3000);
}
</script>

</body>
</html>