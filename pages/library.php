<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Library';

// Filters
$categorySlug = trim($_GET['category'] ?? '');
if ($categorySlug=='video') {
    header('Location: videos.php');
    exit;
}
$typeFilter   = trim($_GET['type'] ?? '');
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 12;
$offset       = ($page - 1) * $perPage;

// Build query
$where    = ["b.is_published = 1"];
$params   = [];

if ($categorySlug) {
    $where[]  = "c.slug = ?";
    $params[] = $categorySlug;
}
if ($typeFilter) {
    $where[]  = "b.type = ?";
    $params[] = $typeFilter;
}
if ($search) {
    $where[]  = "(b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
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
$stmt = $pdo->prepare("SELECT b.*, c.name as category_name, c.slug as category_slug 
                        FROM books b 
                        LEFT JOIN categories c ON b.category_id = c.id 
                        WHERE $whereSQL 
                        ORDER BY b.created_at DESC 
                        LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$books = $stmt->fetchAll();

$categories   = getCategories($pdo);
$activeCategory = null;
if ($categorySlug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $activeCategory = $cat;
            break;
        }
    }
}

require_once '../includes/seo.php';
$seoOptions = [
    'title'       => $activeCategory ? clean($activeCategory['name']) . ' - Islamic Library' : 'Islamic Library',
    'description' => $activeCategory
        ? 'Browse ' . clean($activeCategory['name']) . ' books in Myanmar Muslims Islamic Library. Free download available.'
        : 'Browse our collection of free Islamic ebooks, duas, research papers and more for Myanmar Muslims.',
    'keywords'    => 'islamic ebooks myanmar, free download, ' . ($activeCategory ? clean($activeCategory['name']) : 'quran hadith fiqh'),
    'url'         => SITE_URL . '/pages/library.php' . ($categorySlug ? '?category=' . $categorySlug : ''),
];

include '../includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">
            <?= $activeCategory ? clean($activeCategory['name']) : 'အစ္စလာမ့် စာကြည့်တိုက်' ?>
        </h1>
        <p class="text-green-100">
            <?= $activeCategory ? clean($activeCategory['description'] ?? '') : 'အစ္စလာမစ်စာအုပ်များ၊ ဒိုအာများ၊ သုတေသနစာတမ်းများနှင့် အခြားစာစောင်များကို ကဏ္ဍအလိုက် ဝင်ရောက်ဖတ်ရှုလေ့လာနိုင်ပါသည်။' ?>
        </p>
    </div>
</div>

