<?php
include 'init_session.php';
include 'header.php';
require_once("dbConn.php");

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: index.php');
    exit;
}

$client_id = intval($_SESSION['user']['client_id']);
$user_role = intval($_SESSION['user']['tipo'] ?? 0);

if ($user_role < 3) {
    echo "<p>No tiene permisos para acceder a esta página.</p>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_empleado_cliente = intval($_POST['id_empleado_cliente'] ?? 0);
    $id_orden = intval($_POST['id_orden'] ?? 0);
    $monto_consumido = floatval($_POST['monto_consumido'] ?? 0);

    if ($id_empleado_cliente <= 0 || $id_orden <= 0 || $monto_consumido <= 0) {
        $error = "Por favor, complete todos los campos correctamente.";
    } else {
        try {
            // Insert new consumption record
            $stmt = $pdo->prepare("INSERT INTO tbl_consumos_empleado (id_empleado_cliente, id_orden, fl_monto_consumido) VALUES (?, ?, ?)");
            $stmt->execute([$id_empleado_cliente, $id_orden, $monto_consumido]);
            $success = "Consumo agregado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al agregar consumo: " . $e->getMessage();
        }
    }
}

// Fetch employees for this client to populate select
$stmtEmpleados = $pdo->prepare("
    SELECT ec.id_empleado_cliente, u.chr_login AS nombre_empleado
    FROM tbl_empleados_cliente ec
    JOIN tbl_usuarios u ON ec.id_empleado = u.id_usuario
    WHERE ec.id_cliente = ? AND ec.bit_activo = 1
");
$stmtEmpleados->execute([$client_id]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar Consumo de Crédito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-5" style="margin-left: 220px;">
        <h2>Agregar Consumo de Crédito para Empleado</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="agrega_consumo.php" class="mt-4">
            <div class="mb-3">
                <label for="id_empleado_cliente" class="form-label">Empleado</label>
                <select name="id_empleado_cliente" id="id_empleado_cliente" class="form-select" required>
                    <option value="">Seleccione un empleado</option>
                    <?php foreach ($empleados as $empleado): ?>
                        <option value="<?= htmlspecialchars($empleado['id_empleado_cliente']) ?>">
                            <?= htmlspecialchars($empleado['nombre_empleado']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_orden" class="form-label">ID Orden</label>
                <input type="number" name="id_orden" id="id_orden" class="form-control" min="1" required />
            </div>
            <div class="mb-3">
                <label for="monto_consumido" class="form-label">Monto Consumido</label>
                <input type="number" name="monto_consumido" id="monto_consumido" class="form-control" step="0.01" min="0.01" required />
            </div>
            <button type="submit" class="btn btn-primary">Agregar Consumo</button>
            <a href="linea_de_credito.php" class="btn btn-secondary ms-2">Volver a Línea de Crédito</a>
        </form>
    </div>
</body>
</html>
