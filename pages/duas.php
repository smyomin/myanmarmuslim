<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Duas & Supplications';

$stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                       FROM books b 
                       LEFT JOIN categories c ON b.category_id = c.id 
                       WHERE b.type = 'dua' AND b.is_published = 1 
                       ORDER BY b.created_at DESC");
$stmt->execute();
$duas = $stmt->fetchAll();

require_once '../includes/seo.php';
$seoOptions = [
    'title'       => 'Duas & Supplications - Myanmar Muslims Library',
    'description' => 'Download free Islamic duas and supplications in Myanmar language. Daily prayers and special duas.',
    'keywords'    => 'duas myanmar, islamic prayers myanmar, supplications download myanmar',
    'url'         => SITE_URL . '/pages/duas.php',
];

include '../includes/header.php';
?>

<div class="bg-primary text-white py-12 px-4">
    <div class="max-w-7xl mx-auto text-center">
        <p class="arabic-text text-3xl text-gold mb-3">ادْعُونِي أَسْتَجِبْ لَكُمْ</p>
        <p class="text-green-100 text-sm mb-4">"ငါ့ကို တိုင်တည်ဆုတောင်းကြလော့၊ ငါသည် သင်တို့အား ပြန်လည်ထူးတော်မူအံ့။" — ကုရ်အာန် (၄၀:၆၀)</p>
        <h1 class="text-3xl font-bold">ဒိုအာနှင့်အအ်မာလ်များ</h1>
        <p class="text-green-100 mt-2">အခြေအနေအချိန်အခါအရပ်ရပ်အတွက် အစ္စလာမ့်ဆုတောင်းဒုအာများ</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-10">
    <?php if (empty($duas)): ?>
    <div class="text-center py-20 text-gray-400">
        <i class="fas fa-hands text-6xl mb-4"></i>
        <p class="text-xl font-medium">No duas uploaded yet</p>
        <p class="text-sm mt-2">Check back soon</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($duas as $dua): ?>
        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 card-hover group">
            <a href="book.php?ref=<?= encodeId($dua['id']) ?>">
                <div class="bg-primary h-40 flex items-center justify-center overflow-hidden">
                    <?php if ($dua['cover_image']): ?>
                        <img src="<?= UPLOAD_URL ?>covers/<?= clean($dua['cover_image']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    <?php else: ?>
                        <i class="fas fa-hands text-white text-4xl opacity-40"></i>
                    <?php endif; ?>
                </div>
            </a>
            <div class="p-4">
                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2">
                    <a href="book.php?ref=<?= encodeId($dua['id']) ?>" class="hover:text-primary">
                        <?= clean($dua['title']) ?>
                    </a>
                </h3>
                <?php if ($dua['author']): ?>
                <p class="text-xs text-gray-400 mt-1"><?= clean($dua['author']) ?></p>
                <?php endif; ?>
                <a href="book.php?ref=<?= encodeId($dua['id']) ?>"
                   class="mt-3 block text-center bg-primary hover:bg-primary-dark text-white text-xs py-2 rounded-lg transition">
                    View Dua
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>