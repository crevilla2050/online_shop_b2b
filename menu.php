<?php
// menu.php

// **1. Define Menu Items (Hierarchical Structure)**
$menu_items = array(
    "plaza.php" => "Inicio",
    "products.php" => "Productos",
    "Datos Empresa" => array(
        "detalle_cliente.php" => "Datos fiscales", // Original entry
        "under_construction.php" => "Documentos",
        "under_construction.php" => "Accesos",
        "linea_de_credito.php" => "Linea de crédito"
    ),
    "Pedidos" => array(
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
        "under_construction.php" => "Buscador"
    ),
    "Mi portal" => array(
        "under_construction.php" => "Logo",
        "under_construction.php" => "Banners"
    ),
    "logout.php" => "Cerrar Sesión"
);

// **2. Get the Current Page (Example)**
$current_page = basename($_SERVER['PHP_SELF']);

// **3. Start HTML Output**
echo '<div class="menu" style="position: fixed; left: 0; top: 0; width: 200px; height: 100%; overflow-y: auto; background-color: #f8f9fa; padding: 15px;">'; // Fixed sidebar style
echo '  <h3>Navegación</h3>';
echo '  <ul class="list-unstyled">'; // Use Bootstrap's list-unstyled class

// **4. Generate Menu Items Dynamically**
foreach ($menu_items as $key => $value) {
    echo '<li>';
    if (is_array($value)) {
        // Generate a unique ID for the collapse element
        $collapse_id = str_replace(' ', '_', strtolower($key)) . "_collapse";

        echo '<a class="btn btn-link" data-toggle="collapse" href="#' . $collapse_id . '" role="button" aria-expanded="false" aria-controls="' . $collapse_id . '">';
        echo $key; // Parent label
        echo '</a>';

        echo '<div class="collapse" id="' . $collapse_id . '">';
        echo '<ul class="list-unstyled">';
        foreach ($value as $url => $label) {
            $active_class = ($url == $current_page) ? ' class="active"' : '';
if ($url === "detalle_cliente.php" || $url === "linea_de_credito.php") {
    $client_id = $_SESSION['usuario']['id_cliente'] ?? $_SESSION['usuario']['client_id'] ?? '';
    if ($client_id) {
        // Check if URL already has query parameters
        if (strpos($url, '?') === false) {
            $url .= '?id=' . htmlspecialchars($client_id);
        } else {
            $url .= '&id=' . htmlspecialchars($client_id);
        }
    }
}
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

// **5. Link to Bootstrap CSS (Important!)**
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';

// **6. JavaScript Initialization (Crucial for Dynamic Content)**
echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>';
echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';

// **7. JavaScript Initialization (Crucial for Dynamic Content)**
echo '<script>
    $(document).ready(function() {
        $(".collapse").collapse("hide"); // Initialize all collapse elements to be hidden
    });
</script>';

?>
