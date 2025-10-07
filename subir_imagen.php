<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id_cliente'])) {
    // Redirect hacia login por si no hay cliente loggeado
    header('Location: login.php');
    exit;
}

require_once 'dbConn.php';

$message = '';
$tiposImagenes = [];

// Obtiene tipos de imagenes del dropdown
try {
    $stmt = $pdo->query("SELECT id_tipo_imagen, chr_tipo_imagen FROM tbl_tipos_imagenes WHERE bit_activo = 1");
    $tiposImagenes = $stmt->fetchAll();
} catch (Exception $e) {
    $message = "Error fetching image types: " . $e->getMessage();
}

// Recibimos el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && isset($_POST['tipo_imagen'])) {
        $tipoImagen = intval($_POST['tipo_imagen']);
        $altText = isset($_POST['alt_text']) ? trim($_POST['alt_text']) : '';
        $clientId = $_SESSION['usuario']['id_cliente'];

        $uploadDir = 'uploads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $originalName = basename($file['name']);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('img_', true) . '.' . $ext;
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Insertamos en tbl_imagenes
                try {
                    $insertStmt = $pdo->prepare("INSERT INTO tbl_imagenes (chr_nombre, id_tipo_imagen, chr_ruta, chr_alt_text, bit_activo) VALUES (?, ?, ?, ?, 1)");
                    $insertStmt->execute([$originalName, $tipoImagen, $targetPath, $altText]);
                    $message = "Image uploaded and saved successfully.";
                } catch (Exception $e) {
                    $message = "Error saving image info to database: " . $e->getMessage();
                    // Si falla la inserción, eliminamos el archivo subido
                    unlink($targetPath);
                }
            } else {
                $message = "Fallo al mover el archivo subido.";
            }
        } else {
            $message = "Error al subir el archivo: " . $file['error'];
        }
    } else {
        $message = "Por favor seleccione el tipo de imagen.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Subir Imagen</title>
<link rel="stylesheet" href="styles/menu.css" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container" style="margin-left:220px; padding-top:20px;">

<h2>Subir Imagen</h2>

<?php if ($message): ?>
  <div class="alert alert-info" role="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form id="uploadForm" method="POST" enctype="multipart/form-data" class="form">

  <div class="form-group">
    <label for="imageUpload" class="upload-area" id="uploadArea" style="border: 2px dashed #ccc; padding: 20px; text-align: center; cursor: pointer;">
      Arrastra y suelta la imagen aquí o haz clic para seleccionar
    </label>
    <input type="file" name="image" id="imageUpload" accept="image/*" required style="display:none;" />
  </div>

  <div class="form-group">
    <label for="tipo_imagen">Tipo de Imagen:</label>
    <select name="tipo_imagen" id="tipo_imagen" class="form-control" required>
      <option value="">-- Selecciona un tipo --</option>
      <?php foreach ($tiposImagenes as $tipo): ?>
        <option value="<?= htmlspecialchars($tipo['id_tipo_imagen']) ?>">
          <?= htmlspecialchars($tipo['chr_tipo_imagen']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="alt_text">Texto alternativo (opcional):</label>
    <input type="text" name="alt_text" id="alt_text" class="form-control" placeholder="Descripción de la imagen" />
  </div>

  <button type="submit" class="btn btn-primary">Subir Imagen</button>
</form>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  const uploadArea = document.getElementById('uploadArea');
  const fileInput = document.getElementById('imageUpload');

  uploadArea.addEventListener('click', () => {
    fileInput.click();
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      uploadArea.textContent = fileInput.files[0].name;
    } else {
      uploadArea.textContent = 'Arrastra y suelta la imagen aquí o haz clic para seleccionar';
    }
  });

  uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
  });

  uploadArea.addEventListener('dragleave', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
  });

  uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) {
      fileInput.files = e.dataTransfer.files;
      uploadArea.textContent = e.dataTransfer.files[0].name;
    }
  });
</script>

</body>
</html>
