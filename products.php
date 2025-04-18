<?php
// Incluir conexión a la base de datos
require_once 'dbConn.php';

// Obtener todas las categorías activas para el desplegable
$categoryStmt = $pdo->prepare("SELECT id_categoria, chr_nombre FROM tbl_categorias WHERE bit_activo = 1 ORDER BY chr_nombre");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll();

// Obtener categoría seleccionada y término de búsqueda de los parámetros GET
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construir consulta base para productos con joins a categorías e imágenes
$query = "
    SELECT p.id_producto, p.chr_nombre_prod, p.chr_desc_prod, c.chr_nombre AS categoria,
           i.chr_ruta AS imagen_ruta, i.chr_alt_text AS imagen_alt
    FROM tbl_productos p
    INNER JOIN tbl_categorias c ON p.id_categoria = c.id_categoria
    LEFT JOIN tbl_productos_imagenes pi ON p.id_producto = pi.id_producto
    LEFT JOIN tbl_imagenes i ON pi.id_imagen = i.id_imagen AND i.bit_activo = 1
    WHERE p.int_activo = 1
";

// Array de parámetros para la consulta preparada
$params = [];

// Filtrar por categoría si está seleccionada
if ($selectedCategory > 0) {
    $query .= " AND p.id_categoria = :category ";
    $params[':category'] = $selectedCategory;
}

// Filtrar por término de búsqueda si se proporciona (buscar en nombre y descripción del producto)
if ($searchTerm !== '') {
    $query .= " AND (p.chr_nombre_prod LIKE :search OR p.chr_desc_prod LIKE :search) ";
    $params[':search'] = '%' . $searchTerm . '%';
}

$query .= " ORDER BY p.chr_nombre_prod ASC ";

$productStmt = $pdo->prepare($query);
$productStmt->execute($params);
$products = $productStmt->fetchAll();

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { margin: 20px; }
        .menu-container { width: 200px; float: left; background-color: #f0f0f0; padding: 10px; margin-right: 20px; }
        .content-container { margin-left: 220px; }
        .product { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; display: flex; align-items: center; }
        .product img { max-width: 100px; max-height: 100px; margin-right: 15px; object-fit: contain; }
        .product-info { flex: 1; }
        .product-name { font-weight: bold; font-size: 1.2em; margin-bottom: 5px; }
        .product-desc { color: #555; }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="content-container">
        <h1>Productos</h1>
        <form method="get" action="products.php" class="mb-3 d-flex flex-wrap align-items-center gap-2">
            <label for="category" class="form-label mb-0 me-2">Categoría:</label>
            <select name="category" id="category" class="form-select" style="width: auto;">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= h($category['id_categoria']) ?>" <?= $selectedCategory === (int)$category['id_categoria'] ? 'selected' : '' ?>>
                        <?= h($category['chr_nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="search" class="form-label mb-0 ms-3 me-2">Buscar:</label>
            <input type="text" name="search" id="search" value="<?= h($searchTerm) ?>" placeholder="Buscar productos..." class="form-control" style="width: 250px;" />
            <button type="submit" class="btn btn-primary ms-3">Filtrar</button>
        </form>

        <?php if (count($products) === 0): ?>
            <p>No se encontraron productos.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product d-flex align-items-center mb-3 border rounded p-2">
                    <?php if (!empty($product['imagen_ruta']) && file_exists($product['imagen_ruta'])): ?>
                        <img src="<?= h($product['imagen_ruta']) ?>" alt="<?= h($product['imagen_alt']) ?>" class="me-3" style="max-width: 100px; max-height: 100px; object-fit: contain;" />
                    <?php else: ?>
                        <img src="placeholder.png" alt="Imagen no disponible" class="me-3" style="max-width: 100px; max-height: 100px; object-fit: contain;" />
                    <?php endif; ?>
                    <div class="product-info flex-grow-1">
                        <div class="product-name fw-bold fs-5 mb-1"><?= h($product['chr_nombre_prod']) ?></div>
                        <div class="product-desc text-secondary mb-1"><?= h($product['chr_desc_prod']) ?></div>
                        <div class="product-category fst-italic">Categoría: <?= h($product['categoria']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
