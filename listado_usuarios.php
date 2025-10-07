<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include 'init_session.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once("dbConn.php"); // Incluir la conexión a la base de datos

// Obtener todos los clientes con información de usuario y rol si existe
$stmt = $pdo->query("
    SELECT c.id_cliente, c.chr_nombre, c.chr_email, u.id_usuario, u.chr_login, u.bit_activo, r.chr_rol_usuario, r.int_nivel_usuario
    FROM tbl_clientes c
    LEFT JOIN tbl_usuarios u ON c.id_cliente = u.id_cliente
    LEFT JOIN tbl_roles_usuario r ON u.int_rol = r.id_rol_usuario
    ORDER BY c.id_cliente
");
$usuariosList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Construir un arreglo asociativo de usuarios por id de cliente
$usuariosByClient = [];
foreach ($usuariosList as $usuario) {
    if ($usuario['id_usuario'] !== null) {
        $usuariosByClient[$usuario['id_cliente']] = $usuario;
    }
}

// Manejar la asignación de usuario/sistema a un cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_login'])) {
    $idCliente = $_POST['id_cliente'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $contrasena_confirm = $_POST['contrasena_confirm'] ?? '';

    if (empty($usuario) || empty($contrasena) || empty($contrasena_confirm)) {
        $assignError = "Se requieren nombre de usuario y ambas contraseñas";
    } elseif ($contrasena !== $contrasena_confirm) {
        $assignError = "Las contraseñas no coinciden";
    } else {
        try {
            // Verificar si el usuario existe para el cliente
            $stmtCheck = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE id_cliente = ?");
            $stmtCheck->execute([$idCliente]);
            $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                // Actualizar contraseña para usuario existente
                $sal = $existingUser['chr_salt'];
                $contrasenaHasheada = hash('sha256', $contrasena . $sal);

                $stmtUpdate = $pdo->prepare("UPDATE tbl_usuarios SET chr_password = ? WHERE id_usuario = ?");
                $stmtUpdate->execute([$contrasenaHasheada, $existingUser['id_usuario']]);

                $assignSuccess = "¡Contraseña actualizada con éxito!";
            } else {
                // Insertar nuevo usuario
                $sal = bin2hex(random_bytes(32));
                $contrasenaHasheada = hash('sha256', $contrasena . $sal);

                $stmtInsert = $pdo->prepare("
                    INSERT INTO tbl_usuarios 
                    (chr_login, chr_password, chr_salt, int_status, bit_activo, id_cliente)
                    VALUES (?, ?, ?, 1, 1, ?)
                ");
                $stmtInsert->execute([$usuario, $contrasenaHasheada, $sal, $idCliente]);

                $assignSuccess = "¡Credenciales asignadas con éxito!";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $assignError = "El nombre de usuario ya existe";
            } else {
                $assignError = "Error al asignar credenciales: " . $e->getMessage();
            }
        }
    }
}

// Obtener tipos de documentos para el dropdown
$docTypesStmt = $pdo->query("SELECT id_documento_tipo, chr_nombre FROM tbl_documentos_tipos WHERE bit_activo = 1 ORDER BY chr_nombre");
$documentTypes = $docTypesStmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar subida de archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document']) && isset($_POST['client_id']) && isset($_POST['document_type'])) {
    $client_id = intval($_POST['client_id']);
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';
    $user_id = $_SESSION['user']['id'] ?? null;
    $documentTypeId = intval($_POST['document_type']);

    if ($user_id === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
        exit;
    }

    if ($_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $uploadDir = __DIR__ . "/uploads/$year/$month/$day";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $originalName = $_FILES['document']['name'];
        $fileTmpPath = $_FILES['document']['tmp_name'];
        $fileType = mime_content_type($fileTmpPath);
        $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);

        // Generar nombre hexadecimal aleatorio sin extensión
        $randomName = bin2hex(random_bytes(16));
        $newFileName = $randomName . '.' . $fileExt;
        $destination = $uploadDir . '/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destination)) {
            // Guardar info del archivo en la base de datos
            $stmt = $pdo->prepare("INSERT INTO tbl_documentos (id_documento_tipo, chr_nombre_archivo, chr_ruta_archivo, chr_tipo_archivo, id_usuario_subida, chr_notas, bit_activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $relativePath = "uploads/$year/$month/$day/$newFileName";
            $stmt->execute([$documentTypeId, $randomName, $relativePath, $fileExt, $user_id, $comments]);

            // Opcionalmente vincular documento al cliente en tbl_clientes_documentos
            $documentId = $pdo->lastInsertId();
            $stmtLink = $pdo->prepare("INSERT INTO tbl_clientes_documentos (id_cliente, id_documento) VALUES (?, ?)");
            $stmtLink->execute([$client_id, $documentId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Documento subido correctamente']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo subido.']);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error en la subida del archivo.']);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include("menu.php"); // Incluir el menú ?>
    <div class="container mt-5" style="margin-left: 200px;">
        <h2>Listado de Usuarios</h2>

        <?php if (isset($assignError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($assignError) ?></div>
        <?php elseif (isset($assignSuccess)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($assignSuccess) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Cliente ID</th>
                        <th>Tipo de Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuariosList as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                        <td><?= htmlspecialchars($usuario['chr_nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['chr_email']) ?></td>
                        <td><?= htmlspecialchars($usuario['chr_login']) ?></td>
                        <td><?= $usuario['bit_activo'] ? 'Activo' : 'Inactivo' ?></td>
                        <td><?= htmlspecialchars($usuario['id_cliente']) ?></td>
                        <td><?= htmlspecialchars($usuario['chr_rol_usuario'] ?? 'Desconocido') ?></td>
                        <td>
                            <?php
                                $clientId = $usuario['id_cliente'];
                                $buttonText = isset($usuariosByClient[$clientId]) ? 'Modificar login/password' : 'Asignar usuario de sistema';
                            ?>
                            <button class="btn btn-sm btn-primary assign-btn" 
                                    data-client-id="<?= $clientId ?>"
                                    data-client-name="<?= htmlspecialchars($usuario['chr_login']) ?>">
                                <?= $buttonText ?>
                            </button>
                            <a href="detalle_cliente.php?id=<?= htmlspecialchars($clientId) ?>" class="btn btn-sm btn-info" style="margin-left: 5px;">
                                Ver Detalles
                            </a>
                            <button class="btn btn-sm btn-success upload-btn"
                                    data-client-id="<?= $clientId ?>"
                                    data-client-name="<?= htmlspecialchars($usuario['chr_login']) ?>"
                                    style="margin-left: 5px;">
                                Subir Documento
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para asignar usuario de sistema -->
    <div class="modal fade" id="assignUserModal" tabindex="-1" aria-labelledby="assignUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="assignUserForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignUserModalLabel">Asignar Usuario de Sistema</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_cliente" id="assignClientId">

                        <div class="mb-3">
                            <label for="usuario" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena_confirm" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="contrasena_confirm" name="contrasena_confirm" required>
                        </div>

                        <input type="hidden" name="asignar_login" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para subir documentos -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadDocumentModalLabel">Subir Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadDocumentForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="client_id" id="uploadClientId">

                        <div class="mb-3">
                            <label for="document_type" class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="document_type" name="document_type" required>
                                <option value="" disabled selected>Seleccione un tipo de documento</option>
                                <?php foreach ($documentTypes as $docType): ?>
                                    <option value="<?= htmlspecialchars($docType['id_documento_tipo']) ?>"><?= htmlspecialchars($docType['chr_nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Arrastra y suelta el archivo aquí o haz clic para seleccionar</label>
                            <div id="dropZone" class="border border-primary rounded p-3 text-center" style="cursor: pointer;">
                                <p class="mb-0">Arrastra y suelta el archivo aquí</p>
                                <p>o</p>
                                <p>Haz clic para seleccionar un archivo</p>
                                <input type="file" class="form-control d-none" id="document" name="document" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="comments" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Agrega comentarios sobre el documento"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Subir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.upload-btn').forEach(button => {
            button.addEventListener('click', function() {
                const clientId = this.getAttribute('data-client-id');
                document.getElementById('uploadClientId').value = clientId;
                const uploadModal = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
                uploadModal.show();
            });
        });

        // Funcionalidad de arrastrar y soltar
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('document');

        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-primary', 'text-white');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-primary', 'text-white');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-primary', 'text-white');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                // Mostrar nombre del archivo seleccionado
                dropZone.querySelector('p.mb-0').textContent = e.dataTransfer.files[0].name;
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                dropZone.querySelector('p.mb-0').textContent = fileInput.files[0].name;
            } else {
                dropZone.querySelector('p.mb-0').textContent = 'Arrastra y suelta el archivo aquí';
            }
        });

        document.getElementById('uploadDocumentForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const form = this;
            const formData = new FormData(form);

            fetch(form.action || window.location.href, {
                method: 'POST',
                body: formData,
            })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la subida del documento.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message + ' para el cliente ID: ' + document.getElementById('uploadClientId').value);
                // Resetear formulario y texto de drop zone
                form.reset();
                dropZone.querySelector('p.mb-0').textContent = 'Arrastra y suelta el archivo aquí';
                // Cerrar modal después de enviar
                const uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal'));
                uploadModal.hide();
                // Opcionalmente recargar la página o actualizar UI
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert(error.message);
        });
        });

        // Manejo del modal para asignar usuario
        document.querySelectorAll('.assign-btn').forEach(button => {
            button.addEventListener('click', function() {
                const clientId = this.getAttribute('data-client-id');
                const clientName = this.getAttribute('data-client-name');
                document.getElementById('assignClientId').value = clientId;

                // Verificar si el usuario existe para este cliente y rellenar el nombre de usuario si es así
                const usuariosByClient = <?= json_encode($usuariosByClient) ?>;
                if (usuariosByClient[clientId]) {
                    document.getElementById('usuario').value = usuariosByClient[clientId]['chr_login'];
                } else {
                    document.getElementById('usuario').value = '';
                }
                // Limpiar campos de contraseña
                document.getElementById('contrasena').value = '';
                document.getElementById('contrasena_confirm').value = '';

                const assignModal = new bootstrap.Modal(document.getElementById('assignUserModal'));
                assignModal.show();
            });
        });
    </script>
</body>
</html>
