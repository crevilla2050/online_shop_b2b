<?php

// Configuración de la conexión a la base de datos usando variables de entorno (Recomendado)
$host = getenv('DB_HOST');
$dbname = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

if (!$host || !$dbname || !$username || !$password) {
    error_log("Error: Variables de entorno de la base de datos no configuradas.");
    die("Ocurrió un error inesperado. Por favor contacte al administrador."); // Mensaje de error genérico
}

try {
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Establecer modo de error de PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Opcional: Habilitar conexiones persistentes
    // $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);

} catch (PDOException $e) {
    $errorMessage = "Fallo en la conexión a la base de datos: " . $e->getMessage();
    error_log($errorMessage); // Registrar el error
    die("Ocurrió un error inesperado. Por favor contacte al administrador."); // Mensaje de error genérico
}
?>
