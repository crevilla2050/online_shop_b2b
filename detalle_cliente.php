<?php
// Start session and check login
$sessionPath = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $sessionPath);
session_start();

if (!isset($_SESSION['usuario'])) {
    // Check if old session key 'user' exists and migrate it
    if (isset($_SESSION['user'])) {
        $_SESSION['usuario'] = $_SESSION['user'];
        unset($_SESSION['user']);
    } else {
        header('Location: index.php');
        exit;
    }
}

require_once("dbConn.php"); // conexión a la base de datos
require_once("dbConnCP.php"); // conexión a la base de datos para codigos postales

// Obtiebne datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chr_direccion'])) {
    try {
        $client_id_post = intval($_POST['client_id']);
        $id_direccion_post = isset($_POST['id_direccion']) && !empty($_POST['id_direccion']) ? intval($_POST['id_direccion']) : null;
        $chr_direccion = htmlspecialchars($_POST['chr_direccion']);
        $chr_calle = htmlspecialchars($_POST['chr_calle']);
        $chr_numero = htmlspecialchars($_POST['chr_numero']);
        $chr_interior = htmlspecialchars($_POST['chr_interior']);
        $codigo_postal = htmlspecialchars($_POST['chr_codigo_postal']);
        $tipo_direccion = htmlspecialchars($_POST['chr_tipo_direccion']);
        $bit_default = isset($_POST['bit_default']) ? 1 : 0;
        $id_ciudad = isset($_POST['id_ciudad']) ? intval($_POST['id_ciudad']) : null;
        $chr_colonia = isset($_POST['chr_colonia']) ? htmlspecialchars($_POST['chr_colonia']) : '';
        $id_colonia = isset($_POST['id_colonia']) ? intval($_POST['id_colonia']) : null;

        // Concatenate address parts into one string
        $direccion_parts = array_filter([$chr_direccion, $chr_calle, $chr_numero, $chr_interior]);
        $direccion = implode(' ', $direccion_parts);

        // Map postal code string to id_codigo_postal
        $id_codigo_postal = null;
        $stmtCP = $pdoCP->prepare("SELECT id_ciudad FROM postal_codes_view WHERE codigo_postal = ?");
        $stmtCP->execute([$codigo_postal]);
        $postalCodeRow = $stmtCP->fetch(PDO::FETCH_ASSOC);
        if ($postalCodeRow) {
            // Unpack binary id_ciudad to int if needed
            if (isset($postalCodeRow['id_ciudad']) && is_string($postalCodeRow['id_ciudad'])) {
                $unpacked = unpack('N', $postalCodeRow['id_ciudad']);
                if ($unpacked !== false && isset($unpacked[1])) {
                    $id_codigo_postal = $unpacked[1];
                }
            } else {
                $id_codigo_postal = $postalCodeRow['id_ciudad'];
            }
        } else {
            throw new Exception("Código postal no encontrado en la base de datos.");
        }

        // Map tipo_direccion string to id_tipo_direccion
        $id_tipo_direccion = null;
        $stmtTipo = $pdo->prepare("SELECT id_tipos_direcciones FROM tbl_tipos_direcciones WHERE chr_tipo_direccion = ?");
        $stmtTipo->execute([$tipo_direccion]);
        $tipoRow = $stmtTipo->fetch(PDO::FETCH_ASSOC);
        if ($tipoRow) {
            $id_tipo_direccion = $tipoRow['id_tipos_direcciones'];
        } else {
            throw new Exception("Tipo de dirección no encontrado en la base de datos.");
        }

        // si la nueva direccion es la principal, actualiza la anterior a no principal
        if ($bit_default) {
            $stmt = $pdo->prepare("UPDATE tbl_direcciones SET bit_default = 0 WHERE id_cliente = ?");
            $stmt->execute([$client_id_post]);
        }

        if ($id_direccion_post) {
            // Actualiza dirección existente
            $stmt = $pdo->prepare("UPDATE tbl_direcciones SET chr_direccion = ?, id_codigo_postal = ?, id_tipo_direccion = ?, bit_default = ?, id_colonia = ? WHERE id_direccion = ?");
            $stmt->execute([$direccion, $id_codigo_postal, $id_tipo_direccion, $bit_default, $id_colonia, $id_direccion_post]);
        } else {
            // Inserta nueva dirección
            $stmt = $pdo->prepare("INSERT INTO tbl_direcciones (id_cliente, chr_direccion, id_codigo_postal, id_tipo_direccion, bit_default, id_colonia) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$client_id_post, $direccion, $id_codigo_postal, $id_tipo_direccion, $bit_default, $id_colonia]);
        }

        // Redirect para evitar re-envio del formulario
        header("Location: detalle_cliente.php?id=" . $client_id_post);
        exit;

    } catch (Exception $e) {
        die("Error al guardar la dirección: " . $e->getMessage());
    } catch (PDOException $e) {
        die("Error al guardar la dirección: " . $e->getMessage());
    }
}

