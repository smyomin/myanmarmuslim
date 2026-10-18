<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Videos';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 9;
$offset    = ($page - 1) * $perPage;
$search    = trim($_GET['search'] ?? '');
$category  = trim($_GET['category'] ?? '');

// Hardcoded categories
$videoCategories = [
    'quran-tafsir'    => ['label' => 'ကုရ်အာန်နှင့် သဖ်စီးရ်',    'icon' => 'book-open'],    
    'history'         => ['label' => 'အစ္စလာမ့်သမိုင်း',          'icon' => 'history'],
    'aqeedah'         => ['label' => 'အကီဒဟ် (ယုံကြည်ချက်)',           'icon' => 'star'],
    'panel-discussion'=> ['label' => 'ဆွေးနွေးပွဲများ',  'icon' => 'users'],
    'lecture'         => ['label' => '‌ဟောပြောပို့ချချက်များ',           'icon' => 'microphone'],
    'other'           => ['label' => 'အခြားအထွေထွေများ',             'icon' => 'video'],
];

// Build query
$where  = ["is_published = 1"];
$params = [];

if ($search) {
    $where[]  = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category && isset($videoCategories[$category])) {
    $where[]  = "category = ?";
    $params[] = $category;
}

$whereSQL = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE $whereSQL");
$countStmt->execute($params);
$totalCount = $countStmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$stmt = $pdo->prepare("SELECT * FROM videos WHERE $whereSQL
                       ORDER BY created_at DESC
                       LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$videos = $stmt->fetchAll();

// Selected video for player
$selectedId = (int)($_GET['watch'] ?? 0);
$selected   = null;
if ($selectedId) {
    $s = $pdo->prepare("SELECT * FROM videos WHERE id = ? AND is_published = 1");
    $s->execute([$selectedId]);
    $selected = $s->fetch();
}


require_once '../includes/seo.php';
$seoOptions = [
    'title'       => 'Islamic Videos & Lectures for Myanmar Muslims',
    'description' => 'Watch free Islamic lectures, panel discussions, Quran tafsir and educational videos for Myanmar Muslims.',
    'keywords'    => 'myanmar islamic videos, islamic lectures myanmar, quran tafsir video myanmar',
    'url'         => SITE_URL . '/pages/videos.php',
];

include '../includes/header.php';
?>

<!-- Page Header -->
<div class="hero-section text-white py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fab fa-youtube mr-3" style="color: var(--color-gold)"></i>
            ဗီဒီယိုများ
        </h1>
        <p style="color: rgba(255,255,255,0.8)">
            အစ္စလာမ်ဘာသာရေးဆိုင်ရာ ဟောပြောပွဲများ၊ စာပေစကားဝိုင်း၊ ဆွေးနွေးပွဲများနှင့် ပညာပေးဗီဒီယိုများ။
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Video Player -->
    <?php if ($selected): ?>
    <div id="player" class="mb-8 rounded-xl overflow-hidden shadow-lg"
         style="background: var(--color-card); border: 1px solid var(--color-border)">
        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
            <iframe
                src="https://www.youtube.com/embed/<?= clean($selected['youtube_id']) ?>?autoplay=1&rel=0"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        </div>
        <div class="p-5">
            <?php if (!empty($selected['category']) && isset($videoCategories[$selected['category']])): ?>
            <span class="text-xs font-semibold px-2 py-1 rounded-full mb-2 inline-block"
                  style="background: var(--color-section-alt); color: var(--color-primary)">
                <i class="fas fa-<?= $videoCategories[$selected['category']]['icon'] ?> mr-1"></i>
                <?= $videoCategories[$selected['category']]['label'] ?>
            </span>
            <?php endif; ?>
            <h2 class="text-xl font-bold mb-2" style="color: var(--color-text)">
                <?= clean($selected['title']) ?>
            </h2>
            <?php if ($selected['description']): ?>
            <p class="text-sm leading-relaxed mb-4" style="color: var(--color-text-muted)">
                <?= nl2br(clean($selected['description'])) ?>
            </p>
            <?php endif; ?>
            <div class="flex gap-3 flex-wrap">
                <a href="https://www.youtube.com/watch?v=<?= clean($selected['youtube_id']) ?>"
                   target="_blank"
                   style="background:#ff0000; color:white; padding:8px 16px; border-radius:8px;
                          font-size:13px; font-weight:600; text-decoration:none;
                          display:inline-flex; align-items:center; gap:6px;">
                    <i class="fab fa-youtube"></i> Watch on YouTube
                </a>
                <a href="videos.php"
                   style="background:var(--color-section-alt); color:var(--color-text);
                          padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600;
                          text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search + Category Filter Bar -->
    <div class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <!-- Search -->
            <div class="relative flex-1" style="min-width: 200px; max-width: 360px;">
                <i class="fas fa-search absolute left-3 top-2.5 text-sm"
                   style="color: var(--color-text-muted)"></i>
                <input type="text" name="search" value="<?= clean($search) ?>"
                       placeholder="Search videos..."
                       style="background: var(--color-card); border: 1px solid var(--color-border);
                              color: var(--color-text); width: 100%; padding: 9px 16px 9px 36px;
                              border-radius: 8px; font-size: 14px;"
                       onfocus="this.style.borderColor='var(--color-primary)'"
                       onblur="this.style.borderColor='var(--color-border)'">
            </div>

            <!-- Category Dropdown -->
            <select name="category"
                    onchange="this.form.submit()"
                    style="background: var(--color-card); border: 1px solid var(--color-border);
                           color: var(--color-text); padding: 9px 16px; border-radius: 8px;
                           font-size: 14px; cursor: pointer; min-width: 180px;">
                <option value="">📂 All Categories</option>
                <?php foreach ($videoCategories as $key => $cat): ?>
                <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                    <?= $cat['label'] ?>
                </option>
                <?php endforeach; ?>
            </select>

            <!-- Search Button -->
            <button type="submit"
                    style="background: var(--color-primary); color: white; padding: 9px 20px;
                           border-radius: 8px; font-size: 14px; font-weight: 600;
                           border: none; cursor: pointer;">
                Search
            </button>

            <!-- Clear -->
            <?php if ($search || $category): ?>
            <a href="videos.php"
               style="background: var(--color-section-alt); color: var(--color-text);
                      padding: 9px 14px; border-radius: 8px; font-size: 14px;
                      text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class="fas fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>

        <!-- Active filters -->
        <?php if ($category && isset($videoCategories[$category])): ?>
        <div class="mt-3 flex items-center gap-2">
            <span class="text-sm" style="color: var(--color-text-muted)">Filtering by:</span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full"
                  style="background: var(--color-primary); color: white;">
                <i class="fas fa-<?= $videoCategories[$category]['icon'] ?> mr-1"></i>
                <?= $videoCategories[$category]['label'] ?>
                &nbsp;·&nbsp;
                <a href="videos.php<?= $search ? '?search=' . urlencode($search) : '' ?>"
                   style="color: rgba(255,255,255,0.8); text-decoration: none;">✕</a>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Results Count -->
    <p class="text-sm mb-5" style="color: var(--color-text-muted)">
        <strong><?= number_format($totalCount) ?></strong> video<?= $totalCount !== 1 ? 's' : '' ?>
        <?php if ($category && isset($videoCategories[$category])): ?>
            in <strong><?= $videoCategories[$category]['label'] ?></strong>
        <?php endif; ?>
        <?php if ($search): ?>
            matching "<strong><?= clean($search) ?></strong>"
        <?php endif; ?>
    </p>

    <?php if (empty($videos)): ?>
    <div class="text-center py-20" style="color: var(--color-text-muted)">
        <i class="fab fa-youtube text-6xl mb-4" style="opacity: 0.2"></i>
        <p class="text-xl font-medium">No videos found</p>
        <p class="text-sm mt-2">Try a different search or category</p>
        <a href="videos.php" style="color: var(--color-primary); text-decoration: none;"
           class="inline-block mt-3 text-sm">Clear all filters →</a>
    </div>
    <?php else: ?>

    <!-- Videos Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($videos as $video): ?>
        <div class="rounded-xl overflow-hidden shadow-sm card-hover"
             style="background: var(--color-card); border: 1px solid var(--color-border)">

            <!-- Thumbnail -->
            <a href="videos.php?watch=<?= $video['id'] ?>#player" class="block relative">
                <img src="https://img.youtube.com/vi/<?= clean($video['youtube_id']) ?>/hqdefault.jpg"
                     onerror="this.src='https://img.youtube.com/vi/<?= clean($video['youtube_id']) ?>/mqdefault.jpg'"
                     alt="<?= clean($video['title']) ?>"
                     class="w-full object-cover"
                     style="height: 190px; object-fit: cover;">

                <!-- Play overlay -->
                <div class="absolute inset-0 flex items-center justify-center"
                     style="background: rgba(0,0,0,0); transition: background 0.2s;"
                     onmouseover="this.style.background='rgba(0,0,0,0.3)'; this.querySelector('.play-btn').style.opacity='1'"
                     onmouseout="this.style.background='rgba(0,0,0,0)'; this.querySelector('.play-btn').style.opacity='0'">
                    <div class="play-btn" style="width:52px; height:52px; background:#ff0000;
                                border-radius:50%; display:flex; align-items:center;
                                justify-content:center; opacity:0; transition:opacity 0.2s;">
                        <i class="fas fa-play text-white" style="margin-left:3px;"></i>
                    </div>
                </div>

                <!-- Category badge -->
                <?php if (!empty($video['category']) && isset($videoCategories[$video['category']])): ?>
                <div style="position:absolute; top:8px; left:8px; background:var(--color-primary);
                            color:white; padding:2px 8px; border-radius:20px; font-size:11px;
                            font-weight:600;">
                    <?= $videoCategories[$video['category']]['label'] ?>
                </div>
                <?php endif; ?>
            </a>

            <!-- Info -->
            <div class="p-4">
                <h3 class="font-semibold line-clamp-2 mb-2"
                    style="color: var(--color-text); font-size: 14px; line-height: 1.4">
                    <a href="videos.php?watch=<?= $video['id'] ?>#player"
                       style="color: inherit; text-decoration: none;"
                       onmouseover="this.style.color='var(--color-primary)'"
                       onmouseout="this.style.color='inherit'">
                        <?= clean($video['title']) ?>
                    </a>
                </h3>
                <?php if ($video['description']): ?>
                <p class="text-xs line-clamp-2 mb-3"
                   style="color: var(--color-text-muted); line-height: 1.5">
                    <?= clean(substr($video['description'], 0, 100)) ?>
                </p>
                <?php endif; ?>
                <div class="flex items-center justify-between">
                    <span class="text-xs" style="color: var(--color-text-muted)">
                        <i class="fas fa-calendar mr-1"></i>
                        <?= formatDate($video['created_at']) ?>
                    </span>
                    <a href="videos.php?watch=<?= $video['id'] ?>#player"
                       style="background:#ff0000; color:white; padding:5px 12px;
                              border-radius:6px; font-size:12px; font-weight:600;
                              text-decoration:none; display:inline-flex;
                              align-items:center; gap:4px;">
                        <i class="fas fa-play"></i> Watch
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
           style="padding:8px 16px; background:var(--color-card); border:1px solid var(--color-border);
                  border-radius:8px; color:var(--color-text); text-decoration:none; font-size:14px;">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
           style="padding:8px 16px; border-radius:8px; font-size:14px; text-decoration:none;
                  background:<?= $i===$page ? 'var(--color-primary)' : 'var(--color-card)' ?>;
                  color:<?= $i===$page ? 'white' : 'var(--color-text)' ?>;
                  border:1px solid <?= $i===$page ? 'var(--color-primary)' : 'var(--color-border)' ?>;">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
           style="padding:8px 16px; background:var(--color-card); border:1px solid var(--color-border);
                  border-radius:8px; color:var(--color-text); text-decoration:none; font-size:14px;">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>