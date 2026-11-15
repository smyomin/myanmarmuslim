<?php
require_once 'config.php';

// Get all published categories
function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at ASC");
    return $stmt->fetchAll();
}

// Get recent books
function getRecentBooks($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                           FROM books b 
                           LEFT JOIN categories c ON b.category_id = c.id 
                           WHERE b.is_published = 1 AND c.id<>7
                           ORDER BY b.created_at DESC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Get recent wallpaper
function getRecentWallpapers($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                           FROM books b 
                           LEFT JOIN categories c ON b.category_id = c.id 
                           WHERE c.id=7 AND b.is_published = 1 
                           ORDER BY b.created_at DESC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Get recent news
function getRecentNews($pdo, $limit = 5) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE is_published = 1 
                           ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Check if email exists
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM visitor_emails WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Save visitor email
function saveVisitorEmail($pdo, $email, $name = '') {
    if (!emailExists($pdo, $email)) {
        $stmt = $pdo->prepare("INSERT INTO visitor_emails (email, name) VALUES (?, ?)");
        $stmt->execute([$email, $name]);
    }
    return true;
}

// Sanitize output
function clean($str) {
    return htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
}

// Format date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Generate slug
function makeSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('Invalid request. Please go back and try again.');
    }
    return true;
}

// Output CSRF hidden field
function csrfField() {
    echo '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Simple rate limiting using sessions
function checkRateLimit($action, $maxAttempts = 5, $perMinutes = 10) {
    $key     = 'rate_' . $action;
    $timeKey = 'rate_time_' . $action;
    $now     = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key]     = 0;
        $_SESSION[$timeKey] = $now;
    }

    // Reset if time window passed
    if ($now - $_SESSION[$timeKey] > ($perMinutes * 60)) {
        $_SESSION[$key]     = 0;
        $_SESSION[$timeKey] = $now;
    }

    $_SESSION[$key]++;

    if ($_SESSION[$key] > $maxAttempts) {
        return false; // Rate limited
    }

    return true;
}

// Encode ID to secure token
function encodeId($id) {
    $secret = 'mm_islamic_lib_2024'; // Secret key - keep this safe
    $token  = base64_encode($id . ':' . hash_hmac('sha256', $id, $secret));
    // Make URL safe
    return rtrim(strtr($token, '+/', '-_'), '=');
}

// Decode token back to ID
function decodeId($token) {
    $secret = 'mm_islamic_lib_2024'; // Same secret key
    // Reverse URL safe encoding
    $token   = strtr($token, '-_', '+/');
    $padding = strlen($token) % 4;
    if ($padding) {
        $token .= str_repeat('=', 4 - $padding);
    }
    $decoded = base64_decode($token);
    if (!$decoded) return null;

    $parts = explode(':', $decoded, 2);
    if (count($parts) !== 2) return null;

    $id   = $parts[0];
    $hash = $parts[1];

    // Verify the token is valid and not tampered
    if (!hash_equals(hash_hmac('sha256', $id, $secret), $hash)) {
        return null;
    }

    return (int)$id;
}

// Extract YouTube ID from any YouTube URL format
function getYoutubeId($url) {
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
        '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Save multiple gallery images for a news article (max 15 per article)
function saveNewsGallery($pdo, $newsId, $files, $maxTotal = 15) {
    $result = ['saved' => 0, 'skipped' => 0];
    if (empty($files['name'][0])) return $result;

    $count = $pdo->prepare("SELECT COUNT(*) FROM news_images WHERE news_id = ?");
    $count->execute([$newsId]);
    $room = $maxTotal - (int)$count->fetchColumn();

    $maxSort = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM news_images WHERE news_id = ?");
    $maxSort->execute([$newsId]);
    $sort = (int)$maxSort->fetchColumn();

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $insert  = $pdo->prepare("INSERT INTO news_images (news_id, image, sort_order) VALUES (?, ?, ?)");

    foreach ($files['name'] as $i => $name) {
        if ($name === '') continue;
        if ($files['error'][$i] !== UPLOAD_ERR_OK || $result['saved'] >= $room) {
            $result['skipped']++;
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)
            || $files['size'][$i] > 5 * 1024 * 1024
            || !@getimagesize($files['tmp_name'][$i])) {
            $result['skipped']++;
            continue;
        }
        $newName = time() . '_g' . $newsId . '_' . $i . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (move_uploaded_file($files['tmp_name'][$i], UPLOAD_PATH . 'news/' . $newName)) {
            $sort++;
            $insert->execute([$newsId, $newName, $sort]);
            $result['saved']++;
        } else {
            $result['skipped']++;
        }
    }
    return $result;
}