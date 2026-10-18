<?php
function getActiveTheme($pdo) {
    // Admin controls theme only - no visitor override
    return getSiteTheme($pdo);
}

function getSiteTheme($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'active_theme'");
        $row  = $stmt->fetch();
        return $row ? $row['setting_value'] : 'default';
    } catch (Exception $e) {
        return 'default';
    }
}

function getThemes() {
    return [
        'default'    => ['name' => 'Default',      'color' => '#1a6b3c', 'icon' => '🌿'],
        'ramadan'    => ['name' => 'Ramadan',       'color' => '#b8860b', 'icon' => '🌙'],
        'muharram'   => ['name' => 'Muharram',      'color' => '#2d2d2d', 'icon' => '🖤'],
        'rabi'       => ['name' => 'Rabi (Spring)', 'color' => '#0d9488', 'icon' => '🌱'],
        'dhulhijjah' => ['name' => 'Dhul Hijjah',  'color' => '#92400e', 'icon' => '🕋'],
        'dark'       => ['name' => 'Dark Mode',     'color' => '#111827', 'icon' => '🌑'],
    ];
}