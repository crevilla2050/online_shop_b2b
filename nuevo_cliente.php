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



// Conexión a la base de datos
require_once 'dbConn.php';
require_once 'dbConnCP.php'; // Add connection to codigos postales DB

// Verificar si el usuario está logueado
if (!isset($_SESSION['user']['client_id'])) {
    echo "<h3>No tiene permiso para acceder a esta página. Por favor, inicie sesión.</h3>";
    exit;
}

// Obtener el nivel del usuario
$stmt_user = $pdo->prepare("SELECT ru.int_nivel_usuario FROM tbl_usuarios u JOIN tbl_roles_usuario ru ON u.int_rol = ru.id_rol_usuario WHERE u.id_cliente = ? LIMIT 1");
$stmt_user->execute([$_SESSION['user']['client_id']]);
$user_role = $stmt_user->fetch(PDO::FETCH_ASSOC);

// DEBUG: Print info de session y usuario
//echo "<pre>DEBUG: \$_SESSION['user']['client_id'] = " . htmlspecialchars($_SESSION['user']['client_id'] ?? 'not set') . "</pre>";
//echo "<pre>DEBUG: \$user_role = " . print_r($user_role, true) . "</pre>";

if (!$user_role || $user_role['int_nivel_usuario'] < 2) {
    echo "<h3>No tiene permiso para acceder a esta página.</h3>";
    exit;
}

$tipos_asentamiento = [];

    // Solo devolver JSON si es una petición AJAX para cargar dropdowns
    if (isset($_GET['ajax']) && $_GET['ajax'] == 'dropdowns') {
        // Obtener todos los asentamientos para dropdown
        $asentamientos = $pdoCP->query("SELECT chr_nombre_asentamiento FROM tbl_asentamientos")->fetchAll(PDO::FETCH_ASSOC);
        $data['asentamientos'] = array_column($asentamientos, 'chr_nombre_asentamiento');

        echo json_encode($data);
        exit;
    }

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validar y limpiar datos de entrada
        $nombre = htmlspecialchars($_POST['nombre']);
        $apellido = isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : null;
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $telefono = isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : null;
        $tipo_cliente = isset($_POST['tipo_cliente']) ? $_POST['tipo_cliente'] : 'persona';
        $es_empresa = ($tipo_cliente === 'empresa') ? 1 : 0;
        $nombre_empresa = $es_empresa ? htmlspecialchars($_POST['nombre_empresa']) : null;
        $tax_id = $es_empresa ? htmlspecialchars($_POST['tax_id']) : null;

        // Insertar en tbl_clientes
