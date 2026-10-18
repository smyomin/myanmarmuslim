<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
$id  = $ref ? decodeId($ref) : 0;

if (!$id || !isset($_SESSION['download_email'])) {
    header('Location: library.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_published = 1");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: library.php');
    exit;
}

$filePath = UPLOAD_PATH . 'books/' . $book['file_path'];

if (!file_exists($filePath)) {
    die('File not found. Please contact admin.');
}

// Force download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $book['title'] . '.pdf"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');
readfile($filePath);
exit;