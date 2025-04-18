<?php
// Database connection configuration
$host = 'localhost';
$dbname = 'db_codigos_postales';
$username = 'root';
$password = 't4a2x0a6';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Optional: Enable persistent connections
    // $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
