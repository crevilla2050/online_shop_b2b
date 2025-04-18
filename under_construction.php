<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En proceso de actualización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            margin: 0;
        }
        .menu {
            width: 200px; /* Ancho fijo para el menú */
            background-color: #f8f9fa; /* Color de fondo para el menú */
            padding: 15px; /* Relleno para el menú */
            position: fixed; /* Posición fija para el menú */
            height: 100%; /* Altura completa */
            overflow-y: auto; /* Desplazamiento si es necesario */
        }
        .mensaje {
            margin-left: 220px; /* Espacio para el menú */
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Altura completa de la ventana para centrar verticalmente */
        }
        h1 {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="menu">
        <?php include 'menu.php'; ?>
    </div>
    <div class="mensaje">
        <h1>En construcción</h1>
        <p>Esta página está en proceso de actualización.</p>
    </div>
</body>
</html>
