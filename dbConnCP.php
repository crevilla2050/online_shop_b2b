<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$dbname = 'db_codigos_postales';
$username = 'root';
$password = 't4a2x0a6';

try {
    // Crear conexión PDO
    $pdoCP = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Establecer modo de error de PDO a excepción
    $pdoCP->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCP->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Opcional: Habilitar conexiones persistentes
    // $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
    
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>
