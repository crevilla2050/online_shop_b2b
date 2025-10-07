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
    echo "Conexión a la base de datos exitosa.\n";
} catch (PDOException $e) {
    die("Fallo en la conexión a la base de datos: " . $e->getMessage());
}

// Leer productos.json
$jsonFile = 'productos.json';
if (!file_exists($jsonFile)) {
    die("Archivo JSON no encontrado: $jsonFile");
}
echo "Archivo JSON encontrado: $jsonFile\n";
$jsonData = file_get_contents($jsonFile);
$products = json_decode($jsonData, true);
if ($products === null) {
    die("Error al decodificar los datos JSON");
}
echo "Datos JSON decodificados correctamente.\n";

// Función auxiliar para obtener el ID de categoría por nombre, insertar si no existe
function getCategoryId(PDO $pdo, $categoryName) {
    $stmt = $pdo->prepare("SELECT id_categoria FROM tbl_categorias WHERE chr_nombre = :name");
    $stmt->execute([':name' => $categoryName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Categoría encontrada: $categoryName (ID: " . $row['id_categoria'] . ")\n";
        return $row['id_categoria'];
    } else {
        // Insertar nueva categoría
        $insert = $pdo->prepare("INSERT INTO tbl_categorias (chr_nombre, bit_activo) VALUES (:name, 1)");
        $insert->execute([':name' => $categoryName]);
        $newId = $pdo->lastInsertId();
        echo "Categoría insertada: $categoryName (ID: $newId)\n";
        return $newId;
    }
}

// Función auxiliar para obtener el ID de tipo de identificador por nombre, insertar si no existe
function getIdentifierTypeId(PDO $pdo, $typeName) {
    $stmt = $pdo->prepare("SELECT id_identificador_tipo FROM tbl_identificadores_tipos WHERE chr_nombre = :name");
    $stmt->execute([':name' => $typeName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Tipo de identificador encontrado: $typeName (ID: " . $row['id_identificador_tipo'] . ")\n";
        return $row['id_identificador_tipo'];
    } else {
        // Insertar nuevo tipo de identificador
        $insert = $pdo->prepare("INSERT INTO tbl_identificadores_tipos (chr_nombre, chr_descripcion, bit_activo) VALUES (:name, :desc, 1)");
        $insert->execute([':name' => $typeName, ':desc' => $typeName]);
        $newId = $pdo->lastInsertId();
        echo "Tipo de identificador insertado: $typeName (ID: $newId)\n";
        return $newId;
    }
}

// Pre-cargar tipos de identificadores
$identifierTypes = [
    'SKU' => getIdentifierTypeId($pdo, 'SKU'),
    'Serial Number' => getIdentifierTypeId($pdo, 'Serial Number'),
    'UPC' => getIdentifierTypeId($pdo, 'UPC'),
    'Part Number' => getIdentifierTypeId($pdo, 'Part Number'),
];
echo "Tipos de identificadores pre-cargados.\n";

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
        echo "Directorio de imágenes creado: $saveDir\n";
    }
    $imageName = basename(parse_url($url, PHP_URL_PATH));
    $savePath = $saveDir . '/' . $imageName;
    if (!file_exists($savePath)) {
        echo "Descargando imagen: $url\n";
        $context = stream_context_create([
            'http' => [
                'timeout' => 15, // 5 seconds timeout
            ]
        ]);
        $imageData = @file_get_contents($url, false, $context);
        if ($imageData !== false) {
            file_put_contents($savePath, $imageData);
            echo "Imagen descargada: $url\n";
        } else {
            echo "Error al descargar la imagen (timeout o inaccesible): $url\n";
            return null;
        }
    } else {
        echo "Imagen ya existe localmente: $savePath\n";
    }
    return $savePath;
}


// Iniciar transacción
$pdo->beginTransaction();
echo "Transacción iniciada.\n";

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
        echo "Producto insertado: " . ($product['nombre'] ?? '') . " (ID: $productId)\n";

        // Insertar identificadores
        if (!empty($product['clave'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['SKU'],
                ':value' => $product['clave'],
            ]);
            echo "Identificador SKU insertado: " . $product['clave'] . "\n";
        }
        if (!empty($product['ean'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['UPC'],
                ':value' => $product['ean'],
            ]);
            echo "Identificador EAN insertado: " . $product['ean'] . "\n";
        }
        if (!empty($product['upc'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['UPC'],
                ':value' => $product['upc'],
            ]);
            echo "Identificador UPC insertado: " . $product['upc'] . "\n";
        }
        if (!empty($product['numParte'])) {
            $insertIdentifierStmt->execute([
                ':product_id' => $productId,
                ':type_id' => $identifierTypes['Part Number'],
                ':value' => $product['numParte'],
            ]);
            echo "Identificador Número de Parte insertado: " . $product['numParte'] . "\n";
        }

        // Insertar precio
        if (isset($product['precio'])) {
            $insertPriceStmt->execute([
                ':product_id' => $productId,
                ':price' => $product['precio'],
            ]);
            echo "Precio insertado: " . $product['precio'] . "\n";
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
                echo "Imagen insertada y vinculada: $imageName (ID: $imageId)\n";
            } else {
                echo "No se pudo descargar o insertar la imagen para el producto: " . ($product['nombre'] ?? '') . "\n";
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
                    echo "Especificación insertada: " . $spec['tipo'] . " = " . $spec['valor'] . "\n";
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
                    echo "Promoción insertada: $promoText\n";
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
