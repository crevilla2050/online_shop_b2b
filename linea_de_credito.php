<?php
session_start();

require_once("dbConn.php");

// Validate client ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de cliente inválido.");
}

$client_id = intval($_GET['id']);

// Set client ID in session for menu.php usage
if (!isset($_SESSION['usuario']['id_cliente']) || $_SESSION['usuario']['id_cliente'] != $client_id) {
    $_SESSION['usuario']['id_cliente'] = $client_id;
}

try {
    // Obtener info del cliente
    $stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("Cliente no encontrado.");
    }

    // Obtener todas las líneas de crédito activas del cliente desde tbl_creditos_empresa
    $stmtCreditos = $pdo->prepare("SELECT id_credito_empresa, fl_monto_credito, dt_fecha_creacion FROM tbl_creditos_empresa WHERE id_cliente = ? AND bit_activo = 1 ORDER BY dt_fecha_creacion DESC");
    $stmtCreditos->execute([$client_id]);
    $creditos = $stmtCreditos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Línea de Crédito - <?= htmlspecialchars($client['chr_nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-5" style="margin-left: 220px;">
        <h2>Línea de Crédito para Cliente: <?= htmlspecialchars($client['chr_nombre']) ?></h2>
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                Líneas de Crédito Activas
            </div>
            <div class="card-body">
                <?php if (empty($creditos)): ?>
                    <p>No hay líneas de crédito activas para este cliente.</p>
                <?php else: ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID Crédito</th>
                                <th>Monto Crédito</th>
                                <th>Fecha Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($creditos as $credito): ?>
                            <tr>
                                <td><?= htmlspecialchars($credito['id_credito_empresa']) ?></td>
                                <td>$<?= number_format($credito['fl_monto_credito'], 2) ?></td>
                                <td><?= htmlspecialchars($credito['dt_fecha_creacion']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <a href="asignar_credito.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-primary mt-3">Asignar Nuevo Crédito</a>
                <a href="detalle_cliente.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-secondary mt-3 ms-2">Volver a Detalle Cliente</a>
            </div>
        </div>
    </div>
</body>
</html>
