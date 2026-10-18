<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Latest News';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 9;
$offset    = ($page - 1) * $perPage;

$total      = $pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 1")->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT * FROM news WHERE is_published = 1 
                       ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute();
$articles = $stmt->fetchAll();

require_once '../includes/seo.php';
$seoOptions = [
    'title'       => 'Latest Islamic News for Myanmar Muslims',
    'description' => 'Read the latest Islamic news, updates and announcements for Myanmar Muslims community.',
    'keywords'    => 'myanmar muslim news, islamic news myanmar, muslim community myanmar',
    'url'         => SITE_URL . '/pages/news.php',
];

include '../includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">သတင်းဆောင်းပါးများ</h1>
        <p class="text-green-100">မွတ်စလင်ကွန်မြူနီတီအတွင်းမှ သတင်းနှင့် ဆောင်းပါးများ</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-10">

    <?php if (empty($articles)): ?>
    <div class="text-center py-20 text-gray-400">
        <i class="fas fa-newspaper text-6xl mb-4"></i>
        <p class="text-xl font-medium">No news articles yet</p>
        <p class="text-sm mt-2">Check back soon for updates</p>
    </div>
    <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($articles as $article): ?>
        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 card-hover group">
            <!-- Image -->
            <a href="news-detail.php?ref=<?= encodeId(clean($article['id'])) ?>">
                <?php if ($article['cover_image']): ?>
                    <img src="<?= UPLOAD_URL ?>news/<?= clean($article['cover_image']) ?>"
                         alt="<?= clean($article['title']) ?>"
                         class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                <?php else: ?>
                    <div class="w-full h-48 bg-primary flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-4xl opacity-30"></i>
                    </div>
                <?php endif; ?>
            </a>

            <div class="p-5">
                <p class="text-xs text-gray-400 mb-2">
                    <i class="fas fa-calendar mr-1"></i>
                    <?= formatDate($article['created_at']) ?>
                </p>
                <h2 class="font-bold text-gray-800 mb-2 line-clamp-2">
                    <a href="news-detail.php?ref=<?= encodeId(clean($article['id'])) ?>"
                       class="hover:text-primary transition">
                        <?= clean($article['title']) ?>
                    </a>
                </h2>
                <p class="text-sm text-gray-500 line-clamp-3 mb-4">
                    <?= clean(substr(strip_tags($article['content']), 0, 150)) ?>...
                </p>
                <a href="news-detail.php?ref=<?= encodeId(clean($article['id'])) ?>"
                   class="inline-flex items-center gap-2 text-primary hover:text-primary-dark text-sm font-medium transition">
                    Read More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2 mt-10">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>"
               class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm hover:border-primary hover:text-primary transition">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>"
               class="px-4 py-2 rounded-lg text-sm transition
                      <?= $i === $page ? 'bg-primary text-white' : 'bg-white border border-gray-200 hover:border-primary hover:text-primary' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>"
               class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm hover:border-primary hover:text-primary transition">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>