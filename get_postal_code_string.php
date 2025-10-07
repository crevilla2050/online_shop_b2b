<?php
require_once 'dbConnCP.php'; // Database connection for postal codes

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID postal code missing']);
    exit;
}

$id = $_GET['id'];

try {
    // Prepare statement to get postal code string by binary ID
    $stmt = $pdoCP->prepare("SELECT code FROM postal_code WHERE id = UNHEX(REPLACE(?, '-', ''))");
    // The ID might be in hex string format, so we remove dashes and convert to binary using UNHEX
    // If ID is already binary, adjust accordingly
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo json_encode(['error' => 'Postal code not found']);
        exit;
    }

    echo json_encode(['codigo_postal' => $result['code']]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
    exit;
}
?>
