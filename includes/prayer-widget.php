<?php
function getPrayerTimesWidget($pdo, $city = 'Yangon') {
    $today  = date('j');
    $month  = date('n');

    // Try database first (PDF data)
    $stmt = $pdo->prepare("SELECT * FROM prayer_times 
                           WHERE city = ? AND month = ? AND day = ?");
    $stmt->execute([$city, $month, $today]);
    $times = $stmt->fetch();

    // If not in DB, use API fallback
    if (!$times) {
        $times = getPrayerTimesFromAPI();
    }

    return $times;
}

function getPrayerTimesFromAPI() {
    // Yangon coordinates: 16.8052800, 96.1561100 // Method 7 = University of Tehran (ID 7)
    $url = "http://api.aladhan.com/v1/timingsByCity/" . date('d-m-Y') .
           "?city=Yangon&country=Myanmar&method=7";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $res = @file_get_contents($url, false, $ctx);

    if (!$res) return null;

    $data = json_decode($res, true);
    if (!$data || $data['code'] !== 200) return null;

    $t = $data['data']['timings'];
    return [
        'imsaak'  => $t['Imsak']   ?? '',
        'fajr'    => $t['Fajr']    ?? '',
        'sunrise' => $t['Sunrise'] ?? '',
        'dhuhr'   => $t['Dhuhr']   ?? '',
        'sunset'  => $t['Sunset']  ?? '',
        'maghrib' => $t['Maghrib'] ?? '',
        'from_api' => true
    ];
}

function getIslamicDate() {
    $url = "http://api.aladhan.com/v1/gToH/" . date('d-m-Y');
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $res = @file_get_contents($url, false, $ctx);

    if (!$res) return null;

    $data = json_decode($res, true);
    if (!$data || $data['code'] !== 200) return null;

    return $data['data']['hijri'] ?? null;
}

function getUpcomingEvents($pdo, $limit = 2) {
    $stmt = $pdo->prepare("SELECT * FROM islamic_events 
                           WHERE gregorian_date >= CURDATE() 
                           AND is_published = 1
                           ORDER BY gregorian_date ASC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getActiveAnnouncement($pdo) {
    $stmt = $pdo->query("SELECT * FROM announcements 
                         WHERE is_active = 1 
                         AND (expires_at IS NULL OR expires_at >= CURDATE())
                         ORDER BY created_at DESC 
                         LIMIT 1");
    return $stmt->fetch();
}