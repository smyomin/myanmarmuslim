<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
$categories = getCategories($pdo);
$recentBooks = getRecentBooks($pdo, 4);
$recentWallpapers = getRecentWallpapers($pdo, 4);
$recentNews = getRecentNews($pdo, 3);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-pattern text-white py-20 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <p class="arabic-text text-4xl text-gold mb-4">بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Myanmar Islamic Library</h1>
        <p class="text-green-100 text-lg mb-8 max-w-2xl mx-auto">
            ဤ အခမဲ့ အွန်လိုင်း အစ္စလာမ့်စာကြည့်တိုက်တွင် ကုရ်အာန်၊ အဲဟ်လေဗိုင်သ် (အ.စ) ၏ဟဒီးစ်တော်များ၊ အစ္စလာမ့်ယုံကြည်ချက်၊ သမိုင်း၊ ရှရီအသ်ဥပဒေ၊ ဒိုအာနှင့် အအ်မာလ်များ၊ ကိုယ်ကျင့်နီတိနှင့် ရင်သွေးငယ်များဆိုင်ရာ စာအုပ်များ၊ သုတေသနစာတမ်းများနှင့် လစဉ်ထုတ် နံရံကပ် စာစောင်များ  ကိုပါ စုံလင်စွာ ဖတ်ရှုလေ့လာနိုင်ပါသည်။
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="pages/library.php"
               class="bg-gold bg-opacity-20 hover:bg-gold hover:bg-opacity-30 text-white font-semibold px-8 py-3 rounded-lg transition">
                <i class="fas fa-book-open mr-2"></i>စာကြည့်တိုက်ဖွင့်ရန်
            </a>
            <a href="pages/duas.php"
               class="bg-primary bg-opacity-20 hover:bg-primary-dark hover:bg-opacity-30 text-white font-semibold px-8 py-3 rounded-lg transition border border-white border-opacity-30">
                <i class="fas fa-hands mr-2"></i>ဒိုအာနှင့်အအ်မာလ်များ
            </a>
        </div>
    </div>
</section>


<!-- Categories Section -->
<section class="py-16 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-primary">ကဏ္ဍအလိုက်ဖတ်ရှုရန်</h2>
            <p class="text-gray-500 mt-2">အကြောင်းအရာကဏ္ဍအလိုက် စာအုပ်များနှင့် စာစောင်များ</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="pages/library.php?category=<?= $cat['slug'] ?>"
               class="card-hover bg-white rounded-xl p-5 text-center shadow-sm border border-gray-100 group">
                <div class="w-12 h-12 bg-green-50 group-hover:bg-primary rounded-full flex items-center justify-center mx-auto mb-3 transition">
                    <i class="fas fa-<?= clean($cat['icon']) ?> text-primary group-hover:text-white text-xl transition"></i>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm"><?= clean($cat['name']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once 'includes/prayer-widget.php';
$prayerTimes    = getPrayerTimesWidget($pdo);
$islamicDate    = getIslamicDate();
$upcomingEvents = getUpcomingEvents($pdo, 2);
$announcement   = getActiveAnnouncement($pdo);
?>

