<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'init_session.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once("dbConn.php"); // conexión a la base de datos
require_once("dbConnCP.php"); // conexión a la base de datos para codigos postales

function getDocumentsTableHtml($pdo, $clientId) {
    $stmtDocs = $pdo->prepare("
        SELECT d.*, dt.chr_nombre AS tipo_nombre
        FROM tbl_documentos d
        JOIN tbl_clientes_documentos cd ON d.id_documento = cd.id_documento
        JOIN tbl_documentos_tipos dt ON d.id_documento_tipo = dt.id_documento_tipo
        WHERE cd.id_cliente = ? AND d.bit_activo = 1
        ORDER BY d.dt_fecha_subida DESC
    ");
    $stmtDocs->execute([$clientId]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

    if (empty($documentos)) {
        return '<p>No hay documentos subidos para este cliente.</p>';
    }

    $html = '<table class="table table-bordered table-sm w-100">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Fecha Subida</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($documentos as $doc) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($doc['tipo_nombre']) . '</td>
                    <td>' . htmlspecialchars($doc['dt_fecha_subida']) . '</td>
                    <td>
                        <a href="ver_documento.php?id=' . intval($doc['id_documento']) . '" target="_blank" class="btn btn-sm btn-primary me-1">Ver</a>
                        <button class="btn btn-sm btn-warning me-1" onclick="openEditDocumentModal(' . intval($doc['id_documento']) . ')">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteDocument(' . intval($doc['id_documento']) . ')">Eliminar</button>
                    </td>
                </tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

// Handle document deletion via DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $documentId = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("UPDATE tbl_documentos SET bit_activo = 0 WHERE id_documento = ?");
        $stmt->execute([$documentId]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        exit;
    }
}

// Handle document update via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'])) {
    $documentId = intval($_POST['document_id']);
    $documentType = isset($_POST['document_type']) ? intval($_POST['document_type']) : null;
    $documentNotes = isset($_POST['document_notes']) ? trim($_POST['document_notes']) : '';

    if ($documentType === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Tipo de documento no especificado']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE tbl_documentos SET id_documento_tipo = ?, chr_notas = ? WHERE id_documento = ?");
        $stmt->execute([$documentType, $documentNotes, $documentId]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        exit;
    }
}

// Obtiebne datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chr_direccion'])) {
    try {
        $client_id_post = intval($_POST['client_id']);
        $id_direccion_post = isset($_POST['id_direccion']) && !empty($_POST['id_direccion']) ? intval($_POST['id_direccion']) : null;
        $chr_direccion = htmlspecialchars($_POST['chr_direccion']);
        $chr_calle = htmlspecialchars($_POST['chr_calle']);
        $chr_numero = htmlspecialchars($_POST['chr_numero']);
        $chr_interior = htmlspecialchars($_POST['chr_interior']);
        $codigo_postal = htmlspecialchars($_POST['chr_codigo_postal']);
        $tipo_direccion = htmlspecialchars($_POST['chr_tipo_direccion']);
        $bit_default = isset($_POST['bit_default']) ? 1 : 0;
        $id_ciudad = isset($_POST['id_ciudad']) ? intval($_POST['id_ciudad']) : null;
        $chr_colonia = isset($_POST['chr_colonia']) ? htmlspecialchars($_POST['chr_colonia']) : '';
        $id_colonia = isset($_POST['id_colonia']) ? intval($_POST['id_colonia']) : null;

        // Concatenate address parts into one string
        $direccion_parts = array_filter([$chr_direccion, $chr_calle, $chr_numero, $chr_interior]);
        $direccion = implode(' ', $direccion_parts);

        // Map postal code string to id_codigo_postal
        $id_codigo_postal = null;
        $stmtCP = $pdoCP->prepare("SELECT id_ciudad FROM postal_codes_view WHERE codigo_postal = ?");
        $stmtCP->execute([$codigo_postal]);
        $postalCodeRow = $stmtCP->fetch(PDO::FETCH_ASSOC);
        if ($postalCodeRow) {
            // Unpack binary id_ciudad to int if needed
            if (isset($postalCodeRow['id_ciudad']) && is_string($postalCodeRow['id_ciudad'])) {
                $unpacked = unpack('N', $postalCodeRow['id_ciudad']);
                if ($unpacked !== false && isset($unpacked[1])) {
                    $id_codigo_postal = $unpacked[1];
                }
            } else {
                $id_codigo_postal = $postalCodeRow['id_ciudad'];
            }
        } else {
            throw new Exception("Código postal no encontrado en la base de datos.");
        }

        // Map tipo_direccion string to id_tipo_direccion
        $id_tipo_direccion = null;
        $stmtTipo = $pdo->prepare("SELECT id_tipos_direcciones FROM tbl_tipos_direcciones WHERE chr_tipo_direccion = ?");
        $stmtTipo->execute([$tipo_direccion]);
        $tipoRow = $stmtTipo->fetch(PDO::FETCH_ASSOC);
        if ($tipoRow) {
            $id_tipo_direccion = $tipoRow['id_tipos_direcciones'];
        } else {
            throw new Exception("Tipo de dirección no encontrado en la base de datos.");
        }

        // si la nueva direccion es la principal, actualiza la anterior a no principal
        if ($bit_default) {
            $stmt = $pdo->prepare("UPDATE tbl_direcciones SET bit_default = 0 WHERE id_cliente = ?");
            $stmt->execute([$client_id_post]);
        }

        if ($id_direccion_post) {
            // Actualiza dirección existente
            $stmt = $pdo->prepare("UPDATE tbl_direcciones SET chr_direccion = ?, id_codigo_postal = ?, id_tipo_direccion = ?, bit_default = ?, id_colonia = ? WHERE id_direccion = ?");
            $stmt->execute([$direccion, $id_codigo_postal, $id_tipo_direccion, $bit_default, $id_colonia, $id_direccion_post]);
        } else {
            // Inserta nueva dirección
            $stmt = $pdo->prepare("INSERT INTO tbl_direcciones (id_cliente, chr_direccion, id_codigo_postal, id_tipo_direccion, bit_default, id_colonia) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$client_id_post, $direccion, $id_codigo_postal, $id_tipo_direccion, $bit_default, $id_colonia]);
        }

        // Redirect para evitar re-envio del formulario
        header("Location: detalle_cliente.php?id=" . $client_id_post);
        exit;

    } catch (Exception $e) {
        die("Error al guardar la dirección: " . $e->getMessage());
    } catch (PDOException $e) {
        die("Error al guardar la dirección: " . $e->getMessage());
    }
}

// Handle address deletion
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id_direccion = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("UPDATE tbl_direcciones SET bit_activa = 0 WHERE id_direccion = ?");
        $stmt->execute([$id_direccion]);

        echo json_encode(['success' => true]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$user_level = $_SESSION['user']['tipo'] ?? 0;

if (!isset($_SESSION['user']['client_id']) || !is_numeric($_SESSION['user']['client_id'])) {
    header('Location: index.php');
    exit;
}
$client_id = intval($_SESSION['user']['client_id']);
$user_id = $_SESSION['user']['id'] ?? null;

try {
    // Obtener info del cliente
    $stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("Cliente no encontrado.");
    }

if ($user_level >= 3 && $user_id !== null) {
    // Obtener id_empleado_cliente para el usuario logueado
    $stmtEmpleado = $pdo->prepare("SELECT id_empleado_cliente FROM tbl_empleados_cliente WHERE id_usuario = ?");
    $stmtEmpleado->execute([$user_id]);
    $empleadoRow = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);
    $id_empleado_cliente = $empleadoRow ? $empleadoRow['id_empleado_cliente'] : null;

    if ($id_empleado_cliente !== null) {
        // Obtener suma total de créditos activos del empleado desde tbl_creditos_empleado
        $stmtCredito = $pdo->prepare("SELECT COALESCE(SUM(fl_monto_credito), 0) AS total_credito FROM tbl_creditos_empleado WHERE id_empleado_cliente = ? AND bit_activo = 1");
        $stmtCredito->execute([$id_empleado_cliente]);
        $creditoRow = $stmtCredito->fetch(PDO::FETCH_ASSOC);
        $total_credito_empleado = $creditoRow ? $creditoRow['total_credito'] : 0;

        // Obtener todas las líneas de crédito activas del empleado desde tbl_creditos_empleado
        $stmtCreditosDetalles = $pdo->prepare("SELECT id_credito_empleado, fl_monto_credito, dt_fecha_creacion FROM tbl_creditos_empleado WHERE id_empleado_cliente = ? AND bit_activo = 1 ORDER BY dt_fecha_creacion DESC");
        $stmtCreditosDetalles->execute([$id_empleado_cliente]);
        $creditos_detalles = $stmtCreditosDetalles->fetchAll(PDO::FETCH_ASSOC);

        $total_credito_empresa = $total_credito_empleado;
    } else {
        $total_credito_empresa = 0;
        $creditos_detalles = [];
    }
} else {
        // Obtener suma total de créditos activos del cliente desde tbl_creditos_empresa
        $stmtCredito = $pdo->prepare("SELECT COALESCE(SUM(fl_monto_credito), 0) AS total_credito FROM tbl_creditos_empresa WHERE id_cliente = ? AND bit_activo = 1");
        $stmtCredito->execute([$client_id]);
        $creditoRow = $stmtCredito->fetch(PDO::FETCH_ASSOC);
        $total_credito_empresa = $creditoRow ? $creditoRow['total_credito'] : 0;

        // Obtener todas las líneas de crédito activas del cliente desde tbl_creditos_empresa
        $stmtCreditosDetalles = $pdo->prepare("SELECT id_credito_empresa, fl_monto_credito, dt_fecha_creacion FROM tbl_creditos_empresa WHERE id_cliente = ? AND bit_activo = 1 ORDER BY dt_fecha_creacion DESC");
        $stmtCreditosDetalles->execute([$client_id]);
        $creditos_detalles = $stmtCreditosDetalles->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar direcciones para el cliente desde tbl_direcciones
    $stmt = $pdo->prepare("SELECT * FROM tbl_direcciones WHERE id_cliente = ? AND bit_activa = 1");
    $stmt->execute([$client_id]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $addresses = [];

    foreach ($direcciones as $direccion) {
        // Obtener info de codigo postal desde db_codigos_postales.postal_codes_view
        $stmtCP = $pdoCP->prepare("SELECT * FROM postal_codes_view");
        $stmtCP->execute();
        $postalCodes = $stmtCP->fetchAll(PDO::FETCH_ASSOC);

        $postalInfo = null;
        foreach ($postalCodes as $pc) {
            // Unpack binary id_ciudad and id_colonia to integers
            $idCiudadInt = null;
            $idColoniaInt = null;
            if (isset($pc['id_ciudad']) && is_string($pc['id_ciudad'])) {
                $unpackedCiudad = unpack('N', substr($pc['id_ciudad'], 0, 4));
                if ($unpackedCiudad !== false && isset($unpackedCiudad[1])) {
                    $idCiudadInt = $unpackedCiudad[1];
                }
            }
            if (isset($pc['id_colonia']) && is_string($pc['id_colonia'])) {
                $unpackedColonia = unpack('N', substr($pc['id_colonia'], 0, 4));
                if ($unpackedColonia !== false && isset($unpackedColonia[1])) {
                    $idColoniaInt = $unpackedColonia[1];
                }
            }

            if ($idCiudadInt === intval($direccion['id_codigo_postal']) && $idColoniaInt === intval($direccion['id_colonia'])) {
                $postalInfo = $pc;
                break;
            }
        }

        // Obtener tipo de direccion desde tbl_tipos_direcciones
        $stmtTipo = $pdo->prepare("SELECT chr_tipo_direccion FROM tbl_tipos_direcciones WHERE id_tipos_direcciones = ?");
        $stmtTipo->execute([$direccion['id_tipo_direccion']]);
        $tipoDireccion = $stmtTipo->fetch(PDO::FETCH_ASSOC);

        $addresses[] = [
            'id_direccion' => $direccion['id_direccion'],
            'chr_direccion' => $direccion['chr_direccion'],
            'chr_codigo_postal' => $postalInfo['codigo_postal'] ?? '',
            'ciudad' => $postalInfo['ciudad'] ?? '',
            'estado' => $postalInfo['estado'] ?? '',
            'municipio' => $postalInfo['municipio'] ?? '',
            'colonia' => $postalInfo['colonia'] ?? '',
            'chr_tipo_direccion' => $tipoDireccion['chr_tipo_direccion'] ?? '',
            'bit_default' => $direccion['bit_default']
        ];
    }

    // Obtener tipos de direcciones para el dropdown
    $stmt = $pdo->prepare("SELECT * FROM tbl_tipos_direcciones ORDER BY chr_tipo_direccion ASC");
    $stmt->execute();
    $tipos_direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener documentos del cliente
    $stmtDocs = $pdo->prepare("
        SELECT d.*, dt.chr_nombre AS tipo_nombre
        FROM tbl_documentos d
        JOIN tbl_clientes_documentos cd ON d.id_documento = cd.id_documento
        JOIN tbl_documentos_tipos dt ON d.id_documento_tipo = dt.id_documento_tipo
        WHERE cd.id_cliente = ? AND d.bit_activo = 1
        ORDER BY d.dt_fecha_subida DESC
    ");
    $stmtDocs->execute([$client_id]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

    // Obtener tipos de documentos para el dropdown de subir documentos
    $stmtDocTypes = $pdo->prepare("SELECT * FROM tbl_documentos_tipos WHERE bit_activo = 1 ORDER BY chr_nombre ASC");
    $stmtDocTypes->execute();
    $docTypes = $stmtDocTypes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

// Handle AJAX request to get updated documents table HTML
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $clientIdAjax = intval($_GET['id']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'documentsHtml' => getDocumentsTableHtml($pdo, $clientIdAjax)
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detalle Cliente - <?php echo htmlspecialchars($client['chr_nombre']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <style>
        body {
            padding-left: 220px;
            padding-top: 20px;
        }
        .container {
            max-width: 900px;
        }
        .address-table th, .address-table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container">
    <h1>Detalle Cliente: <?php echo htmlspecialchars($client['chr_nombre']); ?></h1>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($client['chr_email']); ?></p>
    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($client['chr_telefono']); ?></p>
    <p><strong>Crédito Total:</strong> $<?php echo number_format($total_credito_empresa, 2); ?></p>

    <h2>Direcciones</h2>
    <table class="table table-bordered address-table">
        <thead>
            <tr>
                <th>Dirección</th>
                <th>Código Postal</th>
                <th>Ciudad</th>
                <th>Estado</th>
                <th>Municipio</th>
                <th>Colonia</th>
                <th>Tipo Dirección</th>
                <th>Principal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($addresses as $address): ?>
            <tr>
                <td><?php echo htmlspecialchars($address['chr_direccion']); ?></td>
                <td><?php echo htmlspecialchars($address['chr_codigo_postal']); ?></td>
                <td><?php echo htmlspecialchars($address['ciudad']); ?></td>
                <td><?php echo htmlspecialchars($address['estado']); ?></td>
                <td><?php echo htmlspecialchars($address['municipio']); ?></td>
                <td><?php echo htmlspecialchars($address['colonia']); ?></td>
                <td><?php echo htmlspecialchars($address['chr_tipo_direccion']); ?></td>
                <td><?php echo $address['bit_default'] ? 'Sí' : 'No'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Documentos</h2>
    <?php echo getDocumentsTableHtml($pdo, $client_id); ?>

</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

