<?php
// Iniciar sesión y verificar inicio de sesión
$sessionPath = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $sessionPath);
session_start();

// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("dbConn.php"); // conexión a la base de datos

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Verificar que el tipo de usuario sea menor a 3 (1 = superadmin, 2 = admin)
if ($_SESSION['user']['tipo'] >= 3) {
    header('Location: index.php');
    exit;
}

// Si el tipo de usuario no está seteado en la sesión, obtenerlo de la base de datos
if (!isset($_SESSION['user']['tipo'])) {
    // Asumir que $_SESSION['user']['id'] contiene el ID del usuario
    if (isset($_SESSION['user']['id'])) {
        $stmtUser = $pdo->prepare("SELECT r.int_nivel_usuario AS tipo FROM tbl_usuarios u LEFT JOIN tbl_roles_usuario r ON u.int_rol = r.id_rol_usuario WHERE u.id_usuario = ?");
        $stmtUser->execute([$_SESSION['user']['id']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($user && isset($user['tipo'])) {
            $_SESSION['user']['tipo'] = $user['tipo'];
        } else {
            // Usuario no encontrado o sin rol, cerrar sesión y redirigir
            session_destroy();
            header('Location: index.php');
            exit;
        }
    } else {
        // No hay ID de usuario en sesión, cerrar sesión y redirigir
        session_destroy();
        header('Location: index.php');
        exit;
    }
}


// Agregar manejo de errores para mostrar detalles del error
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        echo "<pre>Error fatal: {$error['message']} en {$error['file']} línea {$error['line']}</pre>";
    }
});

$errors = [];
$success = false;

// Obtener todas las empresas (clientes con bit_es_empresa=1)
$stmtEmpresas = $pdo->query("SELECT id_cliente, chr_nombre_empresa FROM tbl_clientes WHERE bit_es_empresa = 1 AND bit_activo = 1 ORDER BY chr_nombre_empresa");
$empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

// ID de empresa seleccionada desde GET o POST
$selectedEmpresaId = null;
if (isset($_GET['empresa_id']) && is_numeric($_GET['empresa_id'])) {
    $selectedEmpresaId = intval($_GET['empresa_id']);
} elseif (isset($_POST['empresa_id']) && is_numeric($_POST['empresa_id'])) {
    $selectedEmpresaId = intval($_POST['empresa_id']);
}

// Manejar la adición de un nuevo empleado a la empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_employee'])) {
        $selectedEmpresaId = intval($_POST['empresa_id']);
        $nombre = trim($_POST['chr_nombre'] ?? '');
        $apellido = trim($_POST['chr_apellido'] ?? '');
        $email = trim($_POST['chr_email'] ?? '');
        $telefono = trim($_POST['chr_telefono'] ?? '');

        // Validar entradas
        if ($nombre === '') {
            $errors[] = "El nombre es obligatorio.";
        }
        if ($apellido === '') {
            $errors[] = "El apellido es obligatorio.";
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido.";
        }

        // Verificar si el email ya existe en tbl_clientes
        $stmtCheckEmail = $pdo->prepare("SELECT COUNT(*) FROM tbl_clientes WHERE chr_email = ?");
        $stmtCheckEmail->execute([$email]);
        if ($stmtCheckEmail->fetchColumn() > 0) {
            $errors[] = "El email ya está asignado a otro empleado.";
        }

        if (empty($errors)) {
            // Insertar nuevo empleado vinculado a la empresa
            // Dado que tbl_empleados_cliente no tiene chr_nombre, etc., primero debemos insertar en tbl_clientes y luego vincular
            try {
                $pdo->beginTransaction();

                // Insertar nuevo cliente como empleado
                $stmtInsertClient = $pdo->prepare("INSERT INTO tbl_clientes (chr_nombre, chr_apellido, chr_email, chr_telefono, bit_es_empresa, bit_activo) VALUES (?, ?, ?, ?, 0, 1)");
                $stmtInsertClient->execute([$nombre, $apellido, $email, $telefono]);
                $newClientId = $pdo->lastInsertId();

                // Insertar en tbl_empleados_cliente vinculando empresa y nuevo cliente empleado
                $stmtInsertEmpleado = $pdo->prepare("INSERT INTO tbl_empleados_cliente (id_cliente, id_empleado, fl_limite_credito_individual, fl_credito_disponible, bit_activo) VALUES (?, ?, 0, 0, 1)");
                $stmtInsertEmpleado->execute([$selectedEmpresaId, $newClientId]);

                $pdo->commit();
                $success = "Empleado agregado exitosamente.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Error al agregar empleado: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['assign_existing'])) {
        // Manejar la asignación de clientes existentes como empleados a la empresa
        $selectedEmpresaId = intval($_POST['empresa_id']);
        $selectedClients = $_POST['clients'] ?? [];

        if (empty($selectedClients)) {
            $errors[] = "Debe seleccionar al menos un cliente para asignar.";
        } else {
            // Insertar nuevas asignaciones de empleados con id_cliente (empresa) y id_empleado (cliente)
            $stmtInsert = $pdo->prepare("INSERT INTO tbl_empleados_cliente (id_cliente, id_empleado, fl_limite_credito_individual, fl_credito_disponible, bit_activo) VALUES (?, ?, 0, 0, 1)");

            try {
                $pdo->beginTransaction();
                foreach ($selectedClients as $clientId) {
                    $clientIdInt = intval($clientId);
                    // Verificar si ya está asignado
                    $stmtCheckAssign = $pdo->prepare("SELECT COUNT(*) FROM tbl_empleados_cliente WHERE id_empleado = ?");
                    $stmtCheckAssign->execute([$clientIdInt]);
                    if ($stmtCheckAssign->fetchColumn() == 0) {
                        $stmtInsert->execute([$selectedEmpresaId, $clientIdInt]);
                    }
                }
                $pdo->commit();
                $success = "Clientes asignados exitosamente como empleados.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Error al asignar clientes: " . $e->getMessage();
            }
        }
    }
}