<!-- Announcement Banner -->
<?php if ($announcement): ?>
<div style="background: var(--color-primary); color: white;"
     class="py-3 px-4 text-center relative">
    <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
        <span class="text-xl">
            <?php
            $icons = ['moon'=>'🌙','eid'=>'🎉','muharram'=>'🖤','general'=>'📢'];
            echo $icons[$announcement['type']] ?? '📢';
            ?>
        </span>
        <div>
            <strong><?= clean($announcement['title']) ?></strong>
            <span class="mx-2 opacity-50">|</span>
            <span class="text-sm opacity-90"><?= clean($announcement['message']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Prayer Times + Islamic Date + Events Widget -->
<section class="py-6 px-4" style="background: var(--color-section-alt)">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <!-- Prayer Times -->
            <div class="lg:col-span-1 rounded-xl overflow-hidden shadow-sm"
                 style="background: var(--color-card); border: 1px solid var(--color-border)">
                <div class="px-4 py-3 flex items-center justify-between"
                     style="background: var(--color-primary)">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🕌</span>
                        <div>
                            <p class="text-white font-bold text-sm">Prayer Times</p>
                            <p class="text-xs" style="color:rgba(255,255,255,0.75)">
                                Yangon — <?= date('d M Y') ?>
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($prayerTimes['from_api'])): ?>
                    <span class="text-xs px-2 py-1 rounded-full"
                          style="background:rgba(255,255,255,0.2); color:white;">
                        Live
                    </span>
                    <?php endif; ?>
                </div>

                <?php if ($prayerTimes): ?>
                <div class="p-4">
                    <?php
                    $prayers = [
                        ['🌅', 'Fajr',     $prayerTimes['fajr']],
                        ['☀️', 'Sunrise',  $prayerTimes['sunrise']],
                        ['🌞', 'Dhuhr/then Asr',    $prayerTimes['dhuhr']],
                        ['🌆', 'Maghrib/ then Isha',  $prayerTimes['maghrib']],
                    ];

                    // Highlight next prayer
                    $now        = strtotime(date('H:i'));
                    $nextPrayer = null;
                    foreach ($prayers as $p) {
                        if ($p[2]) {
                            $t = strtotime(date('Y-m-d') . ' ' . $p[2]);
                            if ($t > $now && !$nextPrayer) $nextPrayer = $p[1];
                        }
                    }
                    ?>
                    <div class="space-y-1">
                        <?php foreach ($prayers as $prayer): ?>
                        <?php
                        $isNext = ($prayer[1] === $nextPrayer);
                        ?>
                        <div class="flex justify-between items-center px-3 py-2 rounded-lg <?= $isNext ? '' : '' ?>"
                             style="<?= $isNext ? 'background: var(--color-section-alt); border: 1px solid var(--color-border);' : '' ?>">
                            <div class="flex items-center gap-2">
                                <span><?= $prayer[0] ?></span>
                                <span class="text-sm font-medium" style="color: var(--color-text)">
                                    <?= $prayer[1] ?>
                                    <?php if ($isNext): ?>
                                    <span class="text-xs ml-1 px-1.5 py-0.5 rounded-full"
                                          style="background: var(--color-primary); color: white;">
                                        Next
                                    </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <span class="text-sm font-bold" style="color: var(--color-primary)">
                                <?= clean($prayer[2]) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-6 text-center" style="color: var(--color-text-muted)">
                    <p class="text-sm">Prayer times not available today.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Islamic Date + Upcoming Events -->
            <div class="lg:col-span-2 flex flex-col gap-4">

                <!-- Islamic Date -->
                <div class="rounded-xl p-5 shadow-sm"
                     style="background: var(--color-hero-bg); border: 1px solid var(--color-border);">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-xs font-medium mb-1" style="color: rgba(255,255,255,0.6)">
                                Today's Islamic Date
                            </p>
                            <?php if ($islamicDate): ?>
                            <h2 class="text-2xl font-bold" style="color: var(--color-gold)">
                                <?= $islamicDate['day'] ?> <?= $islamicDate['month']['en'] ?>
                                <?= $islamicDate['year'] ?> AH
                            </h2>
                            <p class="text-sm mt-1" style="color: rgba(255,255,255,0.7)">
                                <?= $islamicDate['weekday']['en'] ?>,
                                <?= date('d F Y') ?>
                            </p>
                            <?php if ($islamicDate['holidays'] ?? []): ?>
                            <p class="text-sm mt-2 font-medium" style="color: var(--color-gold)">
                                🌟 <?= implode(', ', $islamicDate['holidays']) ?>
                            </p>
                            <?php endif; ?>
                            <?php else: ?>
                            <h2 class="text-xl font-bold" style="color: var(--color-gold)">
                                <?= date('d F Y') ?>
                            </h2>
                            <p class="text-sm" style="color: rgba(255,255,255,0.6)">
                                Islamic date unavailable
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="text-center">
                            <div class="text-6xl">🕌</div>
                            <p class="arabic-text text-lg mt-1" style="color: var(--color-gold)">
                                السَّلَامُ عَلَيْكُمْ
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <?php if (!empty($upcomingEvents)): ?>
                <div class="rounded-xl p-5 shadow-sm"
                     style="background: var(--color-card); border: 1px solid var(--color-border)">
                    <h3 class="font-bold mb-3 flex items-center gap-2"
                        style="color: var(--color-text)">
                        <span>📅</span> Upcoming Islamic Events
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($upcomingEvents as $event): ?>
                        <?php
                        $daysLeft = (strtotime($event['gregorian_date']) - strtotime(date('Y-m-d'))) / 86400;
                        $daysLeft = max(0, (int)$daysLeft);
                        ?>
                        <div class="flex items-start gap-3 p-3 rounded-lg"
                             style="background: var(--color-section-alt)">
                            <div class="text-center px-3 py-2 rounded-lg flex-shrink-0"
                                 style="background: var(--color-primary); color: white; min-width: 60px;">
                                <p class="text-lg font-bold leading-none">
                                    <?= date('d', strtotime($event['gregorian_date'])) ?>
                                </p>
                                <p class="text-xs opacity-80">
                                    <?= date('M', strtotime($event['gregorian_date'])) ?>
                                </p>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm" style="color: var(--color-text)">
                                    <?= clean($event['title']) ?>
                                </p>
                                <p class="text-xs mt-0.5" style="color: var(--color-text-muted)">
                                    <?= clean($event['hijri_date']) ?>
                                </p>
                                <?php if ($daysLeft === 0): ?>
                                <span class="text-xs font-bold mt-1 inline-block"
                                      style="color: var(--color-primary)">Today! 🎉</span>
                                <?php elseif ($daysLeft === 1): ?>
                                <span class="text-xs font-bold mt-1 inline-block"
                                      style="color: var(--color-gold)">Tomorrow!</span>
                                <?php else: ?>
                                <span class="text-xs mt-1 inline-block"
                                      style="color: var(--color-text-muted)">
                                    In <?= $daysLeft ?> days
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<!-- Recent Wallpaper Section -->
<section class="py-16 px-4 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-primary">Latest Wallpaper</h2>
                <p class="text-gray-500 mt-1">နောက်ဆုံးထွက်ရှိထားသော လစဥ်ထုတ်နံရံကပ်စာစောင်များ</p>
            </div>
            <a href="pages/library.php?category=wallpapers" class="text-primary hover:text-primary-dark font-medium text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($recentWallpapers)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-book-open text-5xl mb-4"></i>
            <p class="text-lg">No wallpaper yet. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($recentWallpapers as $wallpaper): ?>
            <div class="card-hover bg-gray-50 rounded-xl overflow-hidden shadow-sm border border-gray-100">
                <div class="bg-primary h-40 flex items-center justify-center">
                    <?php if ($wallpaper['cover_image']): ?>
                        <img src="<?= UPLOAD_URL ?>covers/<?= clean($wallpaper['cover_image']) ?>"
                             alt="<?= clean($wallpaper['title']) ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-book text-white text-4xl opacity-60"></i>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <span class="text-xs text-gold font-medium"><?= clean($wallpaper['category_name'] ?? 'General') ?></span>
                    <h3 class="font-semibold text-gray-800 text-sm mt-1 line-clamp-2"><?= clean($wallpaper['title']) ?></h3>
                    <p class="text-xs text-gray-500 mt-1"><?= clean($wallpaper['author'] ?? '') ?></p>
                    <a href="pages/book.php?ref=<?= encodeId($wallpaper['id']) ?>"
                       class="mt-3 block text-center bg-primary hover:bg-primary-dark text-white text-xs py-2 rounded-lg transition">
                        View Book
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recent Books Section -->
<section class="py-16 px-4 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-primary">Latest Books</h2>
                <p class="text-gray-500 mt-1">Recently added to the library</p>
            </div>
            <a href="pages/library.php" class="text-primary hover:text-primary-dark font-medium text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($recentBooks)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-book-open text-5xl mb-4"></i>
            <p class="text-lg">No books yet. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($recentBooks as $book): ?>
            <div class="card-hover bg-gray-50 rounded-xl overflow-hidden shadow-sm border border-gray-100">
                <div class="bg-primary h-40 flex items-center justify-center">
                    <?php if ($book['cover_image']): ?>
                        <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                             alt="<?= clean($book['title']) ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-book text-white text-4xl opacity-60"></i>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <span class="text-xs text-gold font-medium"><?= clean($book['category_name'] ?? 'General') ?></span>
                    <h3 class="font-semibold text-gray-800 text-sm mt-1 line-clamp-2"><?= clean($book['title']) ?></h3>
                    <p class="text-xs text-gray-500 mt-1"><?= clean($book['author'] ?? '') ?></p>
                    <a href="pages/book.php?ref=<?= encodeId($book['id']) ?>"
                       class="mt-3 block text-center bg-primary hover:bg-primary-dark text-white text-xs py-2 rounded-lg transition">
                        View Book
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Latest News Section -->
<section class="py-16 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-primary">Latest News Article</h2>
                <p class="text-gray-500 mt-1">Updates from the Muslim community</p>
            </div>
            <a href="pages/news.php" class="text-primary hover:text-primary-dark font-medium text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($recentNews)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-newspaper text-5xl mb-4"></i>
            <p class="text-lg">No news yet. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($recentNews as $article): ?>
            <div class="card-hover bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                <?php if ($article['cover_image']): ?>
                    <img src="<?= UPLOAD_URL ?>news/<?= clean($article['cover_image']) ?>"
                         alt="" class="w-full h-48 object-cover">
                <?php else: ?>
                    <div class="w-full h-48 bg-primary flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-4xl opacity-40"></i>
                    </div>
                <?php endif; ?>
                <div class="p-5">
                    <p class="text-xs text-gray-400 mb-2"><?= formatDate($article['created_at']) ?></p>
                    <h3 class="font-semibold text-gray-800 mb-2"><?= clean($article['title']) ?></h3>
                    <a href="pages/news-detail.php?ref=<?= encodeId($article['id']) ?>"
                       class="text-primary hover:text-primary-dark text-sm font-medium">
                        Read More <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<!-- Latest Videos Section -->
