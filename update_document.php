<?php
session_start();
require_once("dbConn.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['document_id']) || !is_numeric($_POST['document_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de documento inválido']);
    exit;
}

$documentId = intval($_POST['document_id']);
$documentType = isset($_POST['document_type']) ? intval($_POST['document_type']) : null;
$documentNotes = isset($_POST['document_notes']) ? trim($_POST['document_notes']) : '';

if ($documentType === null) {
    echo json_encode(['success' => false, 'error' => 'Tipo de documento no especificado']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE tbl_documentos SET id_documento_tipo = ?, chr_notas = ? WHERE id_documento = ?");
    $stmt->execute([$documentType, $documentNotes, $documentId]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
