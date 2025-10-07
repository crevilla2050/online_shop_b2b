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
    $id_cliente = intval($_POST['id_cliente'] ?? 0);
    $id_orden = intval($_POST['id_orden'] ?? 0);
    $fl_monto_total = floatval($_POST['fl_monto_total'] ?? 0);
    $dt_fecha_vencimiento = $_POST['dt_fecha_vencimiento'] ?? null;
    $fl_tasa_recargo = floatval($_POST['fl_tasa_recargo'] ?? 0);
    $chr_notas = trim($_POST['chr_notas'] ?? '');

    if ($id_cliente <= 0 || $id_orden <= 0 || $fl_monto_total <= 0) {
        $error = "Por favor, complete los campos obligatorios correctamente.";
    } else {
        try {
            $fl_monto_pendiente = $fl_monto_total;
            $stmt = $pdo->prepare("INSERT INTO tbl_cuentas_por_cobrar (id_cliente, id_orden, fl_monto_total, fl_monto_pendiente, dt_fecha_vencimiento, fl_tasa_recargo, chr_notas) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_cliente, $id_orden, $fl_monto_total, $fl_monto_pendiente, $dt_fecha_vencimiento, $fl_tasa_recargo, $chr_notas]);
            $success = "Cuenta por cobrar agregada correctamente.";
        } catch (PDOException $e) {
            $error = "Error al agregar cuenta por cobrar: " . $e->getMessage();
        }
    }
}

// Fetch clients for this user to populate select
$stmtClientes = $pdo->prepare("SELECT id_cliente, chr_nombre FROM tbl_clientes WHERE bit_activo = 1");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Fetch orders for this client to populate select
$stmtOrdenes = $pdo->prepare("SELECT id_orden FROM tbl_ordenes WHERE id_cliente_empresa = ? ORDER BY dt_fecha_orden DESC");
$stmtOrdenes->execute([$client_id]);
$ordenes = $stmtOrdenes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar Cuenta por Cobrar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-5" style="margin-left: 220px;">
        <h2>Agregar Cuenta por Cobrar</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="cuentas_x_cobrar.php" class="mt-4">
            <div class="mb-3">
                <label for="id_cliente" class="form-label">Cliente</label>
                <select name="id_cliente" id="id_cliente" class="form-select" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= htmlspecialchars($cliente['id_cliente']) ?>">
                            <?= htmlspecialchars($cliente['chr_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_orden" class="form-label">ID Orden</label>
                <select name="id_orden" id="id_orden" class="form-select" required>
                    <option value="">Seleccione una orden</option>
                    <?php foreach ($ordenes as $orden): ?>
                        <option value="<?= htmlspecialchars($orden['id_orden']) ?>">
                            <?= htmlspecialchars($orden['id_orden']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="fl_monto_total" class="form-label">Monto Total</label>
                <input type="number" name="fl_monto_total" id="fl_monto_total" class="form-control" step="0.01" min="0.01" required />
            </div>
            <div class="mb-3">
                <label for="dt_fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                <input type="date" name="dt_fecha_vencimiento" id="dt_fecha_vencimiento" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="fl_tasa_recargo" class="form-label">Tasa de Recargo (%)</label>
                <input type="number" name="fl_tasa_recargo" id="fl_tasa_recargo" class="form-control" step="0.01" min="0" value="0.00" />
            </div>
            <div class="mb-3">
                <label for="chr_notas" class="form-label">Notas</label>
                <textarea name="chr_notas" id="chr_notas" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Agregar Cuenta por Cobrar</button>
            <a href="linea_de_credito.php" class="btn btn-secondary ms-2">Volver a Línea de Crédito</a>
        </form>
    </div>
</body>
</html>
