<?php
require_once 'includes/config.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl  = SITE_URL;
$today    = date('Y-m-d');

// Fetch dynamic URLs
$books   = $pdo->query("SELECT id, created_at FROM books WHERE is_published = 1")->fetchAll();
$news    = $pdo->query("SELECT id, created_at FROM news WHERE is_published = 1")->fetchAll();
$videos  = $pdo->query("SELECT id, created_at FROM videos WHERE is_published = 1")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">

    <!-- Static Pages -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/pages/library.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/pages/news.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/pages/videos.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/pages/duas.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/pages/about.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/pages/contact.php</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>

    <!-- Books -->
    <?php foreach ($books as $book): ?>
    <url>
        <loc><?= $baseUrl ?>/pages/book.php?ref=<?= encodeId($book['id']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($book['created_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>

    <!-- News Articles -->
    <?php foreach ($news as $article): ?>
    <url>
        <loc><?= $baseUrl ?>/pages/news-detail.php?id=<?= $article['id'] ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($article['created_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

    <!-- Videos -->
    <?php foreach ($videos as $video): ?>
    <url>
        <loc><?= $baseUrl ?>/pages/videos.php?watch=<?= $video['id'] ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($video['created_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

</urlset>