// Obtener empleados asignados a la empresa seleccionada
$empleados = [];
if ($selectedEmpresaId !== null) {
    // Obtener empleados asignados a la empresa seleccionada con detalles del empleado desde tbl_clientes
    $stmtEmpleados = $pdo->prepare("
        SELECT e.id_empleado_cliente, c.chr_nombre, c.chr_apellido, c.chr_email, c.chr_telefono, e.fl_limite_credito_individual, e.fl_credito_disponible, e.bit_activo
        FROM tbl_empleados_cliente e
        JOIN tbl_clientes c ON e.id_empleado = c.id_cliente
        WHERE e.id_cliente = ?
        ORDER BY c.chr_nombre, c.chr_apellido
    ");
    $stmtEmpleados->execute([$selectedEmpresaId]);
    $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

    // Obtener clientes no empresa que aún no están asignados como empleados
    $stmtNonAssigned = $pdo->prepare("
        SELECT c.id_cliente, c.chr_nombre, c.chr_apellido, c.chr_email
        FROM tbl_clientes c
        WHERE c.bit_es_empresa = 0
          AND c.bit_activo = 1
          AND c.id_cliente NOT IN (
              SELECT DISTINCT e.id_empleado
              FROM tbl_empleados_cliente e
          )
        ORDER BY c.chr_nombre, c.chr_apellido
    ");
    $stmtNonAssigned->execute();
    $nonAssignedClients = $stmtNonAssigned->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Asignaciones de Empleados a Empresas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            padding-left: 230px;
        }
        .container {
            max-width: 900px;
            margin-top: 40px;
            margin-left: 0;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h2>Asignar Empleados a Empresas</h2>

        <form method="get" action="asignaciones.php" class="mb-4">
            <div class="mb-3">
                <label for="empresa_id" class="form-label">Seleccione una Empresa</label>
                <select id="empresa_id" name="empresa_id" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= htmlspecialchars($empresa['id_cliente']) ?>" <?= ($empresa['id_cliente'] == $selectedEmpresaId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['chr_nombre_empresa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($selectedEmpresaId === null): ?>
            <p>Por favor, seleccione una empresa para ver y asignar empleados.</p>
        <?php else: ?>
            <h3>Empleados asignados a la empresa</h3>
            <?php if (empty($empleados)): ?>
                <p>No hay empleados asignados a esta empresa.</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Límite Crédito</th>
                            <th>Crédito Disponible</th>
                            <th>Activo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?= htmlspecialchars($empleado['chr_nombre']) ?></td>
                                <td><?= htmlspecialchars($empleado['chr_apellido']) ?></td>
                                <td><?= htmlspecialchars($empleado['chr_email']) ?></td>
                                <td><?= htmlspecialchars($empleado['chr_telefono']) ?></td>
                                <td><?= number_format($empleado['fl_limite_credito_individual'], 2) ?></td>
                                <td><?= number_format($empleado['fl_credito_disponible'], 2) ?></td>
                                <td><?= $empleado['bit_activo'] ? 'Sí' : 'No' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Asignar Clientes Existentes como Empleados</h3>
            <?php if (!empty($nonAssignedClients)): ?>
                <form method="post" action="asignaciones.php" class="mb-4">
                    <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($selectedEmpresaId) ?>">
                    <input type="hidden" name="assign_existing" value="1">
                    <div class="mb-3">
                        <label>Seleccione los clientes para asignar como empleados:</label>
                        <div class="form-check">
                            <?php foreach ($nonAssignedClients as $client): ?>
                                <input class="form-check-input" type="checkbox" name="clients[]" value="<?= htmlspecialchars($client['id_cliente']) ?>" id="client_<?= htmlspecialchars($client['id_cliente']) ?>">
                                <label class="form-check-label" for="client_<?= htmlspecialchars($client['id_cliente']) ?>">
                                    <?= htmlspecialchars($client['chr_nombre'] . ' ' . $client['chr_apellido'] . ' (' . $client['chr_email'] . ')') ?>
                                </label><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Asignar Seleccionados</button>
                </form>
            <?php else: ?>
                <p>No hay clientes disponibles para asignar como empleados.</p>
            <?php endif; ?>

            <h3>Agregar Nuevo Empleado</h3>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="asignaciones.php">
                <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($selectedEmpresaId) ?>">
                <input type="hidden" name="add_employee" value="1">

                <div class="mb-3">
                    <label for="chr_nombre" class="form-label">Nombre</label>
                    <input type="text" id="chr_nombre" name="chr_nombre" class="form-control" required value="<?= isset($_POST['chr_nombre']) ? htmlspecialchars($_POST['chr_nombre']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="chr_apellido" class="form-label">Apellido</label>
                    <input type="text" id="chr_apellido" name="chr_apellido" class="form-control" required value="<?= isset($_POST['chr_apellido']) ? htmlspecialchars($_POST['chr_apellido']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="chr_email" class="form-label">Email</label>
                    <input type="email" id="chr_email" name="chr_email" class="form-control" required value="<?= isset($_POST['chr_email']) ? htmlspecialchars($_POST['chr_email']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="chr_telefono" class="form-label">Teléfono</label>
                    <input type="text" id="chr_telefono" name="chr_telefono" class="form-control" value="<?= isset($_POST['chr_telefono']) ? htmlspecialchars($_POST['chr_telefono']) : '' ?>">
                </div>
                <button type="submit" class="btn btn-primary">Agregar Empleado</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
