<?php
//Load DB Config from Secure Location
// This helps keep sensitive credentials out of public files
$config = require __DIR__ . '/../config/config.php';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Errors as exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Results as associative array
    PDO::ATTR_EMULATE_PREPARES => false                 // Real prepared statements
];

try {
    $pdo = new PDO(
        "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=utf8mb4",
        $config['DB_USER'],
        $config['DB_PASSWORD'],
        $options                // Apply secure PDO options
    );
} catch (PDOException $e) {
    // Log the detailed error, but never show to users
    error_log('Database connection failed: ' . $e->getMessage());

    // Show a generic error to users (never expose DB errors to the frontend!)
    die('Internal Server Error. Please try again later.');
}
?>
