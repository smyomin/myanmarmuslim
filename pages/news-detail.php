<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$ref  = trim($_GET['ref'] ?? '');
$id   = $ref ? decodeId($ref) : 0;
$article = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ? AND is_published = 1");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
}

if (!$article) {
    header('Location: news.php');
    exit;
}

$pageTitle = $article['title'];

// Related news
$related = $pdo->prepare("SELECT * FROM news WHERE is_published = 1 
                          AND id != ? ORDER BY created_at DESC LIMIT 3");
$related->execute([$article['id']]);
$relatedArticles = $related->fetchAll();

require_once '../includes/seo.php';
$articleUrl = SITE_URL . '/pages/news-detail.php?id=' . $article['id'];
$seoOptions = [
    'title'       => clean($article['title']),
    'description' => clean(substr(strip_tags($article['content']), 0, 155)),
    'image'       => $article['cover_image'] ? UPLOAD_URL . 'news/' . clean($article['cover_image']) : null,
    'url'         => $articleUrl,
    'type'        => 'article',
    'json_ld'     => jsonLdArticle($article, $articleUrl)
];

include '../includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-10">

    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-400 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-primary">Home</a>
        <span class="mx-2">/</span>
        <a href="news.php" class="hover:text-primary">News</a>
        <span class="mx-2">/</span>
        <span class="text-gray-600"><?= clean($article['title']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Article -->
        <article class="lg:col-span-2">
            <?php if ($article['cover_image']): ?>
            <img src="<?= UPLOAD_URL ?>news/<?= clean($article['cover_image']) ?>"
                 alt="<?= clean($article['title']) ?>"
                 class="w-full h-72 object-cover rounded-xl mb-6 shadow">
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                <p class="text-xs text-gray-400 mb-3">
                    <i class="fas fa-calendar mr-1"></i>
                    <?= formatDate($article['created_at']) ?>
                </p>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6 leading-snug">
                    <?= clean($article['title']) ?>
                </h1>
                <div class="prose max-w-none text-gray-700 leading-relaxed text-sm">
                    <?= nl2br(clean($article['content'])) ?>
                </div>
            </div>

           <!-- Share -->
            <div class="mt-6 bg-white rounded-xl shadow-sm p-5"
                style="background: var(--color-card); border: 1px solid var(--color-border)">
                <p class="text-sm font-medium mb-3" style="color: var(--color-text)">
                    Share this article:
                </p>
                <?php
                // Build clean share URL - use actual domain on live server
                $shareUrl   = SITE_URL . '/pages/news-detail.php?id=' . $article['id'];
                $shareTitle = urlencode($article['title']);
                $shareUrlEncoded = urlencode($shareUrl);
                ?>
                <div class="flex flex-wrap gap-3">
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrlEncoded ?>"
                    target="_blank" rel="noopener noreferrer"
                    onclick="openShare(this.href); return false;"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>

                    <!-- Twitter / X -->
                    <a href="https://twitter.com/intent/tweet?text=<?= $shareTitle ?>&url=<?= $shareUrlEncoded ?>"
                    target="_blank" rel="noopener noreferrer"
                    onclick="openShare(this.href); return false;"
                    class="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>

                    <!-- WhatsApp -->
                    <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrlEncoded ?>"
                    target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>

                    <!-- Copy Link -->
                    <button onclick="copyLink('<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>')"
                            class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-link" id="copyIcon"></i>
                        <span id="copyText">Copy Link</span>
                    </button>
                </div>
            </div>

            <script>
            // Open share in popup window
            function openShare(url) {
                window.open(url, 'share', 'width=600,height=400,left=' +
                    (window.innerWidth/2 - 300) + ',top=' + (window.innerHeight/2 - 200));
            }

            // Copy link to clipboard
            function copyLink(url) {
                navigator.clipboard.writeText(url).then(function() {
                    document.getElementById('copyIcon').className = 'fas fa-check';
                    document.getElementById('copyText').textContent = 'Copied!';
                    setTimeout(function() {
                        document.getElementById('copyIcon').className = 'fas fa-link';
                        document.getElementById('copyText').textContent = 'Copy Link';
                    }, 2000);
                });
            }
            </script>

        </article>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">
                    Related News
                </h3>
                <?php if (empty($relatedArticles)): ?>
                    <p class="text-gray-400 text-sm">No related articles.</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($relatedArticles as $rel): ?>
                    <div class="flex gap-3">
                        <?php if ($rel['cover_image']): ?>
                            <img src="<?= UPLOAD_URL ?>news/<?= clean($rel['cover_image']) ?>"
                                 class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-newspaper text-white text-sm opacity-50"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <a href="news-detail.php?id=<?= encodeId(clean($rel['id'])) ?>"
                               class="text-sm font-medium text-gray-800 hover:text-primary line-clamp-2 transition">
                                <?= clean($rel['title']) ?>
                            </a>
                            <p class="text-xs text-gray-400 mt-1">
                                <?= formatDate($rel['created_at']) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Subscribe Box -->
            <div class="bg-primary rounded-xl p-5 text-white">
                <h3 class="font-bold mb-2">
                    <i class="fas fa-envelope text-gold mr-2"></i>အဆက်အသွယ်မပြတ်စေရန်
                </h3>
                <p class="text-green-100 text-sm mb-3">
                    စာအုပ်အသစ်များ ထွက်ရှိသည့်အခါ အကြောင်းကြားစာရယူရန် Subscribe လုပ်ထားပါ။
                </p>
                <form action="<?= SITE_URL ?>/includes/subscribe.php" method="POST" class="space-y-2">
                    <input type="email" name="email" placeholder="Your email"
                           class="w-full px-3 py-2 rounded-lg text-gray-800 text-sm focus:outline-none" required>
                    <button type="submit"
                            class="w-full bg-gold hover:bg-yellow-600 text-white py-2 rounded-lg text-sm font-medium transition">
                        Subscribe
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>

<?php include '../includes/footer.php'; ?>