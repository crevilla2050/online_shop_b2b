<?php
require_once("dbConn.php"); // conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id_direccion = intval($_GET['id']);

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch address details
        $stmt = $pdo->prepare("SELECT * FROM tbl_direcciones WHERE id_direccion = ?");
        $stmt->execute([$id_direccion]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($address) {
            echo json_encode($address);
        } else {
            echo json_encode(['error' => 'Address not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
?>
