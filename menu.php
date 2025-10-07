<?php
// menu.php
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

// **1. Definir elementos del menú (estructura jerárquica)**
$menu_items = array(
    "plaza.php" => "Inicio",
    "products.php" => "Productos",
    "Mis detalles" => array(
        "detalle_cliente.php" => "Detalles", // Entrada original
        "under_construction.php" => "Documentos",
        "under_construction.php" => "Accesos",
        "linea_de_credito.php" => "Linea de crédito"
    ),
    "Pedidos" => array(
        "cuentas_x_cobrar.php" => "Alta nuevo pedido",
        "under_construction.php" => "Por Autorizar",
        "under_construction.php" => "Autorizados",
        "under_construction.php" => "Rechazados"
    ),
    "Facturación" => array(
        "under_construction.php" => "Listado Facturas",
        "under_construction.php" => "Reportes"
    ),
    "Usuarios" => array(
        "nuevo_cliente.php" => "Crear",
        "listado_usuarios.php" => "Listado",
        "buscar_usuario.php" => "Buscador",
        "asignaciones.php" => "Asignaciones"
    ),
    "Mi portal" => array(
        "under_construction.php" => "Logo",
        "subir_imagen.php" => "Banners"
    ),
    "logout.php" => "Cerrar Sesión"
);

// **2. Obtener la página actual (ejemplo)**
$current_page = basename($_SERVER['PHP_SELF']);

// ** Filtrar menú según el tipo de usuario **
$user_tipo = $_SESSION['user']['tipo'] ?? 0;
if ($user_tipo > 2) {
    // Ocultar asignaciones.php y nuevo_cliente.php, y el menú Pedidos solo si user_tipo > 2
    unset($menu_items['Pedidos']);
    // Eliminar "nuevo_cliente.php" y "asignaciones.php" de "Usuarios"
    if (isset($menu_items['Usuarios'])) {
        unset($menu_items['Usuarios']['nuevo_cliente.php']);
        unset($menu_items['Usuarios']['asignaciones.php']);
        // Ocultar listado_usuarios.php para user_tipo > 2
        unset($menu_items['Usuarios']['listado_usuarios.php']);
    }
    // Asegurar que products.php esté visible para todos los roles, no eliminarlo
}
// **3. Iniciar salida HTML**
echo '<div class="menu" style="position: fixed; left: 0; top: 0; width: 200px; height: 100%; overflow-y: auto; background-color: #f8f9fa; padding: 15px;">'; // Estilo de barra lateral fija
echo '  <h3>Navegación</h3>';
echo '  <ul class="list-unstyled">'; // Usar la clase list-unstyled de Bootstrap

// **4. Generar elementos del menú dinámicamente**

foreach ($menu_items as $key => $value) {
    echo '<li>';
    if (is_array($value)) {
        // Generar un ID único para el elemento collapse
        $collapse_id = str_replace(' ', '_', strtolower($key)) . "_collapse";

        echo '<a class="btn btn-link" data-toggle="collapse" href="#' . $collapse_id . '" role="button" aria-expanded="false" aria-controls="' . $collapse_id . '">';
        echo $key; // Etiqueta padre
        echo '</a>';

        echo '<div class="collapse" id="' . $collapse_id . '">';
        echo '<ul class="list-unstyled">';

        foreach ($value as $url => $label) {
            $active_class = ($url == $current_page) ? ' class="active"' : '';
            // Remove appending client id from URLs
            echo '<li><a href="' . htmlspecialchars($url) . '"' . $active_class . '>' . htmlspecialchars($label) . '</a></li>';
        }
        echo '</ul>';
        echo '</div>'; // .collapse
    } else {
        $active_class = ($key == $current_page) ? ' class="active"' : '';
        echo '<a href="' . htmlspecialchars($key) . '" class="btn btn-link' . $active_class . '">' . htmlspecialchars($value) . '</a>';
    }
    echo '</li>';
}


echo '  </ul>';
echo '</div>';

// **5. Enlace a Bootstrap CSS (¡Importante!)**
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';

// **6. Inicialización de JavaScript (Crucial para contenido dinámico)**
echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>';
echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';

// **7. Inicialización de JavaScript (Crucial para contenido dinámico)**
echo '<script>
    $(document).ready(function() {
        $(".collapse").collapse("hide"); // Inicializar todos los elementos collapse para que estén ocultos
    });
</script>';

?>
