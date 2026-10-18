<?php
require_once 'auth.php';
require_once '../includes/functions.php';

$success = '';
$error   = '';

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city    = trim($_POST['city'] ?? 'Yangon');
    $month   = (int)($_POST['month'] ?? 0);
    $day     = (int)($_POST['day'] ?? 0);
    $imsaak  = trim($_POST['imsaak'] ?? '');
    $fajr    = trim($_POST['fajr'] ?? '');
    $sunrise = trim($_POST['sunrise'] ?? '');
    $dhuhr   = trim($_POST['dhuhr'] ?? '');
    $sunset  = trim($_POST['sunset'] ?? '');
    $maghrib = trim($_POST['maghrib'] ?? '');

    if (!$month || !$day) {
        $error = 'Month and day are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO prayer_times 
            (city, month, day, imsaak, fajr, sunrise, dhuhr, sunset, maghrib)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            imsaak=VALUES(imsaak), fajr=VALUES(fajr), sunrise=VALUES(sunrise),
            dhuhr=VALUES(dhuhr), sunset=VALUES(sunset), maghrib=VALUES(maghrib)");
        $stmt->execute([$city, $month, $day, $imsaak, $fajr, $sunrise, $dhuhr, $sunset, $maghrib]);
        $success = 'Prayer times saved!';
    }
}

// Get current month data
$filterMonth = (int)($_GET['month'] ?? date('n'));
$filterCity  = $_GET['city'] ?? 'Yangon';
$times       = $pdo->prepare("SELECT * FROM prayer_times 
                               WHERE city = ? AND month = ? 
                               ORDER BY day ASC");
$times->execute([$filterCity, $filterMonth]);
$timesList = $times->fetchAll();

$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$cities = ['Yangon','Mandalay','Naypyidaw','Mawlamyine','Taunggyi'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayer Times - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                gold: { DEFAULT: '#c9a84c' }
            }}}
        }
    </script>
</head>
<body class="bg-gray-100">

<nav class="bg-primary text-white px-6 py-4 flex justify-between items-center shadow">
    <div class="flex items-center gap-3">
        <img src="<?= SITE_URL ?>/assets/images/logowhite.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
        <span class="font-bold text-lg">Myanmar Muslims Admin</span>
    </div>
    <div class="flex gap-3">
        <a href="dashboard.php" class="text-green-200 hover:text-white text-sm">
            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
        </a>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">
            Logout
        </a>
    </div>
</nav>

<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            🕌 Manage Prayer Times
        </h1>
        <a href="import-prayers.php"
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-file-import mr-2"></i>Import from PDF
        </a>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= clean($success) ?>
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex gap-4 flex-wrap">
        <form method="GET" class="flex gap-3 flex-wrap">
            <select name="city" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                <?php foreach ($cities as $c): ?>
                <option value="<?= $c ?>" <?= $filterCity === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
            <select name="month" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                <?php foreach ($months as $i => $m): ?>
                <option value="<?= $i+1 ?>" <?= $filterMonth === $i+1 ? 'selected' : '' ?>>
                    <?= $m ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm">
                Filter
            </button>
        </form>
        <div class="text-sm text-gray-500 flex items-center">
            <?= count($timesList) ?> days loaded for <?= $filterCity ?> - <?= $months[$filterMonth-1] ?>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Day</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Imsaak</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Fajr</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Sunrise</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Dhuhr</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Sunset</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-600">Maghrib</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($timesList)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        No prayer times for this month. 
                        <a href="import-prayers.php" class="text-primary underline">Import from PDF</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($timesList as $t): ?>
                <tr class="hover:bg-gray-50 <?= $t['day'] == date('j') && $filterMonth == date('n') ? 'bg-green-50' : '' ?>">
                    <td class="px-3 py-2 font-bold text-primary"><?= $t['day'] ?></td>
                    <td class="px-3 py-2"><?= clean($t['imsaak']) ?></td>
                    <td class="px-3 py-2"><?= clean($t['fajr']) ?></td>
                    <td class="px-3 py-2"><?= clean($t['sunrise']) ?></td>
                    <td class="px-3 py-2"><?= clean($t['dhuhr']) ?></td>
                    <td class="px-3 py-2"><?= clean($t['sunset']) ?></td>
                    <td class="px-3 py-2"><?= clean($t['maghrib']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Single Day -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4">Add / Update Single Day</h2>
        <form method="POST" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">City</label>
                <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                    <?php foreach ($cities as $c): ?>
                    <option value="<?= $c ?>" <?= $filterCity === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Month</label>
                <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                    <?php foreach ($months as $i => $m): ?>
                    <option value="<?= $i+1 ?>" <?= $filterMonth === $i+1 ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Day</label>
                <input type="number" name="day" min="1" max="31"
                       value="<?= date('j') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Imsaak</label>
                <input type="text" name="imsaak" placeholder="05:09 AM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Fajr</label>
                <input type="text" name="fajr" placeholder="5:19 AM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Sunrise</label>
                <input type="text" name="sunrise" placeholder="6:34 AM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Dhuhr</label>
                <input type="text" name="dhuhr" placeholder="12:09 PM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Sunset/Asr</label>
                <input type="text" name="sunset" placeholder="5:43 PM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600 mb-1 block">Maghrib</label>
                <input type="text" name="maghrib" placeholder="6:00 PM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-2 rounded-lg text-sm transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>