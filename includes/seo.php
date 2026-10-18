<?php
// SEO Configuration
define('SEO_SITE_NAME',    'Myanmar Muslims - Islamic Online Library');
define('SEO_SITE_DESC',    'Free Islamic online library for Myanmar Muslims. Download ebooks, duas, research papers, watch Islamic videos and read latest news.');
define('SEO_SITE_KEYWORDS','myanmar muslims, islamic library, myanmar islam, ebooks, duas, quran, hadith, myanmar islamic books');
define('SEO_SITE_LOCALE',  'my_MM');
define('SEO_TWITTER',      '@myanmarmuslims'); // change to your Twitter handle
define('SEO_FB_APP_ID',    ''); // add your Facebook App ID if you have one
define('SEO_DEFAULT_IMAGE', SITE_URL . '/assets/images/og-default.jpg');

function generateSEO($options = []) {
    $title       = $options['title']       ?? SEO_SITE_NAME;
    $description = $options['description'] ?? SEO_SITE_DESC;
    $keywords    = $options['keywords']    ?? SEO_SITE_KEYWORDS;
    $image       = $options['image']       ?? SEO_DEFAULT_IMAGE;
    $url         = $options['url']         ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $type        = $options['type']        ?? 'website';
    $jsonLd      = $options['json_ld']     ?? null;

    // Clean title
    $fullTitle = ($options['title'] ?? '') 
        ? $options['title'] . ' | ' . SITE_NAME 
        : SEO_SITE_NAME;

    // Clean description (max 160 chars)
    $description = strip_tags($description);
    $description = str_replace(["\n", "\r", "\t"], ' ', $description);
    $description = preg_replace('/\s+/', ' ', trim($description));
    if (strlen($description) > 160) {
        $description = substr($description, 0, 157) . '...';
    }

    // Clean URL
    $url = strtok($url, '?');
    $url = rtrim($url, '/');

    ob_start();
    ?>
    <!-- Primary SEO -->
    <title><?= htmlspecialchars($fullTitle) ?></title>
    <meta name="description"        content="<?= htmlspecialchars($description) ?>">
    <meta name="keywords"           content="<?= htmlspecialchars($keywords) ?>">
    <meta name="author"             content="Myanmar Muslims Library">
    <meta name="robots"             content="index, follow">
    <meta name="language"           content="Myanmar">
    <meta name="revisit-after"      content="7 days">
    <link rel="canonical"           href="<?= htmlspecialchars($url) ?>">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
    <meta property="og:type"        content="<?= htmlspecialchars($type) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($fullTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height"content="630">
    <meta property="og:url"         content="<?= htmlspecialchars($url) ?>">
    <meta property="og:site_name"   content="<?= SITE_NAME ?>">
    <meta property="og:locale"      content="<?= SEO_SITE_LOCALE ?>">
    <?php if (SEO_FB_APP_ID): ?>
    <meta property="fb:app_id"      content="<?= SEO_FB_APP_ID ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="<?= SEO_TWITTER ?>">
    <meta name="twitter:title"       content="<?= htmlspecialchars($fullTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($image) ?>">

    <!-- Mobile & Browser -->
    <meta name="theme-color"         content="#1a6b3c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= SITE_NAME ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">
    <link rel="apple-touch-icon"      href="<?= SITE_URL ?>/assets/images/apple-touch-icon.png">

    <?php if ($jsonLd): ?>
    <!-- Structured Data -->
    <script type="application/ld+json">
    <?= json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

// Structured data helpers
function jsonLdWebsite() {
    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => SITE_NAME,
        'url'             => SITE_URL,
        'description'     => SEO_SITE_DESC,
        'inLanguage'      => 'my',
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => SITE_URL . '/pages/library.php?search={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];
}

function jsonLdBook($book, $url) {
    return [
        '@context'    => 'https://schema.org',
        '@type'       => 'Book',
        'name'        => $book['title'],
        'description' => $book['description'] ?? '',
        'author'      => [
            '@type' => 'Person',
            'name'  => $book['author'] ?? 'Unknown'
        ],
        'inLanguage'  => $book['language'] ?? 'my',
        'url'         => $url,
        'image'       => $book['cover_image']
            ? UPLOAD_URL . 'covers/' . $book['cover_image']
            : SEO_DEFAULT_IMAGE,
        'publisher'   => [
            '@type' => 'Organization',
            'name'  => SITE_NAME,
            'url'   => SITE_URL
        ],
        'offers'      => [
            '@type'       => 'Offer',
            'price'       => '0',
            'priceCurrency' => 'USD',
            'availability'=> 'https://schema.org/InStock'
        ]
    ];
}

function jsonLdArticle($article, $url) {
    return [
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => $article['title'],
        'description'      => substr(strip_tags($article['content'] ?? ''), 0, 160),
        'url'              => $url,
        'datePublished'    => $article['created_at'],
        'dateModified'     => $article['created_at'],
        'inLanguage'       => 'my',
        'image'            => $article['cover_image']
            ? UPLOAD_URL . 'news/' . $article['cover_image']
            : SEO_DEFAULT_IMAGE,
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => SITE_NAME,
            'url'   => SITE_URL,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => SITE_URL . '/assets/images/logo.png'
            ]
        ],
        'author'           => [
            '@type' => 'Organization',
            'name'  => SITE_NAME
        ]
    ];
}

function jsonLdOrganization() {
    return [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => SITE_NAME,
        'url'         => SITE_URL,
        'description' => SEO_SITE_DESC,
        'logo'        => [
            '@type' => 'ImageObject',
            'url'   => SITE_URL . '/assets/images/logo.png'
        ],
        'sameAs'      => [
            // Add your social media URLs here
            // 'https://www.facebook.com/myanmarmuslims',
        ]
    ];
}