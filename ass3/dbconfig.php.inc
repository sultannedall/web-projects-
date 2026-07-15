<?php
$DB_HOST = 'localhost';
$DB_NAME = 'web1203147_souvenirStore';
$DB_USER = 'web1203147_user';
$DB_PASS = 'sultan12031472002';
$DB_DSN = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$DB_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, $DB_OPTIONS);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}


