<?php
require_once("dbConn.php"); // Include the database connection

// Fetch users from the tbl_usuarios table
$stmt = $pdo->query("SELECT * FROM tbl_usuarios");
$usuarios = $stmt->fetchAll();

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
    <?php include("menu.php"); // Include the menu ?>
    <div class="container mt-5">
        <h2>Listado de Usuarios</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Cliente ID</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                        <td><?= htmlspecialchars($usuario['chr_login']) ?></td>
                        <td><?= $usuario['bit_activo'] ? 'Activo' : 'Inactivo' ?></td>
                        <td><?= htmlspecialchars($usuario['id_cliente']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary assign-btn" 
                                    data-client-id="<?= $usuario['id_cliente'] ?>"
                                    data-client-name="<?= htmlspecialchars($usuario['chr_login']) ?>">
                                Asignar
                            </button>
                            <button class="btn btn-sm btn-info view-client-btn" 
                                    data-client-id="<?= $usuario['id_cliente'] ?>"
                                    data-client-name="<?= htmlspecialchars($usuario['chr_login']) ?>"
                                    style="margin-left: 5px;">
                                Ver
                            </button>
                            <button class="btn btn-sm btn-success upload-btn"
                                    data-client-id="<?= $usuario['id_cliente'] ?>"
                                    data-client-name="<?= htmlspecialchars($usuario['chr_login']) ?>"
                                    style="margin-left: 5px;">
                                Subir
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Uploading Documents -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadDocumentModalLabel">Subir Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadDocumentForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="client_id" id="uploadClientId">
                        <div class="mb-3">
                            <label for="document" class="form-label">Seleccionar Documento</label>
                            <input type="file" class="form-control" id="document" name="document" required>
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

        document.getElementById('uploadDocumentForm').addEventListener('submit', function(event) {
            event.preventDefault();
            // Handle the form submission via AJAX or regular form submission
            // Add your AJAX code here if needed
            alert('Document uploaded for client ID: ' + document.getElementById('uploadClientId').value);
            // Close the modal after submission
            const uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal'));
            uploadModal.hide();
        });
    </script>
</body>
</html>