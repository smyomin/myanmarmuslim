<?php
// config.php
$host = 'localhost';
$dbname = 'shia_cemetery';
$username = 'root';       // change to your MySQL username
$password = '';           // change to your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}