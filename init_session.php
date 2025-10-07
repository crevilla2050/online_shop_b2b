<?php
// init_session.php
// Common session initialization and user role setup

$sessionPath = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $sessionPath);
session_start();

require_once 'dbConn.php';

// Set user role in session if not already set
if (!isset($_SESSION['user']['tipo'])) {
    if (isset($_SESSION['user']['id'])) {
        $stmtUser = $pdo->prepare("SELECT r.int_nivel_usuario AS tipo FROM tbl_usuarios u LEFT JOIN tbl_roles_usuario r ON u.int_rol = r.id_rol_usuario WHERE u.id_usuario = ?");
        $stmtUser->execute([$_SESSION['user']['id']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($user && isset($user['tipo'])) {
            $_SESSION['user']['tipo'] = $user['tipo'];
        } else {
            // User not found or no role, assign default role 0
            $_SESSION['user']['tipo'] = -1;
        }
    } else {
        // No user id in session, assign default role 0
        $_SESSION['user']['tipo'] = -1;
    }
}
?>