// Handle address deletion
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id_direccion = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("UPDATE tbl_direcciones SET bit_activa = 0 WHERE id_direccion = ?");
        $stmt->execute([$id_direccion]);

        echo json_encode(['success' => true]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Obtener el ID De cliente del GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de cliente inválido.");
}

$client_id = intval($_GET['id']);

// Set client ID in session for menu.php usage
if (!isset($_SESSION['usuario']['id_cliente']) || $_SESSION['usuario']['id_cliente'] != $client_id) {
    $_SESSION['usuario']['id_cliente'] = $client_id;
}

try {
    // Obtener info del cliente
    $stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("Cliente no encontrado.");
    }

    // Obtener suma total de créditos activos del cliente desde tbl_creditos_empresa
    $stmtCredito = $pdo->prepare("SELECT COALESCE(SUM(fl_monto_credito), 0) AS total_credito FROM tbl_creditos_empresa WHERE id_cliente = ? AND bit_activo = 1");
    $stmtCredito->execute([$client_id]);
    $creditoRow = $stmtCredito->fetch(PDO::FETCH_ASSOC);
    $total_credito_empresa = $creditoRow ? $creditoRow['total_credito'] : 0;

    // Obtener todas las líneas de crédito activas del cliente desde tbl_creditos_empresa
    $stmtCreditosDetalles = $pdo->prepare("SELECT id_credito_empresa, fl_monto_credito, dt_fecha_creacion FROM tbl_creditos_empresa WHERE id_cliente = ? AND bit_activo = 1 ORDER BY dt_fecha_creacion DESC");
    $stmtCreditosDetalles->execute([$client_id]);
    $creditos_detalles = $stmtCreditosDetalles->fetchAll(PDO::FETCH_ASSOC);

    // Buscar direcciones para el cliente desde tbl_direcciones
    $stmt = $pdo->prepare("SELECT * FROM tbl_direcciones WHERE id_cliente = ? AND bit_activa = 1");
    $stmt->execute([$client_id]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $addresses = [];

    foreach ($direcciones as $direccion) {
        // Obtener info de codigo postal desde db_codigos_postales.postal_codes_view
        $stmtCP = $pdoCP->prepare("SELECT * FROM postal_codes_view");
        $stmtCP->execute();
        $postalCodes = $stmtCP->fetchAll(PDO::FETCH_ASSOC);

        $postalInfo = null;
        foreach ($postalCodes as $pc) {
            // Unpack binary id_ciudad and id_colonia to integers
            $idCiudadInt = null;
            $idColoniaInt = null;
            if (isset($pc['id_ciudad']) && is_string($pc['id_ciudad'])) {
                $unpackedCiudad = unpack('N', substr($pc['id_ciudad'], 0, 4));
                if ($unpackedCiudad !== false && isset($unpackedCiudad[1])) {
                    $idCiudadInt = $unpackedCiudad[1];
                }
            }
            if (isset($pc['id_colonia']) && is_string($pc['id_colonia'])) {
                $unpackedColonia = unpack('N', substr($pc['id_colonia'], 0, 4));
                if ($unpackedColonia !== false && isset($unpackedColonia[1])) {
                    $idColoniaInt = $unpackedColonia[1];
                }
            }

            if ($idCiudadInt === intval($direccion['id_codigo_postal']) && $idColoniaInt === intval($direccion['id_colonia'])) {
                $postalInfo = $pc;
                break;
            }
        }

        // Obtener tipo de direccion desde tbl_tipos_direcciones
        $stmtTipo = $pdo->prepare("SELECT chr_tipo_direccion FROM tbl_tipos_direcciones WHERE id_tipos_direcciones = ?");
        $stmtTipo->execute([$direccion['id_tipo_direccion']]);
        $tipoDireccion = $stmtTipo->fetch(PDO::FETCH_ASSOC);

        $addresses[] = [
            'id_direccion' => $direccion['id_direccion'],
            'chr_direccion' => $direccion['chr_direccion'],
            'chr_codigo_postal' => $postalInfo['codigo_postal'] ?? '',
            'ciudad' => $postalInfo['ciudad'] ?? '',
            'estado' => $postalInfo['estado'] ?? '',
            'municipio' => $postalInfo['municipio'] ?? '',
            'colonia' => $postalInfo['colonia'] ?? '',
            'chr_tipo_direccion' => $tipoDireccion['chr_tipo_direccion'] ?? '',
            'bit_default' => $direccion['bit_default']
        ];
    }

    // Obtener tipos de direcciones para el dropdown
    $stmt = $pdo->prepare("SELECT * FROM tbl_tipos_direcciones ORDER BY chr_tipo_direccion ASC");
    $stmt->execute();
    $tipos_direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener documentos del cliente
    $stmtDocs = $pdo->prepare("
        SELECT d.*, dt.chr_nombre AS tipo_nombre
        FROM tbl_documentos d
        JOIN tbl_clientes_documentos cd ON d.id_documento = cd.id_documento
        JOIN tbl_documentos_tipos dt ON d.id_documento_tipo = dt.id_documento_tipo
        WHERE cd.id_cliente = ? AND d.bit_activo = 1
        ORDER BY d.dt_fecha_subida DESC
    ");
    $stmtDocs->execute([$client_id]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detalle del Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .container-fluid {
            max-width: 900px; /* Set a maximum width for the content */
            margin: auto; /* Center the container */
        }
    </style>
</head>
<script>
    // Embed first address data for pre-populating modal on page load
    const firstAddress = <?php echo json_encode($addresses[0] ?? null); ?>;

    // Function to clear address fields
    function clearAddressFields() {
        document.getElementById('chr_ciudad').value = '';
        document.getElementById('chr_estado').value = '';
        document.getElementById('chr_municipio').value = '';
        document.getElementById('id_ciudad').value = '';
        clearColonias();
    }

    // Function to populate colonias (neighborhoods)
    function populateColonias(colonias, selected = '') {
        const select = document.getElementById('chr_colonia_select');
        const input = document.getElementById('chr_colonia_input');
        const hiddenColoniaId = document.getElementById('id_colonia');
        select.innerHTML = '<option value="">Seleccione una colonia</option>';

        colonias.forEach(colonia => {
            const option = document.createElement('option');
            option.value = colonia.id;
            option.textContent = colonia.name;
            if (colonia.name === selected) {
                option.selected = true;
                hiddenColoniaId.value = colonia.id;
            }
            select.appendChild(option);
        });

        const otherOption = document.createElement('option');
        otherOption.value = '_otra';
        otherOption.textContent = 'Otra...';
        select.appendChild(otherOption);

        if (selected && !colonias.some(c => c.name === selected)) {
            select.value = '_otra';
            input.classList.remove('d-none');
            input.value = selected;
            hiddenColoniaId.value = '';
        } else {
            input.classList.add('d-none');
            input.value = '';
            if (select.value !== '_otra') {
                hiddenColoniaId.value = select.value;
            }
        }
    }

    // Function to clear colonias
    function clearColonias() {
        const select = document.getElementById('chr_colonia_select');
        const input = document.getElementById('chr_colonia_input');
        select.innerHTML = '<option value="">Seleccione una colonia</option>';
        const otherOption = document.createElement('option');
        otherOption.value = '_otra';
        otherOption.textContent = 'Otra...';
        select.appendChild(otherOption);
        input.classList.add('d-none');
        input.value = '';
    }

    // Function to search postal code
    function buscarCodigoPostal(codigo, selectedColonia = '') {
        if (!/^\d{5}$/.test(codigo)) {
            clearAddressFields();
            return;
        }

        fetch('ajax_codigo_postal.php?codigo=' + encodeURIComponent(codigo))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    clearAddressFields();
                    return;
                }

                document.getElementById('chr_ciudad').value = data.ciudad || '';
                document.getElementById('chr_estado').value = data.estado || '';
                document.getElementById('chr_municipio').value = data.municipio || '';
                document.getElementById('id_ciudad').value = data.id_ciudad || '';

                populateColonias(data.colonias || [], selectedColonia);
            })
            .catch(error => {
                console.error('Error:', error);
                clearAddressFields();
            });
    }

    // Add event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const chr_colonia_select = document.getElementById('chr_colonia_select');
        const chr_colonia_input = document.getElementById('chr_colonia_input');
        const addressForm = document.getElementById('addressForm');
        const chr_codigo_postal = document.getElementById('chr_codigo_postal');
        const showAddressFormBtn = document.getElementById('showAddressFormBtn');
        const deleteAddressBtn = document.getElementById('deleteAddressBtn');
        const addressFormModalElement = document.getElementById('addressFormModal');

        chr_colonia_select.addEventListener('change', function() {
            const hiddenColoniaId = document.getElementById('id_colonia');
            if (this.value === '_otra') {
                chr_colonia_input.classList.remove('d-none');
                chr_colonia_input.required = true;
                hiddenColoniaId.value = '';
            } else {
                chr_colonia_input.classList.add('d-none');
                chr_colonia_input.required = false;
                chr_colonia_input.value = '';
                hiddenColoniaId.value = this.value;
            }
        });

        addressForm.addEventListener('submit', function() {
            const selected = chr_colonia_select.value;
            const hiddenColoniaId = document.getElementById('id_colonia');
            if (selected !== '_otra') {
                chr_colonia_input.value = selected;
                hiddenColoniaId.value = selected;
            }
        });

        chr_codigo_postal.addEventListener('blur', function() {
            buscarCodigoPostal(this.value);
        });

        showAddressFormBtn.addEventListener('click', function() {
            const form = document.getElementById('addressForm');
            if (form) form.reset();
            document.getElementById('id_direccion').value = '';
            clearColonias();
            const addressFormModal = new bootstrap.Modal(addressFormModalElement);
            addressFormModal.show();
        });

        deleteAddressBtn.addEventListener('click', function() {
            const addressId = document.getElementById('id_direccion').value;
            if (confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
                fetch(`delete_address.php?id=${addressId}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Dirección eliminada con éxito.');
                            location.reload();
                        } else {
                            alert(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    });

    // Make populateAddressForm global for inline onclick usage
    function populateAddressForm(addressId) {
        const addressFormModalElement = document.getElementById('addressFormModal');
        fetch(`get_address.php?id=${addressId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('id_direccion').value = data.id_direccion || '';
                    document.getElementById('chr_direccion').value = data.chr_direccion || '';
                    document.getElementById('chr_codigo_postal').value = data.chr_codigo_postal || '';
                    document.getElementById('chr_ciudad').value = data.ciudad || '';
                    document.getElementById('chr_estado').value = data.estado || '';
                    document.getElementById('chr_municipio').value = data.municipio || '';
                    document.getElementById('chr_tipo_direccion').value = data.chr_tipo_direccion || '';
                    document.getElementById('bit_default').checked = data.bit_default == 1;

                    // Determine if chr_codigo_postal is a 5-digit string or numeric ID
                    let codigoPostalRaw = data.chr_codigo_postal || '';
                    let trimmedCodigoPostal = '';

                    if (/^\d{5}$/.test(codigoPostalRaw)) {
                        // Already a 5-digit postal code string
                        trimmedCodigoPostal = codigoPostalRaw.trim();
                        buscarCodigoPostal(trimmedCodigoPostal, data.colonia);
                        const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                        addressFormModal.show();
                    } else if (codigoPostalRaw) {
                        // Assume numeric ID, fetch 5-digit postal code string first
                        fetch(`get_postal_code_string.php?id=${encodeURIComponent(codigoPostalRaw)}`)
                            .then(response => response.json())
                            .then(postalCodeData => {
                                if (postalCodeData.error) {
                                    alert(postalCodeData.error);
                                    document.getElementById('chr_ciudad').value = '';
                                    document.getElementById('chr_estado').value = '';
                                    document.getElementById('chr_municipio').value = '';
                                    populateColonias([], '');
                                } else {
                                    trimmedCodigoPostal = postalCodeData.codigo_postal || '';
                                    buscarCodigoPostal(trimmedCodigoPostal, data.colonia);
                                }
                                const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                                addressFormModal.show();
                            })
                            .catch(error => {
                                console.error('Error fetching postal code string:', error);
                                const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                                addressFormModal.show();
                            });
                    } else {
                        // No postal code provided
                        document.getElementById('chr_ciudad').value = '';
                        document.getElementById('chr_estado').value = '';
                        document.getElementById('chr_municipio').value = '';
                        populateColonias([], '');
                        const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                        addressFormModal.show();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>

<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-5" style="margin-left: 220px;">
        <h2>Detalle del Cliente: <?= htmlspecialchars($client['chr_nombre']) ?></h2>
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                Información General
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($client['chr_nombre']) ?></p>
                <p><strong>Apellido:</strong> <?= htmlspecialchars($client['chr_apellido']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($client['chr_email']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($client['chr_telefono']) ?></p>
                <p><strong>Es Empresa:</strong> <?= $client['bit_es_empresa'] ? 'Sí' : 'No' ?></p>
<?php if ($client['bit_es_empresa']): ?>
                <p><strong>Nombre de la Empresa:</strong> <?= htmlspecialchars($client['chr_nombre_empresa']) ?></p>
                <p><strong>RFC:</strong> <?= htmlspecialchars($client['chr_RFC']) ?></p>
<?php endif; ?>
                <p><strong>Límite de Crédito:</strong> $<?= number_format($total_credito_empresa, 2) ?></p>
                <button type="button" class="btn btn-info mt-2 me-2" data-bs-toggle="modal" data-bs-target="#creditosModal">Detalles</button>
                <a href="asignar_credito.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-primary mt-2">Asignar Nuevo Crédito</a>
            </div>
        </div>

        <div class="card mt-4 d-flex flex-row" style="width: 100%;">
            <div class="card me-3 flex-grow-1" style="min-width: 0;">
                <div class="card-header bg-secondary text-white">
                    Direcciones
                </div>
                <div class="card-body">
                    <?php if (empty($addresses)): ?>
                        <p>No hay direcciones registradas para este cliente.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>Dirección</th>
                                    <th>Código Postal</th>
                                    <th>Ciudad</th>
                                    <th>Colonia</th>
                                    <th>Tipo</th>
                                    <th>Predeterminada</th>
                                    <th>Modificar</th> <!-- New column for modifying addresses -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($addresses as $address): ?>
                                <tr>
                                    <td><?= htmlspecialchars($address['chr_direccion']) ?></td>
                                    <td><?= htmlspecialchars($address['chr_codigo_postal']) ?></td>
                                    <td><?= htmlspecialchars($address['ciudad']) ?></td>
                                    <td><?= htmlspecialchars($address['colonia']) ?></td>
                                    <td><?= htmlspecialchars($address['chr_tipo_direccion']) ?></td>
                                    <td><?= $address['bit_default'] ? 'Sí' : 'No' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="populateAddressForm(<?= htmlspecialchars($address['id_direccion']) ?>)">Modificar</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card flex-grow-1 ms-3">
                <div class="card-header bg-secondary text-white">
                    Documentos Subidos
                </div>
                <div class="card-body">
                    <?php if (empty($documentos)): ?>
                        <p>No hay documentos subidos para este cliente.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Fecha Subida</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['tipo_nombre']) ?></td>
                                    <td><?= htmlspecialchars($doc['dt_fecha_subida']) ?></td>
                                    <td>
                                        <a href="ver_documento.php?id=<?= intval($doc['id_documento']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <a href="listado_usuarios.php" class="btn btn-secondary mt-4">Volver a Usuarios</a>

        <button id="showAddressFormBtn" class="btn btn-success mt-3">Asignar Dirección</button>
    <!-- Address Form Modal -->
        <div class="modal fade" id="addressFormModal" tabindex="-1" aria-labelledby="addressFormModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <form id="addressForm"  method="post">
                    <div class="modal-header">
                    <h5 class="modal-title" id="addressFormModalLabel">Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <input type="hidden" id="id_direccion" name="id_direccion">
                    <input type="hidden" id="id_ciudad" name="id_ciudad">
                    <input type="hidden" id="id_colonia" name="id_colonia" value="">
                    <input type="hidden" name="client_id" value="<?= htmlspecialchars($client_id) ?>">

                    <div class="mb-3">
                        <label for="chr_direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="chr_direccion" name="chr_direccion" required>
                    </div>

                    <div class="mb-3">
                        <label for="chr_codigo_postal" class="form-label">Código Postal</label>
                        <input type="text" class="form-control" id="chr_codigo_postal" name="chr_codigo_postal" pattern="\d{5}" maxlength="5" required>
                    </div>

                    <div class="mb-3">
                        <label for="chr_ciudad" class="form-label">Ciudad</label>
                        <input type="text" class="form-control" id="chr_ciudad" name="chr_ciudad" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="chr_estado" class="form-label">Estado</label>
                        <input type="text" class="form-control" id="chr_estado" name="chr_estado" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="chr_municipio" class="form-label">Municipio</label>
                        <input type="text" class="form-control" id="chr_municipio" name="chr_municipio" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="chr_colonia" class="form-label">Colonia</label>
                        <select class="form-select" id="chr_colonia_select" name="chr_colonia_select" required>
                        <option value="">Seleccione una colonia</option>
                        <option value="_otra">Otra...</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="chr_colonia_input" name="chr_colonia" placeholder="Escriba la colonia">
                    </div>

                    <div class="mb-3">
                        <label for="chr_tipo_direccion" class="form-label">Tipo de Dirección</label>
                        <select class="form-select" id="chr_tipo_direccion" name="chr_tipo_direccion" required>
                            <option value="">Seleccione un tipo de dirección</option>
                            <?php foreach ($tipos_direcciones as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['chr_tipo_direccion']) ?>"><?= htmlspecialchars($tipo['chr_tipo_direccion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bit_default" name="bit_default">
                        <label class="form-check-label" for="bit_default">Dirección Predeterminada</label>
                    </div>
                    </div>

                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-danger" id="deleteAddressBtn">Eliminar</button>
                    </div>
                </form>
                </div>
            </div>
        </div>

        <script>
            function closeModal() {
                const addressFormModal = new bootstrap.Modal(document.getElementById('addressFormModal'));
                addressFormModal.hide();
            }

            // Function to clear address fields
            function clearAddressFields() {
                document.getElementById('chr_ciudad').value = '';
                document.getElementById('chr_estado').value = '';
                document.getElementById('chr_municipio').value = '';
                document.getElementById('id_ciudad').value = '';
                clearColonias();
            }

            // Function to populate colonias (neighborhoods)
            function populateColonias(colonias, selected = '') {
                const select = document.getElementById('chr_colonia_select');
                const input = document.getElementById('chr_colonia_input');
                const hiddenColoniaId = document.getElementById('id_colonia');
                select.innerHTML = '<option value="">Seleccione una colonia</option>';

                colonias.forEach(colonia => {
                    const option = document.createElement('option');
                    option.value = colonia.id;
                    option.textContent = colonia.name;
                    if (colonia.name === selected) {
                        option.selected = true;
                        hiddenColoniaId.value = colonia.id;
                    }
                    select.appendChild(option);
                });

                const otherOption = document.createElement('option');
                otherOption.value = '_otra';
                otherOption.textContent = 'Otra...';
                select.appendChild(otherOption);

                if (selected && !colonias.some(c => c.name === selected)) {
                    select.value = '_otra';
                    input.classList.remove('d-none');
                    input.value = selected;
                    hiddenColoniaId.value = '';
                } else {
                    input.classList.add('d-none');
                    input.value = '';
                    if (select.value !== '_otra') {
                        hiddenColoniaId.value = select.value;
                    }
                }
            }

            // Function to clear colonias
            function clearColonias() {
                const select = document.getElementById('chr_colonia_select');
                const input = document.getElementById('chr_colonia_input');
                select.innerHTML = '<option value="">Seleccione una colonia</option>';
                const otherOption = document.createElement('option');
                otherOption.value = '_otra';
                otherOption.textContent = 'Otra...';
                select.appendChild(otherOption);
                input.classList.add('d-none');
                input.value = '';
            }

            // Function to search postal code
            function buscarCodigoPostal(codigo, selectedColonia = '') {
                if (!/^\d{5}$/.test(codigo)) {
                    clearAddressFields();
                    return;
                }

                fetch('ajax_codigo_postal.php?codigo=' + codigo)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            clearAddressFields();
                            return;
                        }

                        document.getElementById('chr_ciudad').value = data.ciudad || '';
                        document.getElementById('chr_estado').value = data.estado || '';
                        document.getElementById('chr_municipio').value = data.municipio || '';
                        document.getElementById('id_ciudad').value = data.id_ciudad || '';

                        populateColonias(data.colonias || [], selectedColonia);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        clearAddressFields();
                    });
            }

            // Add event listeners
            document.addEventListener('DOMContentLoaded', function() {
                const chr_colonia_select = document.getElementById('chr_colonia_select');
                const chr_colonia_input = document.getElementById('chr_colonia_input');
                const addressForm = document.getElementById('addressForm');
                const chr_codigo_postal = document.getElementById('chr_codigo_postal');
                const showAddressFormBtn = document.getElementById('showAddressFormBtn');
                const deleteAddressBtn = document.getElementById('deleteAddressBtn');
                const addressFormModalElement = document.getElementById('addressFormModal');

                chr_colonia_select.addEventListener('change', function() {
                    if (this.value === '_otra') {
                        chr_colonia_input.classList.remove('d-none');
                        chr_colonia_input.required = true;
                    } else {
                        chr_colonia_input.classList.add('d-none');
                        chr_colonia_input.required = false;
                        chr_colonia_input.value = '';
                    }
                });

                addressForm.addEventListener('submit', function() {
                    const selected = chr_colonia_select.value;
                    if (selected !== '_otra') {
                        chr_colonia_input.value = selected;
                    }
                });

                chr_codigo_postal.addEventListener('blur', function() {
                    buscarCodigoPostal(this.value);
                });

                showAddressFormBtn.addEventListener('click', function() {
                    const form = document.getElementById('addressForm');
                    if (form) form.reset();
                    document.getElementById('id_direccion').value = '';
                    clearColonias();
                    const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                    addressFormModal.show();
                });

                deleteAddressBtn.addEventListener('click', function() {
                    const addressId = document.getElementById('id_direccion').value;
                    if (confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
                        fetch(`delete_address.php?id=${addressId}`, {
                                method: 'DELETE'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Dirección eliminada con éxito.');
                                    location.reload();
                                } else {
                                    alert(data.error);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }
                });
            });

            // Make populateAddressForm global for inline onclick usage
            function populateAddressForm(addressId) {
                const addressFormModalElement = document.getElementById('addressFormModal');
                fetch(`get_address.php?id=${addressId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            document.getElementById('id_direccion').value = data.id_direccion || '';
                            document.getElementById('chr_direccion').value = data.chr_direccion || '';
                            document.getElementById('chr_codigo_postal').value = data.chr_codigo_postal || '';
                            document.getElementById('chr_tipo_direccion').value = data.chr_tipo_direccion || '';
                            document.getElementById('bit_default').checked = data.bit_default == 1;

                    // Fetch detailed postal code info from ajax_codigo_postal.php
                    let codigoPostalRaw = data.chr_codigo_postal || '';
                    let trimmedCodigoPostal = '';

                    if (/^\d{5}$/.test(codigoPostalRaw)) {
                        // Already a 5-digit postal code string
                        trimmedCodigoPostal = codigoPostalRaw.trim();
                        buscarCodigoPostal(trimmedCodigoPostal, data.colonia);
                        const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                        addressFormModal.show();
                    } else if (codigoPostalRaw) {
                        // Assume numeric ID, fetch 5-digit postal code string first
                        fetch(`get_postal_code_string.php?id=${encodeURIComponent(codigoPostalRaw)}`)
                            .then(response => response.json())
                            .then(postalCodeData => {
                                if (postalCodeData.error) {
                                    alert(postalCodeData.error);
                                    document.getElementById('chr_ciudad').value = '';
                                    document.getElementById('chr_estado').value = '';
                                    document.getElementById('chr_municipio').value = '';
                                    populateColonias([], '');
                                } else {
                                    trimmedCodigoPostal = postalCodeData.codigo_postal || '';
                                    buscarCodigoPostal(trimmedCodigoPostal, data.colonia);
                                }
                                const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                                addressFormModal.show();
                            })
                            .catch(error => {
                                console.error('Error fetching postal code string:', error);
                                const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                                addressFormModal.show();
                            });
                    } else {
                        // No postal code provided
                        document.getElementById('chr_ciudad').value = '';
                        document.getElementById('chr_estado').value = '';
                        document.getElementById('chr_municipio').value = '';
                        populateColonias([], '');
                        const addressFormModal = new bootstrap.Modal(addressFormModalElement);
                        addressFormModal.show();
                    }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnClose = document.querySelector('.btn-close');
        if (btnClose) {
            btnClose.addEventListener('click', function() {
                const addressFormModal = bootstrap.Modal.getInstance(document.getElementById('addressFormModal'));
                if (addressFormModal) {
                    addressFormModal.hide();
                }
            });
        }
    });
</script>
    <!-- Modal para mostrar detalles de líneas de crédito -->
    <div class="modal fade" id="creditosModal" tabindex="-1" aria-labelledby="creditosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="creditosModalLabel">Líneas de Crédito Activas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($creditos_detalles)): ?>
                        <p>No hay líneas de crédito activas para este cliente.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID Crédito</th>
                                    <th>Monto Crédito</th>
                                    <th>Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($creditos_detalles as $credito): ?>
                                <tr>
                                    <td><?= htmlspecialchars($credito['id_credito_empresa']) ?></td>
                                    <td>$<?= number_format($credito['fl_monto_credito'], 2) ?></td>
                                    <td><?= htmlspecialchars($credito['dt_fecha_creacion']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
