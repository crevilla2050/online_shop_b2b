<?php
// Conexión a la base de datos
require_once 'dbConn.php';

// Solo devolver JSON si es una petición AJAX para cargar dropdowns
if (isset($_GET['ajax']) && $_GET['ajax'] == 'dropdowns') {
    // Obtener todos los tipos de asentamiento para dropdown
    $tipos = $pdo->query("SELECT chr_nombre_tipo_asentamiento FROM tbl_tipo_asentamiento")->fetchAll(PDO::FETCH_ASSOC);
    $data['tipos_asentamiento'] = array_column($tipos, 'chr_nombre_tipo_asentamiento');

    // Obtener todos los asentamientos para dropdown
    $asentamientos = $pdo->query("SELECT chr_nombre_asentamiento FROM tbl_asentamientos")->fetchAll(PDO::FETCH_ASSOC);
    $data['asentamientos'] = array_column($asentamientos, 'chr_nombre_asentamiento');

    echo json_encode($data);
    exit;
}

// Procesar el formulario cuando se envía
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
    $limite_credito = isset($_POST['limite_credito']) ? floatval($_POST['limite_credito']) : 0.00;
    
    // Insertar en tbl_clientes
    $stmt = $pdo->prepare("INSERT INTO tbl_clientes (chr_nombre, chr_apellido, chr_email, chr_telefono, bit_es_empresa, chr_nombre_empresa, chr_tax_id, fl_limite_credito_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$nombre, $apellido, $email, $telefono, $es_empresa, $nombre_empresa, $tax_id, $limite_credito])) {
        $cliente_id = $pdo->lastInsertId();
        
        // Insertar dirección de facturación
        $direccion = htmlspecialchars($_POST['direccion']);
        $ciudad = htmlspecialchars($_POST['ciudad']);
        $codigo_postal = htmlspecialchars($_POST['codigo_postal']);
        $direccion_default = isset($_POST['direccion_default']) ? 1 : 0;
        
        $stmt_dir = $pdo->prepare("INSERT INTO tbl_direcciones (chr_direccion, id_ciudad, chr_codigo_postal, chr_tipo_direccion, bit_default) VALUES (?, (SELECT id_ciudad FROM tbl_ciudades WHERE chr_nombre = ? LIMIT 1), ?, 'facturacion', ?)");
        if ($stmt_dir->execute([$direccion, $ciudad, $codigo_postal, $direccion_default])) {
            $direccion_id = $pdo->lastInsertId();
            
            // Actualizar cliente con ID de dirección
            $stmt_update = $pdo->prepare("UPDATE tbl_clientes SET id_direccion_facturacion = ? WHERE id_cliente = ?");
            $stmt_update->execute([$direccion_id, $cliente_id]);
            
            $mensaje = "Cliente registrado exitosamente. ID: " . $cliente_id;
        } else {
            $mensaje = "Error al registrar dirección: " . $stmt_dir->errorInfo()[2];
        }
    } else {
        $mensaje = "Error al registrar cliente: " . $stmt->errorInfo()[2];
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

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo*</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>

                    <div id="empresa_fields" class="mb-3" style="display: none;">
                        <div class="mb-3">
                            <label for="nombre_empresa" class="form-label">Nombre de la Empresa</label>
                            <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa">
                        </div>
                        <div class="mb-3">
                            <label for="tax_id" class="form-label">RFC</label>
                            <input type="text" class="form-control" id="tax_id" name="tax_id">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="limite_credito" class="form-label">Límite de Crédito (MXN)</label>
                        <input type="number" class="form-control" id="limite_credito" name="limite_credito" step="0.01" min="0" value="0.00">
                    </div>

                    <h4 class="mt-4 mb-3">Dirección de Facturación</h4>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección*</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ciudad" class="form-label">Ciudad*</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                        </div>
                        <div class="col-md-6">
                            <label for="codigo_postal" class="form-label">Código Postal*</label>
                            <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required maxlength="5" onblur="buscarCodigoPostal(this.value)">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="municipio" class="form-label">Municipio</label>
                            <input type="text" class="form-control" id="municipio" name="municipio">
                        </div>
                        <div class="col-md-4">
                            <label for="asentamiento" class="form-label">Colonia</label>
                            <select class="form-select" id="asentamiento" name="asentamiento">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_asentamiento" class="form-label">Tipo de Asentamiento</label>
                            <select class="form-select" id="tipo_asentamiento" name="tipo_asentamiento">
                                <option value="">Seleccionar...</option>
                            </select>
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
        // Mostrar/ocultar campos de empresa
        function toggleEmpresaFields() {
            const tipoEmpresa = document.getElementById('tipo_empresa');
            document.getElementById('empresa_fields').style.display = tipoEmpresa.checked ? 'block' : 'none';
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
                    if (data.asentamientos) {
                        data.asentamientos.forEach(asentamiento => {
                            const option = document.createElement('option');
                            option.value = asentamiento;
                            option.textContent = asentamiento;
                            asentamientoSelect.appendChild(option);
                        });
                    }
                    
                    const tipoSelect = document.getElementById('tipo_asentamiento');
                    tipoSelect.innerHTML = '<option value="">Seleccionar...</option>';
                    if (data.tipos_asentamiento) {
                        data.tipos_asentamiento.forEach(tipo => {
                            const option = document.createElement('option');
                            option.value = tipo;
                            option.textContent = tipo;
                            tipoSelect.appendChild(option);
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
