<?php
require_once 'auth.php';
require_once '../includes/functions.php';

// Handle export CSV
if (isset($_GET['export'])) {
    $rows = $pdo->query("SELECT b.title, dl.email, dl.downloaded_at
                         FROM download_logs dl
                         LEFT JOIN books b ON dl.book_id = b.id
                         ORDER BY dl.downloaded_at DESC")->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="downloads_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Book Title', 'Email', 'Downloaded At']);
    foreach ($rows as $row) {
        fputcsv($out, [$row['title'], $row['email'], $row['downloaded_at']]);
    }
    fclose($out);
    exit;
}

// Filters
$search  = trim($_GET['search'] ?? '');
$bookId  = (int)($_GET['book_id'] ?? 0);
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = ["1=1"];
$params = [];

if ($search) {
    $where[]  = "(dl.email LIKE ? OR b.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($bookId) {
    $where[]  = "dl.book_id = ?";
    $params[] = $bookId;
}

$whereSQL = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM download_logs dl
                             LEFT JOIN books b ON dl.book_id = b.id
                             WHERE $whereSQL");
$countStmt->execute($params);
$totalLogs  = $countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

$stmt = $pdo->prepare("SELECT dl.*, b.title as book_title, b.cover_image
                        FROM download_logs dl
                        LEFT JOIN books b ON dl.book_id = b.id
                        WHERE $whereSQL
                        ORDER BY dl.downloaded_at DESC
                        LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Summary stats
$totalDownloads = $pdo->query("SELECT COUNT(*) FROM download_logs")->fetchColumn();
$todayDownloads = $pdo->query("SELECT COUNT(*) FROM download_logs 
                                WHERE DATE(downloaded_at) = CURDATE()")->fetchColumn();
$monthDownloads = $pdo->query("SELECT COUNT(*) FROM download_logs 
                                WHERE downloaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$uniqueEmails   = $pdo->query("SELECT COUNT(DISTINCT email) FROM download_logs")->fetchColumn();

// Top downloaded books for filter dropdown
$allBooks = $pdo->query("SELECT id, title, downloads FROM books 
                          WHERE is_published = 1 
                          ORDER BY downloads DESC")->fetchAll();

// Top 5 most downloaded books
$topBooks = $pdo->query("SELECT b.title, b.cover_image, b.downloads,
                          COUNT(dl.id) as log_count
                          FROM download_logs dl
                          LEFT JOIN books b ON dl.book_id = b.id
                          GROUP BY dl.book_id
                          ORDER BY log_count DESC
                          LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Logs - Admin Panel</title>
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
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-newspaper w-5"></i>Manage News
            </a>
            <a href="videos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fab fa-youtube w-5"></i>Manage Videos
            </a>
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-envelope w-5"></i>Subscribers
            </a>
            <a href="downloads.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-download w-5"></i>Download Logs
            </a>
            <a href="notify.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-bell w-5"></i>Send Notification
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tags w-5"></i>Categories
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

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-download text-primary mr-2"></i>Download Logs
            </h1>
            <a href="downloads.php?export=1"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </a>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-primary">
                <p class="text-xs text-gray-500">Total Downloads</p>
                <p class="text-2xl font-bold text-primary"><?= number_format($totalDownloads) ?></p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
                <p class="text-xs text-gray-500">Today</p>
                <p class="text-2xl font-bold text-teal-600"><?= number_format($todayDownloads) ?></p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">This Month</p>
                <p class="text-2xl font-bold text-blue-600"><?= number_format($monthDownloads) ?></p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-orange-500">
                <p class="text-xs text-gray-500">Unique Emails</p>
                <p class="text-2xl font-bold text-orange-500"><?= number_format($uniqueEmails) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">

            <!-- Top Downloaded Books -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">
                    🏆 Top Downloads
                </h2>
                <?php if (empty($topBooks)): ?>
                <p class="text-gray-400 text-sm">No data yet.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topBooks as $i => $book): ?>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-300 w-4 flex-shrink-0">
                            <?= $i + 1 ?>
                        </span>
                        <div class="w-8 h-8 rounded bg-primary flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <?php if ($book['cover_image']): ?>
                            <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                                 class="w-full h-full object-cover">
                            <?php else: ?>
                            <i class="fas fa-book text-white text-xs"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 truncate font-medium">
                                <?= clean($book['title']) ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?= number_format($book['log_count']) ?> downloads
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Search & Filter + Table -->
            <div class="lg:col-span-3">

                <!-- Search & Filter -->
                <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                    <form method="GET" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-48">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                                <input type="text" name="search" value="<?= clean($search) ?>"
                                       placeholder="Search by email or book title..."
                                       class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                            </div>
                        </div>

                        <div class="min-w-48">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Filter by Book</label>
                            <select name="book_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                                <option value="">All Books</option>
                                <?php foreach ($allBooks as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $bookId === $b['id'] ? 'selected' : '' ?>>
                                    <?= clean(substr($b['title'], 0, 35)) ?>
                                    (<?= $b['downloads'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <?php if ($search || $bookId): ?>
                            <a href="downloads.php"
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
                        Showing <strong><?= number_format(min($perPage, $totalLogs)) ?></strong>
                        of <strong><?= number_format($totalLogs) ?></strong> logs
                    </p>
                    <p class="text-sm text-gray-400">
                        Page <?= $page ?> of <?= max(1, $totalPages) ?>
                    </p>
                </div>

                <!-- Logs Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <?php if (empty($logs)): ?>
                    <div class="text-center py-12 text-gray-400">
                        <i class="fas fa-download text-4xl mb-3"></i>
                        <p>No download logs found.</p>
                        <?php if ($search || $bookId): ?>
                        <a href="downloads.php" class="mt-2 inline-block text-primary hover:underline text-sm">
                            Clear filters
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Book</th>
                                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded bg-primary flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                <?php if ($log['cover_image']): ?>
                                                <img src="<?= UPLOAD_URL ?>covers/<?= clean($log['cover_image']) ?>"
                                                     class="w-full h-full object-cover">
                                                <?php else: ?>
                                                <i class="fas fa-book text-white text-xs"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-xs font-medium text-gray-700 truncate max-w-xs">
                                                <?= clean($log['book_title'] ?? 'Deleted Book') ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-gray-700">
                                            <?= clean($log['email']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 hidden md:table-cell text-xs">
                                        <?= date('d M Y, H:i', strtotime($log['downloaded_at'])) ?>
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
                            Showing <?= ($offset + 1) ?>–<?= min($offset + $perPage, $totalLogs) ?>
                            of <?= number_format($totalLogs) ?> logs
                        </p>
                        <div class="flex gap-1">
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
                            $start = max(1, $page - 2);
                            $end   = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                               class="px-3 py-1.5 rounded-lg text-xs transition
                                      <?= $i === $page ? 'bg-primary text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-600' ?>">
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
            </div>
        </div>
    </main>
</div>

</body>
</html>