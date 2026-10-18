<?php
require_once 'auth.php';
require_once '../includes/functions.php';

// Core stats
$totalBooks     = $pdo->query("SELECT COUNT(*) FROM books WHERE is_published = 1")->fetchColumn();
$totalNews      = $pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 1")->fetchColumn();
$totalVideos    = $pdo->query("SELECT COUNT(*) FROM videos WHERE is_published = 1")->fetchColumn();
$totalSubs      = $pdo->query("SELECT COUNT(*) FROM visitor_emails WHERE is_subscriber = 1")->fetchColumn();
$totalEmails    = $pdo->query("SELECT COUNT(*) FROM visitor_emails")->fetchColumn();
$totalDownloads = $pdo->query("SELECT SUM(downloads) FROM books")->fetchColumn() ?? 0;
$totalVisits    = $pdo->query("SELECT COUNT(*) FROM visitor_logs")->fetchColumn();
$todayVisits    = $pdo->query("SELECT COUNT(*) FROM visitor_logs WHERE DATE(visited_at) = CURDATE()")->fetchColumn();

// Recent content
$recentBooks = $pdo->query("SELECT b.*, c.name as category_name 
                             FROM books b 
                             LEFT JOIN categories c ON b.category_id = c.id 
                             ORDER BY b.created_at DESC LIMIT 6")->fetchAll();
$recentNews  = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 6")->fetchAll();
$recentVideos = $pdo->query("SELECT * FROM videos WHERE is_published = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Most downloaded books
$topBooks = $pdo->query("SELECT title, downloads, cover_image 
                          FROM books WHERE is_published = 1
                          ORDER BY downloads DESC LIMIT 5")->fetchAll();

// Recent subscribers
$recentSubs = $pdo->query("SELECT email, created_at, is_subscriber 
                            FROM visitor_emails 
                            ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28', light: '#2d9e5f' },
                        gold:    { DEFAULT: '#c9a84c' }
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
    <div class="flex items-center gap-4">
        <span class="text-green-200 text-sm hidden md:block">
            <i class="fas fa-user-circle mr-1"></i><?= clean($_SESSION['admin_username']) ?>
        </span>
        <a href="<?= SITE_URL ?>" target="_blank"
           class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-external-link-alt mr-1"></i>View Site
        </a>
        <a href="logout.php"
           class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
    </div>
</nav>

<div class="flex">

    <!-- Sidebar -->
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block flex-shrink-0">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-newspaper w-5"></i>Manage News Article
            </a>
            <a href="videos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fab fa-youtube w-5"></i>Manage Videos
            </a>
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
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
                <i class="fas fa-chart-bar w-5"></i>Full Analytics
            </a>
            <a href="admins.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-user-shield w-5"></i>Manage Admins
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-cog w-5"></i>Settings
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-x-hidden">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-sm text-gray-400">
                <i class="fas fa-clock mr-1"></i>
                <?= date('D, d M Y H:i') ?>
            </p>
        </div>

        <!-- 6 Key Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-primary">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Books</p>
                        <p class="text-3xl font-bold text-primary"><?= number_format($totalBooks) ?></p>
                        <a href="add-book.php" class="text-xs text-primary hover:underline mt-1 inline-block">
                            + Add new book
                        </a>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">News Articles</p>
                        <p class="text-3xl font-bold text-blue-600"><?= number_format($totalNews) ?></p>
                        <a href="add-news.php" class="text-xs text-blue-500 hover:underline mt-1 inline-block">
                            + Add news article
                        </a>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-newspaper text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Videos</p>
                        <p class="text-3xl font-bold text-red-600"><?= number_format($totalVideos) ?></p>
                        <a href="add-video.php" class="text-xs text-red-500 hover:underline mt-1 inline-block">
                            + Add video
                        </a>
                    </div>

                    <div class="w-12 h-12 bg-pink-50 rounded-xl flex items-center justify-center">
                        <i class="fab fa-youtube text-pink-500 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-orange-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Downloads</p>
                        <p class="text-3xl font-bold text-orange-500"><?= number_format($totalDownloads) ?></p>
                        <p class="text-xs text-gray-400 mt-1">across all books</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-download text-orange-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Subscribers</p>
                        <p class="text-3xl font-bold text-purple-600"><?= number_format($totalSubs) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= number_format($totalEmails) ?> total emails</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bell text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-teal-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Visits</p>
                        <p class="text-3xl font-bold text-teal-600"><?= number_format($totalVisits) ?></p>
                        <a href="analytics.php" class="text-xs text-teal-500 hover:underline mt-1 inline-block">
                            View analytics →
                        </a>
                    </div>
                    <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-eye text-teal-500 text-xl"></i>
                    </div>
                </div>
            </div>

            

        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h2 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Quick Actions</h2>
            <div class="flex flex-wrap gap-3">
                <a href="add-book.php"
                   class="flex items-center gap-2 bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-book"></i> Upload Book
                </a>
                <a href="add-news.php"
                   class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-newspaper"></i> Add News Article
                </a>
                 <a href="videos.php" 
                 class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fab fa-youtube"></i>Manage Videos
                </a>
                <a href="announcements.php"
                   class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-bullhorn"></i> Announcement
                </a>
                <a href="notify.php"
                   class="flex items-center gap-2 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-envelope"></i> Email Subscribers
                </a>
                <a href="analytics.php"
                   class="flex items-center gap-2 bg-teal-500 hover:bg-teal-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-chart-bar"></i> Full Analytics
                </a>
                <a href="admins.php" class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-user-shield w-5"></i>Manage Admins
                </a>
                <a href="settings.php"
                   class="flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-palette"></i> Change Theme
                </a>
            </div>
        </div>

        <!-- Recent Books + Recent News -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Recent Books -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">Recent Books</h2>
                    <a href="add-book.php"
                       class="text-xs bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-primary-dark transition">
                        + Add Book
                    </a>
                </div>
                <?php if (empty($recentBooks)): ?>
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-book text-4xl mb-3"></i>
                    <p class="text-sm">No books yet.</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($recentBooks as $book): ?>
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                        <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-primary flex items-center justify-center">
                            <?php if ($book['cover_image']): ?>
                                <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-book text-white text-sm"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                <?= clean($book['title']) ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?= clean($book['category_name'] ?? 'General') ?>
                                · <?= $book['downloads'] ?> downloads
                                · <?= formatDate($book['created_at']) ?>
                            </p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <a href="edit-book.php?id=<?= $book['id'] ?>"
                               class="text-xs text-blue-500 hover:text-blue-700 font-medium">
                                Edit
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    <a href="books.php" class="text-xs text-primary hover:underline">
                        View all books →
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent News -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">Recent News Article</h2>
                    <a href="add-news.php"
                       class="text-xs bg-blue-500 text-white px-3 py-1.5 rounded-lg hover:bg-blue-600 transition">
                        + Add News Article
                    </a>
                </div>
                <?php if (empty($recentNews)): ?>
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-newspaper text-4xl mb-3"></i>
                    <p class="text-sm">No news yet.</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($recentNews as $article): ?>
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                        <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-blue-500 flex items-center justify-center">
                            <?php if ($article['cover_image']): ?>
                                <img src="<?= UPLOAD_URL ?>news/<?= clean($article['cover_image']) ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-newspaper text-white text-sm"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                <?= clean($article['title']) ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?= formatDate($article['created_at']) ?>
                                · <?= $article['is_published'] ? 'Published' : 'Draft' ?>
                            </p>
                        </div>
                        <a href="edit-news.php?id=<?= $article['id'] ?>"
                           class="text-xs text-blue-500 hover:text-blue-700 font-medium flex-shrink-0">
                            Edit
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    <a href="news.php" class="text-xs text-primary hover:underline">
                        View all news →
                    </a>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Top Downloaded + Recent Subscribers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Top Downloads -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">Most Downloaded Books</h2>
                </div>
                <?php if (empty($topBooks)): ?>
                <p class="text-center py-8 text-gray-400 text-sm">No downloads yet.</p>
                <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($topBooks as $i => $book): ?>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="text-lg font-bold text-gray-300 w-6 flex-shrink-0">
                            <?= $i + 1 ?>
                        </span>
                        <div class="w-8 h-8 rounded bg-primary flex items-center justify-center flex-shrink-0">
                            <?php if ($book['cover_image']): ?>
                                <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                                     class="w-full h-full object-cover rounded">
                            <?php else: ?>
                                <i class="fas fa-book text-white text-xs"></i>
                            <?php endif; ?>
                        </div>
                        <p class="flex-1 text-sm text-gray-700 truncate">
                            <?= clean($book['title']) ?>
                        </p>
                        <span class="text-sm font-bold text-primary flex-shrink-0">
                            <?= number_format($book['downloads']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Subscribers -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">Recent Emails Collected</h2>
                    <a href="subscribers.php" class="text-xs text-primary hover:underline">
                        View all →
                    </a>
                </div>
                <?php if (empty($recentSubs)): ?>
                <p class="text-center py-8 text-gray-400 text-sm">No emails yet.</p>
                <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($recentSubs as $sub): ?>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    <?= $sub['is_subscriber'] ? 'bg-purple-100' : 'bg-gray-100' ?>">
                            <i class="fas fa-user text-xs
                                      <?= $sub['is_subscriber'] ? 'text-purple-500' : 'text-gray-400' ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 truncate"><?= clean($sub['email']) ?></p>
                            <p class="text-xs text-gray-400"><?= formatDate($sub['created_at']) ?></p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0
                                     <?= $sub['is_subscriber'] ? 'bg-purple-50 text-purple-600' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $sub['is_subscriber'] ? 'Subscriber' : 'Downloader' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

    </main>
</div>

</body>
</html>