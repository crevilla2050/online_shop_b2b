<?php
// Iniciar sesión y verificar inicio de sesión
$rutaSesion = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $rutaSesion);
session_start();

require_once("dbConn.php");

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$searchTerm = '';
$usuarios = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    if ($searchTerm !== '') {
        $searchTermLike = '%' . $searchTerm . '%';
        $stmt = $pdo->prepare("
            SELECT u.*, c.chr_nombre, c.chr_email 
            FROM tbl_usuarios u
            JOIN tbl_clientes c ON u.id_cliente = c.id_cliente
            WHERE u.chr_login LIKE ? OR c.chr_nombre LIKE ? OR c.chr_email LIKE ?
        ");
        $stmt->execute([$searchTermLike, $searchTermLike, $searchTermLike]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include 'menu.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resultados de Búsqueda de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .container {
            margin-left: 220px; /* Offset for fixed menu */
            max-width: 900px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            margin-left: 220px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h3>Resultados de Búsqueda de Usuarios</h3>
        <form method="get" class="mb-4" action="user_search.php">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por usuario, nombre o correo" value="<?= htmlspecialchars($searchTerm) ?>" />
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])): ?>
            <?php if (count($usuarios) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Usuario</th>
                                <th>Nombre Cliente</th>
                                <th>Correo Cliente</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                                    <td><?= htmlspecialchars($usuario['chr_nombre']) ?></td>
                                    <td><?= htmlspecialchars($usuario['chr_email']) ?></td>
                                    <td><?= htmlspecialchars($usuario['chr_login']) ?></td>
                                    <td>
                                        <a href="detalle_cliente.php?id=<?= urlencode($usuario['id_cliente']) ?>" class="btn btn-sm btn-info">Ver Cliente</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No se encontraron usuarios que coincidan con la búsqueda.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
