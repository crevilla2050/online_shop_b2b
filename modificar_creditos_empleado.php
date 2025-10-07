<?php
include 'dbConn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id_empleado_cliente']) || !is_numeric($_POST['id_empleado_cliente'])) {
        die("ID de empleado inválido.");
    }

    $id_empleado_cliente = intval($_POST['id_empleado_cliente']);
    $monto_credito_updates = $_POST['monto_credito'] ?? [];

    if (empty($monto_credito_updates) || !is_array($monto_credito_updates)) {
        die("No se recibieron datos de crédito para actualizar.");
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Para cada crédito, validar y actualizar si es válido
        foreach ($monto_credito_updates as $id_credito_empleado => $nuevo_monto) {
            $id_credito_empleado = intval($id_credito_empleado);
            $nuevo_monto = floatval($nuevo_monto);

            // Obtener monto actual del crédito desde la base de datos
            $stmt = $pdo->prepare("SELECT fl_monto_credito FROM tbl_creditos_empleado WHERE id_credito_empleado = ? AND id_empleado_cliente = ? AND bit_activo = 1");
            $stmt->execute([$id_credito_empleado, $id_empleado_cliente]);
            $credito = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$credito) {
                throw new Exception("Crédito con ID $id_credito_empleado no encontrado para el empleado.");
            }

            $monto_actual = floatval($credito['fl_monto_credito']);

            // Validar que el nuevo monto no sea mayor que el monto actual
            if ($nuevo_monto > $monto_actual) {
                throw new Exception("El nuevo monto para el crédito ID $id_credito_empleado no puede ser mayor que el monto actual.");
            }

            // Actualizar monto del crédito si es diferente
            if ($nuevo_monto != $monto_actual) {
                $updateStmt = $pdo->prepare("UPDATE tbl_creditos_empleado SET fl_monto_credito = ? WHERE id_credito_empleado = ? AND id_empleado_cliente = ?");
                $updateStmt->execute([$nuevo_monto, $id_credito_empleado, $id_empleado_cliente]);
            }
        }

        // Confirmar transacción
        $pdo->commit();

        // Redirigir a linea_de_credito.php o enviar respuesta de éxito
        header("Location: linea_de_credito.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al actualizar créditos: " . $e->getMessage());
    }
} else {
    die("Método no permitido.");
}
?>
