<?php
// Set custom session path that's writable by web server
$sessionPath = '/var/www/html/online_shop_b2b/sessions';
if (!file_exists($sessionPath)) {
    if (!mkdir($sessionPath, 0770, true) && !is_dir($sessionPath)) {
        error_log("Failed to create session directory: " . $sessionPath);
    }
}
ini_set('session.save_path', $sessionPath);
session_start();
error_log("Session save path set to: " . ini_get('session.save_path'));

// Database connection
require_once 'dbConn.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($login) && !empty($password)) {
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE chr_login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verify password (assuming it's hashed with salt)
            $hashedPassword = hash('sha256', $password . $user['chr_salt']);
            if ($hashedPassword === $user['chr_password']) {
                // Get client info
                $stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = ?");
                $stmt->execute([$user['id_cliente']]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Store user in session
                $_SESSION['user'] = [
                    'id' => $user['id_usuario'],
                    'login' => $user['chr_login'],
                    'client_id' => $user['id_cliente'],
                    'client_name' => $client['chr_nombre'] ?? '',
                    'client_email' => $client['chr_email'] ?? ''
                ];
                
                // Debug logging
                error_log("Login successful for user: " . $user['chr_login']);
                error_log("Session data: " . print_r($_SESSION, true));
                
                // Redirect to plaza
                header('Location: plaza.php');
                exit; // End of successful login logic
            }
        }
        
        $error = 'Credenciales Incorrectas';
    } else {
        $error = 'Por favor introduzca su nombre de usuario y contraseña';
    }
}

// Check if the requested page exists
$request_uri = $_SERVER['REQUEST_URI'];
$requested_page = basename($request_uri);

$valid_pages = ['index.php', 'plaza.php', 'logout.php', 'detalle_cliente.php', 'nuevo_cliente.php', 'ajax_codigo_postal.php']; // Add other valid pages as needed

if (!in_array($requested_page, $valid_pages)) {
    header('Location: under_construction.php'); // Redirect to under construction page
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="login">Usuario</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
