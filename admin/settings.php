<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once '../includes/functions.php';
require_once '../includes/theme.php';

$success = '';
$themes  = getThemes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'default';
    if (array_key_exists($theme, $themes)) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_theme'");
        $stmt->execute([$theme]);
        $success = 'Site theme updated successfully!';
    }
}

$currentTheme = getSiteTheme($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                        gold: { DEFAULT: '#c9a84c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">

<nav class="bg-primary text-white px-6 py-4 flex justify-between items-center shadow">
    <div class="flex items-center gap-3">
        <img src="<?= SITE_URL ?>/assets/images/logowhite.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
        <span class="font-bold text-lg">Myanmar Muslims Admin</span>
    </div>
    <div class="flex items-center gap-3">
        <a href="dashboard.php" class="text-green-200 hover:text-white text-sm">
            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
        </a>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
    </div>
</nav>

<div class="flex">
    <aside class="w-64 min-h-screen bg-white shadow-md hidden md:block">
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a>
            <a href="books.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-book w-5"></i>Manage Books
            </a>
            <a href="news.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-newspaper w-5"></i>Manage News
            </a>
            <a href="subscribers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-envelope w-5"></i>Subscribers
            </a>
            <a href="analytics.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-chart-bar w-5"></i>Analytics
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-cog w-5"></i>Settings
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-cog text-primary mr-2"></i>Site Settings
            </h1>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-gray-800 mb-2">
                    <i class="fas fa-palette text-primary mr-2"></i>Site Theme
                </h2>
                <p class="text-sm text-gray-500 mb-6">
                    Choose the default theme for all visitors. Visitors can still override with their own preference.
                </p>

                <form method="POST">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <?php foreach ($themes as $key => $theme): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="theme" value="<?= $key ?>"
                                   <?= $currentTheme === $key ? 'checked' : '' ?>
                                   class="sr-only peer">
                            <div class="border-2 rounded-xl p-4 transition
                                        peer-checked:border-primary peer-checked:bg-green-50
                                        hover:border-gray-300 border-gray-200">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-full border-2 border-white shadow-md"
                                         style="background: <?= $theme['color'] ?>">
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            <?= $theme['icon'] ?> <?= $theme['name'] ?>
                                        </p>
                                        <?php if ($currentTheme === $key): ?>
                                        <span class="text-xs text-primary font-medium">Current theme</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Theme preview bar -->
                                <div class="h-3 rounded-full" style="background: <?= $theme['color'] ?>"></div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Theme
                    </button>
                </form>
            </div>

        </div>
    </main>
</div>

</body>
</html>