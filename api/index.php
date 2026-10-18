<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// CORS headers - allow app to connect
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Rate limiting
session_start();

// Router
$endpoint = $_GET['endpoint'] ?? '';
$method   = $_SERVER['REQUEST_METHOD'];

function sendResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $code === 200,
        'data'    => $data,
        'time'    => date('c')
    ]);
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error'   => $message,
        'time'    => date('c')
    ]);
    exit;
}

switch ($endpoint) {

    // ==========================================
    // GET /api/?endpoint=books
    // ==========================================
    case 'books':
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = (int)($_GET['per_page'] ?? 12);
        $perPage   = min($perPage, 50); // max 50
        $offset    = ($page - 1) * $perPage;
        $category  = trim($_GET['category'] ?? '');
        $type      = trim($_GET['type'] ?? '');
        $search    = trim($_GET['search'] ?? '');

        $where  = ["b.is_published = 1"];
        $params = [];

        if ($category) {
            $where[]  = "c.slug = ?";
            $params[] = $category;
        }
        if ($type) {
            $where[]  = "b.type = ?";
            $params[] = $type;
        }
        if ($search) {
            $where[]  = "(b.title LIKE ? OR b.author LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSQL = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM books b 
                                    LEFT JOIN categories c ON b.category_id = c.id 
                                    WHERE $whereSQL");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT 
                b.id, b.title, b.description, b.author, b.language,
                b.type, b.cover_image, b.downloads, b.created_at,
                c.name as category_name, c.slug as category_slug
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE $whereSQL
            ORDER BY b.created_at DESC
            LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $books = $stmt->fetchAll();

        // Add full URLs
        foreach ($books as &$book) {
            $book['cover_url'] = $book['cover_image']
                ? UPLOAD_URL . 'covers/' . $book['cover_image']
                : null;
            $book['ref'] = encodeId($book['id']);
        }

        sendResponse([
            'books'       => $books,
            'total'       => (int)$total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage)
        ]);
        break;

    // ==========================================
    // GET /api/?endpoint=book&id=REF
    // ==========================================
    case 'book':
        $ref = trim($_GET['ref'] ?? '');
        $id  = $ref ? decodeId($ref) : (int)($_GET['id'] ?? 0);

        if (!$id) sendError('Book not found', 404);

        $stmt = $pdo->prepare("SELECT b.*, c.name as category_name
                               FROM books b
                               LEFT JOIN categories c ON b.category_id = c.id
                               WHERE b.id = ? AND b.is_published = 1");
        $stmt->execute([$id]);
        $book = $stmt->fetch();

        if (!$book) sendError('Book not found', 404);

        $book['cover_url'] = $book['cover_image']
            ? UPLOAD_URL . 'covers/' . $book['cover_image']
            : null;
        $book['pdf_url'] = UPLOAD_URL . 'books/' . $book['file_path'];
        $book['ref']     = encodeId($book['id']);

        sendResponse($book);
        break;

    // ==========================================
    // POST /api/?endpoint=download
    // ==========================================
    case 'download':
        if ($method !== 'POST') sendError('Method not allowed', 405);

        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $ref   = trim($input['ref'] ?? '');
        $id    = $ref ? decodeId($ref) : 0;

        if (!$id) sendError('Invalid book reference');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Valid email required');
        }

        // Save email
        saveVisitorEmail($pdo, $email);

        // Log download
        $stmt = $pdo->prepare("INSERT INTO download_logs (book_id, email) VALUES (?, ?)");
        $stmt->execute([$id, $email]);

        // Increment downloads
        $pdo->prepare("UPDATE books SET downloads = downloads + 1 WHERE id = ?")
            ->execute([$id]);

        // Get PDF URL
        $book = $pdo->prepare("SELECT file_path, title FROM books WHERE id = ?");
        $book->execute([$id]);
        $bookData = $book->fetch();

        sendResponse([
            'pdf_url'   => UPLOAD_URL . 'books/' . $bookData['file_path'],
            'title'     => $bookData['title'],
            'message'   => 'Download ready'
        ]);
        break;

    // ==========================================
    // GET /api/?endpoint=categories
    // ==========================================
    case 'categories':
        $stmt = $pdo->query("SELECT c.*, COUNT(b.id) as book_count
                             FROM categories c
                             LEFT JOIN books b ON c.id = b.category_id AND b.is_published = 1
                             GROUP BY c.id
                             ORDER BY c.name ASC");
        sendResponse($stmt->fetchAll());
        break;

    // ==========================================
    // GET /api/?endpoint=news
    // ==========================================
    case 'news':
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $total = $pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 1")
                     ->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, title, slug, content, cover_image, created_at
                               FROM news WHERE is_published = 1
                               ORDER BY created_at DESC
                               LIMIT $perPage OFFSET $offset");
        $stmt->execute();
        $articles = $stmt->fetchAll();

        foreach ($articles as &$article) {
            $article['cover_url'] = $article['cover_image']
                ? UPLOAD_URL . 'news/' . $article['cover_image']
                : null;
            $article['excerpt'] = substr(strip_tags($article['content']), 0, 150) . '...';
        }

        sendResponse([
            'articles'    => $articles,
            'total'       => (int)$total,
            'page'        => $page,
            'total_pages' => ceil($total / $perPage)
        ]);
        break;

    // ==========================================
    // GET /api/?endpoint=news_detail&id=1
    // ==========================================
    case 'news_detail':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) sendError('Article not found', 404);

        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ? AND is_published = 1");
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        if (!$article) sendError('Article not found', 404);

        $article['cover_url'] = $article['cover_image']
            ? UPLOAD_URL . 'news/' . $article['cover_image']
            : null;

        sendResponse($article);
        break;

    // ==========================================
    // GET /api/?endpoint=prayer_times
    // ==========================================
    case 'prayer_times':
        $city  = trim($_GET['city'] ?? 'Yangon');
        $month = (int)($_GET['month'] ?? date('n'));
        $day   = (int)($_GET['day'] ?? date('j'));

        $stmt = $pdo->prepare("SELECT * FROM prayer_times
                               WHERE city = ? AND month = ? AND day = ?");
        $stmt->execute([$city, $month, $day]);
        $times = $stmt->fetch();

        if (!$times) {
            // Try API fallback
            $url = "http://api.aladhan.com/v1/timingsByCity/" .
                   date('d-m-Y') . "?city=$city&country=Myanmar&method=3";
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $res = @file_get_contents($url, false, $ctx);

            if ($res) {
                $data = json_decode($res, true);
                if ($data && $data['code'] === 200) {
                    $t     = $data['data']['timings'];
                    $times = [
                        'city'     => $city,
                        'month'    => $month,
                        'day'      => $day,
                        'imsaak'   => $t['Imsak'],
                        'fajr'     => $t['Fajr'],
                        'sunrise'  => $t['Sunrise'],
                        'dhuhr'    => $t['Dhuhr'],
                        'sunset'   => $t['Sunset'],
                        'maghrib'  => $t['Maghrib'],
                        'source'   => 'api'
                    ];
                }
            }
        } else {
            $times['source'] = 'database';
        }

        if (!$times) sendError('Prayer times not available');
        sendResponse($times);
        break;

    // ==========================================
    // GET /api/?endpoint=islamic_date
    // ==========================================
    case 'islamic_date':
        $url = "http://api.aladhan.com/v1/gToH/" . date('d-m-Y');
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $res = @file_get_contents($url, false, $ctx);

        if (!$res) sendError('Islamic date unavailable');

        $data = json_decode($res, true);
        if (!$data || $data['code'] !== 200) sendError('Islamic date unavailable');

        sendResponse($data['data']['hijri']);
        break;

    // ==========================================
    // GET /api/?endpoint=events
    // ==========================================
    case 'events':
        $stmt = $pdo->query("SELECT * FROM islamic_events
                             WHERE gregorian_date >= CURDATE()
                             AND is_published = 1
                             ORDER BY gregorian_date ASC
                             LIMIT 10");
        sendResponse($stmt->fetchAll());
        break;

    // ==========================================
    // GET /api/?endpoint=announcement
    // ==========================================
    case 'announcement':
        $stmt = $pdo->query("SELECT * FROM announcements
                             WHERE is_active = 1
                             AND (expires_at IS NULL OR expires_at >= CURDATE())
                             ORDER BY created_at DESC
                             LIMIT 1");
        $ann = $stmt->fetch();
        sendResponse($ann ?: null);
        break;

    // ==========================================
    // POST /api/?endpoint=subscribe
    // ==========================================
    case 'subscribe':
        if ($method !== 'POST') sendError('Method not allowed', 405);

        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Valid email required');
        }

        if (!emailExists($pdo, $email)) {
            $stmt = $pdo->prepare("INSERT INTO visitor_emails (email, is_subscriber) VALUES (?, 1)");
            $stmt->execute([$email]);
        } else {
            $pdo->prepare("UPDATE visitor_emails SET is_subscriber = 1 WHERE email = ?")
                ->execute([$email]);
        }

        sendResponse(['message' => 'Subscribed successfully']);
        break;

    // ==========================================
    // GET /api/?endpoint=stats
    // ==========================================
    case 'stats':
        sendResponse([
            'total_books'     => (int)$pdo->query("SELECT COUNT(*) FROM books WHERE is_published = 1")->fetchColumn(),
            'total_downloads' => (int)$pdo->query("SELECT SUM(downloads) FROM books")->fetchColumn(),
            'total_news'      => (int)$pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 1")->fetchColumn(),
        ]);
        break;

    default:
        sendResponse([
            'name'      => 'Myanmar Muslims API',
            'version'   => '1.0',
            'endpoints' => [
                'books', 'book', 'download', 'categories',
                'news', 'news_detail', 'prayer_times',
                'islamic_date', 'events', 'announcement',
                'subscribe', 'stats'
            ]
        ]);
        break;
}