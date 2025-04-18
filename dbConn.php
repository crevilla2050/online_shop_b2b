<?php

// Database connection configuration using environment variables (Recommended)
$host = getenv('DB_HOST');
$dbname = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

if (!$host || !$dbname || !$username || !$password) {
    error_log("Error: Database environment variables not set.");
    die("An unexpected error occurred. Please contact the administrator."); // Generic error message
}

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Optional: Enable persistent connections
    // $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);

} catch (PDOException $e) {
    $errorMessage = "Database connection failed: " . $e->getMessage();
    error_log($errorMessage); // Log the error
    die("An unexpected error occurred. Please contact the administrator."); // Generic error message
}
?>