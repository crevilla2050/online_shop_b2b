<?php
include 'init_session.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Database connection
require_once 'dbConn.php';

// Get client info
$stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = ?");
$stmt->execute([$_SESSION['user']['client_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Tienda en línea b2b Test Version</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="styles/header.css" rel="stylesheet" />
</head>
<body>
    <?php include 'menu.php'; ?>
    <?php include 'header.php'; ?>
    
    <div class="container" style="margin-left: 220px;">
        <div class="welcome-message">
            <h2>Bienvenido, <?php echo htmlspecialchars($client['chr_nombre'] ?? 'User'); ?>!</h2>
            <p>Estás dentro del sistema de compras en línea de prueba de tienda online b2b (test version).</p>
            
            <div class="client-info">
                <h3>Tu Información:</h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($client['chr_email'] ?? 'N/A'); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($client['chr_telefono'] ?? 'N/A'); ?></p>
                <?php if ($client['bit_es_empresa'] ?? false): ?>
                    <p><strong>Compañía:</strong> <?php echo htmlspecialchars($client['chr_nombre_empresa'] ?? 'N/A'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