<?php
$latestVideos = $pdo->query("SELECT * FROM videos WHERE is_published = 1 
                              ORDER BY created_at DESC LIMIT 3")->fetchAll();
?>
<?php if (!empty($latestVideos)): ?>
<section class="py-16 px-4" style="background: var(--color-section-alt)">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold" style="color: var(--color-text)">
                    <i class="fab fa-youtube text-red-500 mr-2"></i>Latest Videos
                </h2>
                <p class="mt-1" style="color: var(--color-text-muted)">
                    Watch Islamic panel discussions and lectures
                </p>
            </div>
            <a href="pages/videos.php"
               class="text-sm font-medium"
               style="color: var(--color-primary)">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($latestVideos as $video): ?>
            <div class="rounded-xl overflow-hidden shadow-sm card-hover"
                 style="background: var(--color-card); border: 1px solid var(--color-border)">

                <!-- Thumbnail -->
                <a href="pages/videos.php?watch=<?= $video['id'] ?>" class="block relative group">
                    <img src="https://img.youtube.com/vi/<?= clean($video['youtube_id']) ?>/hqdefault.jpg"
                         alt="<?= clean($video['title']) ?>"
                         class="w-full object-cover"
                         style="height: 180px; object-fit: cover;">
                    <div class="absolute inset-0 flex items-center justify-center"
                         style="background: rgba(0,0,0,0.25); opacity:0; transition:opacity 0.2s;"
                         onmouseover="this.style.opacity='1'"
                         onmouseout="this.style.opacity='0'">
                        <div style="width:50px; height:50px; background:#ff0000;
                                    border-radius:50%; display:flex; align-items:center;
                                    justify-content:center;">
                            <i class="fas fa-play text-white" style="margin-left:3px;"></i>
                        </div>
                    </div>
                </a>

                <!-- Info -->
                <div class="p-4">
                    <h3 class="font-semibold line-clamp-2"
                        style="color: var(--color-text); font-size:14px; line-height:1.4">
                        <a href="pages/videos.php?watch=<?= $video['id'] ?>"
                           style="color: inherit; text-decoration: none;"
                           onmouseover="this.style.color='var(--color-primary)'"
                           onmouseout="this.style.color='inherit'">
                            <?= clean($video['title']) ?>
                        </a>
                    </h3>
                    <?php if ($video['description']): ?>
                    <p class="text-xs mt-1 line-clamp-2"
                       style="color: var(--color-text-muted)">
                        <?= clean(substr($video['description'], 0, 100)) ?>...
                    </p>
                    <?php endif; ?>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs" style="color: var(--color-text-muted)">
                            <?= formatDate($video['created_at']) ?>
                        </span>
                        <a href="pages/videos.php?watch=<?= $video['id'] ?>"
                           style="background:#ff0000; color:white; padding:4px 12px;
                                  border-radius:6px; font-size:12px; font-weight:600;
                                  text-decoration:none;">
                            <i class="fas fa-play mr-1"></i>Watch
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>