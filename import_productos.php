<?php
// import_productos.php
// Este script importa datos de productos desde productos.json a la base de datos
// Replica la lógica de import_productos.sql y import_productos_inserts.sql

// Parámetros de conexión a la base de datos - reemplazar con credenciales reales
$dbHost = 'localhost';
$dbName = 'db_online_shop';
$dbUser = 'root';
$dbPass = 't4a2x0a6';

// Conectar a MySQL usando PDO
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fallo en la conexión a la base de datos: " . $e->getMessage());
}

// Leer productos.json
$jsonFile = 'productos.json';
if (!file_exists($jsonFile)) {
    die("Archivo JSON no encontrado: $jsonFile");
}
$jsonData = file_get_contents($jsonFile);
$products = json_decode($jsonData, true);
if ($products === null) {
    die("Error al decodificar los datos JSON");
}

// Función auxiliar para obtener el ID de categoría por nombre, insertar si no existe
function getCategoryId(PDO $pdo, $categoryName) {
    $stmt = $pdo->prepare("SELECT id_categoria FROM tbl_categorias WHERE chr_nombre = :name");
    $stmt->execute([':name' => $categoryName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return $row['id_categoria'];
    } else {
        // Insertar nueva categoría
        $insert = $pdo->prepare("INSERT INTO tbl_categorias (chr_nombre, bit_activo) VALUES (:name, 1)");
        $insert->execute([':name' => $categoryName]);
        return $pdo->lastInsertId();
    }
}

// Función auxiliar para obtener el ID de tipo de identificador por nombre, insertar si no existe
function getIdentifierTypeId(PDO $pdo, $typeName) {
    $stmt = $pdo->prepare("SELECT id_identificador_tipo FROM tbl_identificadores_tipos WHERE chr_nombre = :name");
    $stmt->execute([':name' => $typeName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return $row['id_identificador_tipo'];
    } else {
        // Insertar nuevo tipo de identificador
        $insert = $pdo->prepare("INSERT INTO tbl_identificadores_tipos (chr_nombre, chr_descripcion, bit_activo) VALUES (:name, :desc, 1)");
        $insert->execute([':name' => $typeName, ':desc' => $typeName]);
        return $pdo->lastInsertId();
    }
}

// Pre-cargar tipos de identificadores
$identifierTypes = [
    'SKU' => getIdentifierTypeId($pdo, 'SKU'),
    'Serial Number' => getIdentifierTypeId($pdo, 'Serial Number'),
    'UPC' => getIdentifierTypeId($pdo, 'UPC'),
    'Part Number' => getIdentifierTypeId($pdo, 'Part Number'),
];

// Preparar sentencias para inserciones
$insertProductStmt = $pdo->prepare("INSERT INTO tbl_productos (chr_nombre_prod, chr_desc_prod, id_categoria, int_activo, bit_es_combo) VALUES (:name, :desc, :category_id, :active, 0)");
$insertIdentifierStmt = $pdo->prepare("INSERT INTO tbl_productos_identificadores (id_producto, id_identificador_tipo, chr_valor) VALUES (:product_id, :type_id, :value)");
$insertPriceStmt = $pdo->prepare("INSERT INTO tbl_precios_productos (id_producto, fl_precio, dt_fecha_inicio) VALUES (:product_id, :price, NOW())");
$insertImageStmt = $pdo->prepare("INSERT INTO tbl_imagenes (chr_nombre, chr_ruta, chr_alt_text, bit_activo) VALUES (:name, :path, :alt, 1)");
$insertProductImageStmt = $pdo->prepare("INSERT INTO tbl_productos_imagenes (id_producto, id_imagen) VALUES (:product_id, :image_id)");
$insertSpecStmt = $pdo->prepare("INSERT INTO tbl_productos_especificaciones (id_producto, chr_clave, chr_valor) VALUES (:product_id, :key, :value)");
$insertPromoStmt = $pdo->prepare("INSERT INTO tbl_productos_promociones (id_producto, chr_promocion) VALUES (:product_id, :promotion)");

// Función para descargar imagen y guardar localmente
function downloadImage($url, $saveDir = 'images') {
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0755, true);
    }
    $imageName = basename(parse_url($url, PHP_URL_PATH));
    $savePath = $saveDir . '/' . $imageName;
    if (!file_exists($savePath)) {
        $imageData = @file_get_contents($url);
        if ($imageData !== false) {
            file_put_contents($savePath, $imageData);
        } else {
            return null;
        }
    }
    return $savePath;
}

// Iniciar transacción
$pdo->beginTransaction();

try {
    foreach ($products as $product) {
        // Obtener o insertar categoría
        $categoryName = $product['categoria'] ?? 'Otros';
        $categoryId = getCategoryId($pdo, $categoryName);

        // Insertar producto
        $insertProductStmt->execute([
            ':name' => $product['nombre'] ?? '',
            ':desc' => $product['descripcion_corta'] ?? '',
            ':category_id' => $categoryId,
            ':active' => isset($product['activo']) ? (int)$product['activo'] : 1,
        ]);
        $productId = $pdo->lastInsertId();

        // Insertar identificadores
        if (!empty($product['clave'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['SKU'],
                ':value' => $product['clave'],
            ]);
        }
        if (!empty($product['ean'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['UPC'],
                ':value' => $product['ean'],
            ]);
        }
        if (!empty($product['upc'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['UPC'],
                ':value' => $product['upc'],
            ]);
        }
        if (!empty($product['numParte'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['Part Number'],
                ':value' => $product['numParte'],
            ]);
        }

        // Insertar precio
        if (isset($product['precio'])) {
            $insertPriceStmt->execute([
                ':product_id' => $productId,
                ':price' => $product['precio'],
            ]);
        }

        // Insertar imagen
        if (!empty($product['imagen'])) {
            $imagePath = downloadImage($product['imagen']);
            if ($imagePath) {
                $imageName = basename($imagePath);
                $insertImageStmt->execute([
                    ':name' => $imageName,
                    ':path' => $imagePath,
                    ':alt' => $product['nombre'] ?? '',
                ]);
                $imageId = $pdo->lastInsertId();
                // Vincular imagen al producto
                $insertProductImageStmt->execute([
                    ':product_id' => $productId,
                    ':image_id' => $imageId,
                ]);
            }
        }

        // Insertar especificaciones
        if (!empty($product['especificaciones']) && is_array($product['especificaciones'])) {
            foreach ($product['especificaciones'] as $spec) {
                if (isset($spec['tipo']) && isset($spec['valor'])) {
                    $insertSpecStmt->execute([
                        ':product_id' => $productId,
                        ':key' => $spec['tipo'],
                        ':value' => $spec['valor'],
                    ]);
                }
            }
        }

        // Insertar promociones
        if (!empty($product['promociones']) && is_array($product['promociones'])) {
            foreach ($product['promociones'] as $promo) {
                if (isset($promo['promocion'])) {
                    $promoText = '';
                    if (isset($promo['tipo'])) {
                        $promoText .= $promo['tipo'] . ': ';
                    }
                    $promoText .= $promo['promocion'];
                    $insertPromoStmt->execute([
                        ':product_id' => $productId,
                        ':promotion' => $promoText,
                    ]);
                }
            }
        }
    }
    // Confirmar transacción
    $pdo->commit();
    echo "Importación completada con éxito.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "La importación falló: " . $e->getMessage() . "\n";
}
?>
