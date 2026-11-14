<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
$categories = getCategories($pdo);
$recentBooks = getRecentBooks($pdo, 4);
$recentWallpapers = getRecentWallpapers($pdo, 4);
$recentNews = getRecentNews($pdo, 3);

?>
<body bgcolor="#20487B">
    <table align="center"><tr><td>
<img src="<?= SITE_URL ?>/assets/images/maintenance.jpeg" alt="Maintenance" class="img-fluid mx-auto d-block" style="max-width: 100%; height: auto;">


    </td></tr></table>
</body>