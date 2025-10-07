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

                // Fetch user role and store in session
                $stmtRole = $pdo->prepare("SELECT r.int_nivel_usuario AS tipo FROM tbl_roles_usuario r WHERE r.id_rol_usuario = ?");
                $stmtRole->execute([$user['int_rol']]);
                $role = $stmtRole->fetch(PDO::FETCH_ASSOC);
                if ($role && isset($role['tipo'])) {
                    $_SESSION['user']['tipo'] = $role['tipo']; // guardar el tipo de usuario
                } else {
                    $_SESSION['user']['tipo'] = 3; // o si no, asignar un valor por defecto
                }
                
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

// Check if user session exists, if not show login form and skip user type check
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    // User not logged in, show login form (rest of the page)
} else {
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
                error_log("Destroying session and redirecting to index.php due to missing user type or id.");
                error_log("Session before destroy: " . print_r($_SESSION, true));
                error_log("Cookies before destroy: " . print_r($_COOKIE, true));
                session_destroy();
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                header('Location: index.php');
                exit;
            }
        } else {
            // No hay ID de usuario en sesión, cerrar sesión y redirigir
            error_log("Destroying session and redirecting to index.php due to missing user type or id.");
            error_log("Session before destroy: " . print_r($_SESSION, true));
            error_log("Cookies before destroy: " . print_r($_COOKIE, true));
            session_destroy();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            header('Location: index.php');
            exit;
        }
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión - Tienda en línea</title>
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
        <h1>Iniciar sesión</h1>
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
            <button type="submit">Iniciar sesión</button>
        </form>
    </div>
</body>
</html>
