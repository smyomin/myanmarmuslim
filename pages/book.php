<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$ref  = trim($_GET['ref'] ?? '');
$id   = $ref ? decodeId($ref) : 0;
$book = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name, c.slug as category_slug 
                           FROM books b 
                           LEFT JOIN categories c ON b.category_id = c.id 
                           WHERE b.id = ? AND b.is_published = 1");
    $stmt->execute([$id]);
    $book = $stmt->fetch();
}

if (!$book) {
    header('Location: library.php');
    exit;
}

$pageTitle     = $book['title'];
$downloadError = '';
if (!isset($_GET['download'])) {
    unset($_SESSION['download_email']);
    unset($_SESSION['download_book']);
}
$canDownload   = isset($_SESSION['download_email']) && isset($_SESSION['download_book']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
     if ($name === '') {
        $downloadError = 'Name is required.';
    } 
    elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $downloadError = 'Please enter a valid email address.';
    } 
    
    if ($downloadError === '') {
       if ($email !== '') {
                saveVisitorEmail($pdo, $email);       
            }
        $stmt = $pdo->prepare("INSERT INTO download_logs (book_id, email, name, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$book['id'], $email, $name, $phone]);
        $pdo->prepare("UPDATE books SET downloads = downloads + 1 WHERE id = ?")->execute([$id]);
        $_SESSION['download_email'] = $email;
        $_SESSION['download_book']  = $id;
        header("Location: book.php?ref=" . encodeId($id) . "&download=1");
        exit;
    }
}

require_once '../includes/seo.php';
$bookUrl    = SITE_URL . '/pages/book.php?ref=' . encodeId($book['id']);
$seoOptions = [
    'title'       => clean($book['title']),
    'description' => $book['description']
        ? clean(substr($book['description'], 0, 155))
        : 'Download ' . clean($book['title']) . ' free from Myanmar Muslims Islamic Library.',
    'keywords'    => clean($book['title']) . ', myanmar islamic book, free download, ' . clean($book['category_name'] ?? ''),
    'image'       => $book['cover_image'] ? UPLOAD_URL . 'covers/' . clean($book['cover_image']) : null,
    'url'         => $bookUrl,
    'type'        => 'book',
    'json_ld'     => jsonLdBook($book, $bookUrl)
];
include '../includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-10">

    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-400 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-primary">Home</a>
        <span class="mx-2">/</span>
        <a href="library.php" class="hover:text-primary">Library</a>
        <?php if ($book['category_name']): ?>
        <span class="mx-2">/</span>
        <a href="library.php?category=<?= clean($book['category_slug']) ?>" class="hover:text-primary">
            <?= clean($book['category_name']) ?>
        </a>
        <?php endif; ?>
        <span class="mx-2">/</span>
        <span class="text-gray-600"><?= clean($book['title']) ?></span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Cover and Stats -->
        <div class="md:col-span-1">
            <div class="bg-primary rounded-xl h-72 flex items-center justify-center overflow-hidden shadow-lg">
                <?php if ($book['cover_image']): ?>
                    <img src="<?= UPLOAD_URL ?>covers/<?= clean($book['cover_image']) ?>"
                         alt="<?= clean($book['title']) ?>"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <i class="fas fa-book text-white text-7xl opacity-30"></i>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="mt-4 bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex justify-between text-sm py-2 border-b border-gray-100">
                    <span class="text-gray-500">Category</span>
                    <span class="font-medium text-gray-800">
                        <?= clean($book['category_name'] ?? 'General') ?>
                    </span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b border-gray-100">
                    <span class="text-gray-500">Language</span>
                    <span class="font-medium text-gray-800"><?= clean($book['language']) ?></span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b border-gray-100">
                    <span class="text-gray-500">Type</span>
                    <span class="font-medium text-gray-800 capitalize"><?= clean($book['type']) ?></span>
                </div>
                <div class="flex justify-between text-sm py-2">
                    <span class="text-gray-500">Downloads</span>
                    <span class="font-medium text-gray-800"><?= $book['downloads'] ?></span>
                </div>
            </div>
        </div>

        <!-- Book Details -->
        <div class="md:col-span-2">
            <span class="text-xs text-gold font-semibold uppercase tracking-wide">
                <?= clean($book['category_name'] ?? '') ?>
            </span>
            <h1 class="text-2xl font-bold text-gray-900 mt-1 mb-2">
                <?= clean($book['title']) ?>
            </h1>

            <?php if ($book['author']): ?>
            <p class="text-gray-500 mb-4">
                <i class="fas fa-user-pen mr-2 text-primary"></i>
                By <strong><?= clean($book['author']) ?></strong>
            </p>
            <?php endif; ?>

            <?php if ($book['description']): ?>
            <div class="text-gray-600 text-sm leading-relaxed mb-6">
                <?= nl2br(clean($book['description'])) ?>
            </div>
            <?php endif; ?>

            <!-- Read Online + Share -->
            <div class="mb-6 flex flex-wrap gap-3">
                <a href="read.php?ref=<?= clean($ref) ?>"
                target="_blank"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-eye"></i> အွန်လိုင်းတွင် တိုက်ရိုက်ဖတ်ရှုရန်
                </a>

                <!-- Facebook Share Button -->
                <button onclick="shareToFacebook()"
                        class="inline-flex items-center gap-2 text-white font-semibold px-6 py-3 rounded-lg transition"
                        style="background:#1877f2;">
                    <i class="fab fa-facebook-f"></i> Facebook တွင် မျှဝေရန်
                </button>
            </div>

            <script>
            function shareToFacebook() {
                const shortUrl  = '<?= SITE_URL ?>/b/<?= $book['id'] ?>';
                const shareUrl  = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shortUrl);
                window.open(shareUrl, 'facebook-share', 'width=600,height=400,left=' +
                    (window.innerWidth/2 - 300) + ',top=' + (window.innerHeight/2 - 200));
            }
            </script>

            <!-- Download Section -->
            <?php if ($canDownload): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-5">
                <p class="text-green-700 font-medium mb-3">
                    <i class="fas fa-check-circle mr-2"></i>
                    ကျေးဇူးပါ! သင်ဒေါင်းလုပ် လုပ်နိုင်ပါပြီ.
                </p>
                <a href="download.php?ref=<?= encodeId($book['id']) ?>"
                   class="inline-flex items-center gap-2 bg-primary hover:bg-primary-dark text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-download"></i> PDF ဖိုင်ဒေါင်းလုပ်လုပ် ရန်
                </a>
            </div>

            <?php else: ?>
            <!-- Email Gate -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                <h3 class="font-semibold text-gray-800 mb-1">
                    <i class="fas fa-lock text-gold mr-2"></i>
                    သင်၏ အီးမေးလ်ကို ထည့်သွင်းပါ
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    အခမဲ့ ဒေါင်းလုဒ် ရယူနိုင်ပါသည်။ စာအုပ်အသစ်များ ထွက်ရှိပါက အကြောင်းကြားပေးနိုင်ရန် သင်၏ အီးမေးလ်လိပ်စာ လိုအပ်ပါသည်။ အီးမေးလ်လိပ်စာ မရှိပါက အမည်မဖြစ်မနေထည့်ပေးပါ။
                </p>

                <?php if ($downloadError): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= clean($downloadError) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-3">
                    
                    <?php csrfField(); ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" name="name"
                               placeholder="အမည်" required
                               class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <input type="text" name="phone"
                               placeholder="ဖုန်းနံပါတ်"
                               class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <input type="email" name="email"
                               placeholder="အီးမေးလ်လိပ်စာ" 
                               class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                    </div>
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-download mr-2"></i>Get Free Download
                    </button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>