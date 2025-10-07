<?php
// Iniciar sesión y verificar inicio de sesión
$rutaSesion = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $rutaSesion);
session_start();

require_once("dbConn.php"); // Ajustar la ruta si dbConn.php está en un directorio diferente

if (!isset($_SESSION['usuario'])) {
    // Check if old session key 'user' exists and migrate it
    if (isset($_SESSION['user'])) {
        $_SESSION['usuario'] = $_SESSION['user'];
        unset($_SESSION['user']);
    } else {
        // Debug output to check session contents before redirect
        error_log('Session usuario not set. Session contents: ' . print_r($_SESSION, true));
        header('Location: index.php');
        exit;
    }
}

error_log("Session usuario data before upload: " . print_r($_SESSION['usuario'] ?? [], true));
if (isset($_SESSION['usuario']) && (empty($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['client_id']))) {
    if (!empty($_SESSION['usuario']['login'])) {
        $stmtUser = $pdo->prepare("SELECT id_usuario, id_cliente FROM tbl_usuarios WHERE chr_login = ? AND bit_activo = 1 LIMIT 1");
        $stmtUser->execute([$_SESSION['usuario']['login']]);
        $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            $_SESSION['usuario']['id'] = $userData['id_usuario'];
            $_SESSION['usuario']['client_id'] = $userData['id_cliente'];
        }
    }
}

include 'menu.php'; // Include menu inside body for navigation

// Manejar envío de formulario para asignar credenciales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_login'])) {
    $idCliente = $_POST['id_cliente'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    
    if (empty($usuario) || empty($contrasena)) {
        $error = "Se requieren nombre de usuario y contraseña";
    } else {
        // Generar sal y hash de la contraseña
        $sal = bin2hex(random_bytes(32));
        $contrasenaHasheada = hash('sha256', $contrasena . $sal);
        
        try {
            // Insertar en tbl_usuarios
            $stmt = $pdo->prepare("
                INSERT INTO tbl_usuarios 
                (chr_login, chr_password, chr_salt, int_status, bit_activo, id_cliente)
                VALUES (?, ?, ?, 1, 1, ?)
            ");
            $stmt->execute([$usuario, $contrasenaHasheada, $sal, $idCliente]);
            
            $exito = "¡Credenciales asignadas con éxito!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "El nombre de usuario ya existe";
            } else {
                $error = "Error al asignar credenciales: " . $e->getMessage();
            }
        }
    }
}

// Obtener todos los clientes
$clientes = $pdo->query("SELECT * FROM tbl_clientes")->fetchAll(PDO::FETCH_ASSOC);

// Obtener usuarios con su información de cliente
$usuarios = $pdo->query("
    SELECT u.*, c.chr_nombre, c.chr_email 
    FROM tbl_usuarios u
    JOIN tbl_clientes c ON u.id_cliente = c.id_cliente
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Adjust table width to match detalle_cliente.php */
        .container {
            margin-left: 220px; /* Offset for fixed menu */
            max-width: 900px; /* Match detalle_cliente.php max width */
        }
        .table-responsive {
            width: 100%;
        }
        .table {
            width: 100% !important;
        }
        /* Remove narrow-table styles */

        /* Header styles from plaza.php */
        .header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 220px;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
<div class="container mt-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (isset($exito)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Tabla de Usuarios Existentes -->
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Usuarios Existentes</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                                        <td><?= htmlspecialchars($cliente['chr_nombre']) ?></td>
                                        <td><?= htmlspecialchars($cliente['chr_email']) ?></td>
                                        <td>
                                            <?php 
                                                $usuario = array_filter($usuarios, function($u) use ($cliente) {
                                                    return $u['id_cliente'] == $cliente['id_cliente'];
                                                });
                                                echo !empty($usuario) ? 'Asignado' : 'No asignado';
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary assign-btn" 
                                                    data-client-id="<?= $cliente['id_cliente'] ?>"
                                                    data-client-name="<?= htmlspecialchars($cliente['chr_nombre']) ?>">
                                                Asignar
                                            </button>
                                            <button class="btn btn-sm btn-info view-client-btn" 
                                                    data-client-id="<?= $cliente['id_cliente'] ?>"
                                                    data-client-name="<?= htmlspecialchars($cliente['chr_nombre']) ?>"
                                                    style="margin-left: 5px;">
                                                Ver
                                            </button>
                                            <button class="btn btn-sm btn-success upload-btn"
                                                    data-client-id="<?= $cliente['id_cliente'] ?>"
                                                    data-client-name="<?= htmlspecialchars($cliente['chr_nombre']) ?>"
                                                    style="margin-left: 5px;">
                                                Subir
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
