<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/tracker.php';
require_once __DIR__ . '/theme.php';
trackVisitor($pdo);
$activeTheme = getActiveTheme($pdo);
$themes      = getThemes();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $activeTheme ?>">
<head>
    <meta charset="UTF-8">
    <script>
    // Apply theme immediately to avoid flash
    (function() {
        const cookie = document.cookie.split(';').find(c => c.trim().startsWith('mm_theme='));
        const theme  = cookie ? cookie.split('=')[1].trim() : '<?= $activeTheme ?>';
        document.documentElement.setAttribute('data-theme', theme);
    })();
</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include SEO
    require_once __DIR__ . '/seo.php';

    // Default SEO — pages override this
    $seoOptions = $seoOptions ?? [];
    if (empty($seoOptions)) {
        $seoOptions = [
            'title'       => $pageTitle ?? '',
            'description' => SEO_SITE_DESC,
            'json_ld'     => jsonLdWebsite()
        ];
    }
    echo generateSEO($seoOptions);
    ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/css/favicon.ico">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:  { DEFAULT: '#1a6b3c', light: '#2d9e5f', dark: '#0f4a28' },
                        gold:     { DEFAULT: '#c9a84c', light: '#e0c070', dark: '#a07830' },
                    },
                    fontFamily: {
                        arabic: ['Amiri', 'serif'],
                        body:   ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .arabic-text { font-family: 'Amiri', serif; }
        .hero-pattern {
            background-color: var(--color-hero-bg);
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }
    </style>
</head>
<body class="transition-colors duration-300" style="background-color: var(--color-bg); color: var(--color-text);">

<!-- Top bar -->
<div class="site-topbar text-sm py-2 px-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <span class="arabic-text text-gold text-lg">بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ</span>
        <span class="hidden md:block text-gray-300">Islamic Online Library for Myanmar Shia Muslims</span>
    </div>
</div>

<!-- Navigation -->
<nav class="site-nav shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="flex items-center gap-3">
                <!--<div class="w-10 h-10 rounded-full flex items-center justify-center"
                     style="background-color: var(--color-primary)">
                    <i class="fas fa-mosque text-white text-lg"></i>
                </div>-->
                <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
                <div>
                    <div class="font-bold text-lg leading-tight theme-primary-text">Myanmar Muslim</div>
                    <div class="text-xs leading-tight theme-text-muted">Islamic Online Library</div>
                </div>
            </a>

             <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-6">
                <a href="<?= SITE_URL ?>" class="nav-link font-medium hover:opacity-80">မူလစာမျက်နှာ</a>
                <a href="<?= SITE_URL ?>/pages/library.php" class="nav-link font-medium hover:opacity-80">စာကြည့်တိုက်</a>
                <a href="<?= SITE_URL ?>/pages/duas.php" class="nav-link font-medium hover:opacity-80">ဒိုအာများ</a>
                <a href="<?= SITE_URL ?>/pages/videos.php" class="nav-link font-medium hover:opacity-80">ဗီဒီယိုများ</a>
                <a href="<?= SITE_URL ?>/pages/news.php" class="nav-link font-medium hover:opacity-80">သတင်းဆောင်းပါးများ</a>
                <a href="<?= SITE_URL ?>/pages/about.php" class="nav-link font-medium hover:opacity-80">ကျွန်ုပ်တို့အကြောင်း</a>
                <a href="<?= SITE_URL ?>/pages/publisher.php" class="nav-link font-medium hover:opacity-80">စာပေဖြန့်ချိရေး</a>
                <a href="<?= SITE_URL ?>/pages/contact.php" class="nav-link font-medium hover:opacity-80">ဆက်သွယ်ရန်</a>
            </div>


            <!-- Mobile menu button -->
            <button id="menuBtn" class="md:hidden theme-text-muted">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>


        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden pb-4 border-t theme-border">
            <div class="flex flex-col gap-1 pt-3">
                <a href="<?= SITE_URL ?>" class="px-3 py-2 rounded hover:opacity-80 font-medium">မူလစာမျက်နှာ</a>
                <a href="<?= SITE_URL ?>/pages/library.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">စာကြည့်တိုက်</a>
                <a href="<?= SITE_URL ?>/pages/duas.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">ဒိုအာများ</a>
                <a href="<?= SITE_URL ?>/pages/videos.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">စာပေစကားဝိုင်း</a>
                <a href="<?= SITE_URL ?>/pages/news.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">သတင်းဆောင်းပါးများ</a>
                <a href="<?= SITE_URL ?>/pages/about.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">ကျွန်ုပ်တို့အကြောင်း</a>
                <a href="<?= SITE_URL ?>/pages/publisher.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">စာပေဖြန့်ချိရေး</a>
                <a href="<?= SITE_URL ?>/pages/contact.php" class="px-3 py-2 rounded hover:opacity-80 font-medium">ဆက်သွယ်ရန်</a>
            </div>
        </div>
    </div>
</nav>

<script>
document.getElementById('menuBtn').addEventListener('click', function() {
    document.getElementById('mobileMenu').classList.toggle('hidden');
});
</script>


<script>
function toggleThemePanel() {
    document.getElementById('themePanel').classList.toggle('open');
}

function setTheme(theme) {
    // Save to cookie for 365 days
    document.cookie = 'mm_theme=' + theme + ';path=/;max-age=' + (365*24*60*60);
    // Apply immediately
    document.documentElement.setAttribute('data-theme', theme);
    // Mark selected
    document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    // Close panel
    document.getElementById('themePanel').classList.remove('open');
}

// Close panel when clicking outside
document.addEventListener('click', function(e) {
    const switcher = document.querySelector('.theme-switcher');
    if (!switcher.contains(e.target)) {
        document.getElementById('themePanel').classList.remove('open');
    }
});
</script>