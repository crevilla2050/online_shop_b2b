<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable detailed error reporting to output
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'dbConnCP.php'; // This will provide the $pdo variable

header('Content-Type: application/json');

error_log("AJAX endpoint accessed");

if (!isset($_GET['codigo']) || strlen($_GET['codigo']) < 5) {
    error_log("Código postal inválido");
    echo json_encode(['error' => 'Código postal inválido']);
    exit;
}

if (!$pdo) {
    error_log("Error de conexión a la base de datos");
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$codigo = $_GET['codigo'];

try {
    $test = $pdo->query("SELECT 1")->fetch();
    if (!$test) {
        throw new PDOException("Test query failed");
    }

    $query = "SELECT 
    `c`.`id` AS `id`,
    `s`.`name` AS `estado`,
    `m`.`name` AS `municipio`,
    `c`.`name` AS `ciudad`,
    `se`.`name` AS `colonia`,
    `pc`.`code` AS `codigo_postal`
    FROM
    `db_codigos_postales`.`catalog_postal_codes` `cp`
    JOIN `db_codigos_postales`.`state` `s` ON `s`.`id` = `cp`.`state_id`
    JOIN `db_codigos_postales`.`municipality` `m` ON `m`.`id` = `cp`.`municipality_id`
    JOIN `db_codigos_postales`.`city` `c` ON `c`.`id` = `cp`.`city_id`
    JOIN `db_codigos_postales`.`settlement` `se` ON `se`.`id` = `cp`.`settlement_id`
    JOIN `db_codigos_postales`.`postal_code` `pc` ON `pc`.`id` = `cp`.`postal_code_id`
    WHERE pc.code = :codigo";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();

    error_log("Query executed, fetching results");

    $result = $stmt->fetchAll();

    if (empty($result)) {
        error_log("No results found for postal code: $codigo");
        echo json_encode(['error' => 'Código postal no encontrado']);
        exit;
    }

    $first = $result[0];
    $colonias = array_map(function($row) {
        return $row['colonia'];
    }, $result);

    // Convert binary id to integer
    $idCiudadBinary = $first['id'];
    $idCiudadInt = null;
    if (is_string($idCiudadBinary)) {
        // Assuming the binary is a 4-byte integer in network order
        $unpacked = unpack('N', $idCiudadBinary);
        if ($unpacked !== false) {
            $idCiudadInt = $unpacked[1];
        } else {
            $idCiudadInt = null;
        }
    }

    $response = [
        'estado' => $first['estado'],
        'municipio' => $first['municipio'],
        'ciudad' => $first['ciudad'],
        'id_ciudad' => $idCiudadInt,
        'codigo_postal' => $first['codigo_postal'],
        'colonias' => $colonias
    ];

    error_log("Response prepared: " . json_encode($response));

    echo json_encode($response);

} catch (PDOException $e) {
    $errorInfo = $pdo->errorInfo();
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'pdo_error' => $errorInfo
    ]);
    exit;
}
?>
