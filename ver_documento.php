<?php
// ver_documento.php
// Display document file, name, and notes in a new tab/window

require_once("dbConn.php"); // database connection

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de documento inválido.");
}

$document_id = intval($_GET['id']);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch document info
    $stmt = $pdo->prepare("SELECT d.*, dt.chr_nombre AS tipo_nombre FROM tbl_documentos d JOIN tbl_documentos_tipos dt ON d.id_documento_tipo = dt.id_documento_tipo WHERE d.id_documento = ? AND d.bit_activo = 1");
    $stmt->execute([$document_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        die("Documento no encontrado o inactivo.");
    }

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Ver Documento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
    <div class="container">
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <p class="mb-2"><strong>Tipo:</strong> <?= htmlspecialchars($document['tipo_nombre']) ?></p>
                <p class="mb-1"><strong>Notas:</strong></p>
                <div class="border rounded p-3 mb-3" style="white-space: pre-wrap; background-color: #f8f9fa;"><?= htmlspecialchars($document['chr_notas']) ?></div>
            </div>
        </div>
        <div class="ratio ratio-16x9 mb-3" style="height: 600px;">
            <iframe src="<?= htmlspecialchars($document['chr_ruta_archivo']) ?>" frameborder="0"></iframe>
        </div>
        <div class="text-end">
            <a href="javascript:window.close()" class="btn btn-secondary">Cerrar</a>
        </div>
    </div>
</body>
</html>
