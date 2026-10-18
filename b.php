<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . SITE_URL);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM books WHERE id = ? AND is_published = 1");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: ' . SITE_URL . '/pages/library.php');
    exit;
}

// Track click (optional)
header('Location: ' . SITE_URL . '/pages/book.php?ref=' . encodeId($book['id']));
exit;