<!-- Search Bar -->
<div class="bg-white border-b border-gray-200 py-4 px-4">
    <div class="max-w-7xl mx-auto">
        <form method="GET" class="flex gap-3">
             <?php csrfField(); ?>
            <?php if ($categorySlug): ?>
                <input type="hidden" name="category" value="<?= clean($categorySlug) ?>">
            <?php endif; ?>
            <div class="relative flex-1 max-w-xl">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="search" value="<?= clean($search) ?>"
                       placeholder="Search books, authors..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            </div>
            <select name="type"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-primary text-sm"
                    onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="ebook"     <?= $typeFilter === 'ebook'     ? 'selected' : '' ?>>Ebooks</option>
                <option value="dua"       <?= $typeFilter === 'dua'       ? 'selected' : '' ?>>Duas</option>
                <option value="wallpaper" <?= $typeFilter === 'wallpaper' ? 'selected' : '' ?>>Wallpapers</option>
                <option value="research"  <?= $typeFilter === 'research'  ? 'selected' : '' ?>>Research Papers</option>
                <option value="other"     <?= $typeFilter === 'other'     ? 'selected' : '' ?>>Other</option>
            </select>
            <button type="submit"
                    class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                Search
            </button>
            <?php if ($search || $typeFilter): ?>
            <a href="library.php<?= $categorySlug ? '?category=' . clean($categorySlug) : '' ?>"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm transition">
                Clear
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Sidebar Categories -->
        <aside class="lg:w-56 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wide">Categories</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="library.php"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                                  <?= !$categorySlug ? 'bg-primary text-white' : 'text-gray-700 hover:bg-green-50 hover:text-primary' ?>">
                            <i class="fas fa-th-large w-4"></i>All Books
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="library.php?category=<?= clean($cat['slug']) ?>"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
                                  <?= $categorySlug === $cat['slug'] ? 'bg-primary text-white' : 'text-gray-700 hover:bg-green-50 hover:text-primary' ?>">
                            <i class="fas fa-<?= clean($cat['icon']) ?> w-4"></i>
                            <?= clean($cat['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Top Downloads Widget -->
            <?php
            $topDownloads = $pdo->query("SELECT b.id, b.title, b.cover_image, b.downloads, b.file_path,
                                        c.name as category_name
                                        FROM books b
                                        LEFT JOIN categories c ON b.category_id = c.id
                                        WHERE b.is_published = 1 AND b.downloads > 0
                                        ORDER BY b.downloads DESC
                                        LIMIT 5")->fetchAll();
            ?>
            <?php if (!empty($topDownloads)): ?>
            <div class="rounded-xl shadow-sm p-4 mt-4"
                style="background: var(--color-card); border: 1px solid var(--color-border)">
                <h3 class="font-bold text-sm uppercase tracking-wide mb-4 flex items-center gap-2"
                    style="color: var(--color-text)">
                    <i class="fas fa-fire" style="color: var(--color-gold)"></i>
                    Top Downloads
                </h3>
                <div class="space-y-3">
                    <?php foreach ($topDownloads as $i => $book): ?>
                    <a href="book.php?ref=<?= encodeId($book['id']) ?>"
                    class="flex items-center gap-3 group"
                    style="text-decoration: none;">

                        <!-- Rank -->
                        <span class="text-sm font-bold flex-shrink-0 w-5 text-center"
                            style="color: <?= $i === 0 ? '#f59e0b' : ($i === 1 ? '#9ca3af' : ($i === 2 ? '#b45309' : 'var(--color-text-muted)')) ?>">
                            <?= $i + 1 ?>
                        </span>

                        <!-- Cover -->
                        <div class="w-9 h-11 rounded flex-shrink-0 overflow-hidden flex items-center justify-center"
                            style="background: var(--color-primary)">
                            <?php if ($book['cover_image']): ?>
                            <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                                class="w-full h-full object-cover">
                            <?php else: ?>
                            <i class="fas fa-book text-white text-xs"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium truncate transition"
                            style="color: var(--color-text);"
                            onmouseover="this.style.color='var(--color-primary)'"
                            onmouseout="this.style.color='var(--color-text)'">
                                <?= clean($book['title']) ?>
                            </p>
                            <p class="text-xs mt-0.5" style="color: var(--color-text-muted)">
                                <i class="fas fa-download mr-1"></i>
                                <?= number_format($book['downloads']) ?>
                            </p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>

        <!-- Books Grid -->
        <div class="flex-1">

            <!-- Results count -->
            <div class="flex justify-between items-center mb-5">
                <p class="text-sm text-gray-500">
                    <?php if ($search): ?>
                        Found <strong><?= $totalBooks ?></strong> results for "<strong><?= clean($search) ?></strong>"
                    <?php else: ?>
                        Showing <strong><?= count($books) ?></strong> of <strong><?= $totalBooks ?></strong> books
                    <?php endif; ?>
                </p>
            </div>

            <?php if (empty($books)): ?>
            <div class="text-center py-20 text-gray-400">
                <i class="fas fa-book-open text-6xl mb-4"></i>
                <p class="text-xl font-medium">No books found</p>
                <p class="text-sm mt-2">Try a different search or category</p>
                <a href="library.php" class="mt-4 inline-block text-primary hover:underline">Browse all books</a>
            </div>
            <?php else: ?>

            <!-- Books Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                <?php foreach ($books as $book): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 card-hover group">

                    <!-- Cover -->
                    <a href="book.php?ref=<?= encodeId($book['id']) ?>">
                        <div class="bg-primary h-44 flex items-center justify-center overflow-hidden">
                            <?php if ($book['cover_image']): ?>
                                <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                                     alt="<?= clean($book['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <?php else: ?>
                                <i class="fas fa-book text-white text-5xl opacity-40"></i>
                            <?php endif; ?>
                        </div>
                    </a>

                    <!-- Info -->
                    <div class="p-4">
                        <span class="text-xs text-gold font-medium">
                            <?= clean($book['category_name'] ?? 'General') ?>
                        </span>
                        <h3 class="font-semibold text-gray-800 text-sm mt-1 line-clamp-2 leading-snug">
                            <a href="book.php?ref=<?= encodeId($book['id']) ?>" class="hover:text-primary">
                                <?= clean($book['title']) ?>
                            </a>
                        </h3>
                        <?php if ($book['author']): ?>
                        <p class="text-xs text-gray-400 mt-1 truncate">
                            <i class="fas fa-user-pen mr-1"></i><?= clean($book['author']) ?>
                        </p>
                        <?php endif; ?>

                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xs text-gray-400">
                                <i class="fas fa-download mr-1"></i><?= $book['downloads'] ?>
                            </span>
                            <a href="book.php?ref=<?= encodeId($book['id']) ?>"
                               class="text-xs bg-primary hover:bg-primary-dark text-white px-3 py-1.5 rounded-lg transition">
                                View
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-10">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                       class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm hover:border-primary hover:text-primary transition">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                       class="px-4 py-2 rounded-lg text-sm transition
                              <?= $i === $page ? 'bg-primary text-white' : 'bg-white border border-gray-200 hover:border-primary hover:text-primary' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                       class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm hover:border-primary hover:text-primary transition">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>