<?php
require_once 'auth.php';
require_once '../includes/functions.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM visitor_emails WHERE id = ?")->execute([$id]);
    header('Location: subscribers.php?deleted=1');
    exit;
}

// Handle export CSV
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    if ($type === 'subscribers') {
        $rows = $pdo->query("SELECT email, name, created_at FROM visitor_emails 
                             WHERE is_subscriber = 1 ORDER BY created_at DESC")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT email, name, created_at FROM visitor_emails 
                             ORDER BY created_at DESC")->fetchAll();
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="emails_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Name', 'Date']);
    foreach ($rows as $row) {
        fputcsv($out, [$row['email'], $row['name'], $row['created_at']]);
    }
    fclose($out);
    exit;
}

// Filters
$search  = trim($_GET['search'] ?? '');
$type    = trim($_GET['type'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = ["1=1"];
$params = [];

if ($search) {
    $where[]  = "(email LIKE ? OR name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($type === 'subscribers') {
    $where[] = "is_subscriber = 1";
} elseif ($type === 'downloaders') {
    $where[] = "is_subscriber = 0";
}

$whereSQL = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM visitor_emails WHERE $whereSQL");
$countStmt->execute($params);
$totalEmails = $countStmt->fetchColumn();
$totalPages  = ceil($totalEmails / $perPage);

$stmt = $pdo->prepare("SELECT * FROM visitor_emails WHERE $whereSQL
                       ORDER BY created_at DESC
                       LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$emails = $stmt->fetchAll();

// Summary stats
$totalAll  = $pdo->query("SELECT COUNT(*) FROM visitor_emails")->fetchColumn();
$totalSubs = $pdo->query("SELECT COUNT(*) FROM visitor_emails WHERE is_subscriber = 1")->fetchColumn();
$totalDL   = $pdo->query("SELECT COUNT(*) FROM visitor_emails WHERE is_subscriber = 0")->fetchColumn();
$thisMonth = $pdo->query("SELECT COUNT(*) FROM visitor_emails 
                          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribers - Admin Panel</title>
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
        <i class="fas fa-mosque text-gold text-xl"></i>
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
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-envelope w-5"></i>Subscribers
            </a>
            <a href="downloads.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
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
            <h1 class="text-2xl font-bold text-gray-800">Subscribers & Emails</h1>
            <div class="flex gap-2">
                <a href="notify.php"
                   class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-bell mr-2"></i>Notify All
                </a>
                <div class="relative group">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <div class="hidden group-hover:block absolute right-0 top-full mt-1 bg-white rounded-lg shadow-lg border border-gray-100 z-10 w-48">
                        <a href="subscribers.php?export=subscribers"
                           class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 hover:text-primary">
                            <i class="fas fa-bell mr-2 text-purple-500"></i>Subscribers only
                        </a>
                        <a href="subscribers.php?export=all"
                           class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 hover:text-primary">
                            <i class="fas fa-users mr-2 text-primary"></i>All emails
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-primary">
                <p class="text-xs text-gray-500">Total Emails</p>
                <p class="text-2xl font-bold text-primary"><?= number_format($totalAll) ?></p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">Subscribers</p>
                <p class="text-2xl font-bold text-purple-600"><?= number_format($totalSubs) ?></p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-orange-500">
                <p class="text-xs text-gray-500">Downloaders Only</p>
                <p class="text-2xl font-bold text-orange-500"><?= number_format($totalDL) ?></p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
                <p class="text-xs text-gray-500">This Month</p>
                <p class="text-2xl font-bold text-teal-600"><?= number_format($thisMonth) ?></p>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i>Email removed successfully.
        </div>
        <?php endif; ?>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input type="text" name="search" value="<?= clean($search) ?>"
                               placeholder="Search by email or name..."
                               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                    </div>
                </div>

                <div class="min-w-40">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="">All Types</option>
                        <option value="subscribers"  <?= $type === 'subscribers'  ? 'selected' : '' ?>>Subscribers</option>
                        <option value="downloaders"  <?= $type === 'downloaders'  ? 'selected' : '' ?>>Downloaders Only</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <?php if ($search || $type): ?>
                    <a href="subscribers.php"
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
                Showing <strong><?= number_format(min($perPage, $totalEmails)) ?></strong>
                of <strong><?= number_format($totalEmails) ?></strong> emails
            </p>
            <p class="text-sm text-gray-400">Page <?= $page ?> of <?= max(1, $totalPages) ?></p>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <?php if (empty($emails)): ?>
            <div class="text-center py-16 text-gray-400">
                <i class="fas fa-envelope text-5xl mb-4"></i>
                <p class="text-lg">No emails found.</p>
                <?php if ($search || $type): ?>
                <a href="subscribers.php" class="mt-3 inline-block text-primary hover:underline text-sm">
                    Clear filters
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Name</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Type</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Joined</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($emails as $email): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                <?= clean($email['email']) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                <?= clean($email['name'] ?: '—') ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($email['is_subscriber']): ?>
                                <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded text-xs font-medium">
                                    <i class="fas fa-bell mr-1"></i>Subscriber
                                </span>
                                <?php else: ?>
                                <span class="bg-orange-50 text-orange-600 px-2 py-1 rounded text-xs font-medium">
                                    <i class="fas fa-download mr-1"></i>Downloader
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-400 hidden md:table-cell text-xs">
                                <?= formatDate($email['created_at']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <a href="subscribers.php?delete=<?= $email['id'] ?>&<?= http_build_query(array_diff_key($_GET, ['delete'=>''])) ?>"
                                   onclick="return confirm('Remove <?= clean(addslashes($email['email'])) ?>?')"
                                   class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    <i class="fas fa-trash"></i> Remove
                                </a>
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
                    Showing <?= ($offset + 1) ?>–<?= min($offset + $perPage, $totalEmails) ?>
                    of <?= number_format($totalEmails) ?> emails
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
    </main>
</div>

</body>
</html>