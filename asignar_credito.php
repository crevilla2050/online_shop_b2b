<?php
// Start session and check login
$sessionPath = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $sessionPath);
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once("dbConn.php"); // conexión a la base de datos

// Validate client ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de cliente inválido.");
}

$client_id = intval($_GET['id']);
$errors = [];
$success = false;

try {
    // Get client info for display
    $stmt = $pdo->prepare("SELECT chr_nombre, chr_apellido, chr_nombre_empresa FROM tbl_clientes WHERE id_cliente = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("Cliente no encontrado.");
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $monto_credito = isset($_POST['fl_monto_credito']) ? trim($_POST['fl_monto_credito']) : '';

        if ($monto_credito === '' || !is_numeric($monto_credito) || floatval($monto_credito) <= 0) {
            $errors[] = "Por favor ingrese un monto de crédito válido mayor a cero.";
        }

        if (empty($errors)) {
            $stmtInsert = $pdo->prepare("INSERT INTO tbl_creditos_empresa (id_cliente, fl_monto_credito, bit_activo) VALUES (?, ?, 1)");
            $stmtInsert->execute([$client_id, floatval($monto_credito)]);
            $success = true;
        }
    }
} catch (PDOException $e) {
    $errors[] = "Error en la base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Asignar Nuevo Crédito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .container {
            max-width: 600px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h2>Asignar Nuevo Crédito para Cliente</h2>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($client['chr_nombre'] . ' ' . $client['chr_apellido']) ?></p>
        <?php if (!empty($client['chr_nombre_empresa'])): ?>
            <p><strong>Empresa:</strong> <?= htmlspecialchars($client['chr_nombre_empresa']) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                Crédito asignado exitosamente.
            </div>
            <a href="detalle_cliente.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-secondary">Volver al detalle del cliente</a>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="asignar_credito.php?id=<?= htmlspecialchars($client_id) ?>">
                <div class="mb-3">
                    <label for="fl_monto_credito" class="form-label">Monto de Crédito</label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="fl_monto_credito" name="fl_monto_credito" required value="<?= isset($_POST['fl_monto_credito']) ? htmlspecialchars($_POST['fl_monto_credito']) : '' ?>">
                </div>
                <button type="submit" class="btn btn-primary">Asignar Crédito</button>
                <a href="detalle_cliente.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
