<?php
$sessionPath = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $sessionPath);
session_start();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Tienda en línea b2b Test Version</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .welcome-message {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        .client-info {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="header" style="margin-left: 220px;">
        <h1>Tienda en línea b2b Test Version</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    
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
