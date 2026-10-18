<?php
require_once 'auth.php';
require_once '../includes/functions.php';

// Date filter
$range = $_GET['range'] ?? '30';
$days  = in_array($range, ['7','30','90','365']) ? (int)$range : 30;

// Stats for selected range
$visits   = $pdo->prepare("SELECT COUNT(*) FROM visitor_logs WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$visits->execute([$days]);
$totalVisits = $visits->fetchColumn();

$unique = $pdo->prepare("SELECT COUNT(DISTINCT ip_address) FROM visitor_logs WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$unique->execute([$days]);
$uniqueVisitors = $unique->fetchColumn();

// By country
$countries = $pdo->prepare("SELECT country, country_code, COUNT(*) as visits,
                             COUNT(DISTINCT ip_address) as unique_visitors
                             FROM visitor_logs
                             WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                             AND country != 'Local'
                             GROUP BY country, country_code
                             ORDER BY visits DESC");
$countries->execute([$days]);
$byCountry = $countries->fetchAll();

// Daily breakdown
$daily = $pdo->prepare("SELECT DATE(visited_at) as date,
                         COUNT(*) as visits,
                         COUNT(DISTINCT ip_address) as unique_visitors
                         FROM visitor_logs
                         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                         GROUP BY DATE(visited_at)
                         ORDER BY date ASC");
$daily->execute([$days]);
$dailyData = $daily->fetchAll();

// Top pages
$pages = $pdo->prepare("SELECT page, COUNT(*) as visits
                         FROM visitor_logs
                         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                         GROUP BY page
                         ORDER BY visits DESC
                         LIMIT 20");
$pages->execute([$days]);
$topPages = $pages->fetchAll();

// Recent visitors
$recent = $pdo->query("SELECT * FROM visitor_logs 
                        ORDER BY visited_at DESC LIMIT 20")->fetchAll();

// Chart data
$chartLabels  = [];
$chartVisits  = [];
$chartUnique  = [];
$dateMap      = [];
foreach ($dailyData as $row) {
    $dateMap[$row['date']] = $row;
}
for ($i = $days - 1; $i >= 0; $i--) {
    $date           = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[]  = date('M d', strtotime($date));
    $chartVisits[]  = $dateMap[$date]['visits']          ?? 0;
    $chartUnique[]  = $dateMap[$date]['unique_visitors'] ?? 0;
}

$maxCountry = !empty($byCountry) ? $byCountry[0]['visits'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                        gold:    { DEFAULT: '#c9a84c' }
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
            <a href="notify.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-primary transition">
                <i class="fas fa-bell w-5"></i>Send Notification
            </a>
            <a href="analytics.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 text-primary font-medium">
                <i class="fas fa-chart-bar w-5"></i>Full Analytics
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-chart-bar text-primary mr-2"></i>Analytics
            </h1>
            <!-- Date Range Filter -->
            <div class="flex gap-2">
                <?php foreach (['7' => '7 Days', '30' => '30 Days', '90' => '90 Days', '365' => '1 Year'] as $val => $label): ?>
                <a href="analytics.php?range=<?= $val ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition
                          <?= $range == $val ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-green-50 shadow-sm' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-primary">
                <p class="text-sm text-gray-500">Total Visits</p>
                <p class="text-3xl font-bold text-primary"><?= number_format($totalVisits) ?></p>
                <p class="text-xs text-gray-400">last <?= $days ?> days</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-teal-500">
                <p class="text-sm text-gray-500">Unique Visitors</p>
                <p class="text-3xl font-bold text-teal-600"><?= number_format($uniqueVisitors) ?></p>
                <p class="text-xs text-gray-400">last <?= $days ?> days</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-indigo-500">
                <p class="text-sm text-gray-500">Countries</p>
                <p class="text-3xl font-bold text-indigo-600"><?= count($byCountry) ?></p>
                <p class="text-xs text-gray-400">different countries</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-gold">
                <p class="text-sm text-gray-500">Avg Per Day</p>
                <p class="text-3xl font-bold text-yellow-600">
                    <?= $days > 0 ? round($totalVisits / $days) : 0 ?>
                </p>
                <p class="text-xs text-gray-400">visits per day</p>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-bold text-gray-800 mb-4">Visits Over Time</h2>
            <canvas id="analyticsChart" height="100"></canvas>
        </div>

        <!-- Countries + Pages -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Countries -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-globe text-primary mr-2"></i>Visitors by Country
                </h2>
                <?php if (empty($byCountry)): ?>
                    <p class="text-gray-400 text-sm text-center py-8">No data yet.</p>
                <?php else: ?>
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    <?php foreach ($byCountry as $i => $country): ?>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 w-4"><?= $i+1 ?></span>
                                <?php if ($country['country_code']): ?>
                                <img src="https://flagcdn.com/20x15/<?= strtolower(clean($country['country_code'])) ?>.png"
                                     alt="" class="rounded-sm"
                                     onerror="this.style.display='none'">
                                <?php endif; ?>
                                <span class="text-sm text-gray-700"><?= clean($country['country']) ?></span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-primary"><?= number_format($country['visits']) ?></span>
                                <span class="text-xs text-gray-400 ml-1">(<?= $country['unique_visitors'] ?> unique)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-primary h-1.5 rounded-full"
                                 style="width:<?= round(($country['visits'] / $maxCountry) * 100) ?>%">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Top Pages -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-file-alt text-primary mr-2"></i>Top Pages
                </h2>
                <?php if (empty($topPages)): ?>
                    <p class="text-gray-400 text-sm text-center py-8">No data yet.</p>
                <?php else: ?>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    <?php
                    $maxPage = $topPages[0]['visits'];
                    foreach ($topPages as $pg):
                        $pageName = $pg['page'];
                        $pageName = preg_replace('/\?.*/', '', $pageName);
                        $pageName = basename($pageName) ?: 'Homepage';
                    ?>
                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 truncate font-medium">
                                <?= clean($pageName) ?>
                            </p>
                            <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                <div class="bg-primary h-1 rounded-full"
                                     style="width:<?= round(($pg['visits'] / $maxPage) * 100) ?>%">
                                </div>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-primary flex-shrink-0">
                            <?= number_format($pg['visits']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Visitors -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">
                    <i class="fas fa-users text-primary mr-2"></i>Recent Visitors
                </h2>
            </div>
            <?php if (empty($recent)): ?>
                <p class="text-gray-400 text-sm text-center py-8">No visitors yet.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">IP</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Country</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">City</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Page</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recent as $visitor): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">
                                <?= clean($visitor['ip_address']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <?php if ($visitor['country_code']): ?>
                                    <img src="https://flagcdn.com/20x15/<?= strtolower(clean($visitor['country_code'])) ?>.png"
                                         alt="" class="rounded-sm"
                                         onerror="this.style.display='none'">
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-700">
                                        <?= clean($visitor['country']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 hidden md:table-cell">
                                <?= clean($visitor['city']) ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 hidden md:table-cell truncate max-w-xs">
                                <?= clean(basename(preg_replace('/\?.*/', '', $visitor['page']))) ?: '/' ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                <?= date('M d, H:i', strtotime($visitor['visited_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
const ctx = document.getElementById('analyticsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            {
                label: 'Total Visits',
                data: <?= json_encode($chartVisits) ?>,
                backgroundColor: 'rgba(26,107,60,0.7)',
                borderColor: '#1a6b3c',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Unique Visitors',
                data: <?= json_encode($chartUnique) ?>,
                backgroundColor: 'rgba(201,168,76,0.7)',
                borderColor: '#c9a84c',
                borderWidth: 1,
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>