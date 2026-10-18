<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}