$stmt = $pdo->prepare("INSERT INTO tbl_clientes (chr_nombre, chr_apellido, chr_email, chr_telefono, bit_es_empresa, chr_nombre_empresa, chr_RFC) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $apellido, $email, $telefono, $es_empresa, $nombre_empresa, $tax_id]);
        $cliente_id = $pdo->lastInsertId();

        // Obtener campos de dirección
        $calle = isset($_POST['calle']) ? htmlspecialchars($_POST['calle']) : '';
        $numero = isset($_POST['numero']) ? htmlspecialchars($_POST['numero']) : '';
        $interior = isset($_POST['interior']) ? htmlspecialchars($_POST['interior']) : '';
        $codigo_postal = htmlspecialchars($_POST['codigo_postal']);
        $ciudad = htmlspecialchars($_POST['ciudad']);
        $tipo_direccion = 'facturacion'; // hardcoded or could be from form if needed
        $direccion_default = isset($_POST['direccion_default']) ? 1 : 0;

        // Obtener id_ciudad
        $stmt_ciudad = $pdo->prepare("SELECT id_ciudad FROM tbl_ciudades WHERE chr_nombre = ? LIMIT 1");
        $stmt_ciudad->execute([$ciudad]);
        $row_ciudad = $stmt_ciudad->fetch(PDO::FETCH_ASSOC);
        $id_ciudad = $row_ciudad ? $row_ciudad['id_ciudad'] : null;

        // Validar id_ciudad
        if ($id_ciudad === null) {
            throw new Exception("Ciudad no encontrada en la base de datos.");
        }

        // Obtener id_tipo_direccion (assuming 'facturacion' corresponds to id 1 or get from tbl_tipos_direcciones)
        $stmt_tipo_dir = $pdo->prepare("SELECT id_tipos_direcciones FROM tbl_tipos_direcciones WHERE chr_tipo_direccion = ? LIMIT 1");
        $stmt_tipo_dir->execute([$tipo_direccion]);
        $row_tipo_dir = $stmt_tipo_dir->fetch(PDO::FETCH_ASSOC);
        $id_tipo_direccion = $row_tipo_dir ? $row_tipo_dir['id_tipos_direcciones'] : 1;

        // Validar id_tipo_asentamiento
        $id_tipo_asentamiento = 2435439259; // default para 'Colonia'
        if (!is_int($id_tipo_asentamiento) || $id_tipo_asentamiento <= 0) {
            $id_tipo_asentamiento = null;
        }

        // Concatenar partes de dirección
        $direccion_completa = trim($calle . ' ' . $numero . ' ' . $interior);

        // Insertar dirección en tbl_direcciones
        $stmt_dir = $pdo->prepare("INSERT INTO tbl_direcciones (id_cliente, chr_direccion, id_ciudad, chr_codigo_postal, chr_tipo_direccion, id_tipo_direccion, id_tipo_asentamiento, bit_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_dir->execute([$cliente_id, $direccion_completa, $id_ciudad, $codigo_postal, $tipo_direccion, $id_tipo_direccion, $id_tipo_asentamiento, $direccion_default]);
        $direccion_id = $pdo->lastInsertId();

        // Actualizar cliente con ID de dirección
        $stmt_update = $pdo->prepare("UPDATE tbl_clientes SET id_direccion_facturacion = ? WHERE id_cliente = ?");
        $stmt_update->execute([$direccion_id, $cliente_id]);

        $mensaje = "Cliente registrado exitosamente. ID: " . $cliente_id;
    }
} catch (Exception $e) {
    error_log("Error al registrar cliente: " . $e->getMessage());
    $errorMessage = $e->getMessage();

    // Detectar error de clave duplicada para el email
    if (strpos($errorMessage, 'Duplicate entry') !== false && strpos($errorMessage, 'chr_email_UNIQUE') !== false) {
        $mensaje = "Error: El correo electrónico ya está registrado. Por favor, use otro correo.";
    } else {
        $mensaje = "Error al registrar cliente: " . htmlspecialchars($errorMessage);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Nuevo Cliente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-5" style="margin-left: 220px;">
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-<?= strpos($mensaje, 'Error') !== false ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Crear Nuevo Cliente</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="nuevo_cliente.php">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Cliente</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo_cliente" id="tipo_persona" value="persona" checked>
                                <label class="form-check-label" for="tipo_persona">Persona</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo_cliente" id="tipo_empresa" value="empresa">
                                <label class="form-check-label" for="tipo_empresa">Empresa</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre*</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo*</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono">
                        </div>
                    </div>

                    <div id="empresa_fields" class="row mb-3" style="display: none;">
                        <div class="col-md-6">
                            <label for="nombre_empresa" class="form-label">Nombre de la Empresa</label>
                            <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa">
                        </div>
                        <div class="col-md-6">
                            <label for="tax_id" class="form-label">RFC</label>
                            <input type="text" class="form-control" id="tax_id" name="tax_id">
                        </div>
                    </div>

                    <h4 class="mt-4 mb-3">Dirección de Facturación</h4>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="calle" class="form-label">Calle</label>
                            <input type="text" class="form-control" id="calle" name="calle">
                        </div>
                        <div class="col-md-4">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero">
                        </div>
                        <div class="col-md-4">
                            <label for="interior" class="form-label">Interior</label>
                            <input type="text" class="form-control" id="interior" name="interior">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="codigo_postal" class="form-label">Código Postal*</label>
                            <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required maxlength="5" onblur="buscarCodigoPostal(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label for="ciudad" class="form-label">Ciudad*</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="municipio" class="form-label">Municipio</label>
                            <input type="text" class="form-control" id="municipio" name="municipio" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="asentamiento" class="form-label">Colonia</label>
                            <select class="form-select" id="asentamiento" name="asentamiento">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <!-- Removed Tipo de Asentamiento dropdown -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="estado" readonly>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="direccion_default" name="direccion_default" checked>
                        <label class="form-check-label" for="direccion_default">Dirección principal</label>
                    </div>

                    <button type="submit" class="btn btn-success">Registrar Cliente</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar campos de empresa y campos de nombre/apellido
        function toggleEmpresaFields() {
            const tipoEmpresa = document.getElementById('tipo_empresa');
            const empresaFields = document.getElementById('empresa_fields');
            const nombreApellidoRow = document.querySelector('.row.mb-3 > .col-md-6').parentElement; 
            const nombreInput = document.getElementById('nombre');

            if (tipoEmpresa.checked) {
                empresaFields.style.display = 'block';
                nombreApellidoRow.style.display = 'none';
                nombreInput.removeAttribute('required');
            } else {
                empresaFields.style.display = 'none';
                nombreApellidoRow.style.display = 'flex';
                nombreInput.setAttribute('required', 'required');
            }
        }

document.getElementById('tipo_persona').addEventListener('change', toggleEmpresaFields);
document.getElementById('tipo_empresa').addEventListener('change', toggleEmpresaFields);

        // Inicializar estado al cargar la página
        toggleEmpresaFields();

        function buscarCodigoPostal(codigo) {
            if (codigo.length != 5) return;

            fetch('/online_shop_b2b/ajax_codigo_postal.php?codigo=' + codigo)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    document.getElementById('ciudad').value = data.ciudad || '';
                    document.getElementById('municipio').value = data.municipio || '';

                    // Llenar dropdowns
                    const asentamientoSelect = document.getElementById('asentamiento');
                    asentamientoSelect.innerHTML = '<option value="">Seleccionar...</option>';
                    if (data.colonias) {
                        data.colonias.forEach(asentamiento => {
                            const option = document.createElement('option');
                            option.value = asentamiento.name;
                            option.textContent = asentamiento.name;
                            asentamientoSelect.appendChild(option);
                        });
                    }


                    const estadoInput = document.getElementById('estado');
                    if (data.estado) {
                        estadoInput.value = data.estado;
                    } else {
                        estadoInput.value = '';
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

<?php
$pdo = null; // Close the PDO connection
?>
