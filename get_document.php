<?php
session_start();
require_once("dbConn.php");

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID de documento inválido.']);
    exit;
}

$documentId = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("
        SELECT d.id_documento, d.id_documento_tipo, d.chr_nombre_archivo, d.chr_notas
        FROM tbl_documentos d
        WHERE d.id_documento = ? AND d.bit_activo = 1
    ");
    $stmt->execute([$documentId]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        echo json_encode(['error' => 'Documento no encontrado.']);
        exit;
    }

    echo json_encode($document);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
