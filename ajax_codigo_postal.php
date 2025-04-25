<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Enable detailed error reporting to output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

 
//require_once 'dbConn.php'; // This will provide the $pdo variable
require_once 'dbConnCP.php'; // This will provide the $pdo variable

header('Content-Type: application/json');

error_log("AJAX endpoint accessed");

if (!isset($_GET['codigo']) || strlen($_GET['codigo']) < 5) {
    error_log("Código postal inválido");
    echo json_encode(['error' => 'Código postal inválido']);
    exit;
}

if (!$pdoCP) {
    error_log("Error de conexión a la base de datos");
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$codigo = $_GET['codigo'];
error_log("Received codigo: " . $codigo);

try {
    $test = $pdoCP->query("SELECT 1")->fetch();
    if (!$test) {
        throw new PDOException("Test query failed");
    }

    $query = "SELECT * FROM db_codigos_postales.postal_codes_view where codigo_postal = :codigo";

    $stmt = $pdoCP->prepare($query);
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();

    error_log("Query executed, fetching results");

    $result = $stmt->fetchAll();
    error_log("Number of results fetched: " . count($result));

    if (empty($result)) {
        error_log("No results found for postal code: $codigo");
        echo json_encode(['error' => 'Código postal no encontrado']);
        exit;
    }

    $first = $result[0];
    $colonias = array_map(function($row) {
        return [
            'id' => (isset($row['id_colonia']) && is_string($row['id_colonia'])) ? unpack('N', substr($row['id_colonia'], 0, 4))[1] : null,
            'name' => isset($row['colonia']) ? $row['colonia'] : null
        ];
    }, $result);

    // Convert binary(16) id_ciudad to integer equivalent (first 4 bytes)
    $idCiudadInt = null;
    if (isset($first['id_ciudad']) && is_string($first['id_ciudad'])) {
        $unpacked = unpack('N', substr($first['id_ciudad'], 0, 4));
        if ($unpacked !== false && isset($unpacked[1])) {
            $idCiudadInt = $unpacked[1];
        } else {
            $idCiudadInt = null;
        }
    }

    $response = [
        'estado' => isset($first['estado']) ? $first['estado'] : null,
        'municipio' => isset($first['municipio']) ? $first['municipio'] : null,
        'ciudad' => isset($first['ciudad']) ? $first['ciudad'] : null,
        'id_ciudad' => $idCiudadInt,
        'codigo_postal' => isset($first['codigo_postal']) ? $first['codigo_postal'] : null,
        'colonias' => $colonias
    ];

    // Ensure colonias array has only strings and integers
    $colonias = array_map(function($colonia) {
        return [
            'id' => is_string($colonia['id']) ? $colonia['id'] : (int)$colonia['id'],
            'name' => $colonia['name'] !== null ? (string)$colonia['name'] : null
        ];
    }, $colonias);

    $response['colonias'] = $colonias;

    // Helper function to recursively utf8 encode all strings in array
    function utf8ize($mixed) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = utf8ize($value);
            }
        } else if (is_string($mixed)) {
            // Use iconv as fallback if mbstring is not available
            return iconv('UTF-8', 'UTF-8//IGNORE', $mixed);
        }
        return $mixed;
    }

    $response = utf8ize($response);

    $json_response = json_encode($response);
    if ($json_response === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        echo json_encode(['error' => 'JSON encode error', 'message' => json_last_error_msg()]);
        exit;
    }

    error_log("Response prepared: " . $json_response);

    echo $json_response;

} catch (PDOException $e) {
    $errorInfo = $pdoCP->errorInfo();
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
