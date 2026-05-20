<?php
// db_connect.php

$host = 'localhost';
$db   = 'espolybot_db'; // Your newly created database name
$user = 'root';         // Default XAMPP username
$pass = '';             // Default XAMPP password is empty
$charset = 'utf8mb4';

// Data Source Name configuration string
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Set secure configurations for data transaction
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw crashes as visible errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch database items as clean arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use true native prepared statements
];

try {
     // Establish the live connection instance
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If the database connection fails, return a JSON error structure to the frontend
     header('Content-Type: application/json');
     echo json_encode(['status' => 'error', 'reply' => 'Database connection failed: ' . $e->getMessage()]);
     exit;
}
?>