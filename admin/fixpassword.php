<?php
require_once '../includes/config.php';

$newPassword = 'My0Nass2012$';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update in database
$stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash]);

echo "Password updated successfully!<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "Hash: " . $hash;
?>