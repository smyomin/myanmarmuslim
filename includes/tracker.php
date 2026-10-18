<?php
function trackVisitor($pdo) {
    // Don't track admin pages
    $currentPage = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($currentPage, '/admin/') !== false) return;

    // Don't track bots
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $bots = ['bot', 'crawler', 'spider', 'slurp', 'baiduspider',
             'googlebot', 'bingbot', 'yandex', 'facebookexternalhit'];
    foreach ($bots as $bot) {
        if (stripos($userAgent, $bot) !== false) return;
    }

    // Get real IP address
    $ip = 'Unknown';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip  = trim($ips[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // Skip localhost IPs
    if (in_array($ip, ['127.0.0.1', '::1', 'Unknown'])) {
        $ip      = '127.0.0.1';
        $country = 'Local';
        $city    = 'Localhost';
        $code    = 'LO';
    } else {
        // Get country from IP using free API
        $geo     = getCountryFromIP($ip);
        $country = $geo['country'];
        $city    = $geo['city'];
        $code    = $geo['code'];
    }

    $page    = substr($currentPage, 0, 255);
    $agent   = substr($userAgent, 0, 500);
    $referer = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500);

    // Only log once per session per page
    $sessionKey = 'tracked_' . md5($ip . $page . date('Y-m-d'));
    if (isset($_SESSION[$sessionKey])) return;
    $_SESSION[$sessionKey] = true;

    try {
        $stmt = $pdo->prepare("INSERT INTO visitor_logs 
            (ip_address, country, country_code, city, page, user_agent, referer) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ip, $country, $code, $city, $page, $agent, $referer]);
    } catch (Exception $e) {
        // Silently fail - don't break the page
    }
}

function getCountryFromIP($ip) {
    $default = ['country' => 'Unknown', 'code' => '', 'city' => ''];

    try {
        // Using free ip-api.com (no key needed, 45 req/min)
        $url      = "http://ip-api.com/json/{$ip}?fields=country,countryCode,city,status";
        $context  = stream_context_create([
            'http' => ['timeout' => 2]
        ]);
        $response = @file_get_contents($url, false, $context);

        if (!$response) return $default;

        $data = json_decode($response, true);

        if ($data && $data['status'] === 'success') {
            return [
                'country' => $data['country']     ?? 'Unknown',
                'code'    => $data['countryCode'] ?? '',
                'city'    => $data['city']         ?? ''
            ];
        }
    } catch (Exception $e) {
        // Silently fail
    }

    return $default